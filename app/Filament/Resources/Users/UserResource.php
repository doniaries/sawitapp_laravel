<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UserTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Pengguna';

    protected static ?string $tenantOwnershipRelationshipName = 'perusahaans';

    public static function form(Schema $form): Schema
    {
        return UserForm::configure($form);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return UserTable::configure($table);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['perusahaan', 'roles']);
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Perusahaan' => $record->perusahaan?->name ?? 'Semua Perusahaan',
        ];
    }
}
