<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use App\Notifications\RecipientStoreCreated;
use App\Notifications\RecipientStoreUpdated;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class CleanupStoreNotificationsForKepalaDinas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup-store-recipients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus notifikasi toko lama yang sudah terlanjur dikirim ke kepala dinas.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $kepalaDinasIds = User::query()
            ->where('role', Role::KepalaDinas->value)
            ->pluck('id');

        $deleted = DatabaseNotification::query()
            ->whereIn('notifiable_id', $kepalaDinasIds)
            ->whereIn('type', [RecipientStoreCreated::class, RecipientStoreUpdated::class])
            ->delete();

        $this->info("{$deleted} notifikasi toko milik kepala dinas berhasil dihapus.");

        return self::SUCCESS;
    }
}
