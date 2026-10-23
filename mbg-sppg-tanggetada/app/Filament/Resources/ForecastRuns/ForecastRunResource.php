<?php

namespace App\Filament\Resources\ForecastRuns;

use App\Filament\Resources\ForecastRuns\Pages\CreateForecastRun;
use App\Filament\Resources\ForecastRuns\Pages\EditForecastRun;
use App\Filament\Resources\ForecastRuns\Pages\ListForecastRuns;
use App\Filament\Resources\ForecastRuns\Pages\ViewForecastRun;
use App\Filament\Resources\ForecastRuns\Schemas\ForecastRunForm;
use App\Filament\Resources\ForecastRuns\Schemas\ForecastRunInfolist;
use App\Filament\Resources\ForecastRuns\Tables\ForecastRunsTable;
use App\Models\ForecastRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ForecastRunResource extends Resource
{
    protected static ?string $model = ForecastRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ForecastRunForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ForecastRunInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ForecastRunsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForecastRuns::route('/'),
            'create' => CreateForecastRun::route('/create'),
            'view' => ViewForecastRun::route('/{record}'),
            'edit' => EditForecastRun::route('/{record}/edit'),
        ];
    }
}
