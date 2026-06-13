<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Requests;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Purchases\Services\PurchaseRequestService;
use Dev3bdulrahman\Purchases\Models\PurchaseRequest;
use Dev3bdulrahman\Purchases\Models\Supplier;
use App\Models\Product;
use App\Models\Branch;

class Index extends Component
{
    use WithPagination;

    // Filters
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    // Form fields
    public ?int $requestId = null;
    public string $request_number = '';
    public string $request_date = '';
    public string $status = 'draft';
    public ?int $branch_id = null;
    public string $notes = '';

    // Items list
    public array $items = [];

    // Totals
    public float $subtotal = 0.0000;
    public float $tax_total = 0.0000;
    public float $discount_total = 0.0000;
    public float $grand_total = 0.0000;

    // Modals
    public bool $showFormModal = false;
    public bool $showConvertModal = false;

    // Conversion fields
    public ?int $supplier_id = null;
    public string $order_number = '';
    public string $order_date = '';
    public string $delivery_date = '';
    public float $tax_rate = 15.00;
    public string $conversion_notes = '';

    protected $listeners = ['delete' => 'deleteRequest'];

    #[Layout('layouts.admin')]
    public function mount()
    {
        $this->request_date = now()->format('Y-m-d');
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
        $this->requestId = null;
        $this->request_number = 'PR-' . strtoupper(uniqid());
        $this->request_date = now()->format('Y-m-d');
        $this->status = 'draft';
        $this->branch_id = null;
        $this->notes = '';
        $this->items = [];
        $this->subtotal = 0.00;
        $this->tax_total = 0.00;
        $this->discount_total = 0.00;
        $this->grand_total = 0.00;
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->addItem();
        $this->showFormModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $request = PurchaseRequest::with('items')->findOrFail($id);

        $this->requestId = $request->id;
        $this->request_number = $request->request_number;
        $this->request_date = $request->request_date->format('Y-m-d');
        $this->status = $request->status;
        $this->branch_id = $request->branch_id;
        $this->notes = $request->notes ?? '';

        foreach ($request->items as $item) {
            $this->items[] = [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => (float)$item->quantity,
                'unit_price' => (float)$item->unit_price,
                'total' => (float)$item->total,
            ];
        }

        $this->recalculateTotals();
        $this->showFormModal = true;
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => null,
            'product_variant_id' => null,
            'quantity' => 1,
            'unit_price' => 0.00,
            'total' => 0.00,
        ];
        $this->recalculateTotals();
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalculateTotals();
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2 && $parts[1] === 'product_id' && $value) {
            $product = Product::find($value);
            if ($product) {
                $this->items[$parts[0]]['unit_price'] = (float)$product->purchase_price;
            }
        }

        $this->recalculateTotals();
    }

    public function recalculateTotals()
    {
        $this->subtotal = 0.00;

        foreach ($this->items as $index => $item) {
            $qty = (float)($item['quantity'] ?? 0);
            $price = (float)($item['unit_price'] ?? 0);
            $itemTotal = $qty * $price;

            $this->items[$index]['total'] = $itemTotal;
            $this->subtotal += $itemTotal;
        }

        $this->grand_total = $this->subtotal;
    }

    public function save(PurchaseRequestService $service)
    {
        $rules = [
            'request_number' => 'required|string|max:255',
            'request_date' => 'required|date',
            'status' => 'required|in:draft,pending,approved,rejected',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ];

        $this->validate($rules);

        $data = [
            'company_id' => session('active_company_id') ?: auth()->user()->company_id,
            'request_number' => $this->request_number,
            'request_date' => $this->request_date,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'notes' => $this->notes,
        ];

        if ($this->requestId) {
            $request = PurchaseRequest::findOrFail($this->requestId);
            $service->updateRequest($request, $data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_updated')]);
        } else {
            $service->createRequest($data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_created')]);
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteRequest(PurchaseRequestService $service, $id)
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($targetId) {
            $request = PurchaseRequest::findOrFail($targetId);
            $service->deleteRequest($request);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_deleted')]);
        }
    }

    public function openConvertModal($id)
    {
        $request = PurchaseRequest::findOrFail($id);
        $this->requestId = $request->id;
        $this->order_number = 'PO-' . strtoupper(uniqid());
        $this->order_date = now()->format('Y-m-d');
        $this->delivery_date = now()->addDays(7)->format('Y-m-d');
        $this->tax_rate = 15.00;
        $this->conversion_notes = $request->notes ?? '';
        $this->showConvertModal = true;
    }

    public function convert(PurchaseRequestService $service)
    {
        $rules = [
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'order_number' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'conversion_notes' => 'nullable|string',
        ];

        $this->validate($rules);

        $request = PurchaseRequest::findOrFail($this->requestId);
        $service->convertToOrder($request, [
            'supplier_id' => $this->supplier_id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date ?: null,
            'tax_rate' => $this->tax_rate,
            'notes' => $this->conversion_notes,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.convert_to_order') . ' ' . __('purchases::purchases.success_created')]);
        $this->showConvertModal = false;
    }

    public function render(PurchaseRequestService $service)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->statusFilter,
        ];

        $requests = $service->listRequests($filters, 10);
        $products = Product::active()->get();
        $branches = Branch::all();
        $suppliers = Supplier::all();

        return view('purchases::livewire.admin.requests.index', [
            'requests' => $requests,
            'products' => $products,
            'branches' => $branches,
            'suppliers' => $suppliers,
        ])->title(__('purchases::purchases.requests'));
    }
}
