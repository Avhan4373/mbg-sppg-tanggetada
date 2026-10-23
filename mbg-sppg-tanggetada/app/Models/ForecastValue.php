<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'forecast_run_id',
        'period_date',
        'actual',
        'level',
        'trend',
        'forecast',
        'error',
        'is_future',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'actual' => 'decimal:3',
            'level' => 'decimal:4',
            'trend' => 'decimal:4',
            'forecast' => 'decimal:4',
            'error' => 'decimal:4',
            'is_future' => 'boolean',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ForecastRun::class, 'forecast_run_id');
    }
}
