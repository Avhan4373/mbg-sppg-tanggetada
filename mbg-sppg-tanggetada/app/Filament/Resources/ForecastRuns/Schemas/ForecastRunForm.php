<?php

namespace App\Filament\Resources\ForecastRuns\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ForecastRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ingredient_id')
                    ->label('Bahan makanan')
                    ->relationship('ingredient', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('start_date')
                    ->label('Tanggal awal data')
                    ->helperText('Minimal 12 minggu. Data akan diagregasi per minggu.')
                    ->default(now()->subWeeks(23)->startOfWeek())
                    ->required()
                    ->maxDate(now()),
                DatePicker::make('end_date')
                    ->label('Tanggal akhir data')
                    ->default(now()->endOfWeek())
                    ->required()
                    ->maxDate(now())
                    ->after('start_date'),
                Select::make('horizon')
                    ->label('Horizon prediksi')
                    ->options([
                        1 => '1 minggu',
                        2 => '2 minggu',
                        4 => '4 minggu',
                        6 => '6 minggu',
                        8 => '8 minggu',
                        12 => '12 minggu',
                    ])
                    ->default(4)
                    ->required(),
                Select::make('service_level')
                    ->label('Service level')
                    ->options([
                        '0.90' => '90%',
                        '0.95' => '95%',
                        '0.99' => '99%',
                    ])
                    ->default('0.95')
                    ->required(),
                TextInput::make('on_order')
                    ->label('Stok dalam perjalanan')
                    ->helperText('Opsional. Jumlah yang sudah dipesan tetapi belum diterima.')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.001)
                    ->default(0),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
