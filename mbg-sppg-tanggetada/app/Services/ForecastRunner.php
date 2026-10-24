<?php

namespace App\Services;

use App\Models\ForecastRun;
use App\Models\Ingredient;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class ForecastRunner
{
    public function __construct(private readonly HoltForecastService $holt)
    {
    }

    /**
     * Agregasi pemakaian mingguan, hitung Holt, lalu simpan hasilnya.
     *
     * @throws InvalidArgumentException
     */
    public function run(
        Ingredient $ingredient,
        CarbonImmutable $start,
        CarbonImmutable $end,
        int $horizon,
        float $serviceLevel = 0.95,
        float $onOrder = 0,
        ?int $userId = null,
        ?string $note = null,
    ): ForecastRun {
        $start = $start->startOfWeek();
        $end = $end->endOfWeek();
        $series = $this->weeklyUsageSeries($ingredient, $start, $end);

        $result = $this->holt->forecast(
            series: $series,
            horizon: $horizon,
            serviceLevel: $serviceLevel,
            leadTimeDays: (int) $ingredient->lead_time_days,
            currentStock: $ingredient->currentStock(),
            onOrder: $onOrder,
        );

        $note = trim(implode(' ', array_filter([
            $note,
            $result['notes'],
        ])));

        $run = ForecastRun::query()->create([
            'ingredient_id' => $ingredient->id,
            'method' => 'holt',
            'period_type' => 'weekly',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'horizon' => $horizon,
            'training_periods' => $result['training_periods'],
            'validation_periods' => $result['validation_periods'],
            'alpha' => $result['alpha'],
            'beta' => $result['beta'],
            'mape' => $result['mape'],
            'mae' => $result['mae'],
            'rmse' => $result['rmse'],
            'service_level' => $result['service_level'],
            'current_stock' => $result['current_stock'],
            'on_order' => $result['on_order'],
            'safety_stock' => $result['safety_stock'],
            'reorder_point' => $result['reorder_point'],
            'recommended_purchase' => $result['recommended_purchase'],
            'status' => 'completed',
            'notes' => $note,
            'created_by' => $userId,
        ]);

        $run->values()->createMany($result['values']);

        return $run;
    }

    /**
     * @return array<int, array{date: string, value: float}>
     */
    public function weeklyUsageSeries(Ingredient $ingredient, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $series = [];
        $cursor = $start->copy();

        while ($cursor->lessThanOrEqualTo($end)) {
            $series[$cursor->toDateString()] = [
                'date' => $cursor->toDateString(),
                'value' => 0.0,
            ];

            $cursor = $cursor->addWeek();
        }

        $transactions = $ingredient->stockTransactions()
            ->where('type', 'out')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        foreach ($transactions as $transaction) {
            $week = CarbonImmutable::parse($transaction->transaction_date)
                ->startOfWeek()
                ->toDateString();

            if (array_key_exists($week, $series)) {
                $series[$week]['value'] += (float) $transaction->quantity;
            }
        }

        return array_values($series);
    }
}
