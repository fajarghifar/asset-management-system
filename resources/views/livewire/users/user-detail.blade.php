<div>
    <x-modal name="user-detail-modal" :title="''" maxWidth="lg">
        @if($user)
            <div class="p-6">
                <!-- Custom Header -->
                <div class="mb-6 space-y-1.5 text-center sm:text-left">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                            User Details
                        </h3>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Detailed information about {{ $user->name }}.
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Name -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Name
                        </label>
                        <p class="text-sm text-foreground">{{ $user->name }}</p>
                    </div>

                    <!-- Username -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Username
                        </label>
                        <p class="text-sm text-foreground">{{ $user->username }}</p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Email
                        </label>
                        <p class="text-sm text-foreground">{{ $user->email }}</p>
                    </div>

                    <!-- Created At -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Created At
                        </label>
                        <p class="text-sm text-muted-foreground">{{ $user->created_at->format('d M Y H:i') }}</p>
                    </div>

                    <!-- Updated At -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Updated At
                        </label>
                        <p class="text-sm text-muted-foreground">{{ $user->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-6 flex items-center justify-end gap-x-2">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'user-detail-modal' })">
                        {{ __('Close') }}
                    </x-secondary-button>
                    <x-primary-button type="button" x-on:click="$dispatch('close-modal', { name: 'user-detail-modal' }); $dispatch('edit-user', { user: {{ $user->id }} })">
                        <x-heroicon-o-pencil-square class="w-4 h-4 mr-2" />
                        {{ __('Edit User') }}
                    </x-primary-button>
                </div>
            </div>
        @else
            <div class="p-8 text-center flex flex-col items-center justify-center space-y-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                <span class="text-sm text-muted-foreground">{{ __('Loading details...') }}</span>
            </div>
        @endif
    </x-modal>
</div>
