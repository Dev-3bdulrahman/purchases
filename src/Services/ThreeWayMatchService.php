<?php

namespace Dev3bdulrahman\Purchases\Services;

use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Dev3bdulrahman\Purchases\Models\GoodsReceiptNote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ThreeWayMatchService
{
    const MATCH_OK = 'match';
    const MISMATCH_QUANTITY = 'quantity_mismatch';
    const MISMATCH_PRICE = 'price_mismatch';
    const MISMATCH_BOTH = 'both_mismatch';
    const MISSING_GRN = 'missing_grn';
    const MISSING_INVOICE = 'missing_invoice';

    public function match(int $purchaseOrderId, int $invoiceId): array
    {
        $po = PurchaseOrder::with('items')->findOrFail($purchaseOrderId);
        $invoice = SupplierInvoice::with('items')->findOrFail($invoiceId);
        $grns = GoodsReceiptNote::where('purchase_order_id', $purchaseOrderId)
            ->where('status', 'completed')
            ->with('items')
            ->get();

        $results = [];
        $totalMatched = 0;
        $totalDiscrepancies = 0;

        foreach ($po->items as $poItem) {
            $grnItems = $grns->flatMap(fn($grn) => $grn->items)
                ->where('product_id', $poItem->product_id);

            $invoiceItems = $invoice->items
                ->where('product_id', $poItem->product_id);

            $totalReceived = $grnItems->sum('accepted_quantity');
            $totalInvoiced = $invoiceItems->sum('quantity');
            $invoicePrice = $invoiceItems->first()?->unit_price ?? 0;

            $result = $this->compareQuantities($poItem, $totalReceived, $totalInvoiced);
            $priceResult = $this->comparePrices($poItem, $invoicePrice);

            $itemResult = [
                'product_id' => $poItem->product_id,
                'product_name' => $poItem->product?->translated_name ?? 'Product #' . $poItem->product_id,
                'po_quantity' => (float)$poItem->quantity,
                'po_price' => (float)$poItem->unit_price,
                'grn_quantity' => $totalReceived,
                'invoice_quantity' => $totalInvoiced,
                'invoice_price' => $invoicePrice,
                'quantity_status' => $result,
                'price_status' => $priceResult,
                'overall_status' => $this->getOverallStatus($result, $priceResult),
            ];

            if ($itemResult['overall_status'] === self::MATCH_OK) {
                $totalMatched++;
            } else {
                $totalDiscrepancies++;
            }

            $results[] = $itemResult;
        }

        return [
            'purchase_order_id' => $purchaseOrderId,
            'order_number' => $po->order_number,
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoice->invoice_number,
            'grn_count' => $grns->count(),
            'has_grn' => $grns->isNotEmpty(),
            'has_invoice' => true,
            'items' => $results,
            'total_items' => count($results),
            'total_matched' => $totalMatched,
            'total_discrepancies' => $totalDiscrepancies,
            'overall_match' => $totalDiscrepancies === 0,
        ];
    }

    public function compareQuantities($poItem, float $totalReceived, float $totalInvoiced): string
    {
        $poQty = (float)$poItem->quantity;

        $receivedMatch = abs($poQty - $totalReceived) < 0.0001;
        $invoicedMatch = abs($poQty - $totalInvoiced) < 0.0001;

        if ($receivedMatch && $invoicedMatch) {
            return self::MATCH_OK;
        }

        return self::MISMATCH_QUANTITY;
    }

    public function comparePrices($poItem, float $invoicePrice): string
    {
        $poPrice = (float)$poItem->unit_price;

        if (abs($poPrice - $invoicePrice) < 0.0001) {
            return self::MATCH_OK;
        }

        return self::MISMATCH_PRICE;
    }

    protected function getOverallStatus(string $quantityStatus, string $priceStatus): string
    {
        if ($quantityStatus === self::MATCH_OK && $priceStatus === self::MATCH_OK) {
            return self::MATCH_OK;
        }

        if ($quantityStatus !== self::MATCH_OK && $priceStatus !== self::MATCH_OK) {
            return self::MISMATCH_BOTH;
        }

        if ($quantityStatus !== self::MATCH_OK) {
            return self::MISMATCH_QUANTITY;
        }

        return self::MISMATCH_PRICE;
    }

    public function getMatchingStatus(int $poId): array
    {
        $po = PurchaseOrder::findOrFail($poId);

        $grns = GoodsReceiptNote::where('purchase_order_id', $poId)
            ->where('status', 'completed')
            ->get();

        $invoices = SupplierInvoice::where('purchase_order_id', $poId)->get();

        $totalGrnQty = 0;
        foreach ($grns as $grn) {
            $totalGrnQty += $grn->items->sum('accepted_quantity');
        }

        $totalInvoiceQty = 0;
        foreach ($invoices as $inv) {
            $totalInvoiceQty += $inv->items->sum('quantity');
        }

        $totalPoQty = $po->items->sum('quantity');

        return [
            'purchase_order_id' => $poId,
            'order_number' => $po->order_number,
            'po_quantity' => (float)$totalPoQty,
            'grn_quantity' => $totalGrnQty,
            'invoice_quantity' => $totalInvoiceQty,
            'has_grn' => $grns->isNotEmpty(),
            'has_invoice' => $invoices->isNotEmpty(),
            'grn_count' => $grns->count(),
            'invoice_count' => $invoices->count(),
            'quantity_match' => abs($totalPoQty - $totalGrnQty) < 0.0001 && abs($totalPoQty - $totalInvoiceQty) < 0.0001,
        ];
    }

    public function generateMatchingReport(int $poId): array
    {
        $status = $this->getMatchingStatus($poId);

        $discrepancies = [];

        if (!$status['has_grn']) {
            $discrepancies[] = [
                'type' => 'missing_grn',
                'message' => 'No goods receipt note found for this purchase order.',
                'severity' => 'high',
            ];
        }

        if (!$status['has_invoice']) {
            $discrepancies[] = [
                'type' => 'missing_invoice',
                'message' => 'No supplier invoice found for this purchase order.',
                'severity' => 'high',
            ];
        }

        if (!$status['quantity_match']) {
            $discrepancies[] = [
                'type' => 'quantity_mismatch',
                'message' => "PO quantity ({$status['po_quantity']}) does not match GRN ({$status['grn_quantity']}) or Invoice ({$status['invoice_quantity']}).",
                'severity' => 'medium',
            ];
        }

        return [
            'status' => $status,
            'discrepancies' => $discrepancies,
            'match_percentage' => $this->calculateMatchPercentage($status),
            'overall_status' => empty($discrepancies) ? self::MATCH_OK : 'has_discrepancies',
        ];
    }

    protected function calculateMatchPercentage(array $status): float
    {
        $score = 0;
        $total = 2;

        if ($status['has_grn']) {
            $score++;
        }

        if ($status['has_invoice']) {
            $score++;
        }

        if ($status['quantity_match']) {
            $score++;
            $total++;
        }

        return round(($score / $total) * 100, 2);
    }

    public function resolveDiscrepancy(int $poId, string $type, array $resolution): bool
    {
        try {
            Log::info('Three-way match discrepancy resolution', [
                'purchase_order_id' => $poId,
                'discrepancy_type' => $type,
                'resolution' => $resolution,
                'resolved_by' => auth()->id(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to resolve discrepancy', [
                'error' => $e->getMessage(),
                'purchase_order_id' => $poId,
            ]);
            return false;
        }
    }
}
