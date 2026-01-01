<?php

namespace App\Filament\Resources\Loans\Schemas;

use App\Models\Asset;
use App\Enums\LoanStatus;
use App\Enums\AssetStatus;
use App\Enums\ProductType;
use Filament\Schemas\Schema;
use App\Models\ConsumableStock;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;

class LoanForm
{
    public static function configure(Schema $schema): Schema
    {
        // Check if form should be read-only (only Pending loans are editable)
        $isReadOnly = function ($livewire) {
            $record = $livewire->getRecord();
            return $record && $record->status !== LoanStatus::Pending;
        };

        return $schema
            ->components([
                Section::make('Informasi Peminjaman')
                    ->schema([
                        // Hidden Auto-filled fields
                        Hidden::make('user_id')
                            ->default(fn() => Auth::id())
                            ->required(),

                        TextInput::make('borrower_name')
                            ->label('Nama Peminjam')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama Karyawan / Divisi')
                            ->disabled(fn($livewire) => $isReadOnly($livewire))
                            ->columnSpanFull(),

                        DateTimePicker::make('loan_date')
                            ->label('Tanggal Pinjam')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),

                        DateTimePicker::make('due_date')
                            ->label('Tenggat Pengembalian')
                            ->default(now()->addDays(1))
                            ->required()
                            ->native(false)
                            ->afterOrEqual('loan_date')
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),

                        Textarea::make('purpose')
                            ->label('Keperluan')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),

                        FileUpload::make('proof_image')
                            ->label('Foto Bukti / Dokumen')
                            ->image()
                            ->disk('public')
                            ->directory('loan-proofs')
                            ->visibility('public')
                            ->maxSize(5120) // 5MB
                            ->disabled(fn($livewire) => $isReadOnly($livewire))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Daftar Barang')
                    ->description('Pilih barang yang akan dipinjam.')
                    ->schema([
                        Repeater::make('loanItems')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Grid::make(1)->schema([
                                    // Unified Selection Field (Virtual)
                                    Select::make('item_selection')
                                        ->label('Pilih Barang / Aset')
                                        ->searchable()
                                        ->getSearchResultsUsing(function (string $search) {
                                            $results = [];

                                            // Search Assets (In Stock)
                                            $assets = Asset::where('status', AssetStatus::InStock)
                                                ->where(fn($q) => $q->where('asset_tag', 'like', "%{$search}%")
                                                    ->orWhereHas('product', fn($sq) => $sq->where('name', 'like', "%{$search}%")))
                                                ->with(['product', 'location'])
                                                ->limit(20)
                                                ->get();

                                            foreach ($assets as $asset) {
                                                $label = "{$asset->product->name} (Aset: {$asset->asset_tag}) | {$asset->location?->name}";
                                                $results["asset_{$asset->id}"] = $label;
                                            }

                                            // Search Consumables (Has Stock)
                                            $consumables = ConsumableStock::where('quantity', '>', 0)
                                                ->whereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"))
                                                ->with(['product', 'location'])
                                                ->limit(20)
                                                ->get();

                                            foreach ($consumables as $stock) {
                                                $label = "{$stock->product->name} (Stok: {$stock->quantity}) | {$stock->location?->name}";
                                                $results["consumable_{$stock->id}"] = $label;
                                            }

                                            return $results;
                                        })
                                        ->getOptionLabelUsing(function ($value) {
                                            if (!$value) return null;
                                            [$type, $id] = explode('_', $value);

                                            if ($type === 'asset') {
                                                $asset = Asset::with(['product', 'location'])->find($id);
                                                return $asset ? "{$asset->product->name} (Aset: {$asset->asset_tag}) | {$asset->location?->name}" : null;
                                            } else {
                                                $stock = ConsumableStock::with(['product', 'location'])->find($id);
                                                return $stock ? "{$stock->product->name} (Stok: {$stock->quantity}) | {$stock->location?->name}" : null;
                                            }
                                        })
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($set, $state) {
                                            if (!$state) {
                                                $set('type', null);
                                                $set('asset_id', null);
                                                $set('consumable_stock_id', null);
                                                return;
                                            }

                                            [$typeStr, $id] = explode('_', $state);

                                            if ($typeStr === 'asset') {
                                                $set('type', ProductType::Asset->value);
                                                $set('asset_id', $id);
                                                $set('consumable_stock_id', null);
                                                $set('quantity_borrowed', 1);
                                            } else {
                                                $set('type', ProductType::Consumable->value);
                                                $set('asset_id', null);
                                                $set('consumable_stock_id', $id);
                                            }
                                        })
                                        ->afterStateHydrated(function ($component, $record) {
                                            if ($record) {
                                                if ($record->type === ProductType::Asset) {
                                                    $component->state("asset_{$record->asset_id}");
                                                } else {
                                                    $component->state("consumable_{$record->consumable_stock_id}");
                                                }
                                            }
                                        })
                                        ->dehydrated(false) // Virtual field
                                        ->disabled(fn($livewire) => $isReadOnly($livewire)),

                                    // Hidden Fields to Store Type & IDs
                                    Hidden::make('type')->required(),
                                    Hidden::make('asset_id'),
                                    Hidden::make('consumable_stock_id'),

                                    // Quantity
                                    TextInput::make('quantity_borrowed')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->maxValue(function ($get) {
                                            $type = $get('type');
                                            if ($type === ProductType::Consumable->value) {
                                                $stockId = $get('consumable_stock_id');
                                                if ($stockId) {
                                                     return ConsumableStock::find($stockId)?->quantity ?? 1;
                                                }
                                            }
                                            return 1; // Asset always 1
                                        })
                                        ->readOnly(fn($get) => $get('type') === ProductType::Asset->value)
                                        ->required()
                                        ->disabled(fn($livewire) => $isReadOnly($livewire))
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->defaultItems(1)
                            ->columns(1)
                            ->addActionLabel('Tambah Barang')
                            ->deletable(fn($livewire) => !$isReadOnly($livewire))
                            ->addable(fn($livewire) => !$isReadOnly($livewire))
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),
                    ]),
            ]);
    }
}
