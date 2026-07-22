<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Purchases\Http\Resources\GoodsReceiptNoteResource;
use Dev3bdulrahman\Purchases\Services\GoodsReceiptNoteService;
use Dev3bdulrahman\Purchases\Models\GoodsReceiptNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoodsReceiptNoteApiController extends Controller
{
    use HasApiResponse;

    public function index(Request $request, GoodsReceiptNoteService $service): JsonResponse
    {
        $this->authorize('viewAny', GoodsReceiptNote::class);

        $perPage = (int) $request->get('per_page', 10);
        $grns = $service->listGrns($request->all(), $perPage);

        return $this->success(
            GoodsReceiptNoteResource::collection($grns->items()),
            __('Goods receipt notes retrieved successfully'),
            200,
            [
                'current_page' => $grns->currentPage(),
                'last_page' => $grns->lastPage(),
                'per_page' => $grns->perPage(),
                'total' => $grns->total(),
            ]
        );
    }

    public function store(Request $request, GoodsReceiptNoteService $service): JsonResponse
    {
        $this->authorize('create', GoodsReceiptNote::class);

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchases_orders,id',
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'grn_number' => 'required|string|max:255',
            'receipt_date' => 'required|date',
            'status' => 'nullable|in:draft,partial,completed,cancelled',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'received_by' => 'nullable|string|max:255',
            'quality_check_passed' => 'nullable|boolean',
            'quality_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchases_order_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.accepted_quantity' => 'nullable|numeric|min:0',
            'items.*.rejected_quantity' => 'nullable|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|string',
            'items.*.batch_id' => 'nullable|string',
            'items.*.serial_number_id' => 'nullable|string',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;

        $grn = $service->createGrn($validated, $items);
        $grn->load('items');

        return $this->success(
            new GoodsReceiptNoteResource($grn),
            __('Goods receipt note created successfully'),
            201
        );
    }

    public function show(GoodsReceiptNote $goodsReceiptNote): JsonResponse
    {
        $this->authorize('view', $goodsReceiptNote);

        $goodsReceiptNote->load('items');

        return $this->success(
            new GoodsReceiptNoteResource($goodsReceiptNote),
            __('Goods receipt note retrieved successfully')
        );
    }

    public function update(Request $request, GoodsReceiptNote $goodsReceiptNote, GoodsReceiptNoteService $service): JsonResponse
    {
        $this->authorize('update', $goodsReceiptNote);

        $validated = $request->validate([
            'grn_number' => 'nullable|string|max:255',
            'receipt_date' => 'nullable|date',
            'status' => 'nullable|in:draft,partial,completed,cancelled',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'received_by' => 'nullable|string|max:255',
            'quality_check_passed' => 'nullable|boolean',
            'quality_notes' => 'nullable|string',
            'items' => 'nullable|array|min:1',
            'items.*.purchase_order_item_id' => 'required_with:items|exists:purchases_order_items,id',
            'items.*.received_quantity' => 'required_with:items|numeric|min:0',
            'items.*.accepted_quantity' => 'nullable|numeric|min:0',
            'items.*.rejected_quantity' => 'nullable|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|string',
            'items.*.batch_id' => 'nullable|string',
            'items.*.serial_number_id' => 'nullable|string',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $service->updateGrn($goodsReceiptNote->id, $validated, $items);
        $goodsReceiptNote->load('items');

        return $this->success(
            new GoodsReceiptNoteResource($goodsReceiptNote),
            __('Goods receipt note updated successfully')
        );
    }

    public function destroy(GoodsReceiptNote $goodsReceiptNote, GoodsReceiptNoteService $service): JsonResponse
    {
        $this->authorize('delete', $goodsReceiptNote);

        $service->deleteGrn($goodsReceiptNote->id);

        return $this->success(
            null,
            __('Goods receipt note deleted successfully')
        );
    }

    public function complete(GoodsReceiptNote $goodsReceiptNote, GoodsReceiptNoteService $service): JsonResponse
    {
        $this->authorize('complete', $goodsReceiptNote);

        $grn = $service->completeGrn($goodsReceiptNote->id);
        $grn->load('items');

        return $this->success(
            new GoodsReceiptNoteResource($grn),
            __('Goods receipt note completed successfully')
        );
    }

    public function receiveItems(Request $request, GoodsReceiptNote $goodsReceiptNote, GoodsReceiptNoteService $service): JsonResponse
    {
        $this->authorize('receive', $goodsReceiptNote);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:purchases_grn_items,id',
            'items.*.received_quantity' => 'nullable|numeric|min:0',
            'items.*.accepted_quantity' => 'nullable|numeric|min:0',
            'items.*.rejected_quantity' => 'nullable|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|string',
            'items.*.batch_id' => 'nullable|string',
            'items.*.serial_number_id' => 'nullable|string',
        ]);

        $grn = $service->receiveItems($goodsReceiptNote->id, $validated['items']);
        $grn->load('items');

        return $this->success(
            new GoodsReceiptNoteResource($grn),
            __('Items received successfully')
        );
    }
}
