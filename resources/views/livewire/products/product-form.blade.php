<x-modal name="product-form-modal" :title="''">
    <div class="p-6">
        <!-- Custom Header -->
        <div class="mb-6 space-y-1.5 text-center sm:text-left border-b border-gray-200 pb-4">
            <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                {{ $isEditing ? __('Edit Product') : __('Create Product') }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{ $isEditing ? __('Make changes to your product here. Click save when you\'re done.') : __('Add a new product details below.') }}
            </p>
        </div>

        <form wire:submit="save" class="space-y-4">
            <!-- Code -->
            <div class="space-y-2">
                <x-input-label for="code" value="{{ __('Code') }}" required />
                <div class="flex gap-2">
                    <div class="flex-1">
                        <x-form-input
                            name="code"
                            type="text"
                            wire:model="code"
                            placeholder="{{ __('e.g. PRD.2025.001') }}"
                            required
                            class="w-full"
                        />
                    </div>
                    <x-secondary-button type="button" wire:click="generateCode" title="{{ __('Generate Code') }}">
                        <x-heroicon-o-arrow-path class="w-4 h-4" />
                    </x-secondary-button>
                </div>
                <x-input-error :messages="$errors->get('code')" />
            </div>

            <!-- Name -->
            <x-form-input
                name="name"
                label="{{ __('Name') }}"
                type="text"
                wire:model="name"
                placeholder="{{ __('e.g. MacBook Pro M3') }}"
                required
                :messages="$errors->get('name')"
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Category (Tom Select) -->
                <div class="space-y-2">
                    <x-input-label for="category_id" value="{{ __('Category') }}" required />
                    <x-tom-select
                        id="category_id"
                        wire:model="category_id"
                        :options="$categoryOptions"
                        :url="route('ajax.categories.search')"
                        method="POST"
                        placeholder="{{ __('Select category...') }}"
                    />
                    <x-input-error :messages="$errors->get('category_id')" />
                </div>

                <!-- Type -->
                <div class="space-y-2">
                    <x-input-label for="type" value="{{ __('Type') }}" required />
                    <x-tom-select
                        id="type"
                        wire:model="type"
                        :options="$typeOptions"
                        placeholder="{{ __('Select a type...') }}"
                    />
                    <x-input-error :messages="$errors->get('type')" />
                </div>
            </div>

            <!-- Loanable -->
            <div class="flex items-center space-x-2">
                <input
                    id="can_be_loaned"
                    type="checkbox"
                    wire:model="can_be_loaned"
                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                >
                <label for="can_be_loaned" class="text-sm font-medium leading-none text-foreground peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    {{ __('Can be loaned?') }}
                </label>
                <x-input-error :messages="$errors->get('can_be_loaned')" />
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <x-input-label for="description" value="{{ __('Description') }}" />
                <textarea
                    id="description"
                    wire:model="description"
                    rows="3"
                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    placeholder="{{ __('Optional description...') }}"
                ></textarea>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'product-form-modal' })">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit" wire:loading.attr="disabled">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <x-heroicon-o-check wire:loading.remove wire:target="save" class="w-4 h-4 mr-2" />
                    {{ $isEditing ? __('Save Changes') : __('Create Product') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>
