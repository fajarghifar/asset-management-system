<x-app-layout title="{{ __('Create Asset') }}">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Create New Asset') }}
            </h2>
            <x-secondary-button tag="a" href="{{ route('assets.index') }}">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to List') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @livewire('assets.asset-form')
        </div>
    </div>
</x-app-layout>
