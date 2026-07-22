<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Web\Admin\Matching;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Dev3bdulrahman\Purchases\Services\ThreeWayMatchService;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;

class Index extends Component
{
    #[Url(as: 'po')]
    public string $selectedPo = '';

    #[Url(as: 'inv')]
    public string $selectedInvoice = '';

    public ?array $matchResult = null;
    public ?array $matchingStatus = null;

    #[Layout('layouts.admin')]
    public function runMatch(ThreeWayMatchService $service)
    {
        if (!$this->selectedPo || !$this->selectedInvoice) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => __('purchases::purchases.select_po_and_invoice')]);
            return;
        }

        $this->matchResult = $service->match((int)$this->selectedPo, (int)$this->selectedInvoice);
        $this->matchingStatus = $service->getMatchingStatus((int)$this->selectedPo);
    }

    public function render(ThreeWayMatchService $service)
    {
        $orders = PurchaseOrder::with('supplier')->whereIn('status', ['received'])->get();
        $invoices = SupplierInvoice::with('supplier')->whereIn('status', ['unpaid', 'partially_paid'])->get();

        if ($this->selectedPo && !$this->matchResult) {
            $this->matchingStatus = $service->getMatchingStatus((int)$this->selectedPo);
        }

        return view('purchases::livewire.admin.matching.index', [
            'orders' => $orders,
            'invoices' => $invoices,
        ])->title(__('purchases::purchases.three_way_match'));
    }
}
