<?php

namespace App\Filament\Resources\TutupHaris\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Schemas\Schema;
use App\Models\TutupHari;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TutupHariTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(function () {
                $perusahaanId = Filament::getTenant()->id;
                
                // Subquery untuk mendapatkan semua tanggal yang ada transaksinya
                $datesQuery = DB::table('transaksi_do')
                    ->selectRaw('DATE(tanggal) as tanggal')
                    ->where('perusahaan_id', $perusahaanId)
                    ->union(
                        DB::table('jurnal_keuangan')
                            ->selectRaw('DATE(tanggal) as tanggal')
                            ->where('perusahaan_id', $perusahaanId)
                    )
                    ->union(
                        DB::table('tambah_saldo')
                            ->selectRaw('DATE(tanggal) as tanggal')
                            ->where('perusahaan_id', $perusahaanId)
                    )
                    ->union(
                        DB::table('transaksi_operasional')
                            ->selectRaw('DATE(tanggal) as tanggal')
                            ->where('perusahaan_id', $perusahaanId)
                    );

                // Main query: Ambil dari subquery tanggal, LEFT JOIN ke tutup_hari
                return TutupHari::query()
                    ->select([
                        'th_combined.tanggal',
                        'tutup_hari.id',
                        'tutup_hari.perusahaan_id',
                        'tutup_hari.total_do_tonase',
                        'tutup_hari.total_do_rupiah',
                        'tutup_hari.saldo_akhir_sistem',
                        'tutup_hari.saldo_akhir_fisik',
                        'tutup_hari.selisih',
                        'tutup_hari.user_id',
                        'tutup_hari.created_at',
                        DB::raw("COALESCE(tutup_hari.status, 'open') as status"),
                    ])
                    ->fromSub($datesQuery, 'th_combined')
                    ->leftJoin('tutup_hari', function ($join) use ($perusahaanId) {
                        $join->on('th_combined.tanggal', '=', 'tutup_hari.tanggal')
                             ->where('tutup_hari.perusahaan_id', '=', $perusahaanId);
                    });
            })
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'closed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => $state === 'open' ? 'BELUM TUTUP' : 'SUDAH TUTUP'),

                TextColumn::make('total_do_tonase')
                    ->label('Total DO (Kg)')
                    ->numeric(0, ',', '.')
                    ->suffix(' Kg')
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('total_do_rupiah')
                    ->label('Total DO (Rp)')
                    ->currency('IDR')
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('saldo_akhir_sistem')
                    ->label('Saldo Sistem')
                    ->currency('IDR')
                    ->color(Color::Blue)
                    ->weight('bold')
                    ->placeholder('-'),

                TextColumn::make('saldo_akhir_fisik')
                    ->label('Saldo Fisik')
                    ->currency('IDR')
                    ->color(Color::Green)
                    ->weight('bold')
                    ->placeholder('-'),

                TextColumn::make('selisih')
                    ->label('Selisih')
                    ->currency('IDR')
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state > 0 ? 'warning' : 'success'))
                    ->weight('bold')
                    ->placeholder('-'),

                TextColumn::make('user.nama')
                    ->label('Oleh')
                    ->toggleable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('tutup_hari')
                    ->label('Tutup Hari')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'open')
                    ->modalWidth('4xl')
                    ->modalHeading(fn ($record) => 'Tutup Hari Tanggal: ' . Carbon::parse($record->tanggal)->format('d F Y'))
                    ->form(\App\Filament\Resources\TutupHaris\Schemas\TutupHariForm::configure(new Schema())->getComponents())
                    ->action(function ($record, array $data) {
                        TutupHari::performClosing(
                            array_merge($data, ['tanggal' => $record->tanggal]),
                            Filament::getTenant()->id
                        );
                    }),
                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'closed'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
