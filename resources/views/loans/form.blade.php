@props(['loan' => null])

@php
    $isEdit = !is_null($loan);
    $action = $isEdit ? route('loans.update', $loan) : route('loans.store');
    $method = $isEdit ? 'PUT' : 'POST';

    // Prepare initial data (Server Side > Old Input > Defaults)
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
             // ... existing edit logic ...
            $isAsset = $item->type === \App\Enums\LoanItemType::Asset;
            $productName = $isAsset
                ? ($item->asset?->product?->name ?? 'Unknown')
                : ($item->consumableStock?->product?->name ?? 'Unknown');

             // Construct Label to match SearchController: Product (Tag/Qty) (Location - Site)
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
        foreach($oldItems as $oldItem) {
             // We need to fetch label information since it's not in the POST data
            $unifiedValue = null;
            $unifiedLabel = '';
            $type = $oldItem['type'] ?? 'asset';

            if ($type === 'asset' && !empty($oldItem['asset_id'])) {
                $unifiedValue = 'asset_' . $oldItem['asset_id'];
                $asset = \App\Models\Asset::with('product', 'location')->find($oldItem['asset_id']);
                if($asset) {
                    $loc = $asset->location ? "({$asset->location->full_name})" : '';
                    $unifiedLabel = "{$asset->product->name} {$loc}";
                }
            } elseif ($type === 'consumable' && !empty($oldItem['consumable_stock_id'])) {
                $unifiedValue = 'stock_' . $oldItem['consumable_stock_id'];
                $stock = \App\Models\ConsumableStock::with('product', 'location')->find($oldItem['consumable_stock_id']);
                if($stock) {
                    $loc = $stock->location ? "({$stock->location->full_name})" : '';
                    $unifiedLabel = "{$stock->product->name} {$loc}";
                }
            }

            $initialData['items'][] = [
                '_key' => (string) \Illuminate\Support\Str::uuid(),
                'type' => $type,
                'asset_id' => $oldItem['asset_id'] ?? null,
                'consumable_stock_id' => $oldItem['consumable_stock_id'] ?? null,
                'quantity_borrowed' => $oldItem['quantity_borrowed'] ?? 1,
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Borrower -->
        <div class="space-y-4">
            <h3 class="text-md font-medium text-foreground">Borrower Details</h3>
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
            <h3 class="text-md font-medium text-foreground">Loan Terms</h3>
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
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-foreground">Loan Items</h3>
            <x-secondary-button @click="addItem()" type="button">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Add Item
            </x-secondary-button>
        </div>

        <div class="overflow-x-auto border rounded-md">
            <table class="w-full text-sm text-left">
                <thead class="bg-muted text-muted-foreground uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 w-32">Type</th>
                        <th class="px-4 py-3 min-w-[300px]">Item (Search)</th>
                        <th class="px-4 py-3 w-24">Qty</th>
                        <th class="px-4 py-3 w-16 text-center">Action</th>
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
                                    <option value="asset">Asset</option>
                                    <option value="consumable">Consumable</option>
                                </select>
                            </td>

                            <!-- Search Input (Dynamic URL based on Type) -->
                            <td class="px-4 py-3 align-top">
                                <div class="w-full">
                                    <x-searchable-select
                                        :url="route('ajax.unified')"
                                        x-bind:data-type="item.type"
                                        placeholder="Search Asset or Consumable..."
                                        x-model="item.unified_value"
                                        x-init="$watch('item.unified_label', v => query = v); query = item.unified_label"
                                        input-class="h-9 w-full"
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
                                    :class="{'bg-gray-100 dark:bg-zinc-800': item.type === 'asset'}"
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
                    No items added. Click "Add Item" to start.
                </div>
            </template>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
        <x-secondary-button tag="a" href="{{ route('loans.index') }}">
            <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
            Cancel
        </x-secondary-button>
        <x-primary-button type="submit">
            <x-heroicon-o-check class="w-4 h-4 mr-2" />
            {{ $isEdit ? 'Update Loan' : 'Create Loan' }}
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

            init() {
                // Initialize with server data first
                this.form = { ...this.form, ...initialData };

                // If creating, try to restore draft
                // Check if oldInput is effectively empty
                const hasOldInput = oldInput && Object.keys(oldInput).length > 0;

                if (!this.isEdit && !hasOldInput) {
                    const draft = localStorage.getItem('loan_create_draft_v2');
                    if (draft) {
                        try {
                            const parsed = JSON.parse(draft);
                            // Merge draft into form
                            this.form = { ...this.form, ...parsed };
                        } catch(e) {
                            console.error("Failed to restore draft", e);
                        }
                    }
                }

                // If oldInput exists (validation error), it overrides everything
                // But typically standard submission reloads page with 'value' attributes.
                // However, we are using x-model. x-model overrides 'value' initial attribute!
                // So if we have oldInput, we MUST update x-model state.
                if (oldInput) {
                    // Logic to map oldInput flattened array to our structure?
                    // Actually, if we pass oldInput as initialData component props (PHP side), it's easier.
                    // But here we rely on 'value' attributes? No, x-model ignores them.
                    // We must manually set this.form properties if oldInput is present.
                    // Ideally, PHP's $initialData handles old() calls.
                    // AND PHP's $initialData handles items?
                    // "items" in old input are array.
                    // Implementing full old-input restoration for dynamic items is hard without PHP loop.
                    // We'll trust the PHP 'value' attributes?
                    // NO. x-model binds to JS state.
                    // In $initialData, we relied on $loan->items. We didn't handle old('items').
                    // It's acceptable for now to not fully restore REJECTED dynamic items from old(),
                    // BUT valid drafts should persist.
                }

                // Watch ENTIRE form to save draft
                this.$watch('form', (val) => {
                    if (!this.isEdit) {
                        localStorage.setItem('loan_create_draft_v2', JSON.stringify(val));
                    }
                }, { deep: true });
            },

            addItem() {
                this.form.items.push({
                    _key: 'item_' + Date.now() + '_' + Math.random().toString(36).substring(2),
                    type: 'asset', // Default
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

                item.type = data.type;
                item.unified_value = eventDetail.value;
                item.unified_label = data.label; // Label now contains location

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
                if(!this.isEdit) localStorage.removeItem('loan_create_draft_v2');
                this.$el.submit();
            }
        }));
    });
</script>
