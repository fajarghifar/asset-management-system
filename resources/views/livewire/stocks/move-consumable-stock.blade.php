<x-modal name="move-stock-modal" :title="''">
    <div class="p-6">
        @if($stock)
            <div class="mb-6 space-y-1.5 text-center sm:text-left border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                    {{ __('Move Stock') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                    {{ __('Transfer') }} <strong>{{ $stock->product->name }} ({{ $stock->quantity }})</strong> {{ __('to a new location.') }}
                </p>
            </div>

            <form wire:submit="save" class="space-y-4">

                <!-- Current Location -->
                <div class="space-y-1">
                    <x-input-label value="{{ __('Current Location') }}" />
                    <div class="p-2 border border-border bg-muted rounded-md text-sm text-muted-foreground">
                        {{ $stock->location->code }} | {{ $stock->location->site->getLabel() }} - {{ $stock->location->name }}
                    </div>
                </div>

                <!-- New Location -->
                <div>
                    <x-input-label for="move_stock_location_id" value="{{ __('New Location') }}" required />
                    <x-tom-select
                        id="move_stock_location_id"
                        wire:model="location_id"
                        :options="$locationOptions"
                        :url="route('ajax.locations.search')"
                        method="POST"
                        placeholder="{{ __('Select Destination...') }}"
                        class="mt-1"
                        required
                    />
                    <x-input-error :messages="$errors->get('location_id')" class="mt-2" />
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'move-stock-modal' })" wire:loading.attr="disabled">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save" class="flex items-center">
                            <x-heroicon-o-arrow-right-start-on-rectangle class="w-4 h-4 mr-2" />
                            {{ __('Move Stock') }}
                        </span>
                        <span wire:loading.flex wire:target="save" class="items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('Saving...') }}
                        </span>
                    </x-primary-button>
                </div>
            </form>
        @else
            <div class="py-4 text-center text-muted-foreground">
                <div class="animate-pulse">{{ __('Loading...') }}</div>
            </div>
        @endif
    </div>
</x-modal>
