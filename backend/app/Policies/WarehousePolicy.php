<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    /**
     * Determine whether the user can view the warehouse.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->belongsToSeller($warehouse->seller_id);
    }

    /**
     * Determine whether the user can update the warehouse.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (!$user->belongsToSeller($warehouse->seller_id)) {
            return false;
        }

        return $user->isSellerOwner($warehouse->seller_id) || (bool) $user->sellerMember($warehouse->seller_id)?->hasPermission('manage_inventory');
    }

    /**
     * Determine whether the user can adjust warehouse inventory.
     */
    public function adjust(User $user, Warehouse $warehouse): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (!$user->belongsToSeller($warehouse->seller_id)) {
            return false;
        }

        return $user->isSellerOwner($warehouse->seller_id) || (bool) $user->sellerMember($warehouse->seller_id)?->hasPermission('manage_inventory');
    }
}
