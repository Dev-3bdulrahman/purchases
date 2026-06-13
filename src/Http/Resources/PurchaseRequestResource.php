<?php

namespace Dev3bdulrahman\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'request_number' => $this->request_number,
            'request_date' => $this->request_date ? $this->request_date->format('Y-m-d') : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'creator_name' => $this->creator ? $this->creator->name : null,
            'items' => PurchaseRequestItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
        ];
    }
}
