<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Returns;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Purchases\Services\PurchaseReturnService;
use Dev3bdulrahman\Purchases\Models\PurchaseReturn;
use Dev3bdulrahman\Purchases\Models\Supplier;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
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

    #[Url(as: 'supplier')]
    public string $supplierFilter = '';

    // Form fields
    public ?int $returnId = null;
    public ?int $supplier_id = null;
    public ?int $supplier_invoice_id = null;
    public string $return_number = '';
    public string $return_date = '';
    public string $status = 'completed';
    public ?int $branch_id = null;
    public string $reason = '';

    // Items list
    public array $items = [];

    // Totals
    public float $subtotal = 0.0000;
    public float $tax_total = 0.0000;
    public float $discount_total = 0.0000;
    public float $grand_total = 0.0000;

    // Modal
    public bool $showFormModal = false;

    protected $listeners = ['delete' => 'deleteReturn'];

    #[Layout('layouts.admin')]
    public function mount()
    {
        $this->return_date = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSupplierFilter()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->returnId = null;
        $this->supplier_id = null;
        $this->supplier_invoice_id = null;
        $this->return_number = 'RET-' . strtoupper(uniqid());
        $this->return_date = now()->format('Y-m-d');
        $this->status = 'completed';
        $this->branch_id = null;
        $this->reason = '';
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
        $return = PurchaseReturn::with('items')->findOrFail($id);

        $this->returnId = $return->id;
        $this->supplier_id = $return->supplier_id;
        $this->supplier_invoice_id = $return->supplier_invoice_id;
        $this->return_number = $return->return_number;
        $this->return_date = $return->return_date->format('Y-m-d');
        $this->status = $return->status;
        $this->branch_id = $return->branch_id;
        $this->reason = $return->reason ?? '';

        foreach ($return->items as $item) {
            $this->items[] = [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => (float)$item->quantity,
                'unit_price' => (float)$item->unit_price,
                'tax_rate' => (float)$item->tax_rate,
                'discount_amount' => (float)$item->discount_amount,
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
            'tax_rate' => 15.00,
            'discount_amount' => 0.00,
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
        $this->tax_total = 0.00;
        $this->discount_total = 0.00;

        foreach ($this->items as $index => $item) {
            $qty = (float)($item['quantity'] ?? 0);
            $price = (float)($item['unit_price'] ?? 0);
            $discount = (float)($item['discount_amount'] ?? 0);
            $taxRate = (float)($item['tax_rate'] ?? 0);

            $itemSub = $qty * $price;
            $itemTax = ($itemSub - $discount) * ($taxRate / 100);
            $itemTotal = $itemSub - $discount + $itemTax;

            $this->items[$index]['total'] = $itemTotal;

            $this->subtotal += $itemSub;
            $this->discount_total += $discount;
            $this->tax_total += $itemTax;
        }

        $this->grand_total = $this->subtotal - $this->discount_total + $this->tax_total;
    }

    public function save(PurchaseReturnService $service)
    {
        $rules = [
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'supplier_invoice_id' => 'nullable|exists:purchases_invoices,id',
            'return_number' => 'required|string|max:255',
            'return_date' => 'required|date',
            'status' => 'required|in:pending,approved,rejected,completed',
            'branch_id' => 'nullable|exists:branches,id',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ];

        $this->validate($rules);

        $data = [
            'company_id' => session('active_company_id') ?: auth()->user()->company_id,
            'supplier_id' => $this->supplier_id,
            'supplier_invoice_id' => $this->supplier_invoice_id,
            'return_number' => $this->return_number,
            'return_date' => $this->return_date,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'reason' => $this->reason,
        ];

        if ($this->returnId) {
            $return = PurchaseReturn::findOrFail($this->returnId);
            // Delete and recreate return is done in service delete + create
            $service->deleteReturn($return);
            $service->createReturn($data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_updated')]);
        } else {
            $service->createReturn($data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_created')]);
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteReturn(PurchaseReturnService $service, $id)
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($targetId) {
            $return = PurchaseReturn::findOrFail($targetId);
            $service->deleteReturn($return);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_deleted')]);
        }
    }

    public function render(PurchaseReturnService $service)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'supplier_id' => $this->supplierFilter,
        ];

        $returns = $service->listReturns($filters, 10);
        $suppliers = Supplier::all();
        $invoices = SupplierInvoice::all();
        $products = Product::active()->get();
        $branches = Branch::all();

        return view('purchases::livewire.admin.returns.index', [
            'returns' => $returns,
            'suppliers' => $suppliers,
            'invoices' => $invoices,
            'products' => $products,
            'branches' => $branches,
        ])->title(__('purchases::purchases.returns'));
    }
}
