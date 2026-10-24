<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('admin.menus.index', [
            'menus' => Menu::query()->withCount(['ingredients', 'productionPlans'])->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.form', $this->formData(new Menu(['is_active' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($validated, $request): void {
            $menu = Menu::query()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $this->syncComposition($menu, $validated['composition'] ?? []);
        });

        return redirect()->route('admin.menus.index')->with('status', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): View
    {
        $menu->load('ingredients');

        return view('admin.menus.form', $this->formData($menu));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($menu, $validated, $request): void {
            $menu->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $this->syncComposition($menu, $validated['composition'] ?? []);
        });

        return redirect()->route('admin.menus.index')->with('status', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        try {
            $menu->delete();
        } catch (\Throwable) {
            return back()->withErrors(['menu' => 'Menu masih dipakai pada rencana produksi.']);
        }

        return redirect()->route('admin.menus.index')->with('status', 'Menu berhasil dihapus.');
    }

    /**
     * @param  array<int|string, mixed>  $composition
     */
    private function syncComposition(Menu $menu, array $composition): void
    {
        $menu->ingredients()->delete();

        foreach ($composition as $ingredientId => $quantity) {
            $quantity = (float) $quantity;

            if ($quantity <= 0) {
                continue;
            }

            $menu->ingredients()->create([
                'ingredient_id' => (int) $ingredientId,
                'quantity_per_portion' => $quantity,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'composition' => ['nullable', 'array'],
            'composition.*' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Menu $menu): array
    {
        return [
            'menu' => $menu,
            'ingredients' => Ingredient::query()->with('unit')->orderBy('name')->get(),
            'composition' => $menu->ingredients->pluck('quantity_per_portion', 'ingredient_id')->all(),
        ];
    }
}
