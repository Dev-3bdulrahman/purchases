<?php

namespace Dev3bdulrahman\Purchases\Services;

use Dev3bdulrahman\Purchases\Events\GoodsReceiptCompleted;
use Dev3bdulrahman\Purchases\Events\GoodsReceiptCreated;
use Dev3bdulrahman\Purchases\Models\GoodsReceiptNote;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Dev3bdulrahman\Purchases\Models\PurchaseOrderItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class GoodsReceiptNoteService
{
    public function listGrns(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = GoodsReceiptNote::query()->with(['supplier', 'purchaseOrder', 'items']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('grn_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('purchaseOrder', function ($sq) use ($search) {
                      $sq->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id']);
        }

        return $query->paginate($perPage);
    }

    public function createGrn(array $data, array $items = []): GoodsReceiptNote
    {
        return DB::transaction(function () use ($data, $items) {
            $data['status'] = $data['status'] ?? 'draft';
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            $grn = GoodsReceiptNote::create($data);

            foreach ($items as $item) {
                $poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);
                $receivedQty = (float)($item['received_quantity'] ?? 0);
                $acceptedQty = (float)($item['accepted_quantity'] ?? $receivedQty);
                $rejectedQty = (float)($item['rejected_quantity'] ?? 0);
                $unitPrice = (float)($item['unit_price'] ?? $poItem->unit_price);

                $grn->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'ordered_quantity' => $poItem->quantity,
                    'received_quantity' => $receivedQty,
                    'accepted_quantity' => $acceptedQty,
                    'rejected_quantity' => $rejectedQty,
                    'rejection_reason' => $item['rejection_reason'] ?? null,
                    'batch_id' => $item['batch_id'] ?? null,
                    'serial_number_id' => $item['serial_number_id'] ?? null,
                    'unit_price' => $unitPrice,
                    'subtotal' => $acceptedQty * $unitPrice,
                ]);
            }

            if ($data['status'] === 'completed') {
                $this->handleStockMovement($grn);
            }

            event(new GoodsReceiptCreated($grn, auth()->id(), $data['company_id'] ?? auth()->user()->company_id));

            return $grn;
        });
    }

    public function updateGrn(int $id, array $data, array $items = []): GoodsReceiptNote
    {
        return DB::transaction(function () use ($id, $data, $items) {
            $grn = GoodsReceiptNote::findOrFail($id);
            $grn->update($data);
            $grn->items()->delete();

            foreach ($items as $item) {
                $poItem = PurchaseOrderItem::findOrFail($item['purchase_order_item_id']);
                $receivedQty = (float)($item['received_quantity'] ?? 0);
                $acceptedQty = (float)($item['accepted_quantity'] ?? $receivedQty);
                $rejectedQty = (float)($item['rejected_quantity'] ?? 0);
                $unitPrice = (float)($item['unit_price'] ?? $poItem->unit_price);

                $grn->items()->create([
                    'purchase_order_item_id' => $poItem->id,
                    'product_id' => $poItem->product_id,
                    'ordered_quantity' => $poItem->quantity,
                    'received_quantity' => $receivedQty,
                    'accepted_quantity' => $acceptedQty,
                    'rejected_quantity' => $rejectedQty,
                    'rejection_reason' => $item['rejection_reason'] ?? null,
                    'batch_id' => $item['batch_id'] ?? null,
                    'serial_number_id' => $item['serial_number_id'] ?? null,
                    'unit_price' => $unitPrice,
                    'subtotal' => $acceptedQty * $unitPrice,
                ]);
            }

            return $grn;
        });
    }

    public function deleteGrn(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $grn = GoodsReceiptNote::findOrFail($id);
            $grn->items()->delete();
            return $grn->delete();
        });
    }

    public function receiveItems(int $grnId, array $items): GoodsReceiptNote
    {
        return DB::transaction(function () use ($grnId, $items) {
            $grn = GoodsReceiptNote::findOrFail($grnId);

            foreach ($items as $item) {
                $grnItem = $grn->items()->findOrFail($item['id']);
                $receivedQty = (float)($item['received_quantity'] ?? $grnItem->received_quantity);
                $acceptedQty = (float)($item['accepted_quantity'] ?? $receivedQty);
                $rejectedQty = (float)($item['rejected_quantity'] ?? 0);

                $grnItem->update([
                    'received_quantity' => $receivedQty,
                    'accepted_quantity' => $acceptedQty,
                    'rejected_quantity' => $rejectedQty,
                    'rejection_reason' => $item['rejection_reason'] ?? $grnItem->rejection_reason,
                    'batch_id' => $item['batch_id'] ?? $grnItem->batch_id,
                    'serial_number_id' => $item['serial_number_id'] ?? $grnItem->serial_number_id,
                ]);
            }

            $allPartial = $grn->items()->whereRaw('received_quantity < ordered_quantity')->exists();
            $allComplete = !$grn->items()->whereRaw('received_quantity < ordered_quantity')->exists();

            $status = $allComplete ? 'completed' : ($allPartial ? 'partial' : 'draft');
            $grn->update(['status' => $status]);

            if ($status === 'completed') {
                $this->handleStockMovement($grn);
            }

            return $grn;
        });
    }

    public function completeGrn(int $grnId): GoodsReceiptNote
    {
        return DB::transaction(function () use ($grnId) {
            $grn = GoodsReceiptNote::findOrFail($grnId);
            $grn->update(['status' => 'completed']);

            $this->handleStockMovement($grn);

            event(new GoodsReceiptCompleted($grn, auth()->id(), $grn->company_id));

            return $grn;
        });
    }

    public function getGrnByPurchaseOrder(int $poId)
    {
        return GoodsReceiptNote::where('purchase_order_id', $poId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingGrns()
    {
        return GoodsReceiptNote::whereIn('status', ['draft', 'partial'])
            ->with(['supplier', 'purchaseOrder'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    protected function handleStockMovement(GoodsReceiptNote $grn): void
    {
        if (!class_exists(\Dev3bdulrahman\Inventory\Services\StockMoveService::class)) {
            return;
        }

        $stockMoveService = app(\Dev3bdulrahman\Inventory\Services\StockMoveService::class);

        foreach ($grn->items as $item) {
            if ($item->accepted_quantity > 0) {
                $stockMoveService->addStock(
                    productId: $item->product_id,
                    warehouseId: $grn->branch_id,
                    quantity: $item->accepted_quantity,
                    unitPrice: $item->unit_price,
                    referenceType: GoodsReceiptNote::class,
                    referenceId: $grn->id,
                    notes: 'GRN #' . $grn->grn_number,
                );
            }
        }

        $grn->purchaseOrder->update(['status' => 'received']);
    }
}
