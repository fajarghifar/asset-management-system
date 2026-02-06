<x-modal name="move-asset-modal" :title="__('Move Asset')" maxWidth="lg">
    <div class="p-6">
        @if($asset)
            <div class="mb-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                    {{ __('Move Asset') }}
                </h3>
                <p class="text-sm text-muted-foreground mt-1">
                    {{ __('Transfer') }} <strong>{{ $asset->asset_tag }}</strong> {{ __('to a new location.') }}
                </p>
            </div>

            <form wire:submit="save" class="space-y-4">

                <!-- Current Location -->
                <div class="space-y-1">
                    <x-input-label value="{{ __('Current Location') }}" />
                    <div class="p-2 border border-border bg-muted rounded-md text-sm text-muted-foreground">
                        {{ $asset->location->full_name }}
                    </div>
                </div>

                <!-- New Location -->
                <div>
                    <x-input-label for="move_location_id" value="{{ __('New Location') }}" required />
                    <x-tom-select
                        id="move_location_id"
                        wire:model="location_id"
                        :options="$locationOptions"
                        placeholder="{{ __('Select Destination...') }}"
                        class="mt-1"
                    />
                    <x-input-error :messages="$errors->get('location_id')" class="mt-2" />
                </div>

                <!-- Recipient -->
                <div>
                    <x-form-input
                        name="recipient_name"
                        label="{{ __('Recipient / PIC Name') }}"
                        wire:model="recipient_name"
                        placeholder="{{ __('Who is receiving this?') }}"
                        required
                    />
                </div>

                <!-- Notes -->
                <div>
                    <x-input-label for="move_notes" value="{{ __('Notes') }}" />
                    <textarea id="move_notes" wire:model="notes" rows="3" class="mt-1 block w-full border-input bg-background focus:ring-ring focus:border-ring rounded-md shadow-sm sm:text-sm" placeholder="{{ __('Reason for movement...') }}"></textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'move-asset-modal' })">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-primary-button type="submit">
                        <x-heroicon-o-arrow-right-start-on-rectangle class="w-4 h-4 mr-2" />
                        {{ __('Move Asset') }}
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
