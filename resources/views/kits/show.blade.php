<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Kit Details') }}: {{ $kit->name }}
            </h2>
            <div class="flex items-center gap-2">
                <x-secondary-button href="{{ route('kits.index') }}" tag="a">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </x-secondary-button>

                <x-primary-button href="{{ route('kits.edit', $kit) }}" tag="a">
                    <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                    Edit Kit
                </x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-card text-card-foreground shadow-sm rounded-lg p-6 border border-border">

                <!-- Top Section: Kit Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-medium border-b border-border pb-2 mb-4">Kit Overview</h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground">Kit Name</dt>
                                    <dd class="mt-1 text-lg font-semibold text-foreground">{{ $kit->name }}</dd>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                        <dd class="mt-1">
                                            @if($kit->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                    Inactive
                                                </span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-muted-foreground">Total Items</dt>
                                        <dd class="mt-1 text-sm text-foreground">{{ $kit->items->count() }} Products</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium border-b border-border pb-2 mb-4">Description</h3>
                        <p class="text-sm text-foreground whitespace-pre-line">{{ $kit->description ?? '-' }}</p>
                    </div>
                </div>

                <div class="border-t border-border my-8"></div>

                <!-- Bottom Section: Availability & Items -->
                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-foreground">Availability Status</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $availability['is_fully_available'] ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200' }}">
                            {{ $availability['is_fully_available'] ? 'Ready to Loan' : 'Incomplete' }}
                        </span>
                    </div>

                    @if (!$availability['is_fully_available'])
                    <div class="rounded-md bg-yellow-50 p-4 border border-yellow-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <x-heroicon-s-exclamation-triangle class="h-5 w-5 text-yellow-400" />
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Attention Needed</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Some items in this kit are not fully available in their specified locations.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="overflow-visible border rounded-md">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-muted text-muted-foreground uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">Product / Asset</th>
                                    <th class="px-4 py-3">Target Location</th>
                                    <th class="px-4 py-3">Qty Needed</th>
                                    <th class="px-4 py-3">Availability</th>
                                    <th class="px-4 py-3">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach ($availability['details'] as $detail)
                                <tr class="hover:bg-muted/50 transition-colors">
                                    <td class="px-4 py-3 align-top font-medium text-foreground">
                                        {{ $detail['product_name'] }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-muted-foreground">
                                        {{ $detail['location_name'] }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-foreground">
                                        {{ $detail['needed_qty'] }}
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        @if($detail['is_enough'])
                                            <span class="inline-flex items-center text-green-600 font-medium">
                                                <x-heroicon-s-check-circle class="w-4 h-4 mr-1"/>
                                                {{ $detail['available_qty'] }} Available
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-red-600 font-bold">
                                                <x-heroicon-s-x-circle class="w-4 h-4 mr-1"/>
                                                Only {{ $detail['available_qty'] }} Available
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-muted-foreground italic">
                                        {{ $kit->items->where('product.name', $detail['product_name'])->first()?->notes ?? '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
