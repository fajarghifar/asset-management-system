<x-modal name="move-asset-modal" :title="'Move Asset'" maxWidth="lg">
    <div class="p-6">
        @if($asset)
            <div class="mb-6">
                <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                    Move Asset
                </h3>
                <p class="text-sm text-muted-foreground mt-1">
                    Transfer <strong>{{ $asset->asset_tag }}</strong> to a new location.
                </p>
            </div>

            <form wire:submit="save" class="space-y-4">

                <!-- Current Location -->
                <div class="space-y-1">
                    <x-input-label value="Current Location" />
                    <div class="p-2 border border-border bg-muted rounded-md text-sm text-muted-foreground">
                        {{ $asset->location->full_name }}
                    </div>
                </div>

                <!-- New Location -->
                <div>
                    <x-input-label for="move_location_id" value="New Location" required />
                    <select id="move_location_id" wire:model="location_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-input bg-background focus:ring-ring focus:border-ring sm:text-sm rounded-md shadow-sm">
                        <option value="">Select Destination...</option>
                        @foreach($locationOptions as $option)
                            <option value="{{ $option['value'] }}">
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('location_id')" class="mt-2" />
                </div>

                <!-- Recipient -->
                <div>
                    <x-form-input
                        name="recipient_name"
                        label="Recipient / PIC Name"
                        wire:model="recipient_name"
                        placeholder="Who is receiving this?"
                    />
                </div>

                <!-- Notes -->
                <div>
                    <x-input-label for="move_notes" value="Notes" />
                    <textarea id="move_notes" wire:model="notes" rows="3" class="mt-1 block w-full border-input bg-background focus:ring-ring focus:border-ring rounded-md shadow-sm sm:text-sm" placeholder="Reason for movement..."></textarea>
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
                <div class="animate-pulse">Loading...</div>
            </div>
        @endif
    </div>
</x-modal>
