<x-guest-layout title="Forgot Password">
    <div class="mb-4 text-sm text-muted-foreground">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" :required="true" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="w-full" ::disabled="submitting">
                <x-heroicon-o-arrow-path class="w-4 h-4 mr-2 animate-spin" x-show="submitting" />
                <span x-show="!submitting">{{ __('Email Password Reset Link') }}</span>
                <span x-show="submitting">{{ __('Sending...') }}</span>
            </x-primary-button>
        </div>

        <div class="mt-4 text-center text-sm">
            <a href="{{ route('login') }}" class="underline text-muted-foreground hover:text-foreground">
                {{ __('Back to Login') }}
            </a>
        </div>
    </form>
</x-guest-layout>
