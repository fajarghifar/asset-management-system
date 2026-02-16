<?php

namespace App\Livewire\Stocks;

use App\Models\ConsumableStock;
use App\Models\Location;
use App\DTOs\ConsumableStockData;
use App\Services\ConsumableStockService;
use App\Exceptions\ConsumableStockException;
use Livewire\Component;
use Livewire\Attributes\On;

class MoveConsumableStock extends Component
{
    public ?ConsumableStock $stock = null;
    public $location_id; // New Location
    public $locationOptions = [];

    protected function rules()
    {
        return [
            'location_id' => 'required|exists:locations,id',
        ];
    }

    public function validationAttributes()
    {
        return [
            'location_id' => __('Location'),
        ];
    }

    public function mount()
    {
        // For consistent searching, we use LocationController search.
        // But for initial load of "New Location" dropdown (if pre-selected? No).
        // Actually, MoveAsset loads ALL top 20 locations for options.
        // We can do same or leave empty for AJAX only.
        // MoveAsset does: Location::orderBy('name')->limit(20)->...
        // We'll mimic that.
        $this->locationOptions = Location::orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($location) {
                // Determine site label
                $siteLabel = $location->site->getLabel();
                return [
                    'value' => $location->id,
                    'text' => "{$location->code} | {$siteLabel} - {$location->name}",
                ];
            })->toArray();
    }

    #[On('move-stock')]
    public function openModal($stock)
    {
        $this->stock = ConsumableStock::with(['product', 'location'])->find($stock);

        if ($this->stock) {
            $this->reset(['location_id']);
            $this->dispatch('open-modal', name: 'move-stock-modal');
        }
    }

    public function save(ConsumableStockService $service)
    {
        $this->validate();

        if (!$this->stock) return;

        // Prevent moving to same location
        if ($this->location_id == $this->stock->location_id) {
            $this->addError('location_id', __('New location must be different from current location.'));
            return;
        }

        try {
            // Create DTO with new location, keeping other fields same
            $data = new ConsumableStockData(
                product_id: $this->stock->product_id,
                location_id: $this->location_id,
                quantity: $this->stock->quantity,
                min_quantity: $this->stock->min_quantity,
            );

            $service->updateStock($this->stock, $data);

            $this->dispatch('close-modal', name: 'move-stock-modal');
            $this->dispatch('pg:eventRefresh-consumable-stocks-table');
            $this->dispatch('toast', message: __('Stock moved successfully.'), type: 'success');

        } catch (ConsumableStockException $e) {
            // Check if duplicate error
            if ($e->getCode() === 409 || str_contains($e->getMessage(), 'unique constraint') || $e->getMessage() === 'Duplicate entry.') {
                 $this->addError('location_id', __('Product already exists at this location. Cannot move.'));
            } else {
                 $this->dispatch('toast', message: $e->getMessage(), type: 'error');
            }
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: __('Failed to move stock.'), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.stocks.move-consumable-stock');
    }
}
