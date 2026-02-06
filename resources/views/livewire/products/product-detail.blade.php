<div>
    <x-modal name="product-detail-modal" :title="''" maxWidth="lg">
        @if($product)
            <div class="p-6">
                <!-- Custom Header -->
                <div class="mb-6 space-y-1.5 text-center sm:text-left">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                            {{ __('Product Details') }}
                        </h3>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ __('Detailed information about the product :name.', ['name' => $product->name]) }}
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- Name & Code -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ __('Code') }}
                        </label>
                        <p class="text-sm text-muted-foreground">{{ $product->code }}</p>
                    </div>

                    <!-- Name -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ __('Name') }}
                        </label>
                        <p class="text-sm text-foreground">{{ $product->name }}</p>
                    </div>

                    <!-- Category & Type -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                {{ __('Category') }}
                            </label>
                            <p class="text-sm text-foreground">{{ $product->category->name }}</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                {{ __('Type') }}
                            </label>
                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20">
                                {{ $product->type->getLabel() }}
                            </span>
                        </div>
                    </div>

                    <!-- Loanable Status -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ __('Available for Loan') }}
                        </label>
                        <div class="flex items-center">
                            @if($product->can_be_loaned)
                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-1.5" />
                                <span class="text-sm text-green-700 dark:text-green-400">{{ __('Yes, can be loaned') }}</span>
                            @else
                                <x-heroicon-o-x-circle class="w-5 h-5 text-red-500 mr-1.5" />
                                <span class="text-sm text-red-700 dark:text-red-400">{{ __('No, internal use only') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            {{ __('Description') }}
                        </label>
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            {{ $product->description ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="mt-6 flex items-center justify-end gap-x-2">
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
</div>
