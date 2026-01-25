@props(['kit' => null])

@php
    $isEdit = !is_null($kit);
    $action = $isEdit ? route('kits.update', $kit) : route('kits.store');
    $method = $isEdit ? 'PUT' : 'POST';

    $initialData = [
        'name' => old('name', $kit?->name ?? ''),
        'description' => old('description', $kit?->description ?? ''),
        'is_active' => old('is_active', $kit?->is_active ?? true),
        'items' => []
    ];

    if ($isEdit && empty(old('items'))) {
        foreach($kit->items as $item) {
            $locLabel = '';
            if ($item->location) {
                $siteLabel = $item->location->site instanceof \App\Enums\LocationSite
                    ? $item->location->site->getLabel()
                    : $item->location->site;
                $locLabel = "{$siteLabel} - {$item->location->name}";
            }

            $initialData['items'][] = [
                '_key' => (string) \Illuminate\Support\Str::uuid(),
                'product_id' => $item->product_id,
                'product_label' => $item->product?->name ?? 'Unknown',
                'product_type' => $item->product?->type?->value ?? '',
                'location_id' => $item->location_id ?? '',
                'location_label' => $locLabel,
                'quantity' => $item->quantity,
                'notes' => $item->notes ?? '',
            ];
        }
    } elseif (!empty(old('items'))) {
        foreach(old('items') as $oldItem) {
             // Fetch label for Product
            $label = '';
            $type = '';

            if (!empty($oldItem['product_id'])) {
                $p = \App\Models\Product::find($oldItem['product_id']);
                $label = $p?->name ?? 'Unknown';
                $type = $p?->type?->value ?? '';
            }

            // Fetch label for Location
            $locLabel = '';
            if (!empty($oldItem['location_id'])) {
                $l = \App\Models\Location::find($oldItem['location_id']);
                 if ($l) {
                     $siteLabel = $l->site instanceof \App\Enums\LocationSite ? $l->site->getLabel() : $l->site;
                     $locLabel = "{$siteLabel} - {$l->name}";
                }
            }

            $initialData['items'][] = [
                '_key' => (string) \Illuminate\Support\Str::uuid(),
                'product_id' => $oldItem['product_id'],
                'product_label' => $label,
                'product_type' => $type,
                'location_id' => $oldItem['location_id'] ?? '',
                'location_label' => $locLabel,
                'quantity' => $oldItem['quantity'],
                'notes' => $oldItem['notes'],
            ];
        }
    }
@endphp

<form
    method="POST"
    action="{{ $action }}"
    class="space-y-6"
    x-data="kitForm({
        initialData: @js($initialData)
    })"
>
    @csrf
    @method($method)

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 border border-red-200 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-s-x-circle class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        There were problems with your submission:
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
        <div class="space-y-4">
             <div>
                <x-input-label for="name" :value="__('Kit Name')" class="after:content-['*'] after:ml-0.5 after:text-red-500" />
                <x-text-input
                    name="name"
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    x-model="form.name"
                    required
                />
            </div>

            <div class="flex items-center space-x-2">
                <input type="hidden" name="is_active" value="0">
                <input
                    type="checkbox"
                    name="is_active"
                    id="is_active"
                    value="1"
                    x-model="form.is_active"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                >
                <label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Active</label>
            </div>
        </div>

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea
                name="description"
                id="description"
                class="flex min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 mt-1"
                x-model="form.description"
            ></textarea>
        </div>
    </div>

    <div class="border-t border-border my-6"></div>

    <!-- Items -->
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-foreground">Kit Items</h3>
            <x-secondary-button @click="addItem()" type="button">
                <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                Add Product
            </x-secondary-button>
        </div>

        <div class="overflow-visible border rounded-md">
            <table class="w-full text-sm text-left">
                <thead class="bg-muted text-muted-foreground uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 min-w-[200px]">Product / Asset</th>
                        <th class="px-4 py-3 min-w-[200px]">Location (Optional)</th>
                        <th class="px-4 py-3 w-24">Qty</th>
                        <th class="px-4 py-3">Notes</th>
                        <th class="px-4 py-3 w-16 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <template x-for="(item, index) in form.items" :key="item._key">
                        <tr>
                            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                            <input type="hidden" :name="`items[${index}][location_id]`" :value="item.location_id">

                            <td class="px-4 py-3 align-top">
                                <x-searchable-select
                                    :url="route('ajax.products')"
                                    placeholder="Search Product..."
                                    x-model="item.product_id"
                                    x-init="$watch('item.product_label', v => query = v); query = item.product_label"
                                    input-class="h-9 w-full"
                                    @option-selected="handleProductSelect(index, $event.detail)"
                                />
                            </td>

                            <td class="px-4 py-3 align-top">
                                <x-searchable-select
                                    :url="route('ajax.locations')"
                                    placeholder="Select Location"
                                    x-model="item.location_id"
                                    x-init="$watch('item.location_label', v => query = v); query = item.location_label"
                                    input-class="h-9 w-full"
                                    @option-selected="
                                        item.location_id = $event.detail.value;
                                        item.location_label = $event.detail.item.label;
                                    "
                                />
                            </td>

                            <td class="px-4 py-3 align-top">
                                <input
                                    type="number"
                                    :name="`items[${index}][quantity]`"
                                    x-model="item.quantity"
                                    min="1"
                                    class="flex h-9 w-full text-center rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 read-only:bg-muted read-only:cursor-not-allowed"
                                    :readonly="item.product_type === 'asset'"
                                    required
                                />
                            </td>

                            <td class="px-4 py-3 align-top">
                                <input
                                    type="text"
                                    :name="`items[${index}][notes]`"
                                    x-model="item.notes"
                                    class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    placeholder="Optional notes"
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
                    No items defined. Click "Add Product" to start building this kit.
                </div>
            </template>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
        <x-secondary-button tag="a" href="{{ route('kits.index') }}">
            Cancel
        </x-secondary-button>
        <x-primary-button type="submit">
            <x-heroicon-o-check class="w-4 h-4 mr-2" />
            {{ $isEdit ? 'Update Kit' : 'Create Kit' }}
        </x-primary-button>
    </div>
</form>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kitForm', ({ initialData }) => ({
            form: {
                name: '',
                description: '',
                is_active: true,
                items: []
            },

            init() {
                this.form = { ...this.form, ...initialData };
            },

            addItem() {
                this.form.items.push({
                    _key: 'kitem_' + Date.now() + '_' + Math.random().toString(36).substring(2),
                    product_id: null,
                    product_label: '',
                    product_type: '', // 'asset' or 'consumable'
                    location_id: '',
                    location_label: '',
                    quantity: 1,
                    notes: ''
                });
            },

            removeItem(index) {
                this.form.items.splice(index, 1);
            },

            handleProductSelect(index, details) {
                const item = this.form.items[index];
                item.product_id = details.value;
                item.product_label = details.item.label;
                item.product_type = details.item.type; // Set type from search result

                // Handle Asset Quantity Lock
                if (item.product_type === 'asset') {
                    item.quantity = 1;
                }
            }
        }));
    });
</script>
