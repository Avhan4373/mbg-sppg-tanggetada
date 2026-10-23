<?php

namespace App\Filament\Resources\ForecastRuns\Pages;

use App\Filament\Resources\ForecastRuns\ForecastRunResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewForecastRun extends ViewRecord
{
    protected static string $resource = ForecastRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
