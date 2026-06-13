<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Suppliers;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Purchases\Services\SupplierService;
use Dev3bdulrahman\Purchases\Models\Supplier;
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
    public ?int $supplierId = null;
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $tax_number = '';
    public string $status = 'active';
    public ?int $branch_id = null;

    // Modal
    public bool $showFormModal = false;

    protected $listeners = ['delete' => 'deleteSupplier'];

    #[Layout('layouts.admin')]
    public function mount()
    {
        //
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
        $this->supplierId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->tax_number = '';
        $this->status = 'active';
        $this->branch_id = null;
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        $supplier = Supplier::findOrFail($id);

        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->email = $supplier->email ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->address = $supplier->address ?? '';
        $this->tax_number = $supplier->tax_number ?? '';
        $this->status = $supplier->status;
        $this->branch_id = $supplier->branch_id;

        $this->showFormModal = true;
    }

    public function save(SupplierService $service)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'branch_id' => 'nullable|exists:branches,id',
        ];

        $validated = $this->validate($rules);
        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;

        if ($this->supplierId) {
            $supplier = Supplier::findOrFail($this->supplierId);
            $service->updateSupplier($supplier, $validated);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_updated')]);
        } else {
            $service->createSupplier($validated);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_created')]);
        }

        $this->showFormModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->status = $supplier->status === 'active' ? 'inactive' : 'active';
        $supplier->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('purchases::purchases.success_updated'),
        ]);
    }

    public function deleteSupplier(SupplierService $service, $id)
    {
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if ($targetId) {
            $supplier = Supplier::findOrFail($targetId);
            $service->deleteSupplier($supplier);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('purchases::purchases.success_deleted')]);
        }
    }

    public function render(SupplierService $service)
    {
        $filters = [
            'search' => $this->search,
            'status' => $this->statusFilter,
        ];

        $suppliers = $service->listSuppliers($filters, 10);
        $branches = Branch::all();

        return view('purchases::livewire.admin.suppliers.index', [
            'suppliers' => $suppliers,
            'branches' => $branches,
        ])->title(__('purchases::purchases.suppliers'));
    }
}
