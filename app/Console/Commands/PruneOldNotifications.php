<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class PruneOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus semua notifikasi yang sudah berumur tiga bulan.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = DatabaseNotification::query()
            ->where('created_at', '<=', now()->subMonths(3))
            ->delete();

        $this->info("{$deleted} notifikasi lama berhasil dihapus.");

        return self::SUCCESS;
    }
}
