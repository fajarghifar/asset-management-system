<?php

namespace App\Filament\Resources\Borrowings\Schemas;

use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Borrowing;
use App\Models\ItemStock;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Enums\BorrowingStatus;
use App\Enums\FixedItemStatus;
use App\Models\FixedItemInstance;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BorrowingForm
{
    public static function configure(Schema $schema): Schema
    {
        // [FIX] Logic ReadOnly yang Aman (Menggunakan $livewire untuk cek Parent Record)
        $isReadOnly = function ($livewire) {
            $record = $livewire->getRecord();
            // Jika record ada (Edit Mode) DAN status bukan Pending -> Read Only
            return $record instanceof Borrowing && $record->status !== BorrowingStatus::Pending;
        };

        return $schema
            ->components([
                Section::make('Informasi Peminjaman')
                    ->schema([
                        Select::make('user_id')
                            ->label('Peminjam')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled($isReadOnly) // Aman dipanggil di mana saja
                            ->columnSpanFull(),

                        DateTimePicker::make('borrow_date')
                                ->label('Tgl. Pinjam')
                                ->default(now())
                                ->required()
                                ->native(false)
                                ->disabled($isReadOnly),

                        DateTimePicker::make('expected_return_date')
                            ->label('Tgl. Rencana Kembali')
                            ->default(now()->addDays(1))
                            ->required()
                            ->native(false)
                            ->afterOrEqual('borrow_date')
                            ->disabled($isReadOnly),

                        Textarea::make('purpose')
                            ->label('Tujuan Peminjaman')
                            ->rows(2)
                            ->required()
                            ->columnSpanFull()
                            ->disabled($isReadOnly),

                        Textarea::make('notes')
                            ->label('Catatan (Opsional)')
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled($isReadOnly),
                    ])
                    ->columns(2),

                Section::make('Barang yang Dipinjam')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Grid::make(4)->schema([
                                    // 1. PILIH BARANG
                                    Select::make('item_id')
                                        ->label('Nama Barang')
                                        ->columnSpan(4)
                                        ->relationship(
                                            name: 'item',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn(Builder $query) => $query->whereIn('type', [ItemType::Fixed, ItemType::Consumable])
                                        )
                                        ->getOptionLabelFromRecordUsing(fn(Item $item) => "{$item->name} ({$item->code})")
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn(Set $set) => $set('fixed_instance_id', null) & $set('location_id', null))
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                    // 2. PILIH UNIT (Khusus Fixed Item)
                                    Select::make('fixed_instance_id')
                                        ->label('Pilih Unit')
                                        ->columnSpan(4)
                                        ->options(function (Get $get, ?string $state) {
                                            $itemId = $get('item_id');
                                            if (!$itemId)
                                                return [];

                                            return FixedItemInstance::query()
                                                ->where('item_id', $itemId)
                                                ->where(function ($q) use ($state) {
                                                    $q->where('status', FixedItemStatus::Available)
                                                        ->orWhere('id', $state);
                                                })
                                                ->pluck('code', 'id');
                                        })
                                        ->searchable()
                                        // [FIX] Cek tipe langsung via Item::find (Aman dari error undefined method)
                                        ->visible(fn(Get $get) => $get('item_id') && Item::find($get('item_id'))?->type === ItemType::Fixed)
                                        ->required(fn(Get $get) => $get('item_id') && Item::find($get('item_id'))?->type === ItemType::Fixed)
                                        ->disabled($isReadOnly),

                                    // 3. PILIH LOKASI (Khusus Consumable Item)
                                    Select::make('location_id')
                                        ->label('Lokasi Stok')
                                        ->columnSpan(4)
                                        ->options(function (Get $get) {
                                            $itemId = $get('item_id');
                                            if (!$itemId)
                                                return [];

                                            return ItemStock::where('item_id', $itemId)
                                                ->where('quantity', '>', 0)
                                                ->with('location')
                                                ->get()
                                                ->mapWithKeys(fn($stock) => [
                                                    $stock->location_id => "{$stock->location->name} (Sisa: {$stock->quantity})"
                                                ]);
                                        })
                                        ->searchable()
                                        // [FIX] Cek tipe langsung
                                        ->visible(fn(Get $get) => $get('item_id') && Item::find($get('item_id'))?->type === ItemType::Consumable)
                                        ->required(fn(Get $get) => $get('item_id') && Item::find($get('item_id'))?->type === ItemType::Consumable)
                                        ->disabled($isReadOnly),

                                    // 4. INPUT QUANTITY
                                    TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->columnSpan(1)
                                        ->required()
                                        // Disable jika ReadOnly ATAU Tipe Fixed
                                        ->disabled(
                                            fn(Get $get, $livewire) =>
                                            $isReadOnly($livewire) ||
                                            ($get('item_id') && Item::find($get('item_id'))?->type === ItemType::Fixed)
                                        )
                                        // Paksa nilai jadi 1 jika Fixed
                                        ->formatStateUsing(
                                            fn(Get $get, $state) =>
                                            ($get('item_id') && Item::find($get('item_id'))?->type === ItemType::Fixed) ? 1 : $state
                                        )
                                        ->dehydrated(),
                                ]),
                            ])
                            ->addActionLabel('Tambah Barang')
                            ->itemLabel(fn($state) => $state['item_id'] ? Item::find($state['item_id'])?->name : 'Barang Baru')
                            ->columns(1)

                            // [FIX] Logic Deletable/Addable menggunakan negasi dari variable $isReadOnly
                            ->disabled($isReadOnly)
                            ->deletable(fn($livewire) => !$isReadOnly($livewire))
                            ->addable(fn($livewire) => !$isReadOnly($livewire))
                            ->cloneable(false),
                    ]),
            ]);
    }
}
