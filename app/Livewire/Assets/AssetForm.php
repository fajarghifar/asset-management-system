<?php

namespace App\Livewire\Assets;

use Carbon\Carbon;
use App\Models\Asset;
use App\DTOs\AssetData;
use Livewire\Component;
use App\Enums\AssetStatus;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Services\AssetService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use App\Exceptions\AssetException;
use Illuminate\Support\Facades\Storage;

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

    public function mount(?Asset $asset = null)
    {
        $this->statusOptions = [];
        foreach (AssetStatus::cases() as $case) {
            // Only show 'Loaned' if the asset is currently Loaned
            if ($case === AssetStatus::Loaned) {
                if (!($asset && $asset->status === AssetStatus::Loaned)) {
                    continue;
                }
            }
            $this->statusOptions[] = [
                'value' => $case->value,
                'label' => $case->getLabel(),
            ];
        }

        if ($asset && $asset->exists) {
            $this->assetId = $asset->id;
            $this->product_id = $asset->product_id;
            $this->location_id = $asset->location_id;
            $this->asset_tag = $asset->asset_tag;
            $this->serial_number = $asset->serial_number ?? '';
            $this->status = $asset->status->value;
            $this->purchase_date = $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('Y-m-d') : null;
            $this->notes = $asset->notes ?? '';
            $this->image_path = $asset->image_path;
            $this->isEditing = true;

            // Preload options for selected items
            $this->productOptions = [
                ['value' => $asset->product->id, 'text' => $asset->product->name]
            ];

            $this->locationOptions = [
                ['value' => $asset->location->id, 'text' => $asset->location->full_name]
            ];
        } else {
            $this->status = AssetStatus::InStock->value; // Default
            // Options are loaded via AJAX
        }
    }

    // Initial options are empty for AJAX search
    // private function loadInitialOptions() { ... }

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
        $this->purchase_date = $asset->purchase_date ? Carbon::parse($asset->purchase_date)->format('Y-m-d') : null;
        $this->notes = $asset->notes ?? '';
        $this->image_path = $asset->image_path;
        $this->isEditing = true;

        // Populate options for selected items
        $this->productOptions = [
            ['value' => $asset->product->id, 'text' => $asset->product->name]
        ];

        $this->locationOptions = [
            ['value' => $asset->location->id, 'text' => $asset->location->full_name]
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

        // Security: Prevent modification of Product/Location during Edit
        if ($this->isEditing) {
            $originalAsset = Asset::findOrFail($this->assetId);
            $this->product_id = $originalAsset->product_id;
            $this->location_id = $originalAsset->location_id;
        }

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
                purchase_date: $this->purchase_date ? Carbon::parse($this->purchase_date) : null,
                image_path: $imagePath,
                notes: $this->notes ?: null,
            );

            if ($this->isEditing) {
                $asset = Asset::findOrFail($this->assetId);
                $service->updateAsset($asset, $data);
                $message = __('Asset updated successfully.');
                return redirect()->route('assets.show', $asset)->with('success', $message);
            } else {
                $service->createAsset($data);
                $message = __('Asset created successfully.');
                return redirect()->route('assets.index')->with('success', $message);
            }

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
