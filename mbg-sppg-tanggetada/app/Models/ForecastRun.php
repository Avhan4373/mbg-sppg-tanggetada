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
        'training_periods',
        'validation_periods',
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
            'training_periods' => 'integer',
            'validation_periods' => 'integer',
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

        return number_format(max(0, 100 - (float) $this->mape), 2) . '%';
    }

    /**
     * Kategori akurasi berdasarkan nilai MAPE (skala Lewis).
     *
     * @return array{label: string, tone: string}
     */
    public function accuracyCategory(): array
    {
        $mape = (float) ($this->mape ?? 0);

        return match (true) {
            $mape < 10 => ['label' => 'Sangat akurat', 'tone' => 'success'],
            $mape < 20 => ['label' => 'Baik', 'tone' => 'info'],
            $mape < 50 => ['label' => 'Cukup', 'tone' => 'warning'],
            default => ['label' => 'Kurang akurat', 'tone' => 'danger'],
        };
    }

    public function zScore(): float
    {
        return match (true) {
            (float) $this->service_level >= 0.99 => 2.33,
            (float) $this->service_level >= 0.975 => 1.96,
            (float) $this->service_level >= 0.95 => 1.65,
            (float) $this->service_level >= 0.90 => 1.28,
            default => 1.04,
        };
    }

    public function errorStandardDeviation(): float
    {
        $errors = $this->values()
            ->whereNotNull('error')
            ->pluck('error')
            ->map(fn ($error): float => (float) $error)
            ->all();

        if (count($errors) < 2) {
            return 0.0;
        }

        $mean = array_sum($errors) / count($errors);
        $variance = array_sum(array_map(
            fn (float $error): float => ($error - $mean) ** 2,
            $errors
        )) / (count($errors) - 1);

        return sqrt($variance);
    }

    public function leadTimeWeeks(): float
    {
        return max(1, ((int) ($this->ingredient?->lead_time_days ?? 7)) / 7);
    }
}
