<?php

namespace App\Services;

use App\Notifications\DistributionReportCreated;
use App\Notifications\DistributionReportDeleted;
use App\Notifications\DistributionReportUpdated;
use App\Notifications\FieldDistributionReportCreated;
use App\Notifications\FieldDistributionReportDeleted;
use App\Notifications\FieldDistributionReportUpdated;
use App\Notifications\RecipientStoreCreated;
use App\Notifications\RecipientStoreUpdated;
use Illuminate\Notifications\DatabaseNotification;

class NotificationTemplateRenderer
{
    /**
     * Render judul dan pesan berdasarkan key template.
     *
     * @param  array<string, string>  $replacements
     * @return array{title: string, message: string}
     */
    public function render(string $key, array $replacements): array
    {
        $template = config("notifications.templates.{$key}");

        if (! is_array($template)) {
            return [
                'title' => 'Pemberitahuan',
                'message' => '',
            ];
        }

        $pairs = [];

        foreach ($replacements as $placeholder => $value) {
            $pairs['{'.$placeholder.'}'] = (string) $value;
        }

        return [
            'title' => (string) ($template['title'] ?? 'Pemberitahuan'),
            'message' => strtr((string) ($template['message'] ?? ''), $pairs),
        ];
    }

    /**
     * Tentukan key template dari class notifikasi.
     */
    public function keyForType(string $type): ?string
    {
        return match ($type) {
            DistributionReportCreated::class => 'distribution_report_created',
            DistributionReportUpdated::class => 'distribution_report_updated',
            DistributionReportDeleted::class => 'distribution_report_deleted',
            RecipientStoreCreated::class => 'recipient_store_created',
            RecipientStoreUpdated::class => 'recipient_store_updated',
            FieldDistributionReportCreated::class => 'field_distribution_report_created',
            FieldDistributionReportUpdated::class => 'field_distribution_report_updated',
            FieldDistributionReportDeleted::class => 'field_distribution_report_deleted',
            default => null,
        };
    }

    /**
     * Render ulang notifikasi lama berdasarkan data yang tersimpan.
     *
     * @return array{title: string, message: string}|null
     */
    public function renderForNotification(DatabaseNotification $notification): ?array
    {
        $key = $this->keyForType($notification->type);

        if ($key === null) {
            return null;
        }

        return $this->render($key, $this->replacementsFor($key, $notification->data));
    }

    /**
     * Petakan data notifikasi ke placeholder template.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function replacementsFor(string $key, array $data): array
    {
        $distributor = (string) ($data['distributor_name'] ?? '—');
        $store = (string) ($data['store_name'] ?? '—');
        $contributor = (string) ($data['contributor_name'] ?? '—');
        $distributionDate = (string) ($data['distribution_date'] ?? '—');
        $reportDate = (string) ($data['report_date'] ?? '—');

        return match ($key) {
            'distribution_report_created' => [
                'nama distributor' => $distributor,
            ],
            'distribution_report_updated', 'distribution_report_deleted' => [
                'tanggal' => $distributionDate,
                'nama distributor' => $distributor,
            ],
            'recipient_store_created', 'recipient_store_updated' => [
                'nama toko' => $store,
                'nama distributor' => $distributor,
            ],
            'field_distribution_report_created' => [
                'nama kontributor pasar' => $contributor,
            ],
            'field_distribution_report_updated', 'field_distribution_report_deleted' => [
                'tanggal' => $reportDate,
                'nama kontributor pasar' => $contributor,
            ],
            default => [],
        };
    }
}
