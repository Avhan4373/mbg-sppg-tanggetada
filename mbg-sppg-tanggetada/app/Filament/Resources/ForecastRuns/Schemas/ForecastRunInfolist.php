<?php

namespace App\Filament\Resources\ForecastRuns\Schemas;

use App\Models\ForecastRun;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ForecastRunInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ingredient.name')
                    ->label('Bahan makanan'),
                TextEntry::make('created_at')
                    ->label('Waktu prediksi')
                    ->dateTime('d M Y H:i'),
                TextEntry::make('start_date')
                    ->label('Awal data')
                    ->date('d M Y'),
                TextEntry::make('end_date')
                    ->label('Akhir data')
                    ->date('d M Y'),
                TextEntry::make('horizon')
                    ->label('Horizon')
                    ->suffix(' minggu'),
                TextEntry::make('alpha')
                    ->label('Alpha (α)')
                    ->numeric(decimalPlaces: 4),
                TextEntry::make('beta')
                    ->label('Beta (β)')
                    ->numeric(decimalPlaces: 4),
                TextEntry::make('mape')
                    ->label('MAPE')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state, 2, ',', '.') . ' %'),
                TextEntry::make('mae')
                    ->label('MAE')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state, 3, ',', '.')),
                TextEntry::make('rmse')
                    ->label('RMSE')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state, 3, ',', '.')),
                TextEntry::make('accuracy')
                    ->label('Estimasi akurasi')
                    ->state(fn (ForecastRun $record): string => $record->accuracyLabel()),
                TextEntry::make('current_stock')
                    ->label('Stok saat ini')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.')),
                TextEntry::make('on_order')
                    ->label('Stok dalam perjalanan')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.')),
                TextEntry::make('safety_stock')
                    ->label('Safety stock')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.')),
                TextEntry::make('reorder_point')
                    ->label('Reorder point')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.')),
                TextEntry::make('recommended_purchase')
                    ->label('Saran pembelian')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.'))
                    ->badge()
                    ->color('success'),
                TextEntry::make('notes')
                    ->label('Catatan metode')
                    ->columnSpanFull(),
            ]);
    }
}
