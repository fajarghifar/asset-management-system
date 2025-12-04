<?php

namespace App\Filament\Resources\Borrowings;

use App\Enums\ItemType;
use App\Models\Borrowing;
use Filament\Actions\Action;
use App\Models\BorrowingItem;
use App\Enums\BorrowingStatus;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use App\Services\BorrowingReturnService;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Services\BorrowingApprovalService;
use Filament\Schemas\Components\Utilities\Get;

trait BorrowingActionsTrait
{
    protected function getBorrowingHeaderActions(Model $record): array
    {
        $actions = [];

        // ==========================================
        // 1. DELETE ACTION
        // Hanya muncul jika status masih Pending
        // ==========================================
        $actions[] = DeleteAction::make()
            ->visible(fn() => $record->status === BorrowingStatus::Pending);

        // ==========================================
        // 2. APPROVAL ACTIONS (Pending Only)
        // ==========================================
        if ($record->status === BorrowingStatus::Pending) {

            // --- TOMBOL SETUJUI ---
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

                        Notification::make()
                            ->success()
                            ->title('Berhasil')
                            ->body('Peminjaman telah disetujui.')
                            ->send();

                        // Refresh halaman untuk update status UI
                        $this->redirect(request()->header('Referer'));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Gagal Menyetujui')
                            ->body($e->getMessage())
                            ->send();
                    }
                });

            // --- TOMBOL TOLAK ---
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

                        Notification::make()
                            ->success()
                            ->title('Berhasil')
                            ->body('Peminjaman ditolak.')
                            ->send();

                        $this->redirect(request()->header('Referer'));

                    } catch (\Exception $e) {
                        Notification::make()->danger()->body($e->getMessage())->send();
                    }
                });
        }

        // ==========================================
        // 3. RETURN ACTION (Approved Only)
        // Muncul jika status Approved (Sedang Dipinjam)
        // ==========================================
        if ($record->status === BorrowingStatus::Approved) {
            $actions[] = Action::make('returnItems')
                ->label('Kembalikan Barang')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('info')
                ->modalHeading('Form Pengembalian Barang')
                ->modalWidth('5xl')
                // --- PREPARE DATA UNTUK FORM ---
                ->fillForm(function (Borrowing $record): array {
                    // Ambil item yang BELUM kembali sepenuhnya (qty > returned)
                    $itemsToReturn = $record->items()
                        ->whereRaw('quantity > returned_quantity')
                        ->with(['item', 'fixedInstance', 'location'])
                        ->get()
                        ->map(function (BorrowingItem $item) {
                        if ($item->item->type === ItemType::Fixed) {
                            $identity = $item->fixedInstance->code ?? 'Kode Aset Hilang';
                        } else {
                            $identity = 'Lokasi: ' . ($item->location->name ?? '-');
                        }

                        return [
                            'borrowing_item_id' => $item->id,
                            'item_type' => $item->item->type,
                            'item_name' => $item->item->name,
                            'identity_info' => $identity,
                            'total_borrowed' => $item->quantity,
                            'total_returned' => $item->returned_quantity,
                            'remaining_qty' => $item->quantity - $item->returned_quantity,
                            'return_fixed' => false,
                            'return_quantity' => 0,
                        ];
                    })->values()->toArray();

                    return ['items_to_return' => $itemsToReturn];
                })
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
                                ->label('Kode / Lokasi')
                                ->disabled()
                                ->columnSpan(2),

                            TextInput::make('remaining_qty')
                                ->label('Sisa Pinjam')
                                ->disabled()
                                ->numeric()
                                ->columnSpan(1),

                            Toggle::make('return_fixed')
                                ->label('Kembalikan?')
                                ->onColor('success')
                                ->offColor('gray')
                                ->visible(fn(Get $get) => $get('item_type') === ItemType::Fixed->value)
                                ->columnSpan(1)
                                ->inline(false),

                            TextInput::make('return_quantity')
                                ->label('Jml Kembali')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(fn(Get $get) => (int) $get('remaining_qty'))
                                ->visible(fn(Get $get) => $get('item_type') === ItemType::Consumable->value)
                                ->columnSpan(1),
                        ])
                        ->columns(10)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                ])
                ->action(function (array $data) use ($record) {
                    $service = app(BorrowingReturnService::class);
                    $errors = [];
                    $processedCount = 0;

                    // Loop data dari Repeater
                    foreach ($data['items_to_return'] ?? [] as $returnData) {
                        $borrowingItem = $record->items->find($returnData['borrowing_item_id']);

                        // Skip jika item tidak ditemukan (safety)
                        if (!$borrowingItem)
                            continue;

                        $qtyToProcess = 0;
                        $shouldProcess = false;

                        // Logic Penentuan Jumlah yang dikembalikan
                        if ($returnData['item_type'] === ItemType::Fixed->value) {
                            // Fixed Item: Jika Toggle ON -> Qty 1
                            if (!empty($returnData['return_fixed'])) {
                                $qtyToProcess = 1;
                                $shouldProcess = true;
                            }
                        } else {
                            // Consumable: Ambil angka dari input
                            $qty = (int) ($returnData['return_quantity'] ?? 0);
                            if ($qty > 0) {
                                $qtyToProcess = $qty;
                                $shouldProcess = true;
                            }
                        }

                        // Eksekusi Service per Item
                        if ($shouldProcess) {
                            try {
                                $service->processReturn(
                                    $borrowingItem,
                                    $qtyToProcess,
                                    $returnData['condition_notes'] ?? null
                                );
                                $processedCount++;
                            } catch (\Exception $e) {
                                $errors[] = "Item {$borrowingItem->item->name}: " . $e->getMessage();
                            }
                        }
                    }

                    // Feedback Notifikasi ke User
                    if (!empty($errors)) {
                        Notification::make()
                            ->danger()
                            ->title('Terdapat Kesalahan')
                            ->body(implode("\n", $errors))
                            ->send();
                    } elseif ($processedCount > 0) {
                        Notification::make()
                            ->success()
                            ->title('Berhasil')
                            ->body("Berhasil mengembalikan $processedCount barang.")
                            ->send();
                    } else {
                        Notification::make()
                            ->warning()
                            ->title('Tidak Ada Perubahan')
                            ->body('Silakan pilih barang atau masukkan jumlah yang ingin dikembalikan.')
                            ->send();
                    }

                    // Refresh halaman
                    $this->redirect(request()->header('Referer'));
                });
        }

        return $actions;
    }
}
