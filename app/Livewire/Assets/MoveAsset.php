<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Location;
use App\Services\AssetService;
use Livewire\Component;
use Livewire\Attributes\On;

class MoveAsset extends Component
{
    public ?Asset $asset = null;
    public $location_id; // New Location
    public $recipient_name;
    public $notes;

    public $locationOptions = [];

    protected function rules()
    {
        return [
            'location_id' => 'required|exists:locations,id|different:asset.location_id',
            'recipient_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function mount()
    {
        $this->locationOptions = Location::orderBy('name')->get()->map(function ($location) {
            return [
                'value' => $location->id,
                'label' => $location->full_name,
            ];
        })->toArray();
    }

    #[On('move-asset')]
    public function openModal($assetId)
    {
        $this->asset = Asset::find($assetId);

        if ($this->asset) {
            $this->reset(['location_id', 'recipient_name', 'notes']);
            $this->dispatch('open-modal', name: 'move-asset-modal');
        }
    }

    public function save(AssetService $assetService)
    {
        $this->validate();

        try {
            $assetService->updateAsset($this->asset, [
                'location_id' => $this->location_id,
                'recipient_name' => $this->recipient_name,
                'history_notes' => $this->notes,
            ]);

            $this->dispatch('close-modal', name: 'move-asset-modal');
            $this->dispatch('pg:eventRefresh-assets-table');
            $this->dispatch('toast', message: 'Asset moved successfully.', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Failed to move asset: ' . $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.assets.move-asset');
    }
}
