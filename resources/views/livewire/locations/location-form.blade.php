<div>
    <x-modal name="location-form-modal" :title="''" maxWidth="2xl">
        <div class="p-6">
            <!-- Custom Header -->
            <div class="mb-6 space-y-1.5 text-center sm:text-left">
                <h3 class="text-lg font-semibold leading-none tracking-tight text-foreground">
                    {{ $isEditing ? __('Edit Location') : __('Create Location') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                    {{ $isEditing ? __('Make changes to your location here. Click save when you\'re done.') : __('Add a new location to your workspace. Click save when you\'re done.') }}
                </p>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Code -->
                    <x-form-input
                        name="code"
                        label="{{ __('Code') }}"
                        placeholder="{{ __('e.g. JMP2.RIT, JMP2.RMEET') }}"
                        required
                        wire:model="code"
                    />

                    <!-- Site (Searchable) -->
                    <x-searchable-select
                        name="site"
                        label="{{ __('Site') }}"
                        :options="$sites"
                        wire:model="site"
                        required
                        placeholder="{{ __('Select or search a site...') }}"
                    />
                </div>

                <!-- Name -->
                <x-form-input
                    name="name"
                    label="{{ __('Name') }}"
                    placeholder="{{ __('e.g. IT Room') }}"
                    required
                    wire:model="name"
                />

                <!-- Description -->
                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm"
                        placeholder="{{ __('Optional description...') }}"
                    ></textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-2">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', { name: 'location-form-modal' })">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <x-primary-button type="submit">
                        <x-heroicon-o-check class="w-4 h-4 mr-2" />
                        <span wire:loading.remove wire:target="save">
                            {{ $isEditing ? __('Save Changes') : __('Create Location') }}
                        </span>
                        <span wire:loading wire:target="save">
                            {{ __('Saving...') }}
                        </span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
