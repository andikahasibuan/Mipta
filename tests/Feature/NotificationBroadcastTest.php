<?php

use App\Models\User;
use App\Notifications\DistributionReportCreated;
use App\Notifications\DistributionReportDeleted;
use App\Notifications\DistributionReportUpdated;
use App\Notifications\FieldDistributionReportCreated;
use App\Notifications\FieldDistributionReportDeleted;
use App\Notifications\FieldDistributionReportUpdated;
use App\Notifications\RecipientStoreCreated;
use App\Notifications\RecipientStoreUpdated;

it('mengirim notifikasi melalui channel database dan broadcast', function (string $notificationClass) {
    $notification = match ($notificationClass) {
        DistributionReportCreated::class,
        DistributionReportDeleted::class,
        DistributionReportUpdated::class => new $notificationClass('20/05/2026', 'PT Contoh', 1),
        FieldDistributionReportCreated::class,
        FieldDistributionReportDeleted::class,
        FieldDistributionReportUpdated::class => new $notificationClass('20/05/2026', 'Pasar Raya', 'Kontributor', 1),
        RecipientStoreCreated::class,
        RecipientStoreUpdated::class => new $notificationClass('Toko Contoh', 'Pasar Raya', 'PT Contoh', 1),
    };

    expect($notification->via(new User))
        ->toContain('database')
        ->toContain('broadcast');
})->with([
    DistributionReportCreated::class,
    DistributionReportDeleted::class,
    DistributionReportUpdated::class,
    FieldDistributionReportCreated::class,
    FieldDistributionReportDeleted::class,
    FieldDistributionReportUpdated::class,
    RecipientStoreCreated::class,
    RecipientStoreUpdated::class,
]);
