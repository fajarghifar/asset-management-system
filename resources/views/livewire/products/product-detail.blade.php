    <x-modal name="product-detail-modal" :title="''">
        @if($product)
            <div class="p-6">
                <!-- Custom Header -->
                <div class="mb-6 space-y-1.5 text-center sm:text-left border-b border-gray-200 pb-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                            {{ __('Product Details') }}
                        </h3>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ __('Detailed information about the product :name.', ['name' => $product->name]) }}
                    </p>
                </div>

                <div class="space-y-6">
                    <!-- Name & Code -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Code') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $product->code }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Name') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $product->name }}</p>
                        </div>
                    </div>

                    <!-- Category & Type -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Category') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $product->category->name }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Type') }}</label>
                            <div>
                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                    {{ $product->type->getLabel() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Loanable Status -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Available for Loan') }}</label>
                        <div class="flex items-center mt-1">
                            @if($product->can_be_loaned)
                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-1.5" />
                                <span class="text-sm text-green-700 font-medium">{{ __('Yes, can be loaned') }}</span>
                            @else
                                <x-heroicon-o-x-circle class="w-5 h-5 text-red-500 mr-1.5" />
                                <span class="text-sm text-red-700 font-medium">{{ __('No, internal use only') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Description') }}</label>
                        <p class="text-sm text-foreground font-medium">
                            {{ $product->description ?? '-' }}
                        </p>
                    </div>

                    <!-- Meta -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Created At') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $product->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none text-muted-foreground">{{ __('Last Updated') }}</label>
                            <p class="text-sm text-foreground font-medium">{{ $product->updated_at?->format('d M Y, H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-6 flex items-center justify-end gap-x-2 pt-4 border-t border-gray-200">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'product-detail-modal' })">
                        {{ __('Close') }}
                    </x-secondary-button>
                    <x-primary-button type="button" x-on:click="$dispatch('close-modal', { name: 'product-detail-modal' }); $dispatch('edit-product', { product: {{ $product->id }} })">
                        <x-heroicon-o-pencil-square class="w-4 h-4 mr-2" />
                        {{ __('Edit Product') }}
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
