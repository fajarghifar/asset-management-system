<x-app-layout title="{{ __('Edit Kit') }}">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Edit Kit') }}
            </h2>
            <x-secondary-button href="{{ route('kits.index') }}" tag="a">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to List') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm sm:rounded-lg border border-border p-6">
                @include('kits.form', ['kit' => $kit])
            </div>
        </div>
    </div>
</x-app-layout>
