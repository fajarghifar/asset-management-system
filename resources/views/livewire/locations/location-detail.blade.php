<div>
    <x-modal name="location-detail-modal" :title="''" maxWidth="lg">
        @if($location)
            <div class="p-6">
                <!-- Custom Header -->
                <div class="mb-6 space-y-1.5 text-center sm:text-left">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                            {{ __('Location Details') }}
                        </h3>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ __('Detailed information about the location :code.', ['code' => $location->code]) }}
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Code & Site -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                {{ __('Code') }}
                            </label>
                            <p class="text-sm text-muted-foreground">{{ $location->code }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                {{ __('Site') }}
                            </label>
                            <p class="text-sm text-muted-foreground">{{ $location->site }}</p>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ __('Name') }}
                        </label>
                        <p class="text-sm text-foreground">{{ $location->name }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ __('Description') }}
                        </label>
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            {{ $location->description ?? '-' }}
                        </p>
                    </div>

                    <!-- Created At -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ __('Created At') }}
                        </label>
                        <p class="text-sm text-muted-foreground">{{ $location->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-6 flex items-center justify-end gap-x-2">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'location-detail-modal' })">
                        {{ __('Close') }}
                    </x-secondary-button>
                    <x-primary-button type="button" x-on:click="$dispatch('close-modal', { name: 'location-detail-modal' }); $dispatch('edit-location', { location: {{ $location->id }} })">
                        <x-heroicon-o-pencil-square class="w-4 h-4 mr-2" />
                        {{ __('Edit Location') }}
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
