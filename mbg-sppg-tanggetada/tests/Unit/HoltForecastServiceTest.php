<?php

namespace Tests\Unit;

use App\Services\HoltForecastService;
use InvalidArgumentException;
use Tests\TestCase;

class HoltForecastServiceTest extends TestCase
{
    public function test_it_rejects_series_with_less_than_twelve_periods(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(HoltForecastService::class)->forecast($this->series(11), 4);
    }

    public function test_it_returns_historical_and_future_values(): void
    {
        $result = app(HoltForecastService::class)->forecast(
            series: $this->series(24),
            horizon: 4,
            serviceLevel: 0.95,
            leadTimeDays: 7,
            currentStock: 25,
            onOrder: 5,
        );

        $this->assertCount(28, $result['values']);
        $this->assertCount(4, array_filter($result['values'], fn (array $value): bool => $value['is_future']));
        $this->assertGreaterThanOrEqual(0.01, $result['alpha']);
        $this->assertLessThanOrEqual(0.99, $result['alpha']);
        $this->assertGreaterThanOrEqual(0.01, $result['beta']);
        $this->assertLessThanOrEqual(0.99, $result['beta']);
        $this->assertNotNull($result['mape']);
        $this->assertGreaterThanOrEqual(0, $result['safety_stock']);
        $this->assertGreaterThanOrEqual(0, $result['recommended_purchase']);

        foreach ($result['values'] as $value) {
            $this->assertGreaterThanOrEqual(0, $value['forecast']);
        }
    }

    public function test_forecast_is_deterministic_for_the_same_input(): void
    {
        $service = app(HoltForecastService::class);
        $first = $service->forecast($this->series(24), 4);
        $second = $service->forecast($this->series(24), 4);

        $this->assertSame($first['alpha'], $second['alpha']);
        $this->assertSame($first['beta'], $second['beta']);
        $this->assertSame($first['forecast_total'], $second['forecast_total']);
    }

    public function test_recommended_purchase_uses_stock_and_on_order(): void
    {
        $result = app(HoltForecastService::class)->forecast(
            series: $this->series(24),
            horizon: 4,
            currentStock: 10,
            onOrder: 5,
        );

        $expected = max(
            0,
            $result['forecast_total'] + $result['safety_stock'] - 10 - 5
        );

        $this->assertEqualsWithDelta($expected, $result['recommended_purchase'], 0.01);
    }

    /**
     * @return array<int, array{date: string, value: float}>
     */
    private function series(int $periods): array
    {
        return array_map(
            fn (int $index): array => [
                'date' => now()->startOfWeek()->subWeeks($periods - $index - 1)->toDateString(),
                'value' => 100 + ($index * 3) + (($index % 4) * 2),
            ],
            range(0, $periods - 1)
        );
    }
}
