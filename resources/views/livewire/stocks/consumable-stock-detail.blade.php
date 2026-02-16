<x-modal name="consumable-stock-detail-modal" :title="''">
    @if($stock)
        <div class="p-6">
            <!-- Custom Header -->
            <div class="mb-6 space-y-1.5 text-center sm:text-left border-b border-gray-200 pb-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                        {{ __('Stock Details') }}
                    </h3>
                </div>
                <p class="text-sm text-muted-foreground">
                    {{ __('Detailed information for :product in :location.', ['product' => $stock->product->name ?? __('Unknown Product'), 'location' => $stock->location->full_name ?? __('Unknown Location')]) }}
                </p>
            </div>

            <div class="space-y-6">
                <!-- Product Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Product Code') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $stock->product->code }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Product Name') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $stock->product->name }}</p>
                    </div>
                </div>

                <!-- Location Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Site') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $stock->location->site->getLabel() }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Location') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $stock->location->name }}</p>
                    </div>
                </div>

                <!-- Stock Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Quantity') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $stock->quantity }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Minimum Quantity') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $stock->min_quantity }}</p>
                    </div>
                </div>

                <!-- Status -->
                <div class="space-y-1">
                    <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Status') }}</label>
                    <div class="mt-1">
                        @if ($stock->quantity <= $stock->min_quantity)
                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 mr-1"><path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" /></svg>
                                {{ __('Low Stock') }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 mr-1"><path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" /></svg>
                                {{ __('Safe') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Meta -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Created At') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $stock->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Last Updated') }}</label>
                        <p class="text-sm text-foreground font-medium">{{ $stock->updated_at?->format('d M Y, H:i') ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-6 flex items-center justify-end gap-x-2 pt-4 border-t border-gray-200">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'consumable-stock-detail-modal' })">
                    {{ __('Close') }}
                </x-secondary-button>
                <x-primary-button type="button" x-on:click="$dispatch('close-modal', { name: 'consumable-stock-detail-modal' }); $dispatch('edit-stock', { stock: {{ $stock->id }} })">
                    <x-heroicon-o-pencil-square class="w-4 h-4 mr-2" />
                    {{ __('Edit Stock') }}
                </x-primary-button>
            </div>
        </div>
    @else
        <div class="p-8 text-center flex flex-col items-center justify-center space-y-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            <span class="text-sm text-muted-foreground">{{ __('Loading details...') }}</span>
        </div>
    @endif
</x-modal>
