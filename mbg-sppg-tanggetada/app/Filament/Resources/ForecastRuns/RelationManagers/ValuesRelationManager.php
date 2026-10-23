<?php

namespace App\Filament\Resources\ForecastRuns\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('period_date')
                    ->required(),
                TextInput::make('actual')
                    ->numeric(),
                TextInput::make('level')
                    ->required()
                    ->numeric(),
                TextInput::make('trend')
                    ->required()
                    ->numeric(),
                TextInput::make('forecast')
                    ->required()
                    ->numeric(),
                TextInput::make('error')
                    ->numeric(),
                Toggle::make('is_future')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('period_date')
            ->columns([
                TextColumn::make('period_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('actual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trend')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('forecast')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('error')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_future')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
