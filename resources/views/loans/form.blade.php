@props(['loan' => null])

@php
    $isEdit = !is_null($loan);
    $action = $isEdit ? route('loans.update', $loan) : route('loans.store');
    $method = $isEdit ? 'PUT' : 'POST';

    // Initial Data
    $initialData = [
        'borrower_name' => old('borrower_name', $loan?->borrower_name ?? ''),
        'loan_date' => old('loan_date', $loan?->loan_date?->format('Y-m-d') ?? now()->format('Y-m-d')),
        'due_date' => old('due_date', $loan?->due_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d')),
        'purpose' => old('purpose', $loan?->purpose ?? ''),
        'notes' => old('notes', $loan?->notes ?? ''),
        'items' => []
    ];

    if ($isEdit && empty(old('items'))) {
        foreach($loan->items as $item) {
            $isAsset = $item->type === \App\Enums\LoanItemType::Asset;
            $productName = $isAsset
                ? ($item->asset?->product?->name ?? __('Unknown'))
                : ($item->consumableStock?->product?->name ?? __('Unknown'));

             // Construct Label: Product (Tag/Qty) (Location - Site)
            $location = $isAsset ? $item->asset?->location : $item->consumableStock?->location;
            $locName = $location?->name;
            $siteLabel = $location?->site?->getLabel();

            $locString = '';
            if ($locName) {
                $locString = "({$locName}" . ($siteLabel ? " - {$siteLabel}" : "") . ")";
            }

            $assetTag = $isAsset ? ($item->asset?->asset_tag ?? '-') : '';
            $stockQty = !$isAsset ? ($item->consumableStock?->quantity ?? 0) : 0;

            $initialData['items'][] = [
                '_key' => (string) \Illuminate\Support\Str::uuid(),
                'type' => $item->type->value,
                'asset_id' => $item->asset_id,
                'consumable_stock_id' => $item->consumable_stock_id,
                'quantity_borrowed' => $item->quantity_borrowed,
                'unified_value' => $isAsset ? 'asset_' . $item->asset_id : 'stock_' . $item->consumable_stock_id,
                'unified_label' => $isAsset
                    ? "{$productName} ({$assetTag}) {$locString}"
                    : "{$productName} (Stock: {$stockQty}) {$locString}",
            ];
        }
    } elseif (!empty(old('items'))) {
        // Restore items from validation failure
        $oldItems = old('items');
        foreach($oldItems as $index => $oldItem) {
             // We need to fetch label information since it's not in the POST data
            $type = $oldItem['type'] ?? 'asset';
            $assetId = $oldItem['asset_id'] ?? null;
            $stockId = $oldItem['consumable_stock_id'] ?? null;
            $qty = $oldItem['quantity_borrowed'] ?? 1;

            $unifiedValue = null;
            $unifiedLabel = '';

            if ($type === 'asset' && $assetId) {
                $unifiedValue = 'asset_' . $assetId;
                $asset = \App\Models\Asset::with('product', 'location')->find($assetId);
                if($asset) {
                    $loc = $asset->location ? "({$asset->location->site->getLabel()} - {$asset->location->name})" : '';
                    $unifiedLabel = "{$asset->asset_tag} | {$asset->product->name} {$loc}";
                } else {
                     $unifiedLabel = __('Unknown Asset');
                }
            } elseif ($type === 'consumable' && $stockId) {
                $unifiedValue = 'stock_' . $stockId;
                $stock = \App\Models\ConsumableStock::with('product', 'location')->find($stockId);
                if($stock) {
                    $loc = $stock->location ? "({$stock->location->site->getLabel()} - {$stock->location->name})" : '';
                    $unifiedLabel = "{$stock->product->name} (Stock: {$stock->quantity}) {$loc}";
                } else {
                    $unifiedLabel = __('Unknown Stock');
                }
            }

            $initialData['items'][] = [
                '_key' => (string) \Illuminate\Support\Str::uuid(),
                'type' => $type,
                'asset_id' => $assetId,
                'consumable_stock_id' => $stockId,
                'quantity_borrowed' => $qty,
                'unified_value' => $unifiedValue,
                'unified_label' => $unifiedLabel,
            ];
        }
    }
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6"
    enctype="multipart/form-data"
    x-data="loanForm({
        isEdit: @js($isEdit),
        initialData: @js($initialData),
        oldInput: @js(session()->getOldInput())
    })"
    x-on:submit.prevent="submitForm"
>
    @csrf
    @method($method)

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 border border-red-200 mb-6" x-init="$dispatch('toast', { message: '{{ __('Validation failed. Please check the form.') }}', type: 'error' })">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-s-x-circle class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        {{ __('There were problems with your submission:') }}
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul role="list" class="list-disc leading-tight pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Borrower -->
        <div class="space-y-4">
            <h3 class="text-md font-medium text-foreground">{{ __('Borrower Details') }}</h3>
            <div>
                <x-input-label for="borrower_name" :value="__('Borrower Name')" class="after:content-['*'] after:ml-0.5 after:text-red-500" />
                <x-text-input
                    name="borrower_name"
                    id="borrower_name"
                    type="text"
                    class="mt-1 block w-full"
                    x-model="form.borrower_name"
                    required
                />
            </div>
            <div>
                <x-input-label for="proof_image" :value="__('Proof Image (Optional)')" />
                <input
                    type="file"
                    name="proof_image"
                    id="proof_image"
                    class="mt-1 block w-full text-sm text-muted-foreground
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-primary file:text-primary-foreground
                        hover:file:bg-primary/90"
                    accept="image/*"
                />
                @if($isEdit && $loan->proof_image)
                    <div class="mt-2 text-xs text-green-600">
                        Current file: <a href="{{ Storage::url($loan->proof_image) }}" target="_blank" class="underline">View</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Terms -->
        <div class="space-y-4">
            <h3 class="text-md font-medium text-foreground">{{ __('Loan Terms') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="loan_date" :value="__('Loan Date')" class="after:content-['*'] after:ml-0.5 after:text-red-500" />
                    <x-text-input
                        name="loan_date"
                        id="loan_date"
                        type="date"
                        class="mt-1 block w-full"
                        x-model="form.loan_date"
                        required
                    />
                </div>
                <div>
                    <x-input-label for="due_date" :value="__('Due Date')" class="after:content-['*'] after:ml-0.5 after:text-red-500" />
                    <x-text-input
                        name="due_date"
                        id="due_date"
                        type="date"
                        class="mt-1 block w-full"
                        x-model="form.due_date"
                        required
                    />
                </div>
            </div>
            <div>
                <x-input-label for="purpose" :value="__('Purpose')" class="after:content-['*'] after:ml-0.5 after:text-red-500" />
                <textarea
                    name="purpose"
                    id="purpose"
                    class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 mt-1"
                    placeholder="e.g., Project A site visit"
                    x-model="form.purpose"
                    required
                ></textarea>
            </div>
            <div>
                <x-input-label for="notes" :value="__('Notes')" />
                <textarea
                    name="notes"
                    id="notes"
                    class="flex min-h-[60px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 mt-1"
                    placeholder="Optional details..."
                    x-model="form.notes"
                ></textarea>
            </div>

        </div>
    </div>

    <div class="border-t border-border my-6"></div>

    <!-- Items -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-lg font-medium text-foreground">{{ __('Loan Items') }}</h3>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                 <!-- Kit Loading -->
                <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto" x-data="{ kitId: '', locId: '' }">
                    <select x-model="kitId" class="flex h-9 w-full sm:w-64 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                        <option value="">{{ __('Select Kit...') }}</option>
                        @foreach(\App\Models\Kit::where('is_active', true)->get() as $kit)
                            <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                        @endforeach
                    </select>

                    <div class="w-full sm:w-64">
                        <x-tom-select
                            :url="route('ajax.locations.search')"
                            method="POST"
                            placeholder="{{ __('Priority Loc (Opt)...') }}"
                            x-model="locId"
                            @option-selected="locId = $event.detail.value"
                        />
                    </div>

                    <x-secondary-button @click.prevent="loadKit(kitId, locId)" x-bind:disabled="!kitId" type="button" class="w-full sm:w-auto justify-center">
                        <x-heroicon-o-archive-box-arrow-down class="w-4 h-4 mr-2" />
                        {{ __('Load Kit') }}
                    </x-secondary-button>
                </div>

                <x-secondary-button @click="addItem()" type="button" class="w-full sm:w-auto justify-center">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    {{ __('Add Item') }}
                </x-secondary-button>
            </div>
        </div>

        <div class="overflow-x-auto border rounded-md">
            <table class="w-full text-sm text-left">
                <thead class="bg-muted text-muted-foreground uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 w-32">{{ __('Type') }}</th>
                        <th class="px-4 py-3 min-w-[300px]">{{ __('Item (Search)') }}</th>
                        <th class="px-4 py-3 w-24">{{ __('Qty') }}</th>
                        <th class="px-4 py-3 w-16 text-center">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <template x-for="(item, index) in form.items" :key="item._key">
                        <tr>
                            <!-- Hidden Inputs -->
                            <input type="hidden" :name="`items[${index}][type]`" :value="item.type">
                            <input type="hidden" :name="`items[${index}][asset_id]`" :value="item.asset_id">
                            <input type="hidden" :name="`items[${index}][consumable_stock_id]`" :value="item.consumable_stock_id">

                            <!-- Type Selector -->
                            <td class="px-4 py-3 align-top">
                                <select
                                    x-model="item.type"
                                    @change="item.unified_value = null; item.unified_label = ''; item.asset_id = null; item.consumable_stock_id = null; if(item.type === 'asset') item.quantity_borrowed = 1;"
                                    class="flex h-9 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="asset">{{ __('Asset') }}</option>
                                    <option value="consumable">{{ __('Consumable') }}</option>
                                </select>
                            </td>

                            <!-- Search Input (Dynamic URL based on Type) -->
                            <td class="px-4 py-3 align-top">
                                <div class="w-full">
                                    <x-tom-select
                                        :url="route('ajax.loans.items.search')"
                                        method="POST"
                                        x-bind:data-params="JSON.stringify({ type: item.type })"
                                        x-bind:placeholder="!item.unified_value ? item.unified_label : '{{ __('Search Asset or Consumable...') }}'"
                                        x-model="item.unified_value"
                                        x-bind:data-initial-label="item.unified_label"
                                        x-bind:data-initial-search="!item.unified_value ? item.unified_label : ''"
                                        class="w-full"
                                        @option-selected="updateItem(index, $event.detail)"
                                    />
                                </div>
                            </td>

                            <td class="px-4 py-3 align-top">
                                <input
                                    type="number"
                                    :name="`items[${index}][quantity_borrowed]`"
                                    x-model="item.quantity_borrowed"
                                    min="1"
                                    class="flex h-9 w-20 text-center rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    :readonly="item.type === 'asset'"
                                    :class="{'bg-muted': item.type === 'asset'}"
                                    required
                                />
                            </td>
                            <td class="px-4 py-3 align-top text-center">
                                <button @click="removeItem(index)" type="button" class="text-destructive hover:text-destructive/80 transition-colors pt-2">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <template x-if="form.items.length === 0">
                 <div class="px-4 py-8 text-center text-muted-foreground">
                     {{ __('No items added. Click "Add Item" to start.') }}
                </div>
            </template>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4 border-t border-border">
        <x-secondary-button type="button" x-on:click="window.history.back()" class="w-full sm:w-auto justify-center">
            <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
            {{ __('Cancel') }}
        </x-secondary-button>
        <x-primary-button type="submit" x-bind:disabled="isSubmitting" class="w-full sm:w-auto justify-center">
            <template x-if="isSubmitting">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </template>
            <template x-if="!isSubmitting">
                <x-heroicon-o-check class="w-4 h-4 mr-2" />
            </template>
            {{ $isEdit ? __('Update Loan') : __('Create Loan') }}
        </x-primary-button>
    </div>
</form>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('loanForm', ({ isEdit, initialData, oldInput }) => ({
            form: {
                borrower_name: '',
                loan_date: '',
                due_date: '',
                purpose: '',
                notes: '',
                items: []
            },
            isEdit: isEdit,
            isSubmitting: false,

            init() {
                this.form = { ...this.form, ...initialData };

                // Handle Draft Restoration for Create Mode
                const hasOldInput = oldInput && Object.keys(oldInput).length > 0;
                if (!this.isEdit && !hasOldInput) {
                    const draft = localStorage.getItem('loan_create_draft_v2');
                    if (draft) {
                        try {
                            this.form = { ...this.form, ...JSON.parse(draft) };
                        } catch(e) {
                            console.error("Draft restore failed", e);
                        }
                    }
                }

                // Handle Autosave
                this.$watch('form', (val) => {
                    if (!this.isEdit) {
                        localStorage.setItem('loan_create_draft_v2', JSON.stringify(val));
                    }
                }, { deep: true });
            },

            async loadKit(kitId, locId) {
                if (!kitId) return;

                this.$dispatch('toast', { message: '{{ __('Loading kit items...') }}', type: 'info' });

                try {
                    const response = await fetch(`/kits/${kitId}/resolve?location_id=${locId}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const result = await response.json();

                    if (result.items && result.items.length > 0) {
                        result.items.forEach(newItem => {
                            // If item_id is NULL, it means not found/fallback -> User must search
                            const isResolved = newItem.item_id !== null;
                            const unifiedVal = isResolved
                                ? (newItem.type === 'Asset' ? 'asset_' : 'stock_') + newItem.item_id
                                : null;

                            this.form.items.push({
                                _key: 'item_' + Date.now() + '_' + Math.random().toString(36).substring(2),
                                type: newItem.type.toLowerCase(),
                                asset_id: (isResolved && newItem.type === 'Asset') ? newItem.item_id : null,
                                consumable_stock_id: (isResolved && newItem.type === 'Consumable') ? newItem.item_id : null,
                                quantity_borrowed: newItem.quantity,
                                unified_value: unifiedVal,
                                unified_label: newItem.item_label, // Will contain "Product Name" or resolved format
                            });
                        });
                        this.$dispatch('toast', { message: `{{ __('Loaded :count items from kit.', ['count' => '${result.items.length}']) }}`.replace(':count', result.items.length), type: 'success' });
                    } else {
                        this.$dispatch('toast', { message: result.message || '{{ __('No items found.') }}', type: 'warning' });
                    }
                } catch (error) {
                    console.error('Kit load failed', error);
                    this.$dispatch('toast', { message: '{{ __('Failed to load kit.') }}', type: 'error' });
                }
            },

            addItem() {
                this.form.items.push({
                    _key: 'item_' + Date.now() + '_' + Math.random().toString(36).substring(2),
                    type: 'asset',
                    asset_id: null,
                    consumable_stock_id: null,
                    quantity_borrowed: 1,
                    unified_value: null,
                    unified_label: '',
                });
            },

            removeItem(index) {
                this.form.items.splice(index, 1);
            },

            updateItem(index, eventDetail) {
                const data = eventDetail.item;
                const item = this.form.items[index];

                const item = this.form.items[index];

                // Ensure type consistency
                if (data.type) {
                     item.type = data.type;
                }

                item.unified_value = eventDetail.value;
                item.unified_label = data.text;

                if (data.type === 'asset') {
                    item.asset_id = data.id;
                    item.consumable_stock_id = null;
                    item.quantity_borrowed = 1;
                } else {
                    item.asset_id = null;
                    item.consumable_stock_id = data.id;
                }
            },

            submitForm() {
                this.isSubmitting = true;
                if(!this.isEdit) localStorage.removeItem('loan_create_draft_v2');
                this.$el.submit();
            }
        }));
    });
</script>
