<?php

namespace App\Observers;

use App\Models\TransaksiOperasional;
use App\Services\DebtService;
use App\Enums\KategoriOperasional;
use Illuminate\Support\Facades\DB;

class TransaksiOperasionalObserver
{
    public function __construct()
    {
    }

    public function created(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();
            $this->processHutang($operasional);
            \App\Jobs\ProcessOperasionalJournals::dispatch($operasional);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updated(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();
            \App\Jobs\ProcessOperasionalJournals::dispatch($operasional);
            if ($operasional->isDirty(['nominal', 'kategori'])) {
                $this->rollbackHutang($operasional);
                $this->processHutang($operasional);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleted(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();
            \App\Jobs\ProcessOperasionalJournals::dispatch($operasional);
            $this->rollbackHutang($operasional);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function restored(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();
            \App\Jobs\ProcessOperasionalJournals::dispatch($operasional);
            $this->processHutang($operasional);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function forceDeleted(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();
            \App\Jobs\ProcessOperasionalJournals::dispatch($operasional);
            $this->rollbackHutang($operasional);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processHutang(TransaksiOperasional $operasional): void
    {
        if ($operasional->kategori === KategoriOperasional::PINJAMAN && $operasional->pihak) {
            DebtService::increaseDebt(
                $operasional->pihak, 
                $operasional->nominal, 
                $operasional, 
                "Pinjaman via operasional: " . ($operasional->keterangan ?: '-')
            );
        }
    }
    
    private function rollbackHutang(TransaksiOperasional $operasional): void
    {
        if ($operasional->kategori === KategoriOperasional::PINJAMAN && $operasional->pihak) {
            DebtService::recordPayment(
                $operasional->pihak, 
                $operasional->nominal, 
                $operasional, 
                "Pembatalan pinjaman #{$operasional->id}"
            );
        }
    }
}
