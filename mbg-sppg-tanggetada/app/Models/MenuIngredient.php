<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'ingredient_id',
        'quantity_per_portion',
    ];

    protected function casts(): array
    {
        return [
            'quantity_per_portion' => 'decimal:4',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
