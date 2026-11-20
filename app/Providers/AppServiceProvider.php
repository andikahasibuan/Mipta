<?php

namespace App\Providers;

use App\Models\Distribution;
use App\Models\FieldDistributionReport;
use App\Models\RecipientStore;
use App\Policies\DistributionPolicy;
use App\Policies\FieldDistributionReportPolicy;
use App\Policies\RecipientStorePolicy;
use App\Services\HelpCenterService;
use App\Services\NotificationFeedService;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        Gate::policy(Distribution::class, DistributionPolicy::class);
        Gate::policy(RecipientStore::class, RecipientStorePolicy::class);
        Gate::policy(FieldDistributionReport::class, FieldDistributionReportPolicy::class);

        if (config('app.trusted_proxies') !== []) {
            TrustProxies::at(config('app.trusted_proxies'));
        }

        if ($this->app->environment('production')) {
            config([
                'app.debug' => false,
                'session.encrypt' => true,
                'session.secure' => true,
            ]);

            URL::forceScheme('https');
        }

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

        View::composer('layouts.partials.sidebar', function (ViewInstance $view): void {
            $view->with('helpWhatsappUrl', app(HelpCenterService::class)->whatsappUrl());
        });
    }
}
