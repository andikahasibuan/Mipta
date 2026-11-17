<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\RecipientStore;
use App\Models\User;

class RecipientStorePolicy
{
    public function update(User $user, RecipientStore $store): bool
    {
        return $user->role === Role::Distributor
            && $user->distributor_id !== null
            && $user->distributor_id === $store->distributor_id;
    }
}
