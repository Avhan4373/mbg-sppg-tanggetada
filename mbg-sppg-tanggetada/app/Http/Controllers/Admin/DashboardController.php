<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForecastRun;
use App\Models\Ingredient;
use App\Models\StockTransaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $ingredients = Ingredient::query()
            ->where('is_active', true)
            ->with(['unit', 'category'])
            ->get();

        $critical = $ingredients
            ->filter(fn (Ingredient $ingredient): bool => in_array($ingredient->stockStatus(), ['Kritis', 'Habis'], true))
            ->sortBy(fn (Ingredient $ingredient): float => $ingredient->currentStock());

        $inventoryValue = $ingredients->sum(
            fn (Ingredient $ingredient): float => $ingredient->currentStock() * (float) $ingredient->purchase_price
        );

        return view('admin.dashboard', [
            'stats' => [
                'ingredients' => $ingredients->count(),
                'critical' => $critical->count(),
                'value' => $inventoryValue,
                'runs' => ForecastRun::query()->count(),
            ],
            'critical' => $critical->take(6),
            'recentRuns' => ForecastRun::query()
                ->with(['ingredient.unit'])
                ->latest()
                ->limit(6)
                ->get(),
            'recentTransactions' => StockTransaction::query()
                ->with('ingredient.unit')
                ->latest('transaction_date')
                ->limit(8)
                ->get(),
        ]);
    }
}
