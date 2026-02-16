<form wire:submit="save">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Primary Information -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border">
                <div class="p-6 border-b border-border">
                    <h3 class="font-semibold text-lg leading-none tracking-tight">{{ __('Asset Information') }}</h3>
                    <p class="text-sm text-muted-foreground mt-1.5">{{ __('Enter the core details of the asset.') }}</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Product -->
                        <div class="col-span-1 md:col-span-2 space-y-2">
                            @if($isEditing)
                                <x-form-input name="product_name_readonly" label="{{ __('Product') }}" value="{{ $productOptions[0]['text'] ?? '' }}" readonly class="bg-muted" />
                            @else
                                <x-input-label for="product_id" value="{{ __('Product') }}" required />
                                <x-tom-select
                                    name="product_id"
                                    wire:model="product_id"
                                    id="product_id"
                                    :url="route('ajax.products.assets.search')"
                                    method="POST"
                                    :options="$productOptions"
                                    placeholder="{{ __('Search Product...') }}"
                                    required
                                />
                                <x-input-error :messages="$errors->get('product_id')" />
                            @endif
                        </div>
                        <!-- Location -->
                        <div class="col-span-1 md:col-span-2 space-y-2">
                            @if($isEditing)
                                <x-form-input name="location_name_readonly" label="{{ __('Location') }}" value="{{ $locationOptions[0]['text'] ?? '' }}" readonly class="bg-muted" />
                            @else
                                <x-input-label for="location_id" value="{{ __('Location') }}" required />
                                <x-tom-select
                                    name="location_id"
                                    wire:model="location_id"
                                    id="location_id"
                                    :url="route('ajax.locations.search')"
                                    method="POST"
                                    :options="$locationOptions"
                                    placeholder="{{ __('Search Location...') }}"
                                    required
                                />
                                <x-input-error :messages="$errors->get('location_id')" />
                            @endif
                        </div>
                        <!-- Asset Tag -->
                        <div class="space-y-2">
                            <x-input-label for="asset_tag" value="{{ __('Asset Tag') }}" required />
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <x-form-input
                                        name="asset_tag"
                                        type="text"
                                        wire:model="asset_tag"
                                        placeholder="{{ __('e.g. INV.260206.ABCD') }}"
                                        required
                                        class="w-full"
                                    />
                                </div>
                                <x-secondary-button type="button" wire:click="generateTag" title="{{ __('Generate Tag') }}">
                                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                                </x-secondary-button>
                            </div>
                            <x-input-error :messages="$errors->get('asset_tag')" />
                        </div>
                        <!-- Serial Number -->
                        <x-form-input
                            name="serial_number"
                            label="{{ __('Serial Number') }}"
                            type="text"
                            wire:model="serial_number"
                            placeholder="{{ __('Factory S/N') }}"
                        />
                        <!-- Status -->
                        <div class="space-y-2">
                            <x-input-label for="status" value="{{ __('Status') }}" required />
                            <select
                                id="status"
                                wire:model="status"
                                class="flex w-full h-10 px-3 py-2 text-sm bg-transparent border rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                @disabled($isEditing && $status === \App\Enums\AssetStatus::Loaned->value)
                            >
                                @foreach($statusOptions as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" />
                        </div>
                        <!-- Purchase Date -->
                        <x-form-input
                            name="purchase_date"
                            label="{{ __('Purchase Date') }}"
                            type="date"
                            wire:model="purchase_date"
                        />
                    </div>
                </div>
                <div class="p-6 border-t border-border flex justify-end gap-3 bg-muted/20 rounded-b-xl">
                    <x-secondary-button tag="a" href="{{ route('assets.index') }}">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <x-primary-button type="submit" wire:loading.attr="disabled">
                        <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <x-heroicon-o-check wire:loading.remove wire:target="save" class="w-4 h-4 mr-2" />
                        {{ $isEditing ? __('Save Changes') : __('Create Asset') }}
                    </x-primary-button>
                </div>
            </div>
        </div>

        <!-- Right Column: Media & Extras -->
        <div class="space-y-6">
            <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border">
                <div class="p-6 border-b border-border">
                    <h3 class="font-semibold text-lg leading-none tracking-tight">{{ __('Media & Notes') }}</h3>
                </div>

                <div class="p-6 space-y-4">
                    <!-- Image Upload -->
                    <div class="space-y-2">
                        <x-input-label for="image" value="{{ __('Asset Image') }}" />
                        <div class="flex flex-col items-center justify-center border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 hover:bg-muted/50 transition-colors">
                            @if ($image)
                                <div class="relative group w-full aspect-square mb-2 overflow-hidden rounded-md bg-muted">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <p class="text-white text-xs font-medium">{{ __('Change') }}</p>
                                    </div>
                                </div>
                            @elseif ($image_path)
                                <div class="relative group w-full aspect-square mb-2 overflow-hidden rounded-md bg-muted">
                                    <img src="{{ Storage::url($image_path) }}" class="w-full h-full object-cover" alt="Current Image">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <p class="text-white text-xs font-medium">{{ __('Change') }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="mb-2 text-muted-foreground">
                                    <x-heroicon-o-photo class="w-12 h-12 mx-auto mb-2 opacity-50" />
                                    <p class="text-xs text-center">{{ __('No image uploaded') }}</p>
                                </div>
                            @endif
                            <input
                                id="image"
                                type="file"
                                wire:model="image"
                                class="w-full text-xs text-muted-foreground
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-xs file:font-semibold
                                    file:bg-primary file:text-primary-foreground
                                    hover:file:bg-primary/90 cursor-pointer"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('image')" />
                    </div>

                    <!-- Notes -->
                    <div class="space-y-2">
                        <x-input-label for="notes" value="{{ __('Notes') }}" />
                        <textarea
                            id="notes"
                            wire:model="notes"
                            rows="5"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="{{ __('Optional notes about this asset...') }}"
                        ></textarea>
                        <x-input-error :messages="$errors->get('notes')" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
