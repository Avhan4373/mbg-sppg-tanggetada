<?php

namespace App\Filament\Resources\ForecastRuns\Pages;

use App\Filament\Resources\ForecastRuns\ForecastRunResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForecastRun extends CreateRecord
{
    protected static string $resource = ForecastRunResource::class;
}
