<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ForecastChart extends ChartWidget
{
    protected ?string $heading = 'Forecast Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
