<x-app-layout title="Categories">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Categories') }}
            </h2>
            <x-primary-button x-data x-on:click="Livewire.dispatch('create-category')" class="w-full sm:w-auto justify-center">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                {{ __('Create Category') }}
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:categories.categories-table />
        </div>
    </div>

    <livewire:categories.category-form />
    <livewire:categories.category-detail />
</x-app-layout>
