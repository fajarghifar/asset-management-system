    <x-modal name="location-detail-modal" :title="''">
        @if($location)
            <div class="p-6">
                <!-- Header -->
                <div class="mb-6 space-y-1.5 text-center sm:text-left border-b border-gray-200 pb-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                            {{ __('Location Details') }}
                        </h3>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ __('Detailed information about the location :code.', ['code' => $location->code]) }}
                    </p>
                </div>

                <div class="space-y-6">
                    <!-- Code & Site -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Code') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $location->code }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Site') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $location->site }}</p>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Name') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $location->name }}</p>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Description') }}</label>
                        <p class="text-sm text-foreground font-medium">
                            {{ $location->description ?? '-' }}
                        </p>
                    </div>

                    <!-- Meta -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Created At') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $location->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Last Updated') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $location->updated_at?->format('d M Y, H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-6 flex items-center justify-end gap-x-2 pt-4 border-t border-gray-200">
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
