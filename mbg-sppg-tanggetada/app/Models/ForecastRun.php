<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForecastRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'method',
        'period_type',
        'start_date',
        'end_date',
        'horizon',
        'alpha',
        'beta',
        'mape',
        'mae',
        'rmse',
        'service_level',
        'current_stock',
        'on_order',
        'safety_stock',
        'reorder_point',
        'recommended_purchase',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'horizon' => 'integer',
            'alpha' => 'decimal:4',
            'beta' => 'decimal:4',
            'mape' => 'decimal:4',
            'mae' => 'decimal:3',
            'rmse' => 'decimal:3',
            'service_level' => 'decimal:4',
            'current_stock' => 'decimal:3',
            'on_order' => 'decimal:3',
            'safety_stock' => 'decimal:3',
            'reorder_point' => 'decimal:3',
            'recommended_purchase' => 'decimal:3',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ForecastValue::class)->orderBy('period_date');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accuracyLabel(): string
    {
        if ($this->mape === null) {
            return '-';
        }

        return number_format(100 - (float) $this->mape, 2) . '%';
    }
}
