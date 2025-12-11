<?php

namespace App\Services;

use App\Enums\ItemType;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Enums\BorrowingStatus;
use App\Enums\InventoryStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowingReturnService
{
    public function processReturn(Borrowing $borrowing, BorrowingItem $borrowingItem, int $returnQty, ?string $conditionNotes = null): void
    {
        $inventoryItem = $borrowingItem->inventoryItem;
        $item = $inventoryItem->item;

        if ($item->type === ItemType::Consumable) {
            if ($returnQty < 0) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah pengembalian tidak boleh negatif.']);
            }

            DB::transaction(function () use ($borrowing, $borrowingItem, $returnQty, $inventoryItem, $conditionNotes) {
                if ($returnQty === 0) {
                    $borrowingItem->update([
                        'returned_quantity' => $borrowingItem->quantity,
                        'status' => 'returned',
                        'returned_at' => now(),
                    ]);
                } else {
                    $inventoryItem->increment('quantity', $returnQty);
                    $borrowingItem->increment('returned_quantity', $returnQty);

                    if ($borrowingItem->returned_quantity >= $borrowingItem->quantity) {
                        $borrowingItem->update([
                            'status' => 'returned',
                            'returned_at' => now(),
                        ]);
                    } else {
                        $borrowingItem->update(['returned_at' => now()]);
                    }
                }

                $this->checkBorrowingCompletion($borrowing);
            });
        } else {
            if ($returnQty <= 0) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah pengembalian harus lebih dari 0.']);
            }
        }

        // Validasi sisa pinjam (hanya jika returnQty > 0)
        if ($returnQty > 0) {
            $remaining = $borrowingItem->quantity - $borrowingItem->returned_quantity;
            if ($returnQty > $remaining) {
                throw ValidationException::withMessages(['quantity' => "Jumlah kembali melebihi sisa pinjaman ({$remaining})."]);
            }
        }

        if ($borrowingItem->borrowing_id !== $borrowing->id) {
            throw ValidationException::withMessages(['system' => 'Mismatch Data: Item tidak sesuai dengan Header Peminjaman.']);
        }

        DB::transaction(function () use ($borrowing, $borrowingItem, $returnQty, $conditionNotes) {
            $inventoryItem = $borrowingItem->inventoryItem;
            $item = $inventoryItem->item;

            if ($item->type === ItemType::Consumable && $returnQty > 0) {
                // ✅ Hanya tambah stok jika returnQty > 0
                $inventoryItem->increment('quantity', $returnQty);
            } elseif ($item->type === ItemType::Fixed) {
                $inventoryItem->update([
                    'status' => InventoryStatus::Available,
                    'notes' => $conditionNotes ?: $inventoryItem->notes
                ]);
            }

            if ($returnQty > 0) {
                $borrowingItem->increment('returned_quantity', $returnQty);

                if (($borrowingItem->returned_quantity + $returnQty) >= $borrowingItem->quantity) {
                    $borrowingItem->update([
                        'status' => 'returned',
                        'returned_at' => now(),
                    ]);
                } else {
                    $borrowingItem->update(['returned_at' => now()]);
                }
            }

            $this->checkBorrowingCompletion($borrowing);
        });
    }

    /**
     * Periksa apakah semua item dalam peminjaman sudah dikembalikan.
     */
    private function checkBorrowingCompletion(Borrowing $borrowing): void
    {
        $borrowing->load('items');

        $allReturned = $borrowing->items->every(function ($item) {
            return $item->returned_quantity >= $item->quantity;
        });

        if ($allReturned) {
            $borrowing->update([
                'status' => BorrowingStatus::Completed,
                'actual_return_date' => now(),
            ]);
        }
    }
}
