<x-app-layout title="Edit Loan">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Edit Loan') }}
            </h2>
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <x-secondary-button href="{{ route('loans.index') }}" tag="a" class="w-full sm:w-auto justify-center">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </x-secondary-button>
                <x-primary-button href="{{ route('loans.show', $loan) }}" tag="a" class="w-full sm:w-auto justify-center">
                    <x-heroicon-o-eye class="w-4 h-4 mr-2" />
                    {{ __('View Details') }}
                </x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm rounded-lg p-6 border border-border">
                @include('loans.form', ['loan' => $loan])
            </div>
        </div>
    </div>
</x-app-layout>
