<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(Request $request): View
    {
        $ingredients = Ingredient::query()
            ->with(['category', 'unit', 'supplier'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%' . $request->string('q') . '%';
                $query->where(fn ($query) => $query
                    ->where('name', 'like', $term)
                    ->orWhere('code', 'like', $term));
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->orderBy('name')
            ->get();

        return view('admin.ingredients.index', [
            'ingredients' => $ingredients,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.ingredients.form', $this->formData(new Ingredient([
            'minimum_stock' => 0,
            'lead_time_days' => 1,
            'purchase_price' => 0,
            'is_active' => true,
        ])));
    }

    public function store(Request $request): RedirectResponse
    {
        Ingredient::query()->create($this->validated($request));

        return redirect()->route('admin.ingredients.index')->with('status', 'Bahan makanan berhasil ditambahkan.');
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('admin.ingredients.form', $this->formData($ingredient));
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $ingredient->update($this->validated($request, $ingredient));

        return redirect()->route('admin.ingredients.index')->with('status', 'Bahan makanan berhasil diperbarui.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        try {
            $ingredient->delete();
        } catch (\Throwable) {
            return back()->withErrors(['ingredient' => 'Bahan tidak dapat dihapus karena masih dipakai pada data lain.']);
        }

        return redirect()->route('admin.ingredients.index')->with('status', 'Bahan makanan berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Ingredient $ingredient = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('ingredients', 'code')->ignore($ingredient)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'lead_time_days' => ['required', 'integer', 'min:1', 'max:365'],
            'shelf_life_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Ingredient $ingredient): array
    {
        return [
            'ingredient' => $ingredient,
            'categories' => Category::query()->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
        ];
    }
}
