<x-app-layout title="{{ __('Products') }}">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Products') }}
            </h2>
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <x-secondary-button tag="a" href="{{ route('products.import') }}" class="w-full sm:w-auto justify-center">
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
                    {{ __('Import') }}
                </x-secondary-button>
                <x-primary-button x-data x-on:click="Livewire.dispatch('create-product')" class="w-full sm:w-auto justify-center">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    {{ __('Create Product') }}
                </x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:products.products-table />
        </div>
    </div>

    <livewire:products.product-form />
    <livewire:products.product-detail />
</x-app-layout>
