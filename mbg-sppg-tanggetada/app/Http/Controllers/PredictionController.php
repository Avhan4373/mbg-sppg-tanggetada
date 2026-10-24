<?php

namespace App\Http\Controllers;

use App\Models\ForecastRun;
use App\Models\Ingredient;
use App\Models\StockTransaction;
use App\Services\ForecastRunner;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class PredictionController extends Controller
{
    public function index(): View
    {
        $ingredients = Ingredient::query()
            ->where('is_active', true)
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get();

        $recentRuns = ForecastRun::query()
            ->with(['ingredient.unit'])
            ->latest()
            ->limit(8)
            ->get();

        $latest = $recentRuns->first();

        $stats = [
            'ingredients' => $ingredients->count(),
            'runs' => ForecastRun::query()->count(),
            'critical' => $ingredients->filter(fn (Ingredient $ingredient): bool => in_array($ingredient->stockStatus(), ['Kritis', 'Habis'], true))->count(),
            'usage' => (float) StockTransaction::query()
                ->where('type', 'out')
                ->where('transaction_date', '>=', now()->subWeeks(4)->startOfWeek())
                ->sum('quantity'),
        ];

        return view('predictions.index', [
            'ingredients' => $ingredients,
            'recentRuns' => $recentRuns,
            'latest' => $latest,
            'stats' => $stats,
            'defaultStart' => now()->subWeek()->startOfWeek()->subWeeks(23),
            'defaultEnd' => now()->subWeek()->endOfWeek(),
        ]);
    }

    public function store(Request $request, ForecastRunner $runner): RedirectResponse
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'before_or_equal:today'],
            'horizon' => ['required', 'integer', Rule::in([1, 2, 4, 6, 8, 12])],
            'service_level' => ['required', 'numeric', Rule::in(['0.90', '0.95', '0.99'])],
            'on_order' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [], [
            'ingredient_id' => 'bahan makanan',
            'start_date' => 'tanggal awal',
            'end_date' => 'tanggal akhir',
            'horizon' => 'horizon prediksi',
            'service_level' => 'service level',
            'on_order' => 'stok dalam perjalanan',
        ]);

        $ingredient = Ingredient::query()->findOrFail($validated['ingredient_id']);

        try {
            $run = $runner->run(
                ingredient: $ingredient,
                start: CarbonImmutable::parse($validated['start_date']),
                end: CarbonImmutable::parse($validated['end_date']),
                horizon: (int) $validated['horizon'],
                serviceLevel: (float) $validated['service_level'],
                onOrder: (float) ($validated['on_order'] ?? 0),
                userId: $request->user()?->id,
                note: $validated['note'] ?? null,
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'ingredient_id' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('predictions.show', $run)
            ->with('status', 'Prediksi berhasil dihitung. Parameter α dan β dipilih otomatis dari proses validasi.');
    }

    public function show(ForecastRun $forecastRun): View
    {
        $forecastRun->load([
            'ingredient.unit',
            'ingredient.category',
            'ingredient.supplier',
            'values',
            'creator',
        ]);

        /** @var Collection<int, \App\Models\ForecastValue> $values */
        $values = $forecastRun->values;
        $history = $values->where('is_future', false)->values();
        $future = $values->where('is_future', true)->values();

        $first = $history->first();
        $second = $history->get(1);
        $averageFuture = (float) $future->avg('forecast');
        $totalFuture = (float) $future->sum('forecast');
        $sigma = $forecastRun->errorStandardDeviation();
        $z = $forecastRun->zScore();
        $leadTimeWeeks = $forecastRun->leadTimeWeeks();
        $safetyCalculation = $z * $sigma * sqrt($leadTimeWeeks);
        $reorderCalculation = ($averageFuture * $leadTimeWeeks) + (float) $forecastRun->safety_stock;

        $workedExample = $history
            ->slice(1, 3)
            ->values()
            ->map(function ($value, int $index) use ($history): array {
                $position = $index + 1;
                $previous = $history->get($position - 1);

                return [
                    'period' => $value->period_date,
                    'actual' => (float) $value->actual,
                    'previous_level' => (float) $previous->level,
                    'previous_trend' => (float) $previous->trend,
                    'forecast' => (float) $value->forecast,
                    'level' => (float) $value->level,
                    'trend' => (float) $value->trend,
                    'error' => (float) $value->error,
                ];
            });

        return view('predictions.show', [
            'run' => $forecastRun,
            'history' => $history,
            'future' => $future,
            'explanation' => [
                'initial_level' => (float) ($first?->level ?? 0),
                'initial_trend' => (float) ($second?->trend ?? 0),
                'first_period' => $first?->period_date,
                'second_period' => $second?->period_date,
                'first_actual' => (float) ($first?->actual ?? 0),
                'second_actual' => (float) ($second?->actual ?? 0),
                'average_future' => $averageFuture,
                'total_future' => $totalFuture,
                'sigma' => $sigma,
                'z' => $z,
                'lead_time_weeks' => $leadTimeWeeks,
                'safety_calculation' => $safetyCalculation,
                'reorder_calculation' => $reorderCalculation,
                'worked_example' => $workedExample,
                'accuracy' => $forecastRun->accuracyCategory(),
                'last_level' => (float) ($history->last()?->level ?? 0),
                'last_trend' => (float) ($history->last()?->trend ?? 0),
                'last_period' => $history->last()?->period_date,
            ],
        ]);
    }
}
