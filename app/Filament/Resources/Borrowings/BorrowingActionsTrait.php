<?php

namespace App\Filament\Resources\Borrowings;

use App\Enums\ItemType;
use App\Models\Borrowing;
use Filament\Actions\Action;
use App\Models\BorrowingItem;
use App\Enums\BorrowingStatus;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use App\Services\BorrowingReturnService;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use App\Services\BorrowingApprovalService;
use Filament\Schemas\Components\Utilities\Get;

trait BorrowingActionsTrait
{
    protected function getBorrowingHeaderActions(Model $record): array
    {
        $actions = [];

        if ($record->status === BorrowingStatus::Pending) {
            $actions[] = EditAction::make();
            $actions[] = Action::make('approve')
                ->label('Setujui')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Setujui Peminjaman?')
                ->modalDescription('Stok barang akan dikurangi dan status aset akan berubah menjadi "Dipinjam".')
                ->action(function () use ($record) {
                    try {
                        app(BorrowingApprovalService::class)->approve($record);
                        Notification::make()->success()->title('Berhasil')->body('Peminjaman disetujui.')->send();
                        $this->redirect(request()->header('Referer'));
                    } catch (\Exception $e) {
                        Notification::make()->danger()->title('Gagal')->body($e->getMessage())->send();
                    }
                });

            $actions[] = Action::make('reject')
                ->label('Tolak')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->schema([
                    TextInput::make('reason')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) use ($record) {
                    try {
                        app(BorrowingApprovalService::class)->reject($record, $data['reason']);
                        Notification::make()->success()->title('Berhasil')->body('Peminjaman ditolak.')->send();
                        $this->redirect(request()->header('Referer'));
                    } catch (\Exception $e) {
                        Notification::make()->danger()->body($e->getMessage())->send();
                    }
                });
        }

        if ($record->status === BorrowingStatus::Approved) {
            $actions[] = Action::make('returnItems')
                ->label('Kembalikan Barang')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->modalHeading('Form Pengembalian Barang')
                ->modalDescription('Centang barang yang akan dikembalikan. Untuk barang habis pakai, atur jumlah pengembalian.')
                ->modalWidth('6xl')
                ->fillForm(function (Borrowing $record): array {
                    $itemsToReturn = $record->items()
                        ->where('quantity', '>', DB::raw('returned_quantity'))
                        ->with(['inventoryItem.item', 'inventoryItem.location.area'])
                        ->get()
                        ->map(function (BorrowingItem $item) {
                            $inventory = $item->inventoryItem;
                            $locationInfo = "{$inventory->location->name} ({$inventory->location->area->name})";

                            return [
                                'borrowing_item_id' => $item->id,
                                'item_type' => $inventory->item->type,
                                'item_name' => $inventory->item->name,
                                'identity_info' => $inventory->isFixed() ? $inventory->code : $locationInfo,
                                'remaining_qty' => $item->quantity - $item->returned_quantity,
                                'is_returning' => false,
                                'return_quantity' => 1,
                            ];
                        })->values()->toArray();

                    return ['items_to_return' => $itemsToReturn];
                })
                ->schema([
                    Section::make()
                        ->schema([
                            Repeater::make('items_to_return')
                                ->label('Daftar Barang yang Belum Kembali')
                                ->schema([
                                    Hidden::make('borrowing_item_id'),
                                    Hidden::make('item_type'),

                                    TextInput::make('item_name')
                                        ->label('Nama Barang')
                                        ->disabled()
                                        ->columnSpan(3),

                                    TextInput::make('identity_info')
                                        ->label('Kode / Lokasi (Area)')
                                        ->disabled()
                                        ->columnSpan(3),

                                    TextInput::make('remaining_qty')
                                        ->label('Sisa Pinjam')
                                        ->disabled()
                                        ->numeric()
                                        ->columnSpan(2),

                                    Toggle::make('is_returning')
                                        ->label('Kembalikan?')
                                        ->onColor('success')
                                        ->offColor('gray')
                                        ->visible(fn(Get $get) => $get('item_type') === ItemType::Fixed)
                                        ->columnSpan(2)
                                        ->inline(false),

                                    TextInput::make('return_quantity')
                                        ->label('Jml Kembali')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->maxValue(fn(Get $get) => (int) $get('remaining_qty'))
                                        ->visible(fn(Get $get) => $get('item_type') === ItemType::Consumable)
                                        ->columnSpan(2),
                                ])
                                ->columns(12)
                                ->addable(false)
                                ->deletable(false)
                                ->reorderable(false),
                        ])
                ])
                ->action(function (array $data) use ($record) {
                    $service = app(BorrowingReturnService::class);
                    $errors = [];
                    $processedCount = 0;

                    foreach ($data['items_to_return'] ?? [] as $returnData) {
                        $borrowingItem = $record->items->find($returnData['borrowing_item_id']);
                        if (!$borrowingItem)
                            continue;

                        $qtyToProcess = 0;
                        $shouldProcess = false;

                        if ($returnData['item_type'] === ItemType::Fixed) {
                            if (!empty($returnData['is_returning'])) {
                                $qtyToProcess = 1;
                                $shouldProcess = true;
                            }
                        } else {
                            $qty = (int) ($returnData['return_quantity'] ?? 0);
                            if ($qty >= 0) {
                                $qtyToProcess = $qty;
                                $shouldProcess = true;
                            }
                        }

                        if ($shouldProcess) {
                            try {
                                $service->processReturn($record, $borrowingItem, $qtyToProcess);
                                $processedCount++;
                            } catch (\Exception $e) {
                                $errors[] = "{$borrowingItem->inventoryItem->item->name}: " . $e->getMessage();
                            }
                        }
                    }

                    if (!empty($errors)) {
                        Notification::make()->danger()->title('Gagal')->body(implode("\n", $errors))->send();
                    } else {
                        // ✅ PERBAIKI LOGIKA DETEKSI AKSI
                        $hasAnyAction = collect($data['items_to_return'] ?? [])
                            ->contains(function ($item) {
                            if ($item['item_type'] === ItemType::Fixed) {
                                return !empty($item['is_returning']); // ✅ Sesuaikan dengan field form
                            }
                            return isset($item['return_quantity']) && (int) $item['return_quantity'] >= 0;
                        });

                        if ($hasAnyAction) {
                            Notification::make()->success()->title('Berhasil')->body("Pengembalian berhasil diproses.")->send();
                        } else {
                            Notification::make()->warning()->title('Dibatalkan')->body('Tidak ada barang yang dipilih untuk dikembalikan.')->send();
                        }
                    }

                    $this->redirect(request()->header('Referer'));
                });
        }

        $actions[] = DeleteAction::make()
            ->visible(fn() => $record->status === BorrowingStatus::Pending);

        return $actions;
    }
}
