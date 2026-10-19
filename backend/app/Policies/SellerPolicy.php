<?php

namespace App\Policies;

use App\Models\Seller;
use App\Models\User;

class SellerPolicy
{
    /**
     * Determine whether the user can view the seller profile.
     */
    public function view(User $user, Seller $seller): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->belongsToSeller($seller->id);
    }

    /**
     * Determine whether the user can update the seller profile.
     */
    public function update(User $user, Seller $seller): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (!$user->belongsToSeller($seller->id)) {
            return false;
        }

        return $user->isSellerOwner($seller->id) || (bool) $user->sellerMember($seller->id)?->hasPermission('manage_seller_profile');
    }

    /**
     * Determine whether the user can manage seller staff/members.
     */
    public function manageMembers(User $user, Seller $seller): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (!$user->belongsToSeller($seller->id)) {
            return false;
        }

        return $user->isSellerOwner($seller->id) || (bool) $user->sellerMember($seller->id)?->hasPermission('manage_seller_members');
    }
}
