<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use Illuminate\Validation\Rule;
use App\Models\ConsumableStock;
use App\DTOs\ConsumableStockData;
use App\Services\ConsumableStockService;
use App\Exceptions\ConsumableStockException;

class ConsumableStockForm extends Component
{
    #[Locked]
    public ?int $stockId = null;

    // Form Fields
    public $product_id;
    public $location_id;
    public int $quantity = 0;
    public int $min_quantity = 0;

    public bool $isEditing = false;

    // Searchable Options
    public array $productOptions = [];
    public array $locationOptions = [];

    public function mount()
    {
        // Options are loaded via AJAX
    }

    #[On('create-stock')]
    public function create()
    {
        $this->reset(['stockId', 'product_id', 'location_id', 'quantity', 'min_quantity', 'productOptions', 'locationOptions']);
        $this->isEditing = false;

        $this->dispatch('open-modal', name: 'consumable-stock-form-modal');
    }

    // Initial options are empty for AJAX search
    // private function loadInitialOptions() { ... }

    #[On('edit-stock')]
    public function edit(ConsumableStock $stock)
    {
        $this->stockId = $stock->id;
        $this->product_id = $stock->product_id;
        $this->location_id = $stock->location_id;
        $this->quantity = $stock->quantity;
        $this->min_quantity = $stock->min_quantity;

        // Populate options for the selected items so the component can display the label
        $this->productOptions = [
            ['value' => $stock->product->id, 'text' => $stock->product->name]
        ];

        $this->locationOptions = [
            ['value' => $stock->location->id, 'text' => $stock->location->code . ' | ' . $stock->location->site->getLabel() . ' - ' . $stock->location->name]
        ];

        $this->isEditing = true;
        $this->dispatch('open-modal', name: 'consumable-stock-form-modal');
    }

    public function validationAttributes()
    {
        return [
            'product_id' => __('Product'),
            'location_id' => __('Location'),
            'quantity' => __('Quantity'),
            'min_quantity' => __('Minimum Quantity'),
        ];
    }

    public function save(ConsumableStockService $service)
    {
        $rules = [
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('consumable_stocks', 'product_id')
                    ->where('location_id', $this->location_id)
                    ->ignore($this->stockId),
            ],
            'location_id' => ['required', 'exists:locations,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:0'],
        ];

        // Security: Prevent modification of Product/Location during Edit
        if ($this->isEditing) {
            $originalStock = ConsumableStock::findOrFail($this->stockId);
            $this->product_id = $originalStock->product_id;
            $this->location_id = $originalStock->location_id;
        }

        $this->validate($rules, [], $this->validationAttributes());

        try {
            $data = new ConsumableStockData(
                product_id: $this->product_id,
                location_id: $this->location_id,
                quantity: $this->quantity,
                min_quantity: $this->min_quantity,
            );

            if ($this->isEditing) {
                $stock = ConsumableStock::findOrFail($this->stockId);
                $service->updateStock($stock, $data);
                $message = __('Stock updated successfully.');
            } else {
                $service->createStock($data);
                $message = __('Stock created successfully.');
            }

            $this->dispatch('close-modal', name: 'consumable-stock-form-modal');
            $this->dispatch('pg:eventRefresh-consumable-stocks-table');
            $this->dispatch('toast', message: $message, type: 'success');

        } catch (ConsumableStockException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: __('An unexpected error occurred.'), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.stocks.consumable-stock-form');
    }
}
