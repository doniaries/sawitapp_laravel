<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

\Illuminate\Support\Facades\Schedule::call(function () {
    $yesterday = now()->subDay()->toDateString();
    $perusahaans = \App\Models\Perusahaan::where('is_active', true)->get();

    foreach ($perusahaans as $perusahaan) {
        if (!\App\Models\TutupHari::isClosed($yesterday, $perusahaan->id)) {
            \App\Models\TutupHari::performClosing([
                'tanggal' => $yesterday,
                'saldo_akhir_fisik' => null, // null berarti otomatis disamakan dengan saldo sistem
                'catatan' => 'Auto-closing otomatis oleh sistem (Jam 00:00)',
            ], $perusahaan->id);
        }
    }
})->dailyAt('00:00');
