<x-modal name="product-form-modal" :title="''" maxWidth="2xl">
    <div class="p-6">
        <!-- Custom Header -->
        <div class="mb-6 space-y-1.5 text-center sm:text-left">
            <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                {{ $isEditing ? __('Edit Product') : __('Create Product') }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{ $isEditing ? __('Make changes to your product here. Click save when you\'re done.') : __('Add a new product details below.') }}
            </p>
        </div>

        <form wire:submit="save" class="space-y-4">
            <!-- Code -->
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
                <!-- Category (Searchable) -->
                <div class="space-y-2">
                    <x-input-label for="category_id" value="{{ __('Category') }}" required />
                    <x-tom-select
                        id="category_id"
                        wire:model="category_id"
                        :options="$categoryOptions"
                        placeholder="{{ __('Select category...') }}"
                    />
                    <x-input-error :messages="$errors->get('category_id')" />
                </div>

                <!-- Type -->
                <div class="space-y-2">
                    <x-input-label for="type" value="{{ __('Type') }}" required />
                    <select
                        id="type"
                        wire:model="type"
                        class="flex w-full h-10 px-3 py-2 text-sm bg-transparent border rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="" disabled>{{ __('Select a type...') }}</option>
                        @foreach($typeOptions as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
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
                <label for="can_be_loaned" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
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
                    class="block w-full rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm"
                    placeholder="{{ __('Optional description...') }}"
                ></textarea>
                <x-input-error :messages="$errors->get('description')" />
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
