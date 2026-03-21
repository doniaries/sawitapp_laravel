<?php

namespace App\Providers;

use URL;
use Carbon\Carbon;
use App\Events\RefreshDashboardWidgets;
use Illuminate\Support\ServiceProvider;
use App\Services\JurnalKeuanganService;
use Illuminate\Support\Facades\Gate;
use App\Models\{TransaksiOperasional, TransaksiDo, JurnalKeuangan};
use App\Observers\{TransaksiOperasionalObserver, JurnalKeuanganObserver, TransaksiDoObserver};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tambahkan binding service di sini
        $this->app->bind(JurnalKeuanganService::class, function ($app) {
            return new JurnalKeuanganService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            return str_replace('Models', 'Policies', $modelClass) . 'Policy';
        });

        // Implicitly grant "Super Admin" and "Admin" roles global permissions
        Gate::before(function ($user, $ability) {
            // Super Admin gets everything bypass
            if ($user->isSuperAdmin()) {
                return true;
            }

            // Admin gets bypass for everything EXCEPT Role-related permissions
            if ($user->isAdminOrSuperAdmin()) {
                // If checking for Role management, skip bypass so permissions/policy can decide
                if (str_contains($ability, ':Role') || str_contains($ability, 'role')) {
                    return null;
                }
                return true;
            }

            return null;
        });

        // //---untuk perbaikan agar ngrok jalan
        // if (config('app.env') === 'local') {
        //     URL::forceScheme('https');
        // }

        // Set default locale ke Indonesia
        setlocale(LC_TIME, 'id_ID');
        Carbon::setLocale('id');

        // Register observers
        TransaksiOperasional::observe(TransaksiOperasionalObserver::class);
        JurnalKeuangan::observe(JurnalKeuanganObserver::class);
        TransaksiDo::observe(TransaksiDoObserver::class);
    }
}
