<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Create New Asset') }}
            </h2>
            <x-secondary-button tag="a" href="{{ route('assets.index') }}">
                {{ __('Back to List') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm sm:rounded-lg border border-border p-6">
                <!-- Header -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium leading-6 text-foreground">Asset Information</h3>
                    <p class="mt-1 text-sm text-muted-foreground">Register a new asset into the system.</p>
                </div>

                <form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product (AJAX Searchable) -->
                        <div class="col-span-1 md:col-span-2">
                            <x-searchable-select
                                name="product_id"
                                label="Product"
                                :url="route('ajax.products')"
                                :value="old('product_id')"
                                placeholder="Search Product (Name or Code)..."
                                required
                            />
                        </div>

                        <!-- Location (AJAX Searchable) -->
                        <div>
                            <x-searchable-select
                                name="location_id"
                                label="Location"
                                :url="route('ajax.locations')"
                                :value="old('location_id')"
                                placeholder="Search Location..."
                                required
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-input bg-background focus:ring-ring focus:border-ring sm:text-sm rounded-md shadow-sm">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status') == $status->value ? 'selected' : '' }}>
                                        {{ $status->getLabel() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Serial Number -->
                        <div>
                            <x-form-input name="serial_number" label="Serial Number" placeholder="Factory S/N" :value="old('serial_number')" />
                        </div>

                        <!-- Purchase Info -->
                        <div>
                            <x-form-input type="date" name="purchase_date" label="Purchase Date" :value="old('purchase_date')" />
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-input bg-background focus:ring-ring focus:border-ring rounded-md shadow-sm sm:text-sm">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <!-- Image -->
                    <div>
                        <x-input-label for="image_path" value="Asset Image" />
                        <input id="image_path" name="image_path" type="file" class="mt-1 block w-full text-sm text-muted-foreground
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-primary file:text-primary-foreground
                            hover:file:bg-primary/90
                        " />
                        <x-input-error :messages="$errors->get('image_path')" class="mt-2" />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                        <x-secondary-button tag="a" href="{{ route('assets.index') }}">
                            Cancel
                        </x-secondary-button>
                        <x-primary-button>
                            Create Asset
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
