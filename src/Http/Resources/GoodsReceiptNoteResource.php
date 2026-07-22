<?php

namespace Dev3bdulrahman\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoodsReceiptNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'purchase_order_id' => $this->purchase_order_id,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier ? $this->supplier->name : null,
            'po_number' => $this->purchaseOrder ? $this->purchaseOrder->order_number : null,
            'grn_number' => $this->grn_number,
            'receipt_date' => $this->receipt_date ? $this->receipt_date->format('Y-m-d') : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'received_by' => $this->received_by,
            'quality_check_passed' => $this->quality_check_passed,
            'quality_notes' => $this->quality_notes,
            'items' => GoodsReceiptNoteItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
