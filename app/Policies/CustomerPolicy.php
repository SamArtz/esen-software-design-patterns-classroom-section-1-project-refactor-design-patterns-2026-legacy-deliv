<?php

use Illuminate\Auth\Access\Response;
namespace App\Policies;

use App\Models\User;
use App\Models\Customer;

class CustomerPolicy
{
    /**
     * Determina si el usuario puede ver o actualizar su perfil de cliente.
     */
    public function viewOrUpdate(User $user, Customer $customer): bool
    {
        // 1. El usuario debe tener el rol 'customer'
        // 2. El registro del customer debe pertenecer al usuario autenticado
        return $user->role === 'customer' && $customer->user_id === $user->id;
    }

    /**
     * Opcional: Si tienes un panel administrativo en ExpBuddy
     */
    public function before(User $user, $ability)
    {
        if ($user->role === 'admin') {
            return true;
        }
    }
}
