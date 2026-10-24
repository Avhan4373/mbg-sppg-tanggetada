<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\StockTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = StockTransaction::query()
            ->with(['ingredient.unit', 'creator'])
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->filled('ingredient'), fn ($query) => $query->where('ingredient_id', $request->integer('ingredient')))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.stock-transactions.index', [
            'transactions' => $transactions,
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
            'types' => StockTransaction::TYPES,
        ]);
    }

    public function create(): View
    {
        return view('admin.stock-transactions.form', [
            'transaction' => new StockTransaction([
                'type' => 'in',
                'transaction_date' => now(),
            ]),
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
            'types' => StockTransaction::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        StockTransaction::query()->create($data);

        return redirect()->route('admin.stock-transactions.index')->with('status', 'Transaksi stok berhasil dicatat.');
    }

    public function edit(StockTransaction $stockTransaction): View
    {
        return view('admin.stock-transactions.form', [
            'transaction' => $stockTransaction,
            'ingredients' => Ingredient::query()->orderBy('name')->get(),
            'types' => StockTransaction::TYPES,
        ]);
    }

    public function update(Request $request, StockTransaction $stockTransaction): RedirectResponse
    {
        $stockTransaction->update($this->validated($request));

        return redirect()->route('admin.stock-transactions.index')->with('status', 'Transaksi stok berhasil diperbarui.');
    }

    public function destroy(StockTransaction $stockTransaction): RedirectResponse
    {
        $stockTransaction->delete();

        return redirect()->route('admin.stock-transactions.index')->with('status', 'Transaksi stok berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'ingredient_id' => ['required', 'exists:ingredients,id'],
            'type' => ['required', Rule::in(array_keys(StockTransaction::TYPES))],
            'quantity' => ['required', 'numeric', 'not_in:0'],
            'transaction_date' => ['required', 'date'],
            'batch_number' => ['nullable', 'string', 'max:50'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:transaction_date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
