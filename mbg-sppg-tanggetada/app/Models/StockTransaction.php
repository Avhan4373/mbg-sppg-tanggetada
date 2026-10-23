<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransaction extends Model
{
    use HasFactory;

    public const TYPES = [
        'in' => 'Stok Masuk',
        'out' => 'Pemakaian',
        'waste' => 'Bahan Terbuang',
        'adjustment' => 'Penyesuaian',
    ];

    protected $fillable = [
        'ingredient_id',
        'type',
        'quantity',
        'transaction_date',
        'batch_number',
        'expiry_date',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'transaction_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
