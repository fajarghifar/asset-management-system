<x-modal name="consumable-stock-form-modal" :title="''" maxWidth="2xl">
    <div class="p-6">
        <!-- Custom Header -->
        <div class="mb-6 space-y-1.5 text-center sm:text-left">
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
                    <x-tom-select
                        name="product_id"
                        wire:model="product_id"
                        id="product_id_select"
                        :url="route('api.products.search')"
                        :options="$productOptions"
                        placeholder="{{ __('Search Product...') }}"
                        required
                        :disabled="$isEditing"
                    />
                    @if($isEditing)
                        <p class="text-xs text-muted-foreground mt-1">{{ __('Product cannot be changed while editing.') }}</p>
                    @endif
                    <x-input-error :messages="$errors->get('product_id')" />
                </div>

                <!-- Location (Searchable) -->
                <div class="space-y-2">
                    <x-input-label for="location_id_select" value="{{ __('Location') }}" required />
                    <x-tom-select
                        name="location_id"
                        wire:model="location_id"
                        id="location_id_select"
                        :url="route('api.locations.search')"
                        :options="$locationOptions"
                        placeholder="{{ __('Search Location...') }}"
                        required
                        :disabled="$isEditing"
                    />
                    @if($isEditing)
                        <p class="text-xs text-muted-foreground mt-1">{{ __('Location cannot be changed while editing.') }}</p>
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
                    label="{{ __('Minimum Stock Alert') }}"
                    type="number"
                    wire:model="min_quantity"
                    placeholder="0"
                    min="0"
                    required
                />
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'consumable-stock-form-modal' })">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit">
                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                    {{ $isEditing ? __('Save Changes') : __('Create Stock') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>
