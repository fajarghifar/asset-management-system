<x-modal name="consumable-stock-form-modal" :title="''">
    <div class="p-6">
        <!-- Custom Header -->
        <div class="mb-6 space-y-1.5 text-center sm:text-left border-b border-gray-200 pb-4">
            <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                {{ $isEditing ? __('Edit Stock') : __('Create Stock') }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{ $isEditing ? __('Update stock quantity and thresholds.') : __('Add new stock for a product at a location.') }}
            </p>
        </div>

        <form wire:submit="save" class="space-y-4">
            <div class="space-y-4">
                <!-- Product (Searchable) -->
                <div class="space-y-2">
                    <x-input-label for="product_id_select" value="{{ __('Product') }}" required />
                    @if($isEditing)
                        <input type="text"
                               value="{{ $productOptions[0]['text'] ?? '' }}"
                               class="w-full rounded-md border-border bg-muted text-muted-foreground shadow-sm sm:text-sm px-3 py-2 cursor-not-allowed opacity-75"
                               readonly disabled
                        >
                    @else
                        <x-tom-select
                            name="product_id"
                            wire:model="product_id"
                            id="product_id_select"
                            :url="route('ajax.products.consumables.search')"
                            method="POST"
                            :options="$productOptions"
                            placeholder="{{ __('Search Product...') }}"
                            required
                        />
                    @endif
                    <x-input-error :messages="$errors->get('product_id')" />
                </div>

                <!-- Location (Searchable) -->
                <div class="space-y-2">
                    <x-input-label for="location_id_select" value="{{ __('Location') }}" required />
                    @if($isEditing)
                        <input type="text"
                               value="{{ $locationOptions[0]['text'] ?? '' }}"
                               class="w-full rounded-md border-border bg-muted text-muted-foreground shadow-sm sm:text-sm px-3 py-2 cursor-not-allowed opacity-75"
                               readonly disabled
                        >
                    @else
                        <x-tom-select
                            name="location_id"
                            wire:model="location_id"
                            id="location_id_select"
                            :url="route('ajax.locations.search')"
                            method="POST"
                            :options="$locationOptions"
                            placeholder="{{ __('Search Location...') }}"
                            required
                        />
                    @endif
                    <x-input-error :messages="$errors->get('location_id')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Quantity -->
                <x-form-input
                    name="quantity"
                    label="{{ __('Quantity') }}"
                    type="number"
                    wire:model="quantity"
                    placeholder="0"
                    min="0"
                    required
                />

                <!-- Min Quantity -->
                <x-form-input
                    name="min_quantity"
                    label="{{ __('Minimum Quantity') }}"
                    type="number"
                    wire:model="min_quantity"
                    placeholder="0"
                    min="0"
                    required
                />
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'consumable-stock-form-modal' })">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <x-heroicon-o-check wire:loading.remove wire:target="save" class="w-4 h-4 mr-2" />
                    {{ $isEditing ? __('Save Changes') : __('Create Stock') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>
