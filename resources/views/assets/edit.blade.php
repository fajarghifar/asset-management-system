<x-app-layout title="{{ __('Edit Asset') }}">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Edit Asset') }}
            </h2>
            <div class="flex items-center gap-2">
                <x-secondary-button x-data="" @click="$dispatch('move-asset', { assetId: {{ $asset->id }} })">
                    {{ __('Move Asset') }}
                </x-secondary-button>
                <x-secondary-button tag="a" href="{{ route('assets.show', $asset) }}">
                    {{ __('View Details') }}
                </x-secondary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm sm:rounded-lg border border-border p-6">
                 @livewire('assets.asset-form', ['asset' => $asset])
            </div>
        </div>

        <!-- History Table -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <h3 class="text-lg font-medium leading-6 text-foreground mb-4 px-1">{{ __('Asset History') }}</h3>
            <livewire:assets.asset-histories-table :asset-id="$asset->id" />
        </div>
    </div>
</x-app-layout>

        <!-- History Table -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <h3 class="text-lg font-medium leading-6 text-foreground mb-4 px-1">Asset History</h3>
            <livewire:assets.asset-histories-table :asset-id="$asset->id" />
        </div>
    </div>
</x-app-layout>

<livewire:assets.move-asset />
