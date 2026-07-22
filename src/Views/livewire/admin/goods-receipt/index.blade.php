<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('purchases::purchases.goods_receipt') }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ __('purchases::purchases.manage_goods_receipt') }}</p>
        </div>
        <button wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ __('purchases::purchases.add_goods_receipt') }}</span>
        </button>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase">{{ __('purchases::purchases.search') }}</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('purchases::purchases.search_placeholder') }}"
                        class="w-full text-right pl-3 pr-10 py-2 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:text-white">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <div class="w-full sm:w-auto sm:min-w-[160px]">
                <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase">{{ __('purchases::purchases.status') }}</label>
                <select wire:model.live="statusFilter"
                    class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <option value="">{{ __('purchases::purchases.status') }}</option>
                    <option value="draft">{{ __('purchases::purchases.draft') }}</option>
                    <option value="partial">{{ __('purchases::purchases.partial') }}</option>
                    <option value="completed">{{ __('purchases::purchases.completed') }}</option>
                    <option value="cancelled">{{ __('purchases::purchases.cancelled') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.grn_number') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.po_number') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.supplier') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.receipt_date') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.status') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase text-center">{{ __('purchases::purchases.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @forelse($grns as $grn)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $grn->grn_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $grn->purchaseOrder ? $grn->purchaseOrder->order_number : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $grn->supplier ? $grn->supplier->name : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $grn->receipt_date->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                        'partial' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400',
                                        'completed' => 'bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400',
                                        'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$grn->status] ?? $statusClasses['draft'] }}">
                                    {{ __('purchases::purchases.' . $grn->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openItemsModal({{ $grn->id }})" title="{{ __('purchases::purchases.view_items') }}"
                                        class="p-2 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </button>
                                    @if($grn->status !== 'completed' && $grn->status !== 'cancelled')
                                        <button wire:click="openEditModal({{ $grn->id }})" title="{{ __('purchases::purchases.edit') }}"
                                            class="p-2 text-gray-500 hover:text-purple-600 dark:hover:text-purple-400 rounded-lg hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button wire:click="completeGrn({{ $grn->id }})" title="{{ __('purchases::purchases.complete') }}"
                                            class="p-2 text-gray-500 hover:text-green-600 dark:hover:text-green-400 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button
                                        wire:click="$dispatch('swal:confirm', {
                                            title: '{{ __('purchases::purchases.delete') }}',
                                            text: '{{ __('purchases::purchases.delete_confirm') }}',
                                            onConfirm: 'delete',
                                            params: { id: {{ $grn->id }} }
                                        })"
                                        title="{{ __('purchases::purchases.delete') }}"
                                        class="p-2 text-gray-500 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <span>{{ __('purchases::purchases.no_goods_receipt') }}</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($grns->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                {{ $grns->links() }}
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <div x-data="{ open: @entangle('showFormModal') }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="open = false" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-950/75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-gray-900 rounded-xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full border border-gray-100 dark:border-gray-800">
                <form wire:submit.prevent="save">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6 border-b border-gray-50 dark:border-gray-800 pb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $grnId ? __('purchases::purchases.edit_goods_receipt') : __('purchases::purchases.add_goods_receipt') }}
                            </h3>
                            <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('purchases::purchases.purchase_order') }} *</label>
                                <select wire:model.live="purchase_order_id" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                    <option value="">{{ __('purchases::purchases.select_po') }}</option>
                                    @foreach($orders as $o)
                                        <option value="{{ $o->id }}">{{ $o->order_number }} - {{ $o->supplier?->name ?? '' }}</option>
                                    @endforeach
                                </select>
                                @error('purchase_order_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('purchases::purchases.grn_number') }} *</label>
                                <input type="text" wire:model="grn_number" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('grn_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('purchases::purchases.receipt_date') }} *</label>
                                <input type="date" wire:model="receipt_date" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('receipt_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('purchases::purchases.status') }} *</label>
                                <select wire:model="status" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                    <option value="draft">{{ __('purchases::purchases.draft') }}</option>
                                    <option value="partial">{{ __('purchases::purchases.partial') }}</option>
                                    <option value="completed">{{ __('purchases::purchases.completed') }}</option>
                                    <option value="cancelled">{{ __('purchases::purchases.cancelled') }}</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('purchases::purchases.branch') }}</label>
                                <select wire:model="branch_id" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                    <option value="">{{ __('purchases::purchases.select_branch') }}</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('purchases::purchases.received_by') }}</label>
                                <input type="text" wire:model="received_by" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                @error('received_by') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Items Table -->
                        @if(count($items) > 0)
                            <div class="mb-6 border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden">
                                <div class="bg-gray-50 dark:bg-gray-800 p-4 border-b border-gray-100 dark:border-gray-800">
                                    <span class="font-bold text-gray-800 dark:text-white">{{ __('purchases::purchases.items') }}</span>
                                </div>
                                <table class="w-full text-right">
                                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                                        <tr>
                                            <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.product') }}</th>
                                            <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.ordered_quantity') }}</th>
                                            <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.received_quantity') }}</th>
                                            <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.accepted_quantity') }}</th>
                                            <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.rejected_quantity') }}</th>
                                            <th class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.unit_price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $index => $item)
                                            <tr class="border-t border-gray-50 dark:border-gray-800">
                                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                    {{ $item['product_name'] ?? __('purchases::purchases.product') . ' #' . $item['product_id'] }}
                                                    <input type="hidden" wire:model="items.{{ $index }}.purchase_order_item_id">
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($item['ordered_quantity'] ?? 0, 2) }}</td>
                                                <td class="px-4 py-2">
                                                    <input type="number" step="0.0001" wire:model.live="items.{{ $index }}.received_quantity" class="w-20 py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" step="0.0001" wire:model.live="items.{{ $index }}.accepted_quantity" class="w-20 py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" step="0.0001" wire:model.live="items.{{ $index }}.rejected_quantity" class="w-20 py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="mb-6 p-8 text-center text-gray-500 bg-gray-50 dark:bg-gray-800/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                                {{ __('purchases::purchases.select_po_to_load_items') }}
                            </div>
                        @endif

                        <!-- Quality Check & Notes -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('purchases::purchases.notes') }}</label>
                                <textarea wire:model="notes" rows="3" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"></textarea>
                                @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="quality_check_passed" class="rounded border-gray-300 dark:border-gray-700 text-blue-600 focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('purchases::purchases.quality_check_passed') }}</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('purchases::purchases.quality_notes') }}</label>
                                    <textarea wire:model="quality_notes" rows="2" class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white"></textarea>
                                    @error('quality_notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors">
                            {{ __('purchases::purchases.save') }}
                        </button>
                        <button type="button" @click="open = false" class="px-4 py-2 bg-white dark:bg-gray-800 hover:bg-gray-50 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-semibold transition-colors">
                            {{ __('purchases::purchases.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Items Modal -->
    <div x-data="{ open: @entangle('showItemsModal') }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="open = false" class="fixed inset-0 bg-gray-500/75 dark:bg-gray-950/75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-gray-900 rounded-xl text-right overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100 dark:border-gray-800">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6 border-b border-gray-50 dark:border-gray-800 pb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('purchases::purchases.grn_items') }}</h3>
                        <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @php
                        $viewingGrn = $grns->firstWhere('id', $viewingGrnId);
                    @endphp
                    @if($viewingGrn)
                        <table class="w-full text-right">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.product') }}</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.ordered_quantity') }}</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.received_quantity') }}</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.accepted_quantity') }}</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.rejected_quantity') }}</th>
                                    <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.unit_price') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                @foreach($viewingGrn->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $item->product?->translated_name ?? 'Product #' . $item->product_id }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ number_format($item->ordered_quantity, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ number_format($item->received_quantity, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-semibold">{{ number_format($item->accepted_quantity, 2) }}</td>
                                        <td class="px-4 py-3 text-sm {{ $item->rejected_quantity > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">{{ number_format($item->rejected_quantity, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ number_format($item->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-row-reverse border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="open = false" class="px-4 py-2 bg-white dark:bg-gray-800 hover:bg-gray-50 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-semibold transition-colors">
                        {{ __('purchases::purchases.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
