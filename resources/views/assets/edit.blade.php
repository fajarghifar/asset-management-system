<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Edit Asset') }}
            </h2>
            <div class="flex items-center gap-2">
                <x-secondary-button x-data="" @click="$dispatch('move-asset', { assetId: {{ $asset->id }} })">
                    Move Asset
                </x-secondary-button>
                <x-secondary-button tag="a" href="{{ route('assets.show', $asset) }}">
                    View Details
                </x-secondary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm sm:rounded-lg border border-border p-6">
                <!-- Header -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium leading-6 text-foreground">Asset Details</h3>
                    <p class="mt-1 text-sm text-muted-foreground">Update asset information and status.</p>
                </div>

                <form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Product (Read-only) -->
                        <div class="col-span-1 md:col-span-2">
                            <x-input-label for="product_id" value="Product" />
                            <select id="product_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-input bg-muted text-muted-foreground sm:text-sm rounded-md shadow-sm cursor-not-allowed" disabled>
                                <option>{{ $asset->product->name }} ({{ $asset->product->code }})</option>
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Product cannot be changed.</p>
                        </div>

                        <!-- Location (Read-only) -->
                        <div>
                            <x-input-label for="location_id" value="Location" />
                            <select id="location_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-input bg-muted text-muted-foreground sm:text-sm rounded-md shadow-sm cursor-not-allowed" disabled>
                                <option>{{ $asset->location->full_name ?? $asset->location->name }}</option>
                            </select>
                            <p class="text-xs text-muted-foreground mt-1">Use 'Move Asset' in list to change location.</p>
                        </div>

                        <!-- Status (Changing triggers history) -->
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-input bg-background focus:ring-ring focus:border-ring sm:text-sm rounded-md shadow-sm">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ (old('status') ?? $asset->status->value) == $status->value ? 'selected' : '' }}>
                                        {{ $status->getLabel() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Asset Tag -->
                        <div>
                            <x-form-input name="asset_tag" label="Asset Tag" :value="old('asset_tag', $asset->asset_tag)" readonly class="bg-muted text-muted-foreground cursor-not-allowed" />
                            <p class="text-xs text-muted-foreground mt-1">Asset Tag cannot be changed.</p>
                        </div>

                        <!-- Serial Number -->
                        <div>
                            <x-form-input name="serial_number" label="Serial Number" :value="old('serial_number', $asset->serial_number)" />
                        </div>

                        <!-- Purchase Info -->
                        <div>
                            <x-form-input type="date" name="purchase_date" label="Purchase Date" :value="old('purchase_date', $asset->purchase_date?->format('Y-m-d'))" />
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <x-input-label for="notes" value="General Notes" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-input bg-background focus:ring-ring focus:border-ring rounded-md shadow-sm sm:text-sm">{{ old('notes', $asset->notes) }}</textarea>
                    </div>

                    <!-- Image -->
                    <div>
                        <x-input-label for="image_path" value="Update Image" />
                        @if($asset->image_path)
                            <div class="mb-2">
                                <img src="{{ Storage::url($asset->image_path) }}" alt="Asset Image" class="h-20 w-20 object-cover rounded-md border border-border">
                            </div>
                        @endif
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
                            Update Asset
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- History Table -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <h3 class="text-lg font-medium leading-6 text-foreground mb-4 px-1">Asset History</h3>
            <livewire:assets.asset-histories-table :asset-id="$asset->id" />
        </div>
    </div>
</x-app-layout>

<livewire:assets.move-asset />
