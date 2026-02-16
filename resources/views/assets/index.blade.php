<x-app-layout title="{{ __('Assets') }}">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Assets') }}
            </h2>
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <x-secondary-button tag="a" href="{{ route('assets.import') }}" class="w-full sm:w-auto justify-center">
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 mr-2" />
                    {{ __('Import') }}
                </x-secondary-button>
                <x-primary-button tag="a" href="{{ route('assets.create') }}" class="w-full sm:w-auto justify-center">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    {{ __('Create New Asset') }}
                </x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:assets.assets-table />
        </div>
    </div>

    <livewire:assets.move-asset />
</x-app-layout>
