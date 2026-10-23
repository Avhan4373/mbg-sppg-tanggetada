<?php

namespace App\Filament\Resources\Ingredients\Tables;

use App\Models\Ingredient;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class IngredientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.symbol')
                    ->label('Satuan')
                    ->badge(),
                TextColumn::make('current_stock')
                    ->label('Stok saat ini')
                    ->state(fn (Ingredient $record): float => $record->currentStock())
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.'))
                    ->alignEnd(),
                TextColumn::make('stock_status')
                    ->label('Status')
                    ->state(fn (Ingredient $record): string => $record->stockStatus())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aman' => 'success',
                        'Kritis' => 'danger',
                        'Habis' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('minimum_stock')
                    ->label('Stok minimum')
                    ->formatStateUsing(fn ($state): string => number_format((float) $state, 3, ',', '.'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('lead_time_days')
                    ->label('Lead time')
                    ->suffix(' hari')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('purchase_price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Status aktif'),
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
