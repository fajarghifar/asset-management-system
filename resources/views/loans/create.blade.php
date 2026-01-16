<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Loan') }}
            </h2>
            <x-secondary-button href="{{ route('loans.index') }}" tag="a">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                Back to List
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 border border-zinc-200">
                @include('loans.form')
            </div>
        </div>
    </div>
</x-app-layout>
