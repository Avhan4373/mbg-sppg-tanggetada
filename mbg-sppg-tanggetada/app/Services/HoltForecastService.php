<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class HoltForecastService
{
    public const MIN_PERIODS = 12;

    /**
     * @param  array<int, array{date: string|\DateTimeInterface, value: float|int}>  $series
     * @return array<string, mixed>
     */
    public function forecast(
        array $series,
        int $horizon,
        float $serviceLevel = 0.95,
        int $leadTimeDays = 7,
        float $currentStock = 0,
        float $onOrder = 0,
    ): array {
        $series = $this->normalizeSeries($series);

        if (count($series) < self::MIN_PERIODS) {
            throw new InvalidArgumentException(
                'Minimal ' . self::MIN_PERIODS . ' periode data diperlukan untuk menjalankan prediksi Holt.'
            );
        }

        if ($horizon < 1) {
            throw new InvalidArgumentException('Horizon prediksi minimal 1 periode.');
        }

        $values = array_column($series, 'value');
        $best = $this->findBestParameters($values);
        $fit = $this->fit($values, $best['alpha'], $best['beta']);
        $lastIndex = count($values) - 1;
        $lastLevel = $fit['levels'][$lastIndex];
        $lastTrend = $fit['trends'][$lastIndex];

        $forecastValues = [];

        foreach ($series as $index => $row) {
            $forecastValues[] = [
                'period_date' => $row['date']->toDateString(),
                'actual' => round($row['value'], 3),
                'level' => round($fit['levels'][$index], 4),
                'trend' => round($fit['trends'][$index], 4),
                'forecast' => round($fit['fitted'][$index] ?? $row['value'], 4),
                'error' => isset($fit['errors'][$index]) ? round($fit['errors'][$index], 4) : null,
                'is_future' => false,
            ];
        }

        $lastDate = $series[$lastIndex]['date'];
        $forecastTotal = 0.0;

        for ($step = 1; $step <= $horizon; $step++) {
            $forecast = max(0, $lastLevel + ($step * $lastTrend));
            $forecastTotal += $forecast;

            $forecastValues[] = [
                'period_date' => $lastDate->addWeeks($step)->toDateString(),
                'actual' => null,
                'level' => round($lastLevel, 4),
                'trend' => round($lastTrend, 4),
                'forecast' => round($forecast, 4),
                'error' => null,
                'is_future' => true,
            ];
        }

        $leadTimeWeeks = max(1, $leadTimeDays / 7);
        $safetyStock = $this->calculateSafetyStock(
            $fit['errors'],
            $serviceLevel,
            $leadTimeWeeks,
            $forecastTotal / $horizon,
        );

        $averageForecast = $forecastTotal / $horizon;
        $reorderPoint = ($averageForecast * $leadTimeWeeks) + $safetyStock;
        $recommendedPurchase = max(
            0,
            $forecastTotal + $safetyStock - $currentStock - $onOrder
        );

        return [
            'alpha' => $best['alpha'],
            'beta' => $best['beta'],
            'mape' => $best['mape'],
            'mae' => $best['mae'],
            'rmse' => $best['rmse'],
            'values' => $forecastValues,
            'forecast_total' => round($forecastTotal, 3),
            'average_forecast' => round($averageForecast, 3),
            'current_stock' => round($currentStock, 3),
            'on_order' => round($onOrder, 3),
            'service_level' => $serviceLevel,
            'safety_stock' => round($safetyStock, 3),
            'reorder_point' => round($reorderPoint, 3),
            'recommended_purchase' => round($recommendedPurchase, 3),
            'lead_time_weeks' => round($leadTimeWeeks, 2),
            'training_periods' => $best['training_periods'],
            'validation_periods' => $best['validation_periods'],
            'notes' => sprintf(
                'Parameter dipilih dengan RMSE terkecil pada %d periode uji. Model dilatih ulang dengan seluruh data untuk prediksi.',
                $best['validation_periods']
            ),
        ];
    }

    /**
     * @param  array<int, array{date: string|\DateTimeInterface, value: float|int}>  $series
     * @return array<int, array{date: CarbonImmutable, value: float}>
     */
    private function normalizeSeries(array $series): array
    {
        $normalized = [];

        foreach ($series as $row) {
            if (! isset($row['date'], $row['value'])) {
                continue;
            }

            $normalized[] = [
                'date' => CarbonImmutable::parse($row['date'])->startOfWeek(),
                'value' => max(0, (float) $row['value']),
            ];
        }

        usort($normalized, fn (array $a, array $b): int => $a['date']->timestamp <=> $b['date']->timestamp);

        return $normalized;
    }

    /**
     * @param  array<int, float>  $values
     * @return array{alpha: float, beta: float, mape: float|null, mae: float, rmse: float, training_periods: int, validation_periods: int}
     */
    private function findBestParameters(array $values): array
    {
        $count = count($values);
        $validationPeriods = min(
            max(4, (int) ceil($count * 0.2)),
            $count - 2
        );
        $trainCount = $count - $validationPeriods;
        $train = array_slice($values, 0, $trainCount);
        $validation = array_slice($values, $trainCount);

        $best = null;

        for ($alphaStep = 1; $alphaStep <= 99; $alphaStep++) {
            for ($betaStep = 1; $betaStep <= 99; $betaStep++) {
                $alpha = $alphaStep / 100;
                $beta = $betaStep / 100;
                $metrics = $this->evaluate($train, $validation, $alpha, $beta);

                if (
                    $best === null
                    || $metrics['rmse'] < $best['rmse']
                    || (
                        abs($metrics['rmse'] - $best['rmse']) < 0.000001
                        && ($metrics['mape'] ?? PHP_FLOAT_MAX) < ($best['mape'] ?? PHP_FLOAT_MAX)
                    )
                ) {
                    $best = [
                        'alpha' => $alpha,
                        'beta' => $beta,
                        'mape' => $metrics['mape'],
                        'mae' => $metrics['mae'],
                        'rmse' => $metrics['rmse'],
                        'training_periods' => $trainCount,
                        'validation_periods' => $validationPeriods,
                    ];
                }
            }
        }

        return $best;
    }

    /**
     * @param  array<int, float>  $train
     * @param  array<int, float>  $validation
     * @return array{mape: float|null, mae: float, rmse: float}
     */
    private function evaluate(array $train, array $validation, float $alpha, float $beta): array
    {
        $fit = $this->fit($train, $alpha, $beta);
        $lastIndex = count($train) - 1;
        $level = $fit['levels'][$lastIndex];
        $trend = $fit['trends'][$lastIndex];
        $errors = [];

        foreach ($validation as $actual) {
            $forecast = $level + $trend;
            $error = $actual - $forecast;
            $errors[] = $error;

            $newLevel = ($alpha * $actual) + ((1 - $alpha) * ($level + $trend));
            $newTrend = ($beta * ($newLevel - $level)) + ((1 - $beta) * $trend);

            $level = $newLevel;
            $trend = $newTrend;
        }

        $mae = $this->meanAbsoluteError($errors);
        $rmse = $this->rootMeanSquaredError($errors);
        $mape = $this->meanAbsolutePercentageError($validation, $errors);

        return [
            'mape' => $mape,
            'mae' => round($mae, 3),
            'rmse' => round($rmse, 3),
        ];
    }

    /**
     * @param  array<int, float>  $values
     * @return array{levels: array<int, float>, trends: array<int, float>, fitted: array<int, float|null>, errors: array<int, float|null>}
     */
    private function fit(array $values, float $alpha, float $beta): array
    {
        $count = count($values);
        $levels = [$values[0]];
        $trends = [$values[1] - $values[0]];
        $fitted = [null];
        $errors = [null];

        for ($t = 1; $t < $count; $t++) {
            $previousLevel = $levels[$t - 1];
            $previousTrend = $trends[$t - 1];
            $forecast = $previousLevel + $previousTrend;

            $level = ($alpha * $values[$t]) + ((1 - $alpha) * $forecast);
            $trend = ($beta * ($level - $previousLevel)) + ((1 - $beta) * $previousTrend);

            $levels[$t] = $level;
            $trends[$t] = $trend;
            $fitted[$t] = $forecast;
            $errors[$t] = $values[$t] - $forecast;
        }

        return [
            'levels' => $levels,
            'trends' => $trends,
            'fitted' => $fitted,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<int, float|null>  $errors
     */
    private function calculateSafetyStock(
        array $errors,
        float $serviceLevel,
        float $leadTimeWeeks,
        float $averageForecast,
    ): float {
        $validErrors = array_values(array_filter(
            $errors,
            fn ($error): bool => $error !== null
        ));

        if (count($validErrors) < 2) {
            return max(0, $averageForecast * 0.2);
        }

        $mean = array_sum($validErrors) / count($validErrors);
        $variance = array_sum(array_map(
            fn (float $error): float => ($error - $mean) ** 2,
            $validErrors
        )) / (count($validErrors) - 1);
        $standardDeviation = sqrt($variance);
        $z = $this->zScore($serviceLevel);
        $safetyStock = $z * $standardDeviation * sqrt($leadTimeWeeks);

        if ($safetyStock <= 0) {
            return max(0, $averageForecast * 0.2);
        }

        return $safetyStock;
    }

    private function zScore(float $serviceLevel): float
    {
        return match (true) {
            $serviceLevel >= 0.99 => 2.33,
            $serviceLevel >= 0.975 => 1.96,
            $serviceLevel >= 0.95 => 1.65,
            $serviceLevel >= 0.90 => 1.28,
            default => 1.04,
        };
    }

    /**
     * @param  array<int, float>  $errors
     */
    private function meanAbsoluteError(array $errors): float
    {
        if ($errors === []) {
            return 0;
        }

        return array_sum(array_map('abs', $errors)) / count($errors);
    }

    /**
     * @param  array<int, float>  $errors
     */
    private function rootMeanSquaredError(array $errors): float
    {
        if ($errors === []) {
            return 0;
        }

        return sqrt(array_sum(array_map(fn (float $error): float => $error ** 2, $errors)) / count($errors));
    }

    /**
     * @param  array<int, float>  $actuals
     * @param  array<int, float>  $errors
     */
    private function meanAbsolutePercentageError(array $actuals, array $errors): ?float
    {
        $percentages = [];

        foreach ($actuals as $index => $actual) {
            if ($actual <= 0) {
                continue;
            }

            $percentages[] = abs($errors[$index] / $actual) * 100;
        }

        if ($percentages === []) {
            return null;
        }

        return round(array_sum($percentages) / count($percentages), 4);
    }
}
