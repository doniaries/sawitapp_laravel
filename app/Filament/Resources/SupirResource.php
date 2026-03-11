<?php

namespace App\Filament\Resources;

use App\Models\Supir;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SupirResource\Pages;

class SupirResource extends Resource
{
    protected static ?string $model = Supir::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    // protected static ?string $navigationLabel = 'Supir';
    // protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('nama')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('telepon')
                                    ->tel(),

                                TextInput::make('alamat'),

                                TextInput::make('hutang')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0)
                                    ->currencyMask(
                                        thousandSeparator: ',',
                                        decimalSeparator: '.',
                                        precision: 0
                                    ),

                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alamat')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('telepon')
                    ->searchable(),

                TextColumn::make('hutang')
                    ->label('Total Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
            ])
            ->defaultSort('nama', 'asc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('has_hutang')
                    ->query(fn(Builder $query): Builder => $query->where('hutang', '>', 0))
                    ->label('Ada Hutang')
                    ->toggle()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupirs::route('/'),
            'create' => Pages\CreateSupir::route('/create'),
            'edit' => Pages\EditSupir::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['kendaraan', 'transaksiDo'])
            ->withTrashed();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    private static function formatCurrency($number): int
    {
        if (empty($number)) return 0;
        // Handle string format currency
        if (is_string($number)) {
            return (int) str_replace(['.', ','], ['', '.'], $number);
        }
        return (int) $number;
    }
}
