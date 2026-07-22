<?php

namespace Dev3bdulrahman\Purchases\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceiptNote extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'purchases_goods_receipt_notes';

    protected $fillable = [
        'company_id', 'branch_id', 'purchase_order_id', 'supplier_id',
        'grn_number', 'receipt_date', 'status', 'notes', 'received_by',
        'quality_check_passed', 'quality_notes', 'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'quality_check_passed' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptNoteItem::class, 'goods_receipt_note_id');
    }
}
