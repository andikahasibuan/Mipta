<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\User;

class DistributionPolicy
{
    public function update(User $user, Distribution $distribution): bool
    {
        return $this->owns($user, $distribution);
    }

    public function delete(User $user, Distribution $distribution): bool
    {
        return $this->owns($user, $distribution);
    }

    private function owns(User $user, Distribution $distribution): bool
    {
        return $user->role === Role::Distributor
            && $user->distributor_id !== null
            && $user->distributor_id === $distribution->distributor_id;
    }
}
