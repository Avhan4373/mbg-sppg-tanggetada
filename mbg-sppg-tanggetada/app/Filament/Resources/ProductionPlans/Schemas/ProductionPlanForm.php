<?php

namespace App\Filament\Resources\ProductionPlans\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('production_date')
                    ->label('Tanggal produksi')
                    ->required()
                    ->default(now()),
                Select::make('menu_id')
                    ->label('Menu')
                    ->relationship('menu', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('portions')
                    ->label('Jumlah porsi')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                Textarea::make('note')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
