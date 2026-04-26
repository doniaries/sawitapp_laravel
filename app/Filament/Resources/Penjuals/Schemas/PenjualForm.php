<?php

namespace App\Filament\Resources\Penjuals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

use App\Filament\Resources\Common\ResourceSchema;

class PenjualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        ResourceSchema::getContactSection('Penjual'),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        ResourceSchema::getHutangSection(),
                    ]),
            ]);
    }
}

