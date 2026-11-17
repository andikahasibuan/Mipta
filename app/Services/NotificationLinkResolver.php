<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\FieldDistributionReport;
use App\Models\RecipientStore;
use App\Models\User;
use App\Notifications\DistributionReportCreated;
use App\Notifications\DistributionReportDeleted;
use App\Notifications\DistributionReportUpdated;
use App\Notifications\FieldDistributionReportCreated;
use App\Notifications\FieldDistributionReportDeleted;
use App\Notifications\FieldDistributionReportUpdated;
use App\Notifications\RecipientStoreCreated;
use App\Notifications\RecipientStoreUpdated;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Throwable;

class NotificationLinkResolver
{
    /**
     * Tentukan URL menu tujuan berdasarkan tipe notifikasi.
     */
    public function resolve(DatabaseNotification $notification, User $user): ?string
    {
        $data = $notification->data;

        return match ($notification->type) {
            DistributionReportCreated::class => $this->distributionUrl($data, $user, withHighlight: true),
            DistributionReportUpdated::class => $this->distributionUrl($data, $user, withHighlight: true),
            DistributionReportDeleted::class => $this->distributionUrl($data, $user, withHighlight: false),
            FieldDistributionReportCreated::class => $this->fieldReportUrl($data, $user, withHighlight: true),
            FieldDistributionReportUpdated::class => $this->fieldReportUrl($data, $user, withHighlight: true),
            FieldDistributionReportDeleted::class => $this->fieldReportUrl($data, $user, withHighlight: false),
            RecipientStoreCreated::class, RecipientStoreUpdated::class => $this->storeUrl($data, $user),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function distributionUrl(array $data, User $user, bool $withHighlight): ?string
    {
        $route = $this->reportRoute($user, 'distribution');

        if ($route === null) {
            return null;
        }

        $params = $this->monthParams($data['distribution_date'] ?? null);

        if ($withHighlight) {
            $highlightId = $this->existingId($data['distribution_id'] ?? null, Distribution::class);

            if ($highlightId !== null) {
                $params['highlight'] = $highlightId;
            }
        }

        return route($route, $params);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function fieldReportUrl(array $data, User $user, bool $withHighlight): ?string
    {
        $route = $this->reportRoute($user, 'supervision');

        if ($route === null) {
            return null;
        }

        $params = $this->monthParams($data['report_date'] ?? null);

        if ($withHighlight) {
            $highlightId = $this->existingId($data['report_id'] ?? null, FieldDistributionReport::class);

            if ($highlightId !== null) {
                $params['highlight'] = $highlightId;
            }
        }

        return route($route, $params);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeUrl(array $data, User $user): ?string
    {
        if ($user->role !== Role::Admin) {
            return null;
        }

        $params = [];
        $highlightId = $this->existingId($data['store_id'] ?? null, RecipientStore::class);

        if ($highlightId !== null) {
            $params['highlight'] = $highlightId;
        }

        return route('admin.recipient-stores.index', $params);
    }

    private function reportRoute(User $user, string $menu): ?string
    {
        return match ($user->role) {
            Role::Admin => "admin.{$menu}.index",
            Role::KepalaDinas => "kepala-dinas.{$menu}.index",
            default => null,
        };
    }

    /**
     * @return array{month?: string}
     */
    private function monthParams(mixed $date): array
    {
        if (! is_string($date) || $date === '') {
            return [];
        }

        try {
            return ['month' => Carbon::createFromFormat('d/m/Y', $date)->format('Y-m')];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param  class-string<Distribution|FieldDistributionReport|RecipientStore>  $model
     */
    private function existingId(mixed $value, string $model): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $id = (int) $value;

        return $model::query()->whereKey($id)->exists() ? $id : null;
    }
}
