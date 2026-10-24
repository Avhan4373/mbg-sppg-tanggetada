<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForecastRun;
use App\Models\Ingredient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForecastController extends Controller
{
    public function index(Request $request): View
    {
        $runs = ForecastRun::query()
            ->with(['ingredient.unit', 'creator'])
            ->when($request->filled('ingredient'), fn ($query) => $query->where('ingredient_id', $request->integer('ingredient')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.forecasts.index', [
            'runs' => $runs,
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
        ]);
    }

    public function destroy(ForecastRun $forecastRun): RedirectResponse
    {
        $forecastRun->delete();

        return redirect()->route('admin.forecasts.index')->with('status', 'Riwayat prediksi berhasil dihapus.');
    }
}
