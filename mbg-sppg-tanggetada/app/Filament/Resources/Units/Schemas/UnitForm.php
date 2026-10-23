<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama satuan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('symbol')
                    ->label('Simbol')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
