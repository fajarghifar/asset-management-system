<x-guest-layout title="Confirm Password">
    <div class="mb-4 text-sm text-muted-foreground">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <!-- Password -->
        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" :required="true" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button class="w-full" ::disabled="submitting">
                <x-heroicon-o-arrow-path class="w-4 h-4 mr-2 animate-spin" x-show="submitting" />
                <span x-show="!submitting">{{ __('Confirm') }}</span>
                <span x-show="submitting">{{ __('Confirming...') }}</span>
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
