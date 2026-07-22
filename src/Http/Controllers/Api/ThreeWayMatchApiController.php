<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Purchases\Services\ThreeWayMatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThreeWayMatchApiController extends Controller
{
    use HasApiResponse;

    public function match(Request $request, ThreeWayMatchService $service): JsonResponse
    {
        $this->authorize('viewAny', \Dev3bdulrahman\Purchases\Models\GoodsReceiptNote::class);

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchases_orders,id',
            'invoice_id' => 'required|exists:purchases_invoices,id',
        ]);

        $result = $service->match($validated['purchase_order_id'], $validated['invoice_id']);

        return $this->success(
            $result,
            __('Three-way matching completed')
        );
    }

    public function status(Request $request, ThreeWayMatchService $service): JsonResponse
    {
        $this->authorize('viewAny', \Dev3bdulrahman\Purchases\Models\GoodsReceiptNote::class);

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchases_orders,id',
        ]);

        $status = $service->getMatchingStatus($validated['purchase_order_id']);

        return $this->success(
            $status,
            __('Matching status retrieved successfully')
        );
    }

    public function report(Request $request, ThreeWayMatchService $service): JsonResponse
    {
        $this->authorize('viewAny', \Dev3bdulrahman\Purchases\Models\GoodsReceiptNote::class);

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchases_orders,id',
        ]);

        $report = $service->generateMatchingReport($validated['purchase_order_id']);

        return $this->success(
            $report,
            __('Matching report generated successfully')
        );
    }

    public function resolve(Request $request, ThreeWayMatchService $service): JsonResponse
    {
        $this->authorize('resolve', \Dev3bdulrahman\Purchases\Policies\ThreeWayMatchPolicy::class);

        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchases_orders,id',
            'type' => 'required|string',
            'resolution' => 'required|array',
        ]);

        $resolved = $service->resolveDiscrepancy(
            $validated['purchase_order_id'],
            $validated['type'],
            $validated['resolution']
        );

        if ($resolved) {
            return $this->success(null, __('Discrepancy resolved successfully'));
        }

        return $this->error(__('Failed to resolve discrepancy'), 500);
    }
}
