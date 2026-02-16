<x-app-layout title="{{ __('Edit Asset') }}">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Edit Asset') }}
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <x-secondary-button tag="a" href="{{ route('assets.index') }}">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </x-secondary-button>
                <x-secondary-button x-data="" @click="$dispatch('move-asset', { assetId: {{ $asset->id }} })">
                    <x-heroicon-o-arrows-right-left class="w-4 h-4 mr-2" />
                    {{ __('Move Asset') }}
                </x-secondary-button>
                <x-secondary-button tag="a" href="{{ route('assets.show', $asset) }}">
                    {{ __('View Details') }}
                </x-secondary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             @livewire('assets.asset-form', ['asset' => $asset])
        </div>

        <!-- History Table -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border">
                <div class="p-6 border-b border-border">
                    <h3 class="font-semibold text-lg leading-none tracking-tight">{{ __('Asset History') }}</h3>
                </div>
                <div class="p-6">
                    <livewire:assets.asset-histories-table :asset-id="$asset->id" />
                </div>
            </div>
        </div>
    </div>

    <livewire:assets.move-asset />
</x-app-layout>
