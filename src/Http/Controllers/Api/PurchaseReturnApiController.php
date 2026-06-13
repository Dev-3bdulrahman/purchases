<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Purchases\Http\Resources\PurchaseReturnResource;
use Dev3bdulrahman\Purchases\Services\PurchaseReturnService;
use Dev3bdulrahman\Purchases\Models\PurchaseReturn;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PurchaseReturnApiController extends Controller
{
    protected PurchaseReturnService $service;

    public function __construct(PurchaseReturnService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 10);
        $returns = $this->service->listReturns($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Purchase returns retrieved successfully'),
            'data' => PurchaseReturnResource::collection($returns->items()),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ],
            'errors' => []
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'supplier_invoice_id' => 'nullable|exists:purchases_invoices,id',
            'return_number' => 'required|string|max:255',
            'return_date' => 'required|date',
            'status' => 'nullable|string|in:pending,approved,rejected,completed',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;
        $return = $this->service->createReturn($validated, $items);
        $return->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Purchase return created successfully'),
            'data' => new PurchaseReturnResource($return),
            'errors' => []
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $return = PurchaseReturn::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Purchase return retrieved successfully'),
            'data' => new PurchaseReturnResource($return),
            'errors' => []
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $return = PurchaseReturn::findOrFail($id);
        $this->service->deleteReturn($return);

        return response()->json([
            'success' => true,
            'message' => __('Purchase return deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
