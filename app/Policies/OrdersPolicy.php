<?php

namespace App\Policies;

use App\Models\Orders;
use App\Models\User;

class OrdersPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('browse_orders');
    }

    public function view(User $user, Orders $order): bool
    {
        return $user->hasPermission('read_orders');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('add_orders');
    }

    public function update(User $user, Orders $order): bool
    {
        return $user->hasPermission('edit_orders');
    }

    public function delete(User $user, Orders $order): bool
    {
        return $user->hasPermission('delete_orders');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('delete_orders');
    }
}
