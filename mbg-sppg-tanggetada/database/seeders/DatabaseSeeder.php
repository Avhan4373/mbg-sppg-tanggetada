<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ForecastRun;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\ProductionPlan;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\ForecastRunner;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin SPPG Tanggetada',
            'email' => 'admin@mbg.test',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Ahli Gizi SPPG',
            'email' => 'gizi@mbg.test',
            'password' => 'password',
            'role' => 'ahli_gizi',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Kepala SPPG Tanggetada',
            'email' => 'kepala@mbg.test',
            'password' => 'password',
            'role' => 'kepala_sppg',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $categories = collect([
            'Karbohidrat' => Category::query()->create(['name' => 'Karbohidrat', 'description' => 'Sumber energi utama.']),
            'Protein Hewani' => Category::query()->create(['name' => 'Protein Hewani', 'description' => 'Protein dari hewan.']),
            'Protein Nabati' => Category::query()->create(['name' => 'Protein Nabati', 'description' => 'Protein dari tumbuhan.']),
            'Sayuran' => Category::query()->create(['name' => 'Sayuran', 'description' => 'Sayur dan dedaunan.']),
            'Buah' => Category::query()->create(['name' => 'Buah', 'description' => 'Buah segar.']),
            'Bumbu & Minyak' => Category::query()->create(['name' => 'Bumbu & Minyak', 'description' => 'Bumbu, garam, gula, dan minyak.']),
        ]);

        $units = collect([
            'kg' => Unit::query()->create(['name' => 'Kilogram', 'symbol' => 'kg']),
            'liter' => Unit::query()->create(['name' => 'Liter', 'symbol' => 'liter']),
            'ikat' => Unit::query()->create(['name' => 'Ikat', 'symbol' => 'ikat']),
            'buah' => Unit::query()->create(['name' => 'Buah', 'symbol' => 'buah']),
        ]);

        $suppliers = collect([
            'pasar' => Supplier::query()->create(['name' => 'Pasar Tanggetada', 'phone' => '0812-0000-0001', 'address' => 'Kecamatan Tanggetada']),
            'sumber' => Supplier::query()->create(['name' => 'Toko Sumber Rejeki', 'phone' => '0812-0000-0002', 'address' => 'Kecamatan Tanggetada']),
            'koperasi' => Supplier::query()->create(['name' => 'Koperasi Tani Tanggetada', 'phone' => '0812-0000-0003', 'address' => 'Kecamatan Tanggetada']),
        ]);

        $ingredientData = [
            ['BRS-001', 'Beras', 'Karbohidrat', 'kg', 'koperasi', 120, 3.50, 3, 180, 14000],
            ['TLR-001', 'Telur Ayam', 'Protein Hewani', 'kg', 'pasar', 30, 3.00, 2, 21, 28000],
            ['AYM-001', 'Ayam Potong', 'Protein Hewani', 'kg', 'pasar', 45, 0.85, 2, 2, 38000],
            ['IKN-001', 'Ikan Kembung', 'Protein Hewani', 'kg', 'pasar', 35, 2.00, 2, 2, 32000],
            ['THU-001', 'Tahu', 'Protein Nabati', 'kg', 'sumber', 20, 2.00, 1, 3, 12000],
            ['TMP-001', 'Tempe', 'Protein Nabati', 'kg', 'sumber', 18, 2.00, 1, 2, 10000],
            ['BYM-001', 'Bayam', 'Sayuran', 'kg', 'pasar', 15, 0.75, 1, 1, 8000],
            ['WRT-001', 'Wortel', 'Sayuran', 'kg', 'pasar', 12, 2.50, 2, 14, 12000],
            ['KGK-001', 'Kangkung', 'Sayuran', 'ikat', 'pasar', 25, 1.50, 1, 1, 3000],
            ['PSG-001', 'Pisang', 'Buah', 'kg', 'pasar', 25, 2.00, 2, 5, 15000],
            ['MYK-001', 'Minyak Goreng', 'Bumbu & Minyak', 'liter', 'sumber', 10, 4.00, 7, 180, 18000],
            ['BWM-001', 'Bawang Merah', 'Bumbu & Minyak', 'kg', 'pasar', 6, 2.00, 3, 30, 35000],
            ['BWP-001', 'Bawang Putih', 'Bumbu & Minyak', 'kg', 'pasar', 4, 2.00, 3, 30, 30000],
            ['GLA-001', 'Gula Pasir', 'Bumbu & Minyak', 'kg', 'sumber', 8, 4.00, 5, 365, 16000],
            ['GRM-001', 'Garam', 'Bumbu & Minyak', 'kg', 'sumber', 3, 3.00, 7, 365, 8000],
            ['KCP-001', 'Kecap', 'Bumbu & Minyak', 'liter', 'sumber', 3, 3.00, 7, 180, 22000],
        ];

        $ingredients = collect();

        foreach ($ingredientData as [$code, $name, $category, $unit, $supplier, $baseUsage, $initialFactor, $leadTime, $shelfLife, $price]) {
            $ingredients->put($code, Ingredient::query()->create([
                'code' => $code,
                'name' => $name,
                'category_id' => $categories[$category]->id,
                'unit_id' => $units[$unit]->id,
                'supplier_id' => $suppliers[$supplier]->id,
                'minimum_stock' => $baseUsage,
                'lead_time_days' => $leadTime,
                'shelf_life_days' => $shelfLife,
                'purchase_price' => $price,
                'is_active' => true,
            ]));

            $ingredients[$code]->setAttribute('_base_usage', $baseUsage);
            $ingredients[$code]->setAttribute('_initial_factor', $initialFactor);
        }

        $menuData = [
            'Nasi Ayam Sayur' => [
                'BRS-001' => 0.1000,
                'AYM-001' => 0.0800,
                'WRT-001' => 0.0300,
                'BYM-001' => 0.0400,
                'BWM-001' => 0.0080,
                'BWP-001' => 0.0050,
                'MYK-001' => 0.0100,
                'GRM-001' => 0.0030,
            ],
            'Nasi Telur Tempe' => [
                'BRS-001' => 0.1000,
                'TLR-001' => 0.0500,
                'TMP-001' => 0.0500,
                'KGK-001' => 0.2000,
                'BWM-001' => 0.0080,
                'BWP-001' => 0.0050,
                'MYK-001' => 0.0100,
                'GRM-001' => 0.0030,
            ],
            'Nasi Ikan Kangkung' => [
                'BRS-001' => 0.1000,
                'IKN-001' => 0.0700,
                'KGK-001' => 0.2000,
                'WRT-001' => 0.0200,
                'BWM-001' => 0.0080,
                'BWP-001' => 0.0050,
                'MYK-001' => 0.0100,
                'GRM-001' => 0.0030,
            ],
            'Nasi Tahu Bayam' => [
                'BRS-001' => 0.1000,
                'THU-001' => 0.0600,
                'BYM-001' => 0.0500,
                'WRT-001' => 0.0200,
                'BWM-001' => 0.0080,
                'BWP-001' => 0.0050,
                'MYK-001' => 0.0100,
                'GRM-001' => 0.0030,
            ],
            'Nasi Ayam Wortel' => [
                'BRS-001' => 0.1000,
                'AYM-001' => 0.0800,
                'WRT-001' => 0.0400,
                'PSG-001' => 0.0500,
                'BWM-001' => 0.0080,
                'BWP-001' => 0.0050,
                'MYK-001' => 0.0100,
                'GLA-001' => 0.0050,
                'KCP-001' => 0.0050,
            ],
        ];

        $menus = collect();

        foreach ($menuData as $menuName => $composition) {
            $menu = Menu::query()->create([
                'name' => $menuName,
                'description' => 'Menu MBG SPPG Tanggetada.',
                'is_active' => true,
            ]);

            foreach ($composition as $code => $quantity) {
                $menu->ingredients()->create([
                    'ingredient_id' => $ingredients[$code]->id,
                    'quantity_per_portion' => $quantity,
                ]);
            }

            $menus->push($menu);
        }

        $firstWeek = CarbonImmutable::parse(now())->startOfWeek()->subWeeks(24);
        $weeks = 24;

        foreach (range(0, $weeks - 1) as $weekIndex) {
            $weekStart = $firstWeek->addWeeks($weekIndex);

            foreach ($menus as $menuIndex => $menu) {
                foreach (range(0, 4) as $dayIndex) {
                    ProductionPlan::query()->create([
                        'production_date' => $weekStart->addDays($dayIndex)->toDateString(),
                        'menu_id' => $menu->id,
                        'portions' => 240 + (($weekIndex + $menuIndex) % 4) * 10,
                        'note' => 'Rencana produksi demo',
                        'created_by' => $admin->id,
                    ]);
                }
            }

            $ingredientIndex = 0;

            foreach ($ingredients as $ingredient) {
                $base = (float) $ingredient->getAttribute('_base_usage');
                $initialFactor = (float) $ingredient->getAttribute('_initial_factor');
                $trend = 1 + ($weekIndex * 0.002);
                $season = 1 + (0.025 * sin(($weekIndex + $ingredientIndex) * M_PI / 4));
                $noise = 1 + (((($weekIndex * 17) + ($ingredientIndex * 7)) % 11) - 5) / 100;
                $ingredientIndex++;
                $usage = max(0.001, round($base * $trend * $season * $noise, 3));

                if ($weekIndex === 0) {
                    StockTransaction::query()->create([
                        'ingredient_id' => $ingredient->id,
                        'type' => 'in',
                        'quantity' => round($base * $initialFactor, 3),
                        'transaction_date' => $weekStart->toDateString(),
                        'batch_number' => 'AWAL-' . $ingredient->code,
                        'note' => 'Stok awal demo',
                        'created_by' => $admin->id,
                    ]);
                }

                StockTransaction::query()->create([
                    'ingredient_id' => $ingredient->id,
                    'type' => 'in',
                    'quantity' => $usage,
                    'transaction_date' => $weekStart->toDateString(),
                    'batch_number' => 'IN-' . $weekStart->format('YW') . '-' . $ingredient->code,
                    'note' => 'Replenishment demo',
                    'created_by' => $admin->id,
                ]);

                StockTransaction::query()->create([
                    'ingredient_id' => $ingredient->id,
                    'type' => 'out',
                    'quantity' => $usage,
                    'transaction_date' => $weekStart->addDays(2)->toDateString(),
                    'note' => 'Pemakaian produksi demo',
                    'created_by' => $admin->id,
                ]);
            }
        }

        $this->seedForecasts($ingredients, $admin);
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Ingredient>  $ingredients
     */
    /**
     * @param  \Illuminate\Support\Collection<string, Ingredient>  $ingredients
     */
    private function seedForecasts($ingredients, User $admin): void
    {
        $runner = app(ForecastRunner::class);
        $firstWeek = CarbonImmutable::parse(now())->startOfWeek()->subWeeks(24);
        $lastWeek = $firstWeek->addWeeks(23);

        foreach (['BRS-001', 'AYM-001', 'TLR-001'] as $code) {
            $runner->run(
                ingredient: $ingredients[$code],
                start: $firstWeek,
                end: $lastWeek->endOfWeek(),
                horizon: 4,
                serviceLevel: 0.95,
                userId: $admin->id,
                note: 'Data demo SPPG Tanggetada.',
            );
        }
    }
}
