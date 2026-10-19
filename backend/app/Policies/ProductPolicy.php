<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view the product.
     */
    public function view(?User $user, Product $product): bool
    {
        if ($product->is_active && $product->status === 'approved') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin', 'moderator')) {
            return true;
        }

        return $user->belongsToSeller($product->seller_id);
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Strict multi-tenant isolation: User must belong to this product's seller
        if (!$user->belongsToSeller($product->seller_id)) {
            return false;
        }

        return $user->isSellerOwner($product->seller_id) || (bool) $user->sellerMember($product->seller_id)?->hasPermission('manage_products');
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (!$user->belongsToSeller($product->seller_id)) {
            return false;
        }

        return $user->isSellerOwner($product->seller_id);
    }
}
