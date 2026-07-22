<?php

namespace Dev3bdulrahman\Purchases\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SupplierPayment extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'purchases_payments';

    protected $fillable = [
        'company_id',
        'branch_id',
        'supplier_invoice_id',
        'payment_number',
        'payment_date',
        'payment_method',
        'amount',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class, 'supplier_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
