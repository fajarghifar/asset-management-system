<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Loan Management') }}
            </h2>
            <x-primary-button tag="a" href="{{ route('loans.create') }}">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                {{ __('Create Loan') }}
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:loans.loan-table />
        </div>
    </div>
</x-app-layout>
