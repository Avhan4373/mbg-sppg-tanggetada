<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ForecastRun;
use App\Models\Ingredient;
use App\Models\StockTransaction;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPredictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_the_prediction_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Prediksi Stok Bahan Makanan')
            ->assertSee('Hitung Prediksi Baru')
            ->assertSee('Double Exponential Smoothing');
    }

    public function test_method_guide_is_available(): void
    {
        $this->get('/panduan')
            ->assertOk()
            ->assertSee('Double Exponential Smoothing (Holt)')
            ->assertSee('Safety stock')
            ->assertSee('9.801 kombinasi');
    }

    public function test_prediction_can_be_created_without_login(): void
    {
        [$ingredient] = $this->ingredientWithHistory();

        $response = $this->post('/prediksi', [
            'ingredient_id' => $ingredient->id,
            'start_date' => now()->subWeeks(23)->startOfWeek()->toDateString(),
            'end_date' => now()->subWeek()->endOfWeek()->toDateString(),
            'horizon' => 4,
            'service_level' => '0.95',
            'on_order' => 5,
        ]);

        $run = ForecastRun::query()->latest('id')->first();

        $this->assertNotNull($run);
        $this->assertSame($ingredient->id, $run->ingredient_id);
        $this->assertSame(4, $run->horizon);
        $this->assertGreaterThan(0, $run->values()->count());
        $this->assertGreaterThan(0, $run->training_periods);
        $this->assertGreaterThan(0, $run->validation_periods);

        $response->assertRedirect(route('predictions.show', $run));

        $this->get(route('predictions.show', $run))
            ->assertOk()
            ->assertSee('Penjelasan Perhitungan Lengkap')
            ->assertSee('Safety stock')
            ->assertSee('Saran pembelian')
            ->assertSee('Detail tabel perhitungan');
    }

    public function test_prediction_rejects_less_than_twelve_weeks(): void
    {
        [$ingredient] = $this->ingredientWithHistory(8);

        $this->from('/')
            ->post('/prediksi', [
                'ingredient_id' => $ingredient->id,
                'start_date' => now()->subWeeks(7)->startOfWeek()->toDateString(),
                'end_date' => now()->subWeek()->endOfWeek()->toDateString(),
                'horizon' => 4,
                'service_level' => '0.95',
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors('ingredient_id');

        $this->assertSame(0, ForecastRun::query()->count());
    }

    public function test_prediction_requires_existing_ingredient(): void
    {
        $this->from('/')
            ->post('/prediksi', [
                'ingredient_id' => 9999,
                'start_date' => now()->subWeeks(23)->toDateString(),
                'end_date' => now()->subWeek()->toDateString(),
                'horizon' => 4,
                'service_level' => '0.95',
            ])
            ->assertSessionHasErrors('ingredient_id');
    }

    /**
     * @return array{0: Ingredient, 1: User}
     */
    private function ingredientWithHistory(int $weeks = 24): array
    {
        $user = User::query()->create([
            'name' => 'Admin Test',
            'email' => 'admin@example.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::query()->create(['name' => 'Karbohidrat']);
        $unit = Unit::query()->create(['name' => 'Kilogram', 'symbol' => 'kg']);
        $ingredient = Ingredient::query()->create([
            'code' => 'BRS-001',
            'name' => 'Beras',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 50,
            'lead_time_days' => 3,
            'purchase_price' => 14000,
            'is_active' => true,
        ]);

        StockTransaction::query()->create([
            'ingredient_id' => $ingredient->id,
            'type' => 'in',
            'quantity' => 1000,
            'transaction_date' => now()->subWeeks($weeks)->startOfWeek()->toDateString(),
            'created_by' => $user->id,
        ]);

        foreach (range(1, $weeks) as $week) {
            StockTransaction::query()->create([
                'ingredient_id' => $ingredient->id,
                'type' => 'out',
                'quantity' => 100 + ($week * 3) + (($week % 4) * 2),
                'transaction_date' => now()->subWeeks($weeks - $week)->startOfWeek()->addDays(2)->toDateString(),
                'created_by' => $user->id,
            ]);
        }

        return [$ingredient, $user];
    }
}
