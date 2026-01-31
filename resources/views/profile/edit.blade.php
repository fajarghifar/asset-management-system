<x-app-layout title="Edit Profile">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Profile') }}
            </h2>
            <x-secondary-button href="{{ route('dashboard') }}" tag="a">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to Dashboard') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-card text-card-foreground shadow-sm rounded-lg border border-border">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-card text-card-foreground shadow-sm rounded-lg border border-border">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-card text-card-foreground shadow-sm rounded-lg border border-border">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
