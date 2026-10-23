<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'unit_id',
        'supplier_id',
        'minimum_stock',
        'lead_time_days',
        'shelf_life_days',
        'purchase_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'minimum_stock' => 'decimal:3',
            'lead_time_days' => 'integer',
            'shelf_life_days' => 'integer',
            'purchase_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function forecastRuns(): HasMany
    {
        return $this->hasMany(ForecastRun::class);
    }

    public function menuIngredients(): HasMany
    {
        return $this->hasMany(MenuIngredient::class);
    }

    public function currentStock(): float
    {
        $total = $this->stockTransactions()
            ->selectRaw("
                COALESCE(SUM(
                    CASE
                        WHEN type = 'in' THEN quantity
                        WHEN type = 'adjustment' THEN quantity
                        WHEN type IN ('out', 'waste') THEN -quantity
                        ELSE 0
                    END
                ), 0) as total
            ")
            ->value('total');

        return round((float) $total, 3);
    }

    public function stockStatus(): string
    {
        $stock = $this->currentStock();

        if ($stock <= 0) {
            return 'Habis';
        }

        if ($stock <= (float) $this->minimum_stock) {
            return 'Kritis';
        }

        return 'Aman';
    }
}
