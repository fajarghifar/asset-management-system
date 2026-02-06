<?php

namespace App\Livewire\Locations;

use Livewire\Component;
use App\Models\Location;
use App\DTOs\LocationData;
use App\Enums\LocationSite;
use Livewire\Attributes\On;
use Illuminate\Validation\Rule;
use App\Services\LocationService;
use App\Exceptions\LocationException;

class LocationForm extends Component
{
    public ?Location $location = null;

    public string $code = '';
    public string $site = '';
    public string $name = '';
    public string $description = '';

    public bool $isEditing = false;
    public array $sites = [];

    public function mount()
    {
        foreach (LocationSite::cases() as $site) {
            $this->sites[] = [
                'value' => $site->value,
                'label' => $site->getLabel(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.locations.location-form');
    }

    protected function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations', 'code')->ignore($this->location?->id),
            ],
            'site' => ['required', Rule::enum(LocationSite::class)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'code' => __('Code'),
            'site' => __('Site'),
            'name' => __('Name'),
            'description' => __('Description'),
        ];
    }

    #[On('create-location')]
    public function create()
    {
        $this->reset(['code', 'site', 'name', 'description', 'location', 'isEditing']);
        $this->isEditing = false;
        $this->dispatch('open-modal', name: 'location-form-modal');
    }

    #[On('edit-location')]
    public function edit(Location $location)
    {
        $this->location = $location;
        $this->code = $location->code;
        $this->site = $location->site->value;
        $this->name = $location->name;
        $this->description = $location->description ?? '';

        $this->isEditing = true;
        $this->dispatch('open-modal', name: 'location-form-modal');
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', name: 'location-form-modal');
        $this->reset(['code', 'site', 'name', 'description', 'location', 'isEditing']);
        $this->resetValidation();
    }

    public function save(LocationService $service)
    {
        $this->validate();

        $data = new LocationData(
            code: $this->code,
            site: LocationSite::from($this->site),
            name: $this->name,
            description: $this->description,
        );

        try {
            if ($this->isEditing && $this->location) {
                $service->updateLocation($this->location, $data);
                $message = __('Location updated successfully.');
            } else {
                $service->createLocation($data);
                $message = __('Location created successfully.');
            }

            $this->dispatch('close-modal', name: 'location-form-modal');
            $this->dispatch('pg:eventRefresh-locations-table');
            $this->dispatch('toast', message: $message, type: 'success');
            $this->reset(['code', 'site', 'name', 'description', 'location', 'isEditing']);

        } catch (LocationException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: __('An unexpected error occurred.'), type: 'error');
        }
    }
}
