<?php

namespace App\Filament\Resources\ForecastRuns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ForecastRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('horizon')
                    ->label('Horizon')
                    ->suffix(' minggu')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('alpha')
                    ->label('α')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('beta')
                    ->label('β')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('mape')
                    ->label('MAPE')
                    ->formatStateUsing(fn ($state): string => $state === null ? '-' : number_format((float) $state, 2, ',', '.') . ' %')
                    ->sortable(),
                TextColumn::make('rmse')
                    ->label('RMSE')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.'))
                    ->sortable(),
                TextColumn::make('recommended_purchase')
                    ->label('Saran beli')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('ingredient_id')
                    ->label('Bahan')
                    ->relationship('ingredient', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
