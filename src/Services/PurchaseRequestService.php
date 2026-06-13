<?php

namespace Dev3bdulrahman\Purchases\Services;

use Dev3bdulrahman\Purchases\Models\PurchaseRequest;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Dev3bdulrahman\Purchases\Models\Supplier;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
{
    public function listRequests(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = PurchaseRequest::query()->with(['creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function createRequest(array $data, array $items = []): PurchaseRequest
    {
        return DB::transaction(function () use ($data, $items) {
            $data['created_by'] = $data['created_by'] ?? auth()->id();
            $request = PurchaseRequest::create($data);

            foreach ($items as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                $price = isset($item['unit_price']) ? (float)$item['unit_price'] : null;
                $total = $price ? ($qty * $price) : null;

                $request->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $total,
                ]);
            }

            return $request;
        });
    }

    public function updateRequest(PurchaseRequest $request, array $data, array $items = []): PurchaseRequest
    {
        return DB::transaction(function () use ($request, $data, $items) {
            $request->update($data);
            $request->items()->delete();

            foreach ($items as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                $price = isset($item['unit_price']) ? (float)$item['unit_price'] : null;
                $total = $price ? ($qty * $price) : null;

                $request->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $total,
                ]);
            }

            return $request;
        });
    }

    public function deleteRequest(PurchaseRequest $request): bool
    {
        return DB::transaction(function () use ($request) {
            $request->items()->delete();
            return $request->delete();
        });
    }

    public function convertToOrder(PurchaseRequest $request, array $orderData): PurchaseOrder
    {
        return DB::transaction(function () use ($request, $orderData) {
            $request->update(['status' => 'approved']);

            // Instantiate order service to handle order creation
            $orderService = new PurchaseOrderService();
            $items = [];
            foreach ($request->items as $item) {
                $items[] = [
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price ?? 0,
                    'tax_rate' => $orderData['tax_rate'] ?? 0,
                    'discount_amount' => 0,
                ];
            }

            $orderData['purchase_request_id'] = $request->id;
            $orderData['company_id'] = $request->company_id;
            $orderData['branch_id'] = $request->branch_id;

            return $orderService->createOrder($orderData, $items);
        });
    }
}
