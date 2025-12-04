<?php

namespace App\Services;

use App\Enums\ItemType;
use App\Models\Borrowing;
use App\Models\ItemStock;
use App\Models\BorrowingItem;
use App\Enums\BorrowingStatus;
use App\Enums\FixedItemStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowingReturnService
{
    /**
     * Memproses pengembalian barang per baris item.
     * Bisa menangani pengembalian parsial (sebagian) atau penuh.
     * * @param BorrowingItem $borrowingItem
     * @param int $returnQty Jumlah yang dikembalikan saat ini
     * @param string|null $conditionNotes Catatan kondisi barang saat kembali (opsional)
     */
    public function processReturn(BorrowingItem $borrowingItem, int $returnQty, ?string $conditionNotes = null): void
    {
        // 1. Validasi Input Dasar
        if ($returnQty <= 0) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah pengembalian harus lebih dari 0.']);
        }

        // 2. Hitung Sisa yang belum kembali
        // quantity (Total Pinjam) - returned_quantity (Sudah Kembali Sebelumnya)
        $remaining = $borrowingItem->quantity - $borrowingItem->returned_quantity;

        if ($returnQty > $remaining) {
            throw ValidationException::withMessages([
                'quantity' => "Jumlah kembali melebihi sisa pinjaman. Sisa yang belum kembali: {$remaining}."
            ]);
        }

        // 3. Eksekusi Database Transaction
        DB::transaction(function () use ($borrowingItem, $returnQty, $conditionNotes) {

            // A. Kembalikan Barang ke Inventaris (Gudang)
            if ($borrowingItem->item->type === ItemType::Consumable) {
                $this->restoreStock($borrowingItem, $returnQty);
            } elseif ($borrowingItem->item->type === ItemType::Fixed) {
                $this->restoreInstance($borrowingItem, $conditionNotes);
            }

            // B. Update Data pada Baris Item Peminjaman
            $borrowingItem->increment('returned_quantity', $returnQty);
            $borrowingItem->update(['returned_at' => now()]);

            // C. Cek Status Header Peminjaman (Apakah Transaksi Selesai?)
            $this->checkBorrowingCompletion($borrowingItem->borrowing);
        });
    }

    /**
     * Mengembalikan stok barang habis pakai ke lokasi asal.
     */
    private function restoreStock(BorrowingItem $borrowingItem, int $qty): void
    {
        // Pastikan location_id ada (seharusnya ada dari data borrowing_items)
        if (!$borrowingItem->location_id) {
            throw ValidationException::withMessages(['error' => 'Data lokasi stok hilang pada riwayat peminjaman.']);
        }

        // Cari atau buat record stok di lokasi tersebut
        $stock = ItemStock::firstOrCreate(
            [
                'item_id' => $borrowingItem->item_id,
                'location_id' => $borrowingItem->location_id
            ],
            [
                'quantity' => 0,
                'min_quantity' => 0
            ]
        );

        // Tambahkan stok
        $stock->increment('quantity', $qty);
    }

    /**
     * Mengubah status aset tetap menjadi Available kembali.
     */
    private function restoreInstance(BorrowingItem $borrowingItem, ?string $notes): void
    {
        // Pastikan fixed_instance_id ada
        if (!$borrowingItem->fixed_instance_id) {
            throw ValidationException::withMessages(['error' => 'Data ID Aset hilang pada riwayat peminjaman.']);
        }

        // Update Instance Aset
        $instance = $borrowingItem->fixedInstance;

        if ($instance) {
            $instance->update([
                'status' => FixedItemStatus::Available,
                'notes' => $notes ? $notes : $instance->notes
            ]);
        }
    }

    /**
     * Mengecek apakah seluruh transaksi peminjaman sudah selesai.
     * Jika semua item sudah kembali, ubah status Header menjadi Completed.
     */
    private function checkBorrowingCompletion(Borrowing $borrowing): void
    {
        $borrowing->refresh();

        // Logic: Cek apakah SEMUA item memiliki returned_quantity >= quantity
        $allReturned = $borrowing->items->every(function ($item) {
            return $item->returned_quantity >= $item->quantity;
        });

        if ($allReturned) {
            // Jika semua lunas, tutup transaksi
            $borrowing->update([
                'status' => BorrowingStatus::Completed,
                'actual_return_date' => now()
            ]);
        }
    }
}
