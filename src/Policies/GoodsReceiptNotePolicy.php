<?php

namespace Dev3bdulrahman\Purchases\Policies;

use App\Models\User;
use Dev3bdulrahman\Purchases\Models\GoodsReceiptNote;

class GoodsReceiptNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.goods-receipt.view');
    }

    public function view(User $user, GoodsReceiptNote $goodsReceiptNote): bool
    {
        return $user->can('purchases.goods-receipt.view') && $goodsReceiptNote->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.goods-receipt.create');
    }

    public function update(User $user, GoodsReceiptNote $goodsReceiptNote): bool
    {
        return $user->can('purchases.goods-receipt.update') && $goodsReceiptNote->company_id === $user->company_id;
    }

    public function delete(User $user, GoodsReceiptNote $goodsReceiptNote): bool
    {
        return $user->can('purchases.goods-receipt.delete') && $goodsReceiptNote->company_id === $user->company_id;
    }

    public function receive(User $user, GoodsReceiptNote $goodsReceiptNote): bool
    {
        return $user->can('purchases.goods-receipt.receive') && $goodsReceiptNote->company_id === $user->company_id;
    }

    public function complete(User $user, GoodsReceiptNote $goodsReceiptNote): bool
    {
        return $user->can('purchases.goods-receipt.complete') && $goodsReceiptNote->company_id === $user->company_id;
    }
}
