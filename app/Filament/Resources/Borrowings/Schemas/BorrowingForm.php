<?php

namespace App\Filament\Resources\Borrowings\Schemas;

use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use App\Models\InventoryItem;
use App\Enums\BorrowingStatus;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;

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
                Section::make('Informasi Peminjaman')
                    ->schema([
                        Hidden::make('code')
                            ->label('Kode')
                            ->required()
                            ->default('BR' . now()->format('Ymd') . strtoupper(Str::random(6))),
                        TextInput::make('borrower_name')
                            ->label('Nama Peminjam')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama Karyawan / Divisi')
                            ->disabled(fn($livewire) => $isReadOnly($livewire))
                            ->columnSpanFull(),
                        DateTimePicker::make('borrow_date')
                            ->label('Tanggal Pinjam')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),
                        DateTimePicker::make('expected_return_date')
                            ->label('Rencana Kembali')
                            ->default(now()->addDays(1))
                            ->required()
                            ->native(false)
                            ->afterOrEqual('borrow_date')
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),
                        Textarea::make('purpose')
                            ->label('Keperluan')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),
                        Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->rows(2)
                            ->columnSpanFull()
                            ->disabled(fn($livewire) => $isReadOnly($livewire)),
                        FileUpload::make('proof_image')
                            ->label('Foto Bukti / Dokumen')
                            ->image()
                            ->disk('public')
                            ->directory('borrowing-proofs')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->disabled(fn($livewire) => $isReadOnly($livewire))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Daftar Barang')
                    ->description('Pilih barang yang akan dipinjam.')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('inventory_item_id')
                                        ->label('Pilih Barang & Lokasi')
                                        ->options(function () {
                                            return InventoryItem::where('status', 'available')
                                                ->with(['item', 'location.area'])
                                                ->get()
                                                ->mapWithKeys(function ($inventory) {
                                                    $baseLabel = "{$inventory->item->name} | {$inventory->location->name} ({$inventory->location->area->name})";
                                                    return $inventory->isFixed()
                                                        ? [$inventory->id => "{$inventory->code} - {$baseLabel}"]
                                                        : [$inventory->id => "{$baseLabel} - Stok: {$inventory->quantity}"];
                                                });
                                        })
                                        ->searchable()
                                        ->required()
                                        ->live()
                                        ->columnSpan(2),

                                    TextInput::make('quantity')
                                        ->label('Jumlah')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->maxValue(function (Get $get) {
                                            $inventoryId = $get('inventory_item_id');
                                            if (!$inventoryId)
                                                return 1;
                                            $inventory = InventoryItem::find($inventoryId);
                                            return $inventory?->quantity ?? 1;
                                        })
                                        ->required()
                                        ->visible(function (Get $get) {
                                            $inventoryId = $get('inventory_item_id');
                                            if (!$inventoryId)
                                                return false;
                                            $inventory = InventoryItem::find($inventoryId);
                                            return $inventory && $inventory->isConsumable();
                                        })
                                        ->columnSpan(1),
                                ]),
                            ])
                            ->defaultItems(1)
                            ->columns(1)
                            ->addActionLabel('Tambah Barang')
                            ->disabled(fn($livewire) => $isReadOnly($livewire))
                            ->deletable(fn($livewire) => !$isReadOnly($livewire))
                            ->addable(fn($livewire) => !$isReadOnly($livewire)),
                    ]),
            ]);
    }
}
