<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\FieldDistributionReport;
use App\Models\UptdContributor;
use App\Models\User;

class FieldDistributionReportPolicy
{
    public function update(User $user, FieldDistributionReport $report, UptdContributor $contributor): bool
    {
        return $this->owns($user, $report, $contributor);
    }

    public function delete(User $user, FieldDistributionReport $report, UptdContributor $contributor): bool
    {
        return $this->owns($user, $report, $contributor);
    }

    private function owns(User $user, FieldDistributionReport $report, UptdContributor $contributor): bool
    {
        return $user->role === Role::UptdPasar
            && $user->isSharedUptdAccount()
            && $report->uptd_contributor_id === $contributor->id;
    }
}
