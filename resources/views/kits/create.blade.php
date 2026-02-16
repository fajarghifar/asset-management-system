<x-app-layout title="{{ __('Create Kit') }}">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Create Kit') }}
            </h2>
            <x-secondary-button href="{{ route('kits.index') }}" tag="a" class="w-full sm:w-auto justify-center">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to List') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm sm:rounded-lg border border-border p-6">
                <!-- Header -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium leading-6 text-foreground">{{ __('Kit Information') }}</h3>
                    <p class="mt-1 text-sm text-muted-foreground">{{ __('Define a new kit and its components.') }}</p>
                </div>

                @include('kits.form')
            </div>
        </div>
    </div>
</x-app-layout>
