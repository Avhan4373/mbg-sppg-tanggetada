<?php

namespace App\Filament\Resources\ForecastRuns\Pages;

use App\Filament\Resources\ForecastRuns\ForecastRunResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditForecastRun extends EditRecord
{
    protected static string $resource = ForecastRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
