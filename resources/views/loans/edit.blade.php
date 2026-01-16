<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Loan') }}
            </h2>
            <div class="flex items-center gap-2">
                <x-secondary-button href="{{ route('loans.index') }}" tag="a">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    Back to List
                </x-secondary-button>
                <x-primary-button href="{{ route('loans.show', $loan) }}" tag="a">
                    <x-heroicon-o-eye class="w-4 h-4 mr-2" />
                    View Details
                </x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm rounded-lg p-6 border border-border">
                @include('loans.form', ['loan' => $loan])
            </div>
        </div>
    </div>
</x-app-layout>
