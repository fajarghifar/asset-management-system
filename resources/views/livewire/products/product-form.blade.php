<x-modal name="product-form-modal" :title="''" maxWidth="2xl">
    <div class="p-6">
        <!-- Custom Header -->
        <div class="mb-6 space-y-1.5 text-center sm:text-left">
            <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                {{ $isEditing ? 'Edit Product' : 'Create Product' }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{ $isEditing ? 'Make changes to your product here. Click save when you\'re done.' : 'Add a new product details below.' }}
            </p>
        </div>

        <form wire:submit="save" class="space-y-4">
            <!-- Code -->
            <x-form-input
                name="code"
                label="Code / SKU"
                type="text"
                wire:model="code"
                placeholder="e.g. HDD500, SSD258"
                required
                :messages="$errors->get('code')"
            />

            <!-- Name -->
            <x-form-input
                name="name"
                label="Name"
                type="text"
                wire:model="name"
                placeholder="e.g. MacBook Pro M3"
                required
                :messages="$errors->get('name')"
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Category (Searchable) -->
                <div
                    x-data="{
                        open: false,
                        query: '',
                        selected: @entangle('category_id'),
                        options: {{ \Illuminate\Support\Js::from($categoryOptions) }},
                        init() {
                            this.$watch('selected', (value) => {
                                if (!value) { this.query = ''; return; }
                                const option = this.options.find(o => o.value === value);
                                if (option) this.query = option.label;
                            });
                            if (this.selected) {
                                const option = this.options.find(o => o.value === this.selected);
                                if (option) this.query = option.label;
                            }
                        },
                        get filteredOptions() {
                            if (this.query === '') return this.options;
                            return this.options.filter(option =>
                                option.label.toLowerCase().includes(this.query.toLowerCase())
                            );
                        },
                        selectOption(option) {
                            this.selected = option.value;
                            this.query = option.label;
                            this.open = false;
                        }
                    }"
                    class="relative"
                >
                    <x-input-label for="category_id" value="Category" required />

                    <div class="relative mt-1">
                        <input
                            type="text"
                            x-model="query"
                            x-on:focus="open = true"
                            x-on:click.away="open = false"
                            x-on:keydown.escape="open = false"
                            placeholder="Select or search category..."
                            class="block w-full h-10 rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                            <x-heroicon-o-chevron-up-down class="w-5 h-5 text-muted-foreground" />
                        </div>
                    </div>

                    <div
                        x-show="open && filteredOptions.length > 0"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute z-10 w-full mt-1 bg-popover text-popover-foreground rounded-md shadow-lg max-h-60 overflow-auto border border-border"
                        style="display: none;"
                    >
                        <ul class="py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                            <template x-for="option in filteredOptions" :key="option.value">
                                <li
                                    x-on:click="selectOption(option)"
                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-accent hover:text-accent-foreground"
                                >
                                    <span x-text="option.label" class="block truncate" :class="{ 'font-semibold': selected === option.value, 'font-normal': selected !== option.value }"></span>
                                    <span x-show="selected === option.value" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 dark:text-indigo-400">
                                        <x-heroicon-o-check class="w-5 h-5" />
                                    </span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>

                <!-- Type -->
                <div>
                    <x-input-label for="type" value="Type" required />
                    <select
                        id="type"
                        wire:model="type"
                        class="mt-1 block w-full h-10 rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm text-foreground"
                    >
                        <option value="" disabled>Select a type...</option>
                        @foreach($typeOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>
            </div>

            <!-- Loanable -->
            <div class="flex items-center space-x-2">
                <input
                    id="can_be_loaned"
                    type="checkbox"
                    wire:model="can_be_loaned"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                >
                <label for="can_be_loaned" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    Can be loaned?
                </label>
                <x-input-error :messages="$errors->get('can_be_loaned')" class="mt-2" />
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <x-input-label for="description" value="Description" />
                <textarea
                    id="description"
                    wire:model="description"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm"
                    placeholder="Optional description..."
                ></textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'product-form-modal' })">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit">
                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                    {{ $isEditing ? __('Save Changes') : __('Create Product') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>
