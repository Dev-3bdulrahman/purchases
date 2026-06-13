<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Invoices;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Purchases\Services\SupplierInvoiceService;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
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

    #[Url(as: 'supplier')]
    public string $supplierFilter = '';

    // Form fields
    public ?int $invoiceId = null;
    public ?int $supplier_id = null;
    public ?int $purchase_order_id = null;
    public string $invoice_number = '';
    public string $invoice_date = '';
    public string $due_date = '';
    public string $status = 'unpaid';
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
    public bool $showPaymentModal = false;

    // Payment fields
    public string $payment_number = '';
    public string $payment_date = '';
    public string $payment_method = 'cash';
    public float $amount = 0.00;
    public string $reference_number = '';
    public string $payment_notes = '';

    protected $listeners = ['delete' => 'deleteInvoice'];

    #[Layout('layouts.admin')]
    public function mount()
    {
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
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
        $this->invoiceId = null;
        $this->supplier_id = null;
        $this->purchase_order_id = null;
        $this->invoice_number = 'PINV-' . strtoupper(uniqid());
        $this->invoice_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->status = 'unpaid';
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
        $invoice = SupplierInvoice::with('items')->findOrFail($id);

        $this->invoiceId = $invoice->id;
        $this->supplier_id = $invoice->supplier_id;
        $this->purchase_order_id = $invoice->purchase_order_id;
        $this->invoice_number = $invoice->invoice_number;
        $this->invoice_date = $invoice->invoice_date->format('Y-m-d');
        $this->due_date = $invoice->due_date->format('Y-m-d');
        $this->status = $invoice->status;
        $this->branch_id = $invoice->branch_id;
        $this->notes = $invoice->notes ?? '';

        foreach ($invoice->items as $item) {
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

    public function save(SupplierInvoiceService $service)
    {
        $rules = [
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchases_orders,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'status' => 'required|in:draft,unpaid,partially_paid,paid,overdue,cancelled',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
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
            'purchase_order_id' => $this->purchase_order_id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'notes' => $this->notes,
        ];

        if ($this->invoiceId) {
            $invoice = SupplierInvoice::findOrFail($this->invoiceId);
            $service->updateInvoice($invoice, $data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_updated')]);
        } else {
            $service->createInvoice($data, $this->items);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_created')]);
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteInvoice(SupplierInvoiceService $service, $id)
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($targetId) {
            $invoice = SupplierInvoice::findOrFail($targetId);
            $service->deleteInvoice($invoice);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_deleted')]);
        }
    }

    public function openPaymentModal($id)
    {
        $invoice = SupplierInvoice::findOrFail($id);
        $this->invoiceId = $invoice->id;
        $this->payment_number = 'PAY-' . strtoupper(uniqid());
        $this->payment_date = now()->format('Y-m-d');
        $this->amount = (float)($invoice->grand_total - $invoice->paid_amount);
        $this->payment_method = 'cash';
        $this->reference_number = '';
        $this->payment_notes = '';
        $this->showPaymentModal = true;
    }

    public function savePayment(SupplierInvoiceService $service)
    {
        $rules = [
            'payment_number' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,card,check,online',
            'amount' => 'required|numeric|min:0.0001',
            'reference_number' => 'nullable|string|max:255',
            'payment_notes' => 'nullable|string',
        ];

        $this->validate($rules);

        $invoice = SupplierInvoice::findOrFail($this->invoiceId);
        $service->recordPayment($invoice, [
            'payment_number' => $this->payment_number,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'reference_number' => $this->reference_number,
            'notes' => $this->payment_notes,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_created')]);
        $this->showPaymentModal = false;
    }

    public function render(SupplierInvoiceService $service)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'supplier_id' => $this->supplierFilter,
        ];

        $invoices = $service->listInvoices($filters, 10);
        $suppliers = Supplier::all();
        $products = Product::active()->get();
        $branches = Branch::all();

        return view('purchases::livewire.admin.invoices.index', [
            'invoices' => $invoices,
            'suppliers' => $suppliers,
            'products' => $products,
            'branches' => $branches,
        ])->title(__('purchases::purchases.invoices'));
    }
}
