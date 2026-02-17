<x-app-layout title="Import Stocks">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Import Consumable Stocks') }}
            </h2>
            <x-secondary-button tag="a" href="{{ route('stocks.index') }}" class="w-full sm:w-auto justify-center">
                <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to List') }}
            </x-secondary-button>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-card overflow-hidden shadow-sm sm:rounded-lg border border-border">
                <div class="p-6 text-card-foreground">

                    <div class="mb-6">
                        <h3 class="text-lg font-medium">{{ __('Instructions') }}</h3>
                        <ul class="list-disc list-inside mt-2 text-sm text-muted-foreground">
                            <li>{{ __('File must be an Excel file (.xlsx, .xls).') }}</li>
                            <li>{{ __('First row should be the header row.') }}</li>
                            <li>{!! __('Columns order: <strong>Product (Code | Name), Location (Code | Site - Name), Quantity, Min Quantity</strong>.') !!}</li>
                            <li>{{ __('If stock (Product + Location) exists, quantity and min quantity will be updated (overwritten).') }}</li>
                            <li>{{ __('If stock does not exist, it will be created.') }}</li>
                        </ul>
                    </div>

                    <div class="mb-6">
                        <a href="{{ route('stocks.import.template') }}" class="text-sm text-primary hover:underline flex items-center gap-1">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                            {{ __('Download Excel Template') }}
                        </a>
                    </div>

                    <form action="{{ route('stocks.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
                        @csrf

                        <div>
                            <x-input-label for="file" :value="__('Excel File')" />
                            <input type="file" name="file" id="file" class="mt-1 block w-full text-sm text-muted-foreground
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-primary file:text-primary-foreground
                                hover:file:bg-primary/90" required accept=".xlsx, .xls">
                            <x-input-error :messages="$errors->get('file')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t border-border">
                            <x-secondary-button tag="a" href="{{ route('stocks.index') }}">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button class="gap-2" ::disabled="submitting">
                                <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" x-show="submitting" />
                                <span>{{ __('Import Data') }}</span>
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
