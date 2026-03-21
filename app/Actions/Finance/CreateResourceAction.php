<?php

namespace App\Actions\Finance;

use App\Models\Penjual;
use App\Models\Supir;
use App\Services\DebtService;
use Illuminate\Support\Facades\DB;

class CreateResourceAction
{
    protected DebtService $debtService;

    public function __construct(DebtService $debtService)
    {
        $this->debtService = $debtService;
    }

    /**
     * Membuat resource baru (Penjual atau Supir) dan mencatat hutang awal jika ada.
     */
    public function execute(string $type, array $data)
    {
        return DB::transaction(function () use ($type, $data) {
            $modelClass = $type === 'penjual' ? Penjual::class : Supir::class;
            
            // Hutang awal dipisahkan dari create untuk dicatat via DebtService
            $hutangAwal = (float) ($data['hutang'] ?? 0);
            unset($data['hutang']);

            $resource = $modelClass::create($data);

            if ($hutangAwal > 0) {
                $this->debtService->increaseDebt(
                    pihak: $resource,
                    nominal: $hutangAwal,
                    keterangan: 'Hutang Awal (Saldo Pembukaan)',
                    referensi: $resource
                );
            }

            return $resource;
        });
    }
}
