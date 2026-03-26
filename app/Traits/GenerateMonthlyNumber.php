<?php

namespace App\Traits;

use Carbon\Carbon;

trait GenerateMonthlyNumber
{
    public static function generateMonthlyNumber($date = null)
    {
        $date = $date ? Carbon::parse($date) : now();
        $dateString = $date->toDateString();
        $day = $date->format('d');
        $month = $date->format('m');
        $year = $date->format('Y');

        // Ambil nomor terakhir untuk tanggal tersebut
        $lastNumber = static::whereDate('tanggal', $dateString)
            ->withTrashed()
            ->max('nomor');

        if (!$lastNumber) {
            $newNumber = 1;
        } else {
            // Ekstrak nomor dari format DO-YYYYMMDD-XXXX
            preg_match('/DO-\d{8}-(\d+)/', $lastNumber, $matches);
            $newNumber = isset($matches[1]) ? ((int)$matches[1] + 1) : 1;
        }

        // Format: DO-YYYYMMDD-XXXX
        return sprintf(
            'DO-%s%s%s-%s',
            $year,
            $month,
            $day,
            str_pad($newNumber, 4, '0', STR_PAD_LEFT)
        );
    }
}
