<?php

namespace App\Filament\Resources\ForecastRuns\Pages;

use App\Filament\Resources\ForecastRuns\ForecastRunResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListForecastRuns extends ListRecords
{
    protected static string $resource = ForecastRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
