<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\GoodsReceipt;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Purchases\Services\GoodsReceiptNoteService;
use Dev3bdulrahman\Purchases\Models\GoodsReceiptNote;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Dev3bdulrahman\Purchases\Models\Supplier;
use App\Models\Branch;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    public ?int $grnId = null;
    public ?int $purchase_order_id = null;
    public ?int $supplier_id = null;
    public string $grn_number = '';
    public string $receipt_date = '';
    public string $status = 'draft';
    public ?int $branch_id = null;
    public string $notes = '';
    public string $received_by = '';
    public bool $quality_check_passed = false;
    public string $quality_notes = '';

    public array $items = [];

    public bool $showFormModal = false;
    public bool $showItemsModal = false;
    public ?int $viewingGrnId = null;

    protected $listeners = ['delete' => 'deleteGrn'];

    #[Layout('layouts.admin')]
    public function mount()
    {
        $this->receipt_date = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->grnId = null;
        $this->purchase_order_id = null;
        $this->supplier_id = null;
        $this->grn_number = 'GRN-' . strtoupper(uniqid());
        $this->receipt_date = now()->format('Y-m-d');
        $this->status = 'draft';
        $this->branch_id = null;
        $this->notes = '';
        $this->received_by = '';
        $this->quality_check_passed = false;
        $this->quality_notes = '';
        $this->items = [];
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $grn = GoodsReceiptNote::with('items')->findOrFail($id);

        $this->grnId = $grn->id;
        $this->purchase_order_id = $grn->purchase_order_id;
        $this->supplier_id = $grn->supplier_id;
        $this->grn_number = $grn->grn_number;
        $this->receipt_date = $grn->receipt_date->format('Y-m-d');
        $this->status = $grn->status;
        $this->branch_id = $grn->branch_id;
        $this->notes = $grn->notes ?? '';
        $this->received_by = $grn->received_by ?? '';
        $this->quality_check_passed = $grn->quality_check_passed;
        $this->quality_notes = $grn->quality_notes ?? '';

        foreach ($grn->items as $item) {
            $this->items[] = [
                'purchase_order_item_id' => $item->purchase_order_item_id,
                'product_id' => $item->product_id,
                'ordered_quantity' => (float)$item->ordered_quantity,
                'received_quantity' => (float)$item->received_quantity,
                'accepted_quantity' => (float)$item->accepted_quantity,
                'rejected_quantity' => (float)$item->rejected_quantity,
                'rejection_reason' => $item->rejection_reason,
                'batch_id' => $item->batch_id,
                'serial_number_id' => $item->serial_number_id,
                'unit_price' => (float)$item->unit_price,
                'subtotal' => (float)$item->subtotal,
            ];
        }

        $this->showFormModal = true;
    }

    public function openItemsModal($id)
    {
        $this->viewingGrnId = $id;
        $this->showItemsModal = true;
    }

    public function updatedPurchaseOrderId($value)
    {
        if ($value) {
            $order = PurchaseOrder::with('items.product')->find($value);
            if ($order) {
                $this->supplier_id = $order->supplier_id;
                $this->items = [];
                foreach ($order->items as $poItem) {
                    $this->items[] = [
                        'purchase_order_item_id' => $poItem->id,
                        'product_id' => $poItem->product_id,
                        'product_name' => $poItem->product?->translated_name ?? 'Product #' . $poItem->product_id,
                        'ordered_quantity' => (float)$poItem->quantity,
                        'received_quantity' => (float)$poItem->quantity,
                        'accepted_quantity' => (float)$poItem->quantity,
                        'rejected_quantity' => 0,
                        'rejection_reason' => '',
                        'batch_id' => '',
                        'serial_number_id' => '',
                        'unit_price' => (float)$poItem->unit_price,
                        'subtotal' => (float)$poItem->quantity * (float)$poItem->unit_price,
                    ];
                }
            }
        }
    }

    public function save(GoodsReceiptNoteService $service)
    {
        $rules = [
            'purchase_order_id' => 'required|exists:purchases_orders,id',
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'grn_number' => 'required|string|max:255',
            'receipt_date' => 'required|date',
            'status' => 'required|in:draft,partial,completed,cancelled',
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
        ];

        $this->validate($rules);

        $data = [
            'company_id' => session('active_company_id') ?: auth()->user()->company_id,
            'purchase_order_id' => $this->purchase_order_id,
            'supplier_id' => $this->supplier_id,
            'grn_number' => $this->grn_number,
            'receipt_date' => $this->receipt_date,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'notes' => $this->notes,
            'received_by' => $this->received_by,
            'quality_check_passed' => $this->quality_check_passed,
            'quality_notes' => $this->quality_notes,
        ];

        if ($this->grnId) {
            $service->updateGrn($this->grnId, $data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_updated')]);
        } else {
            $service->createGrn($data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_created')]);
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteGrn(GoodsReceiptNoteService $service, $id)
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($targetId) {
            $service->deleteGrn($targetId);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_deleted')]);
        }
    }

    public function completeGrn(GoodsReceiptNoteService $service, $id)
    {
        $service->completeGrn($id);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.goods_receipt_completed')]);
    }

    public function render(GoodsReceiptNoteService $service)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->statusFilter,
        ];

        $grns = $service->listGrns($filters, 10);
        $orders = PurchaseOrder::whereIn('status', ['confirmed', 'received'])->get();
        $suppliers = Supplier::all();
        $branches = Branch::all();

        return view('purchases::livewire.admin.goods-receipt.index', [
            'grns' => $grns,
            'orders' => $orders,
            'suppliers' => $suppliers,
            'branches' => $branches,
        ])->title(__('purchases::purchases.goods_receipt'));
    }
}
