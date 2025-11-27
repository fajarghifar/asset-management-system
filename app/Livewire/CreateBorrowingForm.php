<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\User;
use App\Enums\ItemType;
use Livewire\Component;
use App\Enums\FixedItemStatus;
use App\Services\BorrowingService;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateBorrowingForm extends Component
{
    // --- Header Properties ---
    public $user_id;
    public $borrow_date;
    public $expected_return_date;
    public $purpose;
    public $notes;

    // --- User Search Logic (NEW) ---
    public $userSearch = '';
    public $userSearchResults = [];
    public $selectedUser = null; // Untuk menyimpan detail user terpilih (opsional, untuk UI)

    // --- Items Logic ---
    public array $rows = [];
    public $searchQuery = '';
    public $searchResults = [];

    public function mount()
    {
        // Set default logged-in user
        $this->user_id = Auth::id();
        $user = Auth::user();
        $this->userSearch = $user->name; // Isi input search dengan nama user login
        $this->selectedUser = $user;

        $this->borrow_date = now()->format('Y-m-d\TH:i');
        $this->expected_return_date = now()->addDays(3)->format('Y-m-d\TH:i');
    }

    // --- User Search Function (NEW) ---
    public function updatedUserSearch($query)
    {
        // Jika input dikosongkan, reset user_id
        if (empty($query)) {
            $this->user_id = null;
            $this->selectedUser = null;
            $this->userSearchResults = [];
            return;
        }

        // Cari user berdasarkan nama atau email
        $this->userSearchResults = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function selectUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $this->user_id = $user->id;
            $this->userSearch = $user->name; // Set text input jadi nama user
            $this->selectedUser = $user;     // Simpan objek user untuk display email/info lain
            $this->userSearchResults = [];   // Tutup dropdown
        }
    }

    // --- Search Logic (Mirip POS) ---
    public function updatedSearchQuery($query)
    {
        if (strlen($query) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Item::whereIn('type', [ItemType::Fixed, ItemType::Consumable])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $stockInfo = match ($item->type) {
                    ItemType::Fixed => $item->fixedInstances()
                        ->where('status', FixedItemStatus::Available)->count() . ' Unit',
                    ItemType::Consumable => $item->stocks()->sum('quantity') . ' Stok',
                    default => '0'
                };

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'type' => $item->type,
                    'stock_info' => $stockInfo,
                ];
            })
            ->toArray();
    }

    public function selectItem($itemId)
    {
        $item = Item::find($itemId);
        if (!$item)
            return;

        $details = $this->fetchItemDetails($item);

        if (empty($details)) {
            Notification::make()->title('Stok/Unit tidak tersedia')->warning()->send();
            return;
        }

        $this->rows[] = [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_code' => $item->code,
            'type' => $item->type->value,
            'available_options' => $details,
            'selected_detail_id' => '',
            'quantity' => 1,
            'max_quantity' => 1,
        ];

        $this->searchQuery = '';
        $this->searchResults = [];
    }

    private function fetchItemDetails(Item $item): array
    {
        if ($item->type === ItemType::Fixed) {
            return $item->fixedInstances()
                ->where('status', FixedItemStatus::Available)
                ->with('location')
                ->get()
                ->map(fn($i) => [
                    'id' => $i->id,
                    'label' => "{$i->code} - SN: {$i->serial_number} (Posisi: {$i->location->name})",
                    'max_qty' => 1
                ])->toArray();
        }

        if ($item->type === ItemType::Consumable) {
            return $item->stocks()
                ->where('quantity', '>', 0)
                ->with('location')
                ->get()
                ->map(fn($s) => [
                    'id' => $s->location_id,
                    'label' => "{$s->location->name} (Sisa: {$s->quantity})",
                    'max_qty' => $s->quantity
                ])->toArray();
        }

        return [];
    }

    public function updateRowDetail($index, $detailId)
    {
        $options = $this->rows[$index]['available_options'];
        $selectedOption = collect($options)->firstWhere('id', $detailId);

        if ($selectedOption) {
            $this->rows[$index]['max_quantity'] = $selectedOption['max_qty'];
            if ($this->rows[$index]['quantity'] > $selectedOption['max_qty']) {
                $this->rows[$index]['quantity'] = $selectedOption['max_qty'];
            }
        }
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    public function submit(BorrowingService $service)
    {
        $this->validate([
            'user_id' => 'required', // Pastikan user_id terpilih dari search
            'purpose' => 'required|string|min:5',
            'borrow_date' => 'required|date',
            'rows' => 'required|array|min:1',
            'rows.*.selected_detail_id' => 'required',
            'rows.*.quantity' => 'required|integer|min:1',
        ], [
            'user_id.required' => 'Data peminjam wajib dipilih dari hasil pencarian.',
            'rows.*.selected_detail_id.required' => 'Harap pilih Unit atau Lokasi untuk setiap barang.',
        ]);

        $formattedItems = collect($this->rows)->map(function ($row) {
            return [
                'item_id' => $row['item_id'],
                'quantity' => $row['quantity'],
                'fixed_instance_id' => $row['type'] === 'fixed' ? $row['selected_detail_id'] : null,
                'location_id' => $row['type'] === 'consumable' ? $row['selected_detail_id'] : null,
            ];
        })->toArray();

        try {
            $borrowing = $service->create([
                'user_id' => $this->user_id,
                'purpose' => $this->purpose,
                'borrow_date' => $this->borrow_date,
                'expected_return_date' => $this->expected_return_date,
                'notes' => $this->notes,
            ], $formattedItems);

            Notification::make()->title('Peminjaman Berhasil')->success()->send();
            return redirect()->to('/admin/borrowings');

        } catch (\Exception $e) {
            Notification::make()->title('Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    public function render()
    {
        return view('livewire.borrowing.create-borrowing-form');
    }
}
