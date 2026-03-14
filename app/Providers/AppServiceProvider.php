<?php

namespace App\Providers;

use URL;
use Carbon\Carbon;
use App\Events\RefreshDashboardWidgets;
use Illuminate\Support\ServiceProvider;
use App\Services\JurnalKeuanganService;
use Illuminate\Support\Facades\Gate;
use App\Models\{Operasional, TransaksiDo, LaporanKeuangan};
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

        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->email === 'superadmin@gmail.com' ? true : null;
        });

        // //---untuk perbaikan agar ngrok jalan
        // if (config('app.env') === 'local') {
        //     URL::forceScheme('https');
        // }

        // Set default locale ke Indonesia
        setlocale(LC_TIME, 'id_ID');
        Carbon::setLocale('id');

        // Register observers dengan namespace yang benar
        \App\Models\TransaksiOperasional::observe(TransaksiOperasionalObserver::class);
        \App\Models\JurnalKeuangan::observe(JurnalKeuanganObserver::class);
        \App\Models\TransaksiDo::observe(\App\Observers\TransaksiDoObserver::class);
    }
}
