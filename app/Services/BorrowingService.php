<?php
namespace App\Services;

use App\Models\Item;
use App\Enums\ItemType;
use App\Models\Borrowing;
use App\Models\ItemStock;
use App\Models\BorrowingItem;
use App\Enums\BorrowingStatus;
use App\Enums\FixedItemStatus;
use App\Models\FixedItemInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowingService
{
    /**
     * Membuat pengajuan peminjaman baru.
     * Status awal: Pending.
     * Belum mengurangi stok fisik.
     */
    public function create(array $headerData, array $items): Borrowing
    {
        return DB::transaction(function () use ($headerData, $items) {
            // 1. Buat Header Peminjaman
            $borrowing = Borrowing::create([
                'code' => $this->generateCode(),
                'borrower_name' => $headerData['borrower_name'],
                'proof_image' => $headerData['proof_image'] ?? null,
                'purpose' => $headerData['purpose'],
                'borrow_date' => $headerData['borrow_date'],
                'expected_return_date' => $headerData['expected_return_date'],
                'status' => BorrowingStatus::Pending,
                'notes' => $headerData['notes'] ?? null,
            ]);

            // 2. Proses Item Baris
            foreach ($items as $row) {
                $this->processItemRow($borrowing, $row);
            }

            return $borrowing;
        });
    }

    private function processItemRow(Borrowing $borrowing, array $row): void
    {
        $item = Item::find($row['item_id']);

        if (!$item) {
            throw ValidationException::withMessages(['items' => "Barang dengan ID {$row['item_id']} tidak ditemukan."]);
        }

        // --- VALIDASI KETERSEDIAAN (Soft Check) ---
        // Kita cek apakah barang ada, supaya user tidak mengajukan barang "hantu".
        // Tapi kita BELUM mengurangi stok/status di sini.

        if ($item->type === ItemType::Fixed) {
            // Cek apakah Instance ID valid dan statusnya Available
            $instance = FixedItemInstance::where('id', $row['fixed_instance_id'])
                ->where('status', FixedItemStatus::Available)
                ->first();

            if (!$instance) {
                throw ValidationException::withMessages([
                    'items' => "Unit aset '{$item->name}' yang dipilih tidak tersedia (mungkin sedang dipinjam)."
                ]);
            }
        } elseif ($item->type === ItemType::Consumable) {
            // Cek apakah stok di lokasi tersebut ada
            $stock = ItemStock::where('item_id', $item->id)
                ->where('location_id', $row['location_id'])
                ->first();

            $reqQty = $row['quantity'] ?? 1;

            if (!$stock || $stock->quantity < $reqQty) {
                throw ValidationException::withMessages([
                    'items' => "Stok '{$item->name}' di lokasi tersebut tidak mencukupi untuk pengajuan ini."
                ]);
            }
        }

        // --- SIMPAN ITEM ---
        BorrowingItem::create([
            'borrowing_id' => $borrowing->id,
            'item_id' => $item->id,
            'fixed_instance_id' => $row['fixed_instance_id'] ?? null,
            'location_id' => $row['location_id'] ?? null,
            'quantity' => $item->type === ItemType::Fixed ? 1 : ($row['quantity'] ?? 1),
            'returned_quantity' => 0,
            'status' => 'borrowed',
        ]);
    }

    private function generateCode(): string
    {
        // Format: BRW-YYYYMMDD-XXXX
        $prefix = 'BRW-' . date('Ymd') . '-';

        $last = Borrowing::where('code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if (!$last) {
            return $prefix . '0001';
        }

        $lastNumber = (int) substr($last->code, -4);
        return $prefix . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }
}
