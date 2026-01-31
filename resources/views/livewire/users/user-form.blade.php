<x-modal name="user-form-modal" :title="''" maxWidth="2xl">
    <div class="p-6">
        <!-- Custom Header -->
        <div class="mb-6 space-y-1.5 text-center sm:text-left">
            <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                {{ $isEditing ? 'Edit User' : 'Create User' }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{ $isEditing ? 'Make changes to the user account here. Click save when you\'re done.' : 'Add a new user account. Click save when you\'re done.' }}
            </p>
        </div>

        <form wire:submit="save" class="space-y-4">
            <!-- Name -->
            <x-form-input
                name="name"
                label="Name"
                type="text"
                wire:model="name"
                placeholder="Full Name"
                required
            />

            <!-- Username -->
            <x-form-input
                name="username"
                label="Username"
                type="text"
                wire:model="username"
                placeholder="Username (e.g. johndoe)"
                required
            />

            <!-- Email -->
            <x-form-input
                name="email"
                label="Email"
                type="email"
                wire:model="email"
                placeholder="Email Address"
                required
            />

            <!-- Password -->
            <div class="space-y-2">
                <x-input-label for="password" :value="__('Password')" :required="!$isEditing" />
                <x-text-input
                    id="password"
                    wire:model="password"
                    type="password"
                    class="block w-full"
                    autocomplete="new-password"
                    placeholder="Password"
                    :required="!$isEditing"
                />
                <x-input-error :messages="$errors->get('password')" />
                @if($isEditing)
                     <p class="text-xs text-muted-foreground mt-1">{{ __('Leave blank to keep current password.') }}</p>
                @endif
            </div>

            <!-- Password Confirmation -->
            <x-form-input
                name="password_confirmation"
                label="Confirm Password"
                type="password"
                wire:model="password_confirmation"
                placeholder="Confirm Password"
                :required="!$isEditing"
            />

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'user-form-modal' })" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" wire:loading.attr="disabled">
                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                    {{ $isEditing ? __('Save Changes') : __('Create User') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>
