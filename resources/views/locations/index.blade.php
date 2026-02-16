<x-app-layout title="Locations">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Locations') }}
            </h2>
            <x-primary-button x-data x-on:click="$dispatch('create-location')" class="w-full sm:w-auto justify-center">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                {{ __('Create Location') }}
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:locations.locations-table />
        </div>
    </div>

    <livewire:locations.location-form />
    <livewire:locations.location-detail />
</x-app-layout>
