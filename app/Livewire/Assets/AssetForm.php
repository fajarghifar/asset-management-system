<?php

namespace App\Livewire\Assets;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\Asset;
use App\Models\Product;
use App\Models\Location;
use App\Enums\AssetStatus;
use App\Enums\ProductType;
use App\DTOs\AssetData;
use App\Services\AssetService;
use App\Exceptions\AssetException;

class AssetForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $assetId = null;

    // Form Fields
    public $product_id;
    public $location_id;
    public string $asset_tag = '';
    public string $serial_number = '';
    public string $status = '';
    public ?string $purchase_date = null;
    public string $notes = '';
    public $image; // Temporary upload
    public ?string $image_path = null; // Existing image path

    public bool $isEditing = false;

    // Select Options
    public array $productOptions = [];
    public array $locationOptions = [];
    public array $statusOptions = [];

    public function mount()
    {
        $this->statusOptions = array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
        ], AssetStatus::cases());

        $this->status = AssetStatus::InStock->value; // Default

        $this->loadInitialOptions();
    }

    private function loadInitialOptions()
    {
        // Preload top 20 Assets products
        $this->productOptions = Product::where('type', ProductType::Asset)
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($p) => ['value' => $p->id, 'text' => $p->name])
            ->toArray();

        // Preload locations
        $this->locationOptions = Location::orderBy('site')
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn($l) => ['value' => $l->id, 'text' => "{$l->site->getLabel()} - {$l->name}"])
            ->toArray();
    }

    #[On('create-asset')]
    public function create()
    {
        $this->reset(['assetId', 'product_id', 'location_id', 'asset_tag', 'serial_number', 'purchase_date', 'notes', 'image', 'image_path']);
        $this->status = AssetStatus::InStock->value;
        $this->isEditing = false;

        $this->dispatch('open-modal', name: 'asset-form-modal');
    }

    #[On('edit-asset')]
    public function edit(Asset $asset)
    {
        $this->assetId = $asset->id;
        $this->product_id = $asset->product_id;
        $this->location_id = $asset->location_id;
        $this->asset_tag = $asset->asset_tag;
        $this->serial_number = $asset->serial_number ?? '';
        $this->status = $asset->status->value;
        $this->purchase_date = $asset->purchase_date?->format('Y-m-d');
        $this->notes = $asset->notes ?? '';
        $this->image_path = $asset->image_path;
        $this->isEditing = true;

        // Populate options for selected items
        $this->productOptions = [
            ['value' => $asset->product->id, 'text' => $asset->product->name]
        ];

        $this->locationOptions = [
            ['value' => $asset->location->id, 'text' => $asset->location->name . ' (' . $asset->location->code . ')']
        ];

        $this->dispatch('open-modal', name: 'asset-form-modal');
    }

    public function validationAttributes()
    {
        return [
            'product_id' => __('Product'),
            'location_id' => __('Location'),
            'asset_tag' => __('Asset Tag'),
            'serial_number' => __('Serial Number'),
            'status' => __('Status'),
            'purchase_date' => __('Purchase Date'),
            'image' => __('Image'),
            'notes' => __('Notes'),
        ];
    }

    public function generateTag(AssetService $service): void
    {
        $this->asset_tag = $service->generateAssetTag();
    }

    public function save(AssetService $service)
    {
        $rules = [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'asset_tag' => [
                'required', 'string', 'max:50',
                Rule::unique('assets', 'asset_tag')->ignore($this->assetId),
            ],
            'serial_number' => [
                'nullable', 'string', 'max:255',
                Rule::unique('assets', 'serial_number')->ignore($this->assetId),
            ],
            'status' => ['required', Rule::enum(AssetStatus::class)],
            'purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        $this->validate($rules, [], $this->validationAttributes());

        try {
            $imagePath = $this->image_path;

            if ($this->image) {
                // Delete old image if exists and editing
                if ($this->isEditing && $this->image_path) {
                    Storage::disk('public')->delete($this->image_path);
                }
                $imagePath = $this->image->store('assets', 'public');
            }

            $data = new AssetData(
                product_id: $this->product_id,
                location_id: $this->location_id,
                asset_tag: $this->asset_tag,
                serial_number: $this->serial_number ?: null,
                status: AssetStatus::from($this->status),
                purchase_date: $this->purchase_date ? \Carbon\Carbon::parse($this->purchase_date) : null,
                image_path: $imagePath,
                notes: $this->notes ?: null,
            );

            if ($this->isEditing) {
                $asset = Asset::findOrFail($this->assetId);
                $service->updateAsset($asset, $data);
                $message = __('Asset updated successfully.');
            } else {
                $service->createAsset($data);
                $message = __('Asset created successfully.');
            }

            return redirect()->route('assets.index')->with('success', $message);

        } catch (AssetException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: __('An unexpected error occurred.'), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.assets.asset-form');
    }
}
