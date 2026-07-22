<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('purchases::purchases.three_way_match') }}</h2>
            <p class="text-gray-500 text-sm mt-1">{{ __('purchases::purchases.manage_three_way_match') }}</p>
        </div>
    </div>

    <!-- Selector Panel -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-auto sm:min-w-[220px]">
                <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase">{{ __('purchases::purchases.purchase_order') }}</label>
                <select wire:model.live="selectedPo"
                    class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <option value="">{{ __('purchases::purchases.select_po') }}</option>
                    @foreach($orders as $o)
                        <option value="{{ $o->id }}">{{ $o->order_number }} - {{ $o->supplier?->name ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto sm:min-w-[220px]">
                <label class="block text-xs font-bold text-gray-400 mb-1.5 uppercase">{{ __('purchases::purchases.invoice') }}</label>
                <select wire:model="selectedInvoice"
                    class="w-full py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <option value="">{{ __('purchases::purchases.select_invoice') }}</option>
                    @foreach($invoices as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->invoice_number }} - {{ $inv->supplier?->name ?? '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button wire:click="runMatch"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    {{ __('purchases::purchases.run_match') }}
                </button>
            </div>
        </div>
    </div>

    @if($matchingStatus)
        <!-- Matching Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase mb-2">{{ __('purchases::purchases.po_quantity') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($matchingStatus['po_quantity'], 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase mb-2">{{ __('purchases::purchases.grn_quantity') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($matchingStatus['grn_quantity'], 2) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 shadow-sm">
                <p class="text-xs font-bold text-gray-400 uppercase mb-2">{{ __('purchases::purchases.invoice_quantity') }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($matchingStatus['invoice_quantity'], 2) }}</p>
            </div>
        </div>

        <!-- Status Indicators -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 p-5 mb-6 shadow-sm">
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $matchingStatus['has_grn'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('purchases::purchases.grn') }}: {{ $matchingStatus['has_grn'] ? __('purchases::purchases.received') : __('purchases::purchases.missing') }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $matchingStatus['has_invoice'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('purchases::purchases.invoice') }}: {{ $matchingStatus['has_invoice'] ? __('purchases::purchases.received') : __('purchases::purchases.missing') }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $matchingStatus['quantity_match'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('purchases::purchases.quantities_match') }}: {{ $matchingStatus['quantity_match'] ? __('purchases::purchases.yes') : __('purchases::purchases.no') }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    @if($matchResult && isset($matchResult['items']))
        <!-- Match Result Table -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden mb-6">
            <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <span class="font-bold text-gray-800 dark:text-white">{{ __('purchases::purchases.matching_details') }}</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $matchResult['overall_match'] ? 'bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400' }}">
                    {{ $matchResult['overall_match'] ? __('purchases::purchases.matched') : __('purchases::purchases.discrepancy_found') }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                            <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.product') }}</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.po_qty') }}</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.po_price') }}</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.grn_qty') }}</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.inv_qty') }}</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.inv_price') }}</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">{{ __('purchases::purchases.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($matchResult['items'] as $item)
                            @php
                                $isMatch = $item['overall_status'] === 'match';
                            @endphp
                            <tr class="{{ $isMatch ? '' : 'bg-red-50/50 dark:bg-red-950/10' }} hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $item['product_name'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item['po_quantity'], 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item['po_price'], 2) }}</td>
                                <td class="px-4 py-3 text-sm {{ $item['quantity_status'] === 'match' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($item['grn_quantity'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm {{ $item['quantity_status'] === 'match' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($item['invoice_quantity'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm {{ $item['price_status'] === 'match' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ number_format($item['invoice_price'], 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($isMatch)
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400">
                                            {{ __('purchases::purchases.matched') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400">
                                            {{ __('purchases::purchases.mismatch') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">{{ __('purchases::purchases.total_items') }}: <strong>{{ $matchResult['total_items'] }}</strong></span>
                    <span class="text-gray-500">{{ __('purchases::purchases.matched_items') }}: <strong class="text-green-600">{{ $matchResult['total_matched'] }}</strong></span>
                    <span class="text-gray-500">{{ __('purchases::purchases.discrepancies') }}: <strong class="text-red-600">{{ $matchResult['total_discrepancies'] }}</strong></span>
                </div>
            </div>
        </div>
    @elseif($selectedPo && !$matchResult)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-8 text-center">
            <p class="text-gray-500">{{ __('purchases::purchases.select_po_and_invoice_to_match') }}</p>
        </div>
    @endif
</div>
