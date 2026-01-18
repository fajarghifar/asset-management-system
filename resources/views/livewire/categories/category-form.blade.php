<x-modal name="category-form-modal" :title="''" maxWidth="2xl">
    <div class="p-6">
        <!-- Custom Header -->
        <div class="mb-6 space-y-1.5 text-center sm:text-left">
            <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                {{ $isEditing ? 'Edit Category' : 'Create Category' }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{ $isEditing ? 'Make changes to your category here. Click save when you\'re done.' : 'Add a new category. Click save when you\'re done.' }}
            </p>
        </div>

        <form wire:submit="save" class="space-y-4">
            <!-- Name -->
            <x-form-input
                name="name"
                label="Name"
                type="text"
                wire:model="name"
                placeholder="e.g. Laptops"
                required
            />

            <!-- Description -->
            <div class="space-y-2">
                <x-input-label for="description" :value="__('Description')" />
                <textarea
                    id="description"
                    wire:model="description"
                    rows="3"
                    class="block w-full rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm"
                    placeholder="Optional description..."
                ></textarea>
                <x-input-error :messages="$errors->get('description')" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'category-form-modal' })">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button type="submit">
                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                    {{ $isEditing ? __('Save Changes') : __('Create Category') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>
