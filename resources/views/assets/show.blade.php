<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Asset Details') }}
            </h2>
            <div class="flex items-center gap-2">
                <x-secondary-button tag="a" href="{{ route('assets.index') }}">
                    Back to List
                </x-secondary-button>
                <x-secondary-button x-data="" @click="$dispatch('move-asset', { assetId: {{ $asset->id }} })">
                    Move Asset
                </x-secondary-button>
                <x-primary-button tag="a" href="{{ route('assets.edit', $asset) }}">
                    <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                    Edit Asset
                </x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <!-- Asset Info -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm sm:rounded-lg border border-border p-6">
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Image Column -->
                    <div class="w-full md:w-1/3 space-y-4">
                        @if($asset->image_path)
                            <img src="{{ Storage::url($asset->image_path) }}" alt="Asset Image" class="w-full h-auto rounded-lg border border-border object-cover shadow-sm">
                        @else
                            <div class="w-full h-64 bg-muted rounded-lg flex items-center justify-center text-muted-foreground border border-border border-dashed">
                                <x-heroicon-o-photo class="w-12 h-12 opacity-50" />
                                <span class="ml-2">No Image</span>
                            </div>
                        @endif
                    </div>

                    <!-- Details Column -->
                    <div class="w-full md:w-2/3">
                        <div class="border-b border-border pb-4 mb-4">
                            <h4 class="text-base font-semibold text-foreground">Asset Information</h4>
                        </div>

                        <dl class="divide-y divide-border">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3">
                                <dt class="text-sm font-medium text-muted-foreground">Product Name</dt>
                                <dd class="text-sm text-foreground sm:col-span-2">{{ $asset->product->name }}</dd>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3">
                                <dt class="text-sm font-medium text-muted-foreground">Product Code</dt>
                                <dd class="text-sm text-foreground sm:col-span-2">{{ $asset->product->code }}</dd>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3">
                                <dt class="text-sm font-medium text-muted-foreground">Asset Tag</dt>
                                <dd class="text-sm text-foreground sm:col-span-2">{{ $asset->asset_tag }}</dd>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3">
                                <dt class="text-sm font-medium text-muted-foreground">Serial Number</dt>
                                <dd class="text-sm text-foreground sm:col-span-2">{{ $asset->serial_number ?? '-' }}</dd>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3">
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd class="text-sm text-foreground sm:col-span-2">
                                    @php
                                        $colorClass = match($asset->status->getColor()) {
                                            'success' => 'bg-green-100 text-green-800',
                                            'danger' => 'bg-red-100 text-red-800',
                                            'warning' => 'bg-yellow-100 text-yellow-800',
                                            'info' => 'bg-blue-100 text-blue-800',
                                            'gray' => 'bg-gray-100 text-gray-800',
                                            default => 'bg-indigo-100 text-indigo-800',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ $asset->status->getLabel() }}
                                    </span>
                                </dd>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3">
                                <dt class="text-sm font-medium text-muted-foreground">Location</dt>
                                <dd class="text-sm text-foreground sm:col-span-2">{{ $asset->location->full_name }}</dd>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3">
                                <dt class="text-sm font-medium text-muted-foreground">Purchase Date</dt>
                                <dd class="text-sm text-foreground sm:col-span-2">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</dd>
                            </div>

                            @if($asset->notes)
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 py-3">
                                    <dt class="text-sm font-medium text-muted-foreground">Notes</dt>
                                    <dd class="text-sm text-foreground sm:col-span-2 whitespace-pre-line">{{ $asset->notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="py-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h3 class="text-lg font-medium leading-6 text-foreground mb-4 px-1">Asset History</h3>
                <livewire:assets.asset-histories-table :asset-id="$asset->id" />
            </div>
        </div>
    </div>

    <livewire:assets.move-asset />
</x-app-layout>
