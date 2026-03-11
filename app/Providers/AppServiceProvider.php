<?php

namespace App\Providers;

use URL;
use Carbon\Carbon;
use App\Events\RefreshDashboardWidgets;
use Illuminate\Support\ServiceProvider;
use App\Services\LaporanKeuanganService;
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
