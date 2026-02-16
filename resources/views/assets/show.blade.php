<x-app-layout title="{{ __('Asset Details') }}">


    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-foreground">{{ $asset->product->name }}</h2>
                    <div class="flex flex-wrap items-center gap-2 text-muted-foreground mt-1">
                        <span class="text-sm font-medium bg-muted px-2 py-0.5 rounded">{{ $asset->asset_tag }}</span>
                        <span>&bull;</span>
                        <span class="text-sm">{{ $asset->location->full_name }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-secondary-button tag="a" href="{{ route('assets.index') }}">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                        {{ __('Back to List') }}
                    </x-secondary-button>
                    <x-secondary-button x-data="" @click="$dispatch('move-asset', { assetId: {{ $asset->id }} })">
                        <x-heroicon-o-arrows-right-left class="w-4 h-4 mr-2" />
                        {{ __('Move') }}
                    </x-secondary-button>
                    <x-primary-button tag="a" href="{{ route('assets.edit', $asset) }}">
                        <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                        {{ __('Edit') }}
                    </x-primary-button>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Top Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Image Only -->
                    <div class="space-y-6">
                        <!-- Image Card -->
                        <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border overflow-hidden">
                            <div class="aspect-square w-full bg-muted relative flex items-center justify-center">
                                @if($asset->image_path)
                                    <img src="{{ Storage::url($asset->image_path) }}" alt="{{ __('Asset Image') }}" class="w-full h-full object-cover">
                                @else
                                    <x-heroicon-o-photo class="w-20 h-20 text-muted-foreground/30" />
                                @endif
                                <div class="absolute top-4 right-4">
                                    @php
                                        $colorClass = match($asset->status->getColor()) {
                                            'success' => 'bg-green-100 text-green-800 border-green-200',
                                            'danger' => 'bg-red-100 text-red-800 border-red-200',
                                            'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'info' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'gray' => 'bg-gray-100 text-gray-800 border-gray-200',
                                            default => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $colorClass }}">
                                        {{ $asset->status->getLabel() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Stats, Notes, Meta -->
                    <div class="lg:col-span-2 space-y-6">
                         <!-- Quick Stats (Moved from Left) -->
                        <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border p-5 space-y-4">
                            <div class="flex justify-between items-center pb-3 border-b border-border last:border-0 last:pb-0">
                                <span class="text-sm text-muted-foreground">{{ __('Serial Number') }}</span>
                                <span class="text-sm font-medium font-mono">{{ $asset->serial_number ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-border last:border-0 last:pb-0">
                                <span class="text-sm text-muted-foreground">{{ __('Product Code') }}</span>
                                <span class="text-sm font-medium font-mono">{{ $asset->product->code }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-border last:border-0 last:pb-0">
                                <span class="text-sm text-muted-foreground">{{ __('Purchase Date') }}</span>
                                <span class="text-sm font-medium">{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->format('d M Y') : '-' }}</span>
                            </div>
                        </div>

                        <!-- Info/Notes -->
                        @if($asset->notes)
                            <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border p-6">
                                <h3 class="font-semibold text-lg mb-4">{{ __('Notes') }}</h3>
                                <div class="text-sm text-muted-foreground whitespace-pre-line leading-relaxed">
                                    {{ $asset->notes }}
                                </div>
                            </div>
                        @endif

                        <!-- System Meta -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border p-4">
                                <span class="text-xs text-muted-foreground block mb-1">{{ __('Created At') }}</span>
                                <span class="text-sm font-medium block">{{ $asset->created_at?->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border p-4">
                                <span class="text-xs text-muted-foreground block mb-1">{{ __('Last Updated') }}</span>
                                <span class="text-sm font-medium block">{{ $asset->updated_at?->format('d M Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section: History -->
                <div class="bg-card text-card-foreground shadow-sm rounded-xl border border-border p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-semibold text-lg">{{ __('Asset History') }}</h3>
                    </div>
                    <livewire:assets.asset-histories-table :asset-id="$asset->id" />
                </div>
            </div>
        </div>
    </div>

    <livewire:assets.move-asset />
</x-app-layout>
