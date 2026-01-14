@props([
    'name',
    'label',
    'options' => [],
    'value' => null,
    'placeholder' => 'Select option...',
    'required' => false,
    'disabled' => false,
    'url' => null,
])

<div
    x-data="{
        open: false,
        query: '',
        selected: @js($value),
        options: @js($options),
        isLoading: false,
        init() {
            // If checking existing value against local options
            if (this.selected && this.options.length > 0) {
                const option = this.options.find(o => o.value == this.selected);
                if (option) this.query = option.label;
            }

            this.$watch('selected', (value) => {
                 if (!value) {
                     // Only clear query if it's not a search-based selection flow (optional)
                     // But for now, if value is cleared externally?
                 }
            });

            this.$watch('query', (value) => {
                if ('{{ $url }}' && value.length > 0) {
                    this.fetchOptions(value);
                }
            });
        },
        async fetchOptions(search) {
            this.isLoading = true;
            try {
                // Remove trailing slash if needed, usually route() is fine.
                const res = await fetch('{{ $url }}?q=' + encodeURIComponent(search));
                if (res.ok) {
                    this.options = await res.json();
                }
            } catch (e) {
                console.error(e);
            }
            this.isLoading = false;
        },
        get filteredOptions() {
            if ('{{ $url }}') return this.options;
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
    <x-input-label :for="$name" :value="$label" :required="$required" />

    <div class="relative mt-1">
        <!-- Hidden Input for Form Submission -->
        <input type="hidden" name="{{ $name }}" :value="selected">

        <!-- Display Input for Search -->
        <input
            type="text"
            x-model.debounce.400ms="query"
            x-on:focus="!{{ $disabled ? 'true' : 'false' }} && (open = true)"
            x-on:click.away="open = false"
            x-on:keydown.escape="open = false"
            placeholder="{{ $placeholder }}"
            class="block w-full h-10 rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-500 disabled:cursor-not-allowed {{ $errors->has($name) ? 'border-red-500' : '' }}"
            {{ $disabled ? 'disabled' : '' }}
        />
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <template x-if="isLoading">
                <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </template>
            <template x-if="!isLoading">
                <x-heroicon-o-chevron-up-down class="w-5 h-5 text-muted-foreground" />
            </template>
        </div>
    </div>

    <!-- Dropdown -->
    <div
        x-show="open && (filteredOptions.length > 0 || isLoading)"
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
                    <span x-text="option.label" class="block truncate" :class="{ 'font-semibold': selected == option.value, 'font-normal': selected != option.value }"></span>
                    <span x-show="selected == option.value" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600 dark:text-indigo-400">
                        <x-heroicon-o-check class="w-5 h-5" />
                    </span>
                </li>
            </template>
        </ul>
    </div>

    <x-input-error :messages="$errors->get($name)" class="mt-2" />
</div>
