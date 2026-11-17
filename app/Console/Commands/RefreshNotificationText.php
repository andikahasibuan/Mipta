<?php

namespace App\Console\Commands;

use App\Services\NotificationTemplateRenderer;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class RefreshNotificationText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:refresh-text';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui judul dan pesan notifikasi lama memakai template terbaru.';

    /**
     * Execute the console command.
     */
    public function handle(NotificationTemplateRenderer $renderer): int
    {
        $updated = 0;

        DatabaseNotification::query()
            ->chunkById(100, function ($notifications) use ($renderer, &$updated): void {
                foreach ($notifications as $notification) {
                    $template = $renderer->renderForNotification($notification);

                    if ($template === null) {
                        continue;
                    }

                    $data = array_merge($notification->data, $template);

                    if ($data === $notification->data) {
                        continue;
                    }

                    $notification->forceFill(['data' => $data])->save();
                    $updated++;
                }
            });

        $this->info("{$updated} notifikasi berhasil diperbarui.");

        return self::SUCCESS;
    }
}
