<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'manager', 'employee', 'finance']);
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($user->hasAnyRole(['super-admin', 'finance'])) {
            return true;
        }

        if ($user->hasRole('manager')) {
            $teamIds = User::where('department_id', $user->department_id)->pluck('id');
            return $customer->tasks()->where('department_id', $user->department_id)->exists()
                || $customer->queries()->whereIn('assigned_to', $teamIds)->exists();
        }

        if ($user->hasRole('employee')) {
            return $customer->tasks()->where('assigned_to', $user->id)->exists()
                || $customer->queries()->where('assigned_to', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'manager', 'employee']);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasRole('super-admin');
    }

    public function addInteraction(User $user, Customer $customer): bool
    {
        return $this->view($user, $customer) && !$user->hasRole('finance');
    }

    public function viewFinancials(User $user, Customer $customer): bool
    {
        return $user->hasAnyRole(['super-admin', 'manager', 'finance']) || $this->view($user, $customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasRole('super-admin');
    }
}
