<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use App\DTOs\ProductData;
use App\Enums\ProductType;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use App\Services\ProductService;
use App\Exceptions\ProductException;
use Illuminate\Validation\Rules\Enum;

class ProductForm extends Component
{
    public bool $isEditing = false;
    public ?Product $product = null;

    // Form Fields
    public string $name = '';
    public string $code = '';
    public string $description = '';
    public string $type = '';
    public ?int $category_id = null;
    public bool $can_be_loaned = true;

    // Select Options
    public array $categoryOptions = [];
    public array $typeOptions = [];

    public function mount()
    {
        // $this->categoryOptions is empty initially for AJAX search

        foreach (ProductType::cases() as $type) {
            $this->typeOptions[] = [
                'value' => $type->value,
                'label' => $type->getLabel(),
            ];
        }
        $this->type = ProductType::Asset->value; // Default
    }

    public function render()
    {
        return view('livewire.products.product-form');
    }

    #[On('create-product')]
    public function create(): void
    {
        // Reset categoryOptions to avoid stale data
        $this->reset(['name', 'code', 'description', 'type', 'category_id', 'can_be_loaned', 'product', 'isEditing', 'categoryOptions']);
        $this->type = ProductType::Asset->value;
        $this->can_be_loaned = true;
        $this->dispatch('open-modal', name: 'product-form-modal');
    }

    #[On('edit-product')]
    public function edit(Product $product): void
    {
        $this->product = $product;
        $this->name = $product->name;
        $this->code = $product->code;
        $this->description = $product->description ?? '';
        $this->type = $product->type->value;
        $this->category_id = $product->category_id;
        $this->can_be_loaned = $product->can_be_loaned;

        // Populate categoryOptions for the selected item
        if ($product->category) {
            $this->categoryOptions = [
                ['value' => $product->category->id, 'text' => $product->category->name]
            ];
        }

        $this->isEditing = true;
        $this->dispatch('open-modal', name: 'product-form-modal');
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'code')->ignore($this->product?->id)
            ],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', new Enum(ProductType::class)],
            'category_id' => ['required', 'exists:categories,id'],
            'can_be_loaned' => ['boolean'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'name' => __('Name'),
            'code' => __('Code'),
            'description' => __('Description'),
            'type' => __('Type'),
            'category_id' => __('Category'),
            'can_be_loaned' => __('Loanable'),
        ];
    }

    public function generateCode(ProductService $service): void
    {
        $this->code = $service->generateCode();
    }

    public function save(ProductService $service): void
    {
        $this->validate();

        $data = new ProductData(
            name: $this->name,
            code: $this->code,
            description: $this->description,
            type: ProductType::from($this->type),
            category_id: $this->category_id,
            can_be_loaned: $this->can_be_loaned,
        );

        try {
            if ($this->isEditing && $this->product) {
                $service->updateProduct($this->product, $data);
                $message = __('Product updated successfully.');
            } else {
                $service->createProduct($data);
                $message = __('Product created successfully.');
            }

            $this->dispatch('close-modal', name: 'product-form-modal');
            $this->dispatch('pg:eventRefresh-products-table');
            $this->dispatch('toast', message: $message, type: 'success');
        } catch (ProductException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: __('An unexpected error occurred.'), type: 'error');
        }
    }
}
