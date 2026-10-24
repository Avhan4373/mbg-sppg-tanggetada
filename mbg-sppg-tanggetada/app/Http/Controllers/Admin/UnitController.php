<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        return view('admin.units.index', [
            'units' => Unit::query()->withCount('ingredients')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.units.form', ['unit' => new Unit()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Unit::query()->create($this->validated($request));

        return redirect()->route('admin.units.index')->with('status', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        return view('admin.units.form', ['unit' => $unit]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $unit->update($this->validated($request, $unit));

        return redirect()->route('admin.units.index')->with('status', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        try {
            $unit->delete();
        } catch (\Throwable) {
            return back()->withErrors(['unit' => 'Satuan masih dipakai oleh bahan makanan.']);
        }

        return redirect()->route('admin.units.index')->with('status', 'Satuan berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Unit $unit = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:20', 'unique:units,symbol' . ($unit ? ',' . $unit->id : '')],
        ]);
    }
}
