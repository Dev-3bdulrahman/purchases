<?php

namespace Dev3bdulrahman\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier ? $this->supplier->name : null,
            'purchase_order_id' => $this->purchase_order_id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date ? $this->invoice_date->format('Y-m-d') : null,
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax_total' => $this->tax_total,
            'discount_total' => $this->discount_total,
            'grand_total' => $this->grand_total,
            'paid_amount' => $this->paid_amount,
            'notes' => $this->notes,
            'creator_name' => $this->creator ? $this->creator->name : null,
            'items' => SupplierInvoiceItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
