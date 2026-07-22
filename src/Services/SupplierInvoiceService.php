<?php

namespace Dev3bdulrahman\Purchases\Services;

use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Dev3bdulrahman\Purchases\Models\SupplierPayment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SupplierInvoiceService
{
    public function listInvoices(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = SupplierInvoice::query()->with(['supplier', 'creator', 'order']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->paginate($perPage);
    }

    public function createInvoice(array $data, array $items = []): SupplierInvoice
    {
        return DB::transaction(function () use ($data, $items) {
            $subtotal = 0.0000;
            $taxTotal = 0.0000;
            $discountTotal = 0.0000;

            $itemsToCreate = [];
            foreach ($items as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                $price = (float)($item['unit_price'] ?? 0);
                $discount = (float)($item['discount_amount'] ?? 0);
                $taxRate = (float)($item['tax_rate'] ?? 0);

                $itemSub = $qty * $price;
                $itemTax = ($itemSub - $discount) * ($taxRate / 100);
                $itemTotal = $itemSub - $discount + $itemTax;

                $subtotal += $itemSub;
                $discountTotal += $discount;
                $taxTotal += $itemTax;

                $itemsToCreate[] = [
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTax,
                    'discount_amount' => $discount,
                    'total' => $itemTotal,
                ];
            }

            $data['subtotal'] = $subtotal;
            $data['discount_total'] = $discountTotal;
            $data['tax_total'] = $taxTotal;
            $data['grand_total'] = $subtotal - $discountTotal + $taxTotal;
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            $invoice = SupplierInvoice::create($data);

            foreach ($itemsToCreate as $itemData) {
                $invoice->items()->create($itemData);
            }

            return $invoice;
        });
    }

    public function updateInvoice(SupplierInvoice $invoice, array $data, array $items = []): SupplierInvoice
    {
        return DB::transaction(function () use ($invoice, $data, $items) {
            $subtotal = 0.0000;
            $taxTotal = 0.0000;
            $discountTotal = 0.0000;

            $invoice->items()->delete();

            foreach ($items as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                $price = (float)($item['unit_price'] ?? 0);
                $discount = (float)($item['discount_amount'] ?? 0);
                $taxRate = (float)($item['tax_rate'] ?? 0);

                $itemSub = $qty * $price;
                $itemTax = ($itemSub - $discount) * ($taxRate / 100);
                $itemTotal = $itemSub - $discount + $itemTax;

                $subtotal += $itemSub;
                $discountTotal += $discount;
                $taxTotal += $itemTax;

                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTax,
                    'discount_amount' => $discount,
                    'total' => $itemTotal,
                ]);
            }

            $data['subtotal'] = $subtotal;
            $data['discount_total'] = $discountTotal;
            $data['tax_total'] = $taxTotal;
            $data['grand_total'] = $subtotal - $discountTotal + $taxTotal;

            $invoice->update($data);

            return $invoice;
        });
    }

    public function deleteInvoice(SupplierInvoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->payments()->delete();
            return $invoice->delete();
        });
    }

    public function recordPayment(SupplierInvoice $invoice, array $paymentData): SupplierPayment
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $paymentData['company_id'] = $invoice->company_id;
            $paymentData['branch_id'] = $invoice->branch_id;
            $paymentData['created_by'] = $paymentData['created_by'] ?? auth()->id();

            $payment = $invoice->payments()->create($paymentData);

            $newPaid = $invoice->paid_amount + $payment->amount;
            $status = 'partially_paid';

            if ($newPaid >= $invoice->grand_total) {
                $status = 'paid';
            }

            $invoice->update([
                'paid_amount' => $newPaid,
                'status' => $status
            ]);

            return $payment;
        });
    }
}
