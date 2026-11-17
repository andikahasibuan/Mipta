<?php

namespace App\Providers;

use App\Services\NotificationFeedService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.partials.header', function (ViewInstance $view): void {
            $user = auth()->user();

            if ($user === null || ! $user->role?->canReceiveNotifications()) {
                return;
            }

            $summary = app(NotificationFeedService::class)->summaryFor($user);

            $view->with([
                'notifications' => $summary['notifications'],
                'unreadNotificationCount' => $summary['unread_count'],
            ]);
        });
    }
}
