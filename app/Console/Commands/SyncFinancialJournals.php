<?php

namespace App\Console\Commands;

use App\Models\TransaksiDo;
use App\Models\TransaksiOperasional;
use App\Jobs\ProsesJurnalDo;
use App\Jobs\ProsesJurnalOperasional;
use App\Actions\Finance\RecordFinanceTransactionAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncFinancialJournals extends Command
{
    protected $signature = 'app:sync-journals';
    protected $description = 'Re-sync all financial journals from source transactions';

    public function handle()
    {
        $this->info('Starting financial journal synchronization...');

        // 1. Sync Transaksi DO
        $dos = TransaksiDo::all();
        $this->info("Processing {$dos->count()} Transaksi DO...");
        $bar = $this->output->createProgressBar($dos->count());
        
        $financeAction = app(RecordFinanceTransactionAction::class);

        foreach ($dos as $do) {
            (new ProsesJurnalDo($do))->handle($financeAction);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        // 2. Sync Transaksi Operasional
        $ops = TransaksiOperasional::all();
        $this->info("Processing {$ops->count()} Transaksi Operasional...");
        $bar = $this->output->createProgressBar($ops->count());

        foreach ($ops as $op) {
            (new ProsesJurnalOperasional($op))->handle($financeAction);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->info('Synchronization completed successfully!');
    }
}
