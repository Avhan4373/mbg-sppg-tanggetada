<?php

namespace App\Filament\Resources\ProductionPlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('production_date', 'desc')
            ->columns([
                TextColumn::make('production_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('portions')
                    ->label('Porsi')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Dicatat oleh')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(40)
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
