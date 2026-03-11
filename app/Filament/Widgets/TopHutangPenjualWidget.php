<?php

namespace App\Filament\Widgets;

use App\Models\Penjual;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Colors\Color;

class TopHutangPenjualWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'half';
    protected static ?string $heading = 'Hutang Penjual Terbesar';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Penjual::query()->where('hutang', '>', 0)->orderByDesc('hutang')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Penjual')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('hutang')
                    ->label('Sisa Hutang')
                    ->money('IDR')
                    ->color(Color::Red)
                    ->alignRight(),
            ])
            ->paginated(false);
    }
}
