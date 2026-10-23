<?php

namespace App\Filament\Resources\StockTransactions\Schemas;

use App\Models\StockTransaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockTransactionForm
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
                Select::make('type')
                    ->label('Jenis transaksi')
                    ->options(StockTransaction::TYPES)
                    ->required(),
                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->helperText('Gunakan angka positif. Untuk penyesuaian, angka negatif diperbolehkan.')
                    ->required()
                    ->numeric()
                    ->step(0.001),
                DatePicker::make('transaction_date')
                    ->label('Tanggal transaksi')
                    ->required()
                    ->default(now()),
                TextInput::make('batch_number')
                    ->label('Nomor batch')
                    ->maxLength(50),
                DatePicker::make('expiry_date')
                    ->label('Tanggal kedaluwarsa'),
                Textarea::make('note')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }
}
