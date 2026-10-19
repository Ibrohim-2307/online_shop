<?php

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
    /**
     * Determine whether the user can view the address.
     */
    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the address.
     */
    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the address.
     */
    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->hasRole('admin');
    }
}
