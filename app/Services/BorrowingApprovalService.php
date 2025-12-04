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

class BorrowingApprovalService
{
    /**
     * Menyetujui peminjaman.
     * Mengurangi stok fisik dan mengubah status aset menjadi dipinjam.
     */
    public function approve(Borrowing $borrowing): void
    {
        // 1. Guard: Hanya status Pending yang bisa disetujui
        if ($borrowing->status !== BorrowingStatus::Pending) {
            return;
        }

        DB::transaction(function () use ($borrowing) {
            // 2. Loop setiap item dalam pengajuan
            // Kita load 'item' untuk meminimalisir query di dalam loop (N+1 prevention logic)
            foreach ($borrowing->items->load('item') as $borrowingItem) {
                $this->processApprovalItem($borrowingItem);
            }

            // 3. Update Header Transaksi menjadi Approved
            $borrowing->update([
                'status' => BorrowingStatus::Approved,
                'notes' => trim($borrowing->notes . "\n[Sistem] Disetujui pada " . now()->format('d M Y H:i')),
            ]);
        });
    }

    /**
     * Menolak peminjaman.
     * Tidak ada perubahan stok/aset, hanya status dokumen.
     */
    public function reject(Borrowing $borrowing, string $reason): void
    {
        if ($borrowing->status !== BorrowingStatus::Pending) {
            return;
        }

        $borrowing->update([
            'status' => BorrowingStatus::Rejected,
            'notes' => trim($borrowing->notes . "\n[Sistem] Ditolak: $reason"),
        ]);
    }

    /**
     * Logika percabangan berdasarkan tipe barang
     */
    private function processApprovalItem(BorrowingItem $borrowingItem): void
    {
        $item = $borrowingItem->item;

        if ($item->type === ItemType::Consumable) {
            $this->deductStock($borrowingItem);
        } elseif ($item->type === ItemType::Fixed) {
            $this->markInstanceAsBorrowed($borrowingItem);
        }

        // Opsional: Pertegas status item baris menjadi 'borrowed'
        // Meskipun default database sudah 'borrowed', ini best practice untuk memastikan state.
        $borrowingItem->update(['status' => 'borrowed']);
    }

    /**
     * Logika untuk Barang Habis Pakai (Consumable)
     * Mengurangi stok dari lokasi yang dipilih.
     */
    private function deductStock(BorrowingItem $borrowingItem): void
    {
        // Validasi: Pastikan location_id ada (seharusnya sudah divalidasi di form, tapi ini layer terakhir)
        if (!$borrowingItem->location_id) {
            throw ValidationException::withMessages([
                'error' => "Lokasi pengambilan stok tidak valid untuk item '{$borrowingItem->item->name}'."
            ]);
        }

        // Ambil Stok & LOCK baris database
        $stock = ItemStock::where('item_id', $borrowingItem->item_id)
            ->where('location_id', $borrowingItem->location_id)
            ->lockForUpdate()
            ->first();

        // Cek Ketersediaan
        if (!$stock || $stock->quantity < $borrowingItem->quantity) {
            throw ValidationException::withMessages([
                'error' => "Gagal Menyetujui: Stok barang '{$borrowingItem->item->name}' di lokasi tersebut tidak mencukupi."
            ]);
        }

        // Eksekusi Pengurangan
        $stock->decrement('quantity', $borrowingItem->quantity);
    }

    /**
     * Logika untuk Barang Tetap (Fixed)
     * Mengubah status aset spesifik menjadi 'Borrowed'.
     */
    private function markInstanceAsBorrowed(BorrowingItem $borrowingItem): void
    {
        // Validasi: Pastikan fixed_instance_id ada
        if (!$borrowingItem->fixed_instance_id) {
            throw ValidationException::withMessages([
                'error' => "ID Unit Aset tidak ditemukan untuk item '{$borrowingItem->item->name}'."
            ]);
        }

        // Ambil Instance Aset & LOCK baris database
        $instance = $borrowingItem->fixedInstance()
            ->lockForUpdate()
            ->first();

        if (!$instance) {
            throw ValidationException::withMessages([
                'error' => "Data unit aset tidak ditemukan untuk barang '{$borrowingItem->item->name}'."
            ]);
        }

        // Cek Status Aset (Harus Available)
        if ($instance->status !== FixedItemStatus::Available) {
            throw ValidationException::withMessages([
                'error' => "Gagal Menyetujui: Unit aset '{$instance->code}' saat ini statusnya tidak tersedia (mungkin sudah dipinjam orang lain)."
            ]);
        }

        // Update Status Aset
        $instance->update(['status' => FixedItemStatus::Borrowed]);
    }
}
