<?php

namespace App\Filament\Resources\Borrowings\Schemas;

use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Location;
use App\Models\ItemStock;
use Filament\Schemas\Schema;
use App\Enums\BorrowingStatus;
use App\Enums\FixedItemStatus;
use App\Models\FixedItemInstance;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BorrowingForm
{
    /**
     * Configures the form schema for borrowing requests.
     * The form is dynamically interactive and enforces business rules based on item type (Fixed vs Consumable),
     * availability, and current borrowing status (read-only once approved/rejected).
     */
    public static function configure(Schema $schema): Schema
    {
        // Determine if the form should be read-only based on borrowing status
        $isReadOnly = function ($livewire) {
            $record = $livewire->getRecord();
            return $record && $record->status !== BorrowingStatus::Pending;
        };

        return $schema
            ->components([
                Section::make('Borrowing Information')
                    ->schema([
                        // Borrower name field - editable only when status is 'Pending'
                        TextInput::make('borrower_name')
                            ->label('Borrower Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Employee Name / Department')
                            ->disabled(fn($livewire) => $isReadOnly($livewire))
                            ->columnSpanFull(),

                        // Borrow date - defaults to now, editable only when pending
                        DateTimePicker::make('borrow_date')
                            ->label('Borrow Date')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),

                        // Expected return date - must be on or after borrow date
                        DateTimePicker::make('expected_return_date')
                            ->label('Expected Return Date')
                            ->default(now()->addDays(1))
                            ->required()
                            ->native(false)
                            ->afterOrEqual('borrow_date')
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),

                        // Purpose of borrowing - required
                        Textarea::make('purpose')
                            ->label('Purpose')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),

                        // Optional additional notes
                        Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),

                        // Supporting document upload (e.g., approval letter)
                        FileUpload::make('proof_image')
                            ->label('Proof Document / Photo')
                            ->image()
                            ->directory('borrowing-proofs')
                            ->maxSize(5120) // 5MB max
                            ->disabled(fn($livewire) => $isReadOnly($livewire))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Borrowed Items')
                    ->description('Select items to be borrowed. Availability is validated in real time.')
                    ->schema([
                        // Repeater for multiple borrowed items
                        Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->schema([
                                Grid::make(4)->schema([
                                    // Hidden fields used for internal state tracking (not saved to DB)
                                    Hidden::make('item_type_value')->dehydrated(false),
                                    Hidden::make('max_stock_available')->dehydrated(false),

                                    // 1. ITEM SELECTION DROPDOWN
                                    // Only Fixed and Consumable items can be borrowed
                                    Select::make('item_id')
                                        ->label('Item Name')
                                        ->columnSpan(2)
                                        ->options(Item::whereIn('type', [ItemType::Fixed, ItemType::Consumable])->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        // Hydrate item type and available stock when form loads (e.g., during edit)
                                        ->afterStateHydrated(function (Set $set, $state) {
                                            if ($state) {
                                                $item = Item::find($state);
                                                $set('item_type_value', $item?->type->value);

                                                // Compute real-time availability
                                                $stockCount = 0;
                                                if ($item?->type === ItemType::Consumable) {
                                                    $stockCount = ItemStock::where('item_id', $item->id)->sum('quantity');
                                                } elseif ($item?->type === ItemType::Fixed) {
                                                    $stockCount = FixedItemInstance::where('item_id', $item->id)
                                                        ->where('status', FixedItemStatus::Available)
                                                        ->count();
                                                }
                                                $set('max_stock_available', $stockCount);
                                            }
                                        })
                                        // Recompute availability when user selects a different item
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            // Reset dependent fields
                                            $set('fixed_instance_id', null);
                                            $set('location_id', null);

                                            if ($state) {
                                                $item = Item::find($state);
                                                $set('item_type_value', $item?->type->value);

                                                $stockCount = 0;
                                                if ($item?->type === ItemType::Consumable) {
                                                    $stockCount = ItemStock::where('item_id', $item->id)->sum('quantity');
                                                } elseif ($item?->type === ItemType::Fixed) {
                                                    $stockCount = FixedItemInstance::where('item_id', $item->id)
                                                        ->where('status', FixedItemStatus::Available)
                                                        ->count();
                                                    $set('quantity', 1); // Fixed items always have quantity = 1
                                                }
                                                $set('max_stock_available', $stockCount);
                                            } else {
                                                $set('item_type_value', null);
                                                $set('max_stock_available', 0);
                                            }
                                        })
                                        // Display real-time stock/availability hint
                                        ->hint(function (Get $get) {
                                            $itemId = $get('item_id');
                                            if (!$itemId)
                                                return null;

                                            $count = $get('max_stock_available');
                                            $type = $get('item_type_value');

                                            if ($count == 0) {
                                                // Avoid confusing warning if editing an existing valid entry
                                                if ($get('fixed_instance_id') || $get('location_id')) {
                                                    return null;
                                                }
                                                return str($type === 'fixed' ? 'No units available (0)' : 'Out of stock (0)')->upper();
                                            }
                                            return $type === 'fixed' ? "Units Available: {$count}" : "Total Stock: {$count}";
                                        })
                                        ->hintColor(fn(Get $get) => $get('max_stock_available') > 0 ? 'success' : 'danger')
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->disabled(fn($livewire) => $isReadOnly($livewire)),

                                    // 2. STOCK WARNING PLACEHOLDER
                                    // Shown only when stock is zero AND no dependent fields are filled (to avoid edit confusion)
                                    Placeholder::make('stock_warning')
                                        ->hiddenLabel()
                                        ->content('WARNING: This item has no available stock or units.')
                                        ->visible(
                                            fn(Get $get) =>
                                            $get('item_id') &&
                                            $get('max_stock_available') == 0 &&
                                            $get('fixed_instance_id') == null &&
                                            $get('location_id') == null
                                        )
                                        ->columnSpan(2)
                                        ->extraAttributes(['class' => 'text-danger-600 font-bold text-sm bg-danger-50 p-2 rounded']),

                                    // 3. LOCATION SELECTION (for both Fixed and Consumable)
                                    // Shows only locations with available stock/units
                                    Select::make('location_id')
                                        ->label('Pick From Location')
                                        ->columnSpan(2)
                                        ->options(function (Get $get) {
                                            $itemId = $get('item_id');
                                            $type = $get('item_type_value');

                                            if (!$itemId)
                                                return [];

                                            if ($type === ItemType::Fixed->value) {
                                                // For Fixed items: show locations with available instances
                                                return Location::whereHas('fixedItemInstances', function ($q) use ($itemId) {
                                                    $q->where('item_id', $itemId)
                                                        ->where('status', FixedItemStatus::Available);
                                                })
                                                    ->with('area')
                                                    ->get()
                                                    ->mapWithKeys(fn($l) => [$l->id => "{$l->name} ({$l->area->name})"]);
                                            }

                                            // For Consumable: show locations with positive stock
                                            return ItemStock::where('item_id', $itemId)
                                                ->where('quantity', '>', 0)
                                                ->with('location.area')
                                                ->limit(50)
                                                ->get()
                                                ->mapWithKeys(fn($s) => [$s->location_id => "{$s->location->name} ({$s->location->area->name}) - Remaining: {$s->quantity}"]);
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        // Clear selected unit when location changes (for Fixed items)
                                        ->afterStateUpdated(fn(Set $set) => $set('fixed_instance_id', null))
                                        // Show only if item is selected AND (stock > 0 OR editing existing valid entry)
                                        ->visible(
                                            fn(Get $get) =>
                                            in_array($get('item_type_value'), [ItemType::Consumable->value, ItemType::Fixed->value]) &&
                                            ($get('max_stock_available') > 0 || $get('location_id') !== null)
                                        )
                                        ->required(
                                            fn(Get $get) =>
                                            in_array($get('item_type_value'), [ItemType::Consumable->value, ItemType::Fixed->value]) &&
                                            ($get('max_stock_available') > 0 || $get('location_id') !== null)
                                        )
                                        ->disabled(fn($livewire) => $isReadOnly($livewire)),

                                    // 4. FIXED ITEM INSTANCE SELECTION (only for Fixed items)
                                    Select::make('fixed_instance_id')
                                        ->label('Select Asset Unit')
                                        ->columnSpan(2)
                                        ->options(function (Get $get, ?string $state) {
                                            $itemId = $get('item_id');
                                            $locationId = $get('location_id');

                                            if (!$itemId || !$locationId)
                                                return [];

                                            return FixedItemInstance::where('item_id', $itemId)
                                                ->where('location_id', $locationId)
                                                ->where(function ($q) use ($state) {
                                                    $q->where('status', FixedItemStatus::Available)
                                                        // Critical: include currently selected instance during edit to prevent data loss
                                                        ->when($state, fn($q2) => $q2->orWhere('id', $state));
                                                })
                                                ->with(['location.area'])
                                                ->limit(50)
                                                ->get()
                                                ->mapWithKeys(fn($q) => [
                                                    $q->id => "{$q->code} - SN: " . ($q->serial_number ?? 'N/A')
                                                ]);
                                        })
                                        ->searchable()
                                        ->preload()
                                        // Visible only for Fixed items with available stock or during edit
                                        ->visible(
                                            fn(Get $get) =>
                                            $get('item_type_value') === ItemType::Fixed->value &&
                                            ($get('max_stock_available') > 0 || $get('fixed_instance_id') !== null)
                                        )
                                        ->required(
                                            fn(Get $get) =>
                                            $get('item_type_value') === ItemType::Fixed->value &&
                                            ($get('max_stock_available') > 0 || $get('fixed_instance_id') !== null)
                                        )
                                        ->disabled(fn($livewire) => $isReadOnly($livewire)),

                                    // 5. QUANTITY INPUT (only for Consumable items)
                                    TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->maxValue(function (Get $get) {
                                            $itemId = $get('item_id');
                                            $locationId = $get('location_id');
                                            if (!$itemId || !$locationId)
                                                return 1;

                                            // Enforce max quantity based on selected location's stock
                                            $stock = ItemStock::where('item_id', $itemId)
                                                ->where('location_id', $locationId)
                                                ->value('quantity');

                                            return $stock ?? 1;
                                        })
                                        ->required()
                                        ->columnSpan(1)
                                        // Disabled for Fixed items or when form is read-only
                                        ->disabled(
                                            fn(Get $get, $livewire) =>
                                            $get('item_type_value') === ItemType::Fixed->value || $isReadOnly($livewire)
                                        )
                                        ->dehydrated()
                                        // Always visible once an item is selected
                                        ->visible(fn(Get $get) => $get('item_id') != null),
                                ]),
                            ])
                            ->defaultItems(1)
                            ->columns(1)
                            ->addActionLabel('Add Item')
                            ->disabled(fn($livewire) => $isReadOnly($livewire))
                            ->deletable(fn($livewire) => !$isReadOnly($livewire))
                            ->addable(fn($livewire) => !$isReadOnly($livewire))
                            ->reorderable(false),
                    ]),
            ]);
    }
}
