<?php

namespace App\Filament\Resources\Penjuals\Widgets;

use App\Models\Penjual;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;

class PenjualHutangTertinggiWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Penjual::query()
                    ->withSisaHutang()
                    ->where('sisa_hutang', '>', 0)
                    ->orderBy('sisa_hutang', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('telepon')
                    ->label('Telepon')
                    ->searchable(),

                Tables\Columns\TextColumn::make('sisa_hutang')
                    ->label('Sisa Hutang')
                    ->currency('IDR')
                    ->alignEnd()
                    ->sortable()
                    ->badge()
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->url(fn(Penjual $record): string => route('filament.admin.resources.penjuals.view', $record))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated([5])
            ->heading('Penjual dengan Hutang Tertinggi');
    }
}
