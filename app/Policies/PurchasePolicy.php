<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the purchase.
     */
    public function view(User $user, Purchase $purchase)
    {
        return $user->id === $purchase->user_id;
    }
}