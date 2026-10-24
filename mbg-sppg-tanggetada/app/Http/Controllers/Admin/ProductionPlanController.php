<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\ProductionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionPlanController extends Controller
{
    public function index(): View
    {
        return view('admin.production-plans.index', [
            'plans' => ProductionPlan::query()
                ->with(['menu', 'creator'])
                ->latest('production_date')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.production-plans.form', [
            'plan' => new ProductionPlan(['production_date' => now()]),
            'menus' => Menu::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        ProductionPlan::query()->create($data);

        return redirect()->route('admin.production-plans.index')->with('status', 'Rencana produksi berhasil ditambahkan.');
    }

    public function edit(ProductionPlan $productionPlan): View
    {
        return view('admin.production-plans.form', [
            'plan' => $productionPlan,
            'menus' => Menu::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ProductionPlan $productionPlan): RedirectResponse
    {
        $productionPlan->update($this->validated($request));

        return redirect()->route('admin.production-plans.index')->with('status', 'Rencana produksi berhasil diperbarui.');
    }

    public function destroy(ProductionPlan $productionPlan): RedirectResponse
    {
        $productionPlan->delete();

        return redirect()->route('admin.production-plans.index')->with('status', 'Rencana produksi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'production_date' => ['required', 'date'],
            'menu_id' => ['required', 'exists:menus,id'],
            'portions' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
