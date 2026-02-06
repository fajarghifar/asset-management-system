    <div class="p-6">
        <!-- Header -->
        <div class="mb-6 space-y-1.5 text-center sm:text-left">
            <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                {{ $isEditing ? __('Edit Asset') : __('Create New Asset') }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{ $isEditing ? __('Make changes to your asset here. Click save when you\'re done.') : __('Add a new asset. Click save when you\'re done.') }}
            </p>
        </div>

        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Product -->
                <div class="col-span-1 md:col-span-2 space-y-2">
                    <x-input-label for="product_id" value="{{ __('Product') }}" required />
                    <x-tom-select
                        name="product_id"
                        wire:model="product_id"
                        id="product_id"
                        :url="route('api.products.search', ['type' => 'asset'])"
                        :options="$productOptions"
                        placeholder="{{ __('Search Product...') }}"
                        required
                    />
                    <x-input-error :messages="$errors->get('product_id')" />
                </div>

                <!-- Location -->
                <div class="col-span-1 md:col-span-2 space-y-2">
                    <x-input-label for="location_id" value="{{ __('Location') }}" required />
                    <x-tom-select
                        name="location_id"
                        wire:model="location_id"
                        id="location_id"
                        :url="route('api.locations.search')"
                        :options="$locationOptions"
                        placeholder="{{ __('Search Location...') }}"
                        required
                    />
                    <x-input-error :messages="$errors->get('location_id')" />
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

            <!-- Notes -->
            <div class="space-y-2">
                <x-input-label for="notes" value="{{ __('Notes') }}" />
                <textarea
                    id="notes"
                    wire:model="notes"
                    rows="3"
                    class="block w-full rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm"
                    placeholder="{{ __('Optional notes...') }}"
                ></textarea>
                <x-input-error :messages="$errors->get('notes')" />
            </div>

            <!-- Image Upload -->
            <div class="space-y-2">
                <x-input-label for="image" value="{{ __('Asset Image') }}" />

                @if ($image)
                    <div class="mb-2">
                        <img src="{{ $image->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-md" alt="Preview">
                    </div>
                @elseif ($image_path)
                    <div class="mb-2">
                        <img src="{{ Storage::url($image_path) }}" class="h-20 w-20 object-cover rounded-md" alt="Current Image">
                    </div>
                @endif

                <input
                    id="image"
                    type="file"
                    wire:model="image"
                    class="block w-full text-sm text-slate-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-violet-50 file:text-violet-700
                        hover:file:bg-violet-100"
                />
                <x-input-error :messages="$errors->get('image')" />
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button tag="a" href="{{ route('assets.index') }}">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit">
                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                    {{ $isEditing ? __('Save Changes') : __('Create New Asset') }}
                </x-primary-button>
            </div>
        </form>
    </div>
