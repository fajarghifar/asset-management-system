<x-app-layout title="Loan Details">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                {{ __('Loan Details') }}: {{ $loan->code }}
            </h2>
            <div class="flex items-center gap-2" x-data>
                <x-secondary-button href="{{ route('loans.index') }}" tag="a">
                    <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
                    {{ __('Back to List') }}
                </x-secondary-button>

                @if($loan->status === \App\Enums\LoanStatus::Pending)
                    <x-primary-button href="{{ route('loans.edit', $loan) }}" tag="a">
                        <x-heroicon-o-pencil class="w-4 h-4 mr-2" />
                        Edit Loan
                    </x-primary-button>

                    <x-danger-button
                        x-on:click="$dispatch('loan-action', {
                            action: 'reject',
                            url: '{{ route('loans.reject', $loan) }}',
                            title: 'Reject Loan',
                            description: 'Are you sure you want to REJECT this loan?',
                            buttonText: 'Reject',
                            buttonClass: 'bg-red-600 hover:bg-red-500',
                            method: 'PATCH'
                        })"
                    >
                        <x-heroicon-o-x-circle class="w-4 h-4 mr-2" />
                        Reject
                    </x-danger-button>

                    <x-primary-button
                        class="!bg-green-600 hover:!bg-green-700 focus:!ring-green-500"
                        x-on:click="$dispatch('loan-action', {
                            action: 'approve',
                            url: '{{ route('loans.approve', $loan) }}',
                            title: 'Approve Loan',
                            description: 'Are you sure you want to APPROVE this loan? This will deduct stock from the inventory.',
                            buttonText: 'Approve',
                            buttonClass: 'bg-green-600 hover:bg-green-700',
                            method: 'POST'
                        })"
                    >
                        <x-heroicon-o-check-badge class="w-4 h-4 mr-2" />
                        Approve Loan
                    </x-primary-button>
                @endif

                @if($loan->status === \App\Enums\LoanStatus::Rejected)
                    <x-primary-button
                        class="bg-yellow-500 hover:bg-yellow-600 focus:ring-yellow-500"
                        x-on:click="$dispatch('loan-action', {
                            action: 'restore',
                            url: '{{ route('loans.restore', $loan) }}',
                            title: 'Restore Loan',
                            description: 'Are you sure you want to RESTORE this loan to Pending status?',
                            buttonText: 'Restore',
                            buttonClass: 'bg-yellow-500 hover:bg-yellow-600',
                            method: 'PATCH'
                        })"
                    >
                        <x-heroicon-o-arrow-path class="w-4 h-4 mr-2" />
                        Restore to Pending
                    </x-primary-button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-4" x-data="{
        openConfirmModal(detail) {
            $dispatch('open-modal', { name: 'confirm-loan-action' });
            this.actionUrl = detail.url;
            this.actionMethod = detail.method || 'POST';
            this.modalTitle = detail.title;
            this.modalDescription = detail.description;
            this.confirmButtonText = detail.buttonText;
            this.confirmButtonClass = detail.buttonClass;
        },
        actionUrl: '',
        actionMethod: 'POST',
        modalTitle: '',
        modalDescription: '',
        confirmButtonText: '',
        confirmButtonClass: ''
    }"
    @loan-action.window="openConfirmModal($event.detail)"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Loan Info Card -->
            <div class="bg-card text-card-foreground shadow-sm rounded-lg p-6 border border-border">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Left Column: Borrower & Identity -->
                    <div class="md:col-span-1 space-y-6">
                        <div>
                            <h3 class="text-lg font-medium border-b border-border pb-2 mb-4">Borrower Info</h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground">Borrower Name</dt>
                                    <dd class="mt-1 text-lg font-semibold text-foreground">{{ $loan->borrower_name }}</dd>
                                </div>
                                @if($loan->proof_image)
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-2">Proof / ID Card</dt>
                                    <dd>
                                        <div class="relative aspect-video w-full rounded-lg overflow-hidden border border-border group shadow-sm">
                                            <img src="{{ Storage::url($loan->proof_image) }}" alt="Proof" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300 cursor-pointer" onclick="window.open(this.src)">
                                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                                <span class="text-white text-xs font-medium">Click to view</span>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Right Column: Loan Details -->
                    <div class="md:col-span-2">
                         <h3 class="text-lg font-medium border-b border-border pb-2 mb-4">Loan Details</h3>
                         <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <!-- Status -->
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                                        {{ $loan->status->getBadgeClasses() }}">
                                        {{ $loan->status->getLabel() }}
                                    </span>
                                </dd>
                            </div>

                            <!-- PIC (New) -->
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-muted-foreground">PIC (Handled By)</dt>
                                <dd class="mt-1 text-sm text-foreground font-medium flex items-center">
                                    <div class="h-6 w-6 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary mr-2">
                                        {{ substr($loan->user->name, 0, 1) }}
                                    </div>
                                    {{ $loan->user->name }}
                                </dd>
                            </div>

                            <!-- Transaction Code -->
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-muted-foreground">Loan Transaction</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ $loan->code }}</dd>
                            </div>

                            <!-- Dates -->
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-muted-foreground">Loan Date</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ $loan->loan_date->format('d M Y') }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-muted-foreground">Due Date</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ $loan->due_date->format('d M Y') }}</dd>
                            </div>
                            @if($loan->returned_date)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Returned Date</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ $loan->returned_date->format('d M Y') }}</dd>
                            </div>
                            @endif

                            <!-- Purpose & Notes -->
                            <div class="sm:col-span-2 border-t border-border pt-4 mt-2">
                                <dt class="text-sm font-medium text-muted-foreground">Purpose</dt>
                                <dd class="mt-1 text-sm text-foreground whitespace-pre-line">{{ $loan->purpose }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Notes</dt>
                                <dd class="mt-1 text-sm text-foreground whitespace-pre-line">{{ $loan->notes ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-border my-8"></div>

                <!-- Loan Items Section -->
                <div>
                   <h3 class="text-lg font-medium mb-4">Loan Items</h3>

                   @if($loan->status === \App\Enums\LoanStatus::Approved || $loan->status === \App\Enums\LoanStatus::Overdue)
                   <form action="{{ route('loans.return', $loan) }}" method="POST">
                       @csrf
                   @endif

                   <div class="overflow-x-auto rounded-md border border-border">
                       <table class="min-w-full divide-y divide-border">
                           <thead class="bg-muted/50">
                               <tr>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Type</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Item Name</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Location</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Qty Borrowed</th>
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Qty Returned</th>
                                   @if(($loan->status === \App\Enums\LoanStatus::Approved || $loan->status === \App\Enums\LoanStatus::Overdue))
                                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Return Action</th>
                                   @endif
                               </tr>
                           </thead>
                           <tbody class="divide-y divide-border bg-card">
                               @foreach($loan->items as $item)
                               <tr class="hover:bg-muted/50 transition-colors">
                                   <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                       {{ $item->type->getLabel() }}
                                   </td>
                                   <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">
                                       @if($item->type === \App\Enums\LoanItemType::Asset)
                                           {{ $item->asset->product->name ?? 'Unknown Asset' }} <span class="text-muted-foreground font-normal">({{ $item->asset->asset_tag ?? '-' }})</span>
                                       @else
                                           {{ $item->consumableStock->product->name ?? 'Unknown Stock' }}
                                       @endif
                                   </td>
                                   <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                       @php
                                           $loc = $item->type === \App\Enums\LoanItemType::Asset
                                               ? $item->asset?->location
                                               : $item->consumableStock?->location;
                                       @endphp
                                       @if($loc)
                                           {{ $loc->name }} <span class="text-muted-foreground">({{ $loc->site->getLabel() }})</span>
                                       @else
                                           <span class="text-muted-foreground">-</span>
                                       @endif
                                   </td>
                                   <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                       {{ $item->quantity_borrowed }}
                                   </td>
                                   <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                       {{ $item->quantity_returned }}
                                   </td>

                                   @if(($loan->status === \App\Enums\LoanStatus::Approved || $loan->status === \App\Enums\LoanStatus::Overdue))
                                   <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                       @if($item->quantity_returned < $item->quantity_borrowed)
                                           @if($item->type === \App\Enums\LoanItemType::Asset)
                                               <div class="flex items-center">
                                                   <input type="checkbox" name="items[{{ $item->id }}][is_returned]" value="1" id="return_asset_{{ $item->id }}" class="rounded border-input text-primary shadow-sm focus:ring-ring bg-background">
                                                   <label for="return_asset_{{ $item->id }}" class="ml-2 text-sm text-foreground cursor-pointer select-none">Mark Returned</label>
                                               </div>
                                           @else
                                               <div class="flex items-center space-x-2">
                                                   <input
                                                       type="number"
                                                       name="items[{{ $item->id }}][quantity_returned]"
                                                       min="0"
                                                       max="{{ $item->quantity_borrowed - $item->quantity_returned }}"
                                                       class="w-24 rounded-md border-input bg-background shadow-sm focus:border-ring focus:ring-ring sm:text-sm"
                                                       placeholder="Qty"
                                                   >
                                                   <span class="text-xs text-muted-foreground">Max: {{ $item->quantity_borrowed - $item->quantity_returned }}</span>
                                               </div>
                                           @endif
                                       @else
                                           <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                               Completed
                                           </span>
                                       @endif
                                   </td>
                                   @endif
                               </tr>
                               @endforeach
                           </tbody>
                       </table>
                   </div>

                   @if(($loan->status === \App\Enums\LoanStatus::Approved || $loan->status === \App\Enums\LoanStatus::Overdue))
                   <div class="mt-4 flex justify-end">
                       <x-primary-button>
                           Process Returns
                       </x-primary-button>
                   </div>
                   </form>
                   @endif
                </div>

        </div>
    <x-modal name="confirm-loan-action" :maxWidth="'md'">
        <form method="POST" :action="actionUrl" class="bg-card px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            @csrf
            <input type="hidden" name="_method" :value="actionMethod">

            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                    <h3 class="text-lg font-semibold leading-6 text-foreground" id="modal-title" x-text="modalTitle"></h3>
                    <div class="mt-2">
                        <p class="text-sm text-muted-foreground" x-text="modalDescription"></p>
                    </div>
                </div>
            </div>

            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="submit" :class="`inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 ${confirmButtonClass}`" x-text="confirmButtonText"></button>
                <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-background px-3 py-2 text-sm font-semibold text-foreground shadow-sm ring-1 ring-inset ring-border hover:bg-accent hover:text-accent-foreground sm:mt-0 sm:w-auto transition-colors" x-on:click="$dispatch('close-modal', { name: 'confirm-loan-action' })">
                    Cancel
                </button>
            </div>
        </form>
    </x-modal>
    </div>
</x-app-layout>
