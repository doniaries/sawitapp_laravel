<?php

namespace App\Providers;

use URL;
use Carbon\Carbon;
use App\Events\RefreshDashboardWidgets;
use Illuminate\Support\ServiceProvider;
use App\Services\LaporanKeuanganService;
use Illuminate\Support\Facades\Gate;
use App\Models\{Operasional, TransaksiDo, LaporanKeuangan};
use App\Observers\{OperasionalObserver, TransaksiDoObserver, LaporanKeuanganObserver};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Tambahkan binding service di sini
        $this->app->bind(LaporanKeuanganService::class, function ($app) {
            return new LaporanKeuanganService();
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
        Operasional::observe(OperasionalObserver::class);
        TransaksiDo::observe(TransaksiDoObserver::class);
        LaporanKeuangan::observe(LaporanKeuanganObserver::class);
    }
}
