<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke SIPRES MBG');
    }

    public function test_user_can_login_and_open_dashboard(): void
    {
        $user = $this->user('admin');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->get('/admin')->assertOk()->assertSee('Dasbor');
    }

    public function test_invalid_password_is_rejected(): void
    {
        $user = $this->user('admin');

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'salah-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_resource_pages_render(): void
    {
        $user = $this->user('admin');
        $this->actingAs($user);

        $this->seedMinimalMasterData();

        foreach ([
            '/admin/ingredients',
            '/admin/ingredients/create',
            '/admin/categories',
            '/admin/categories/create',
            '/admin/units',
            '/admin/units/create',
            '/admin/suppliers',
            '/admin/suppliers/create',
            '/admin/stock-transactions',
            '/admin/stock-transactions/create',
            '/admin/menus',
            '/admin/menus/create',
            '/admin/production-plans',
            '/admin/production-plans/create',
            '/admin/forecasts',
            '/admin/users',
            '/admin/users/create',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_ingredient_can_be_created(): void
    {
        $user = $this->user('admin');
        $this->actingAs($user);
        [$category, $unit] = $this->seedMinimalMasterData();

        $this->post('/admin/ingredients', [
            'code' => 'TLR-001',
            'name' => 'Telur Ayam',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => 10,
            'lead_time_days' => 2,
            'purchase_price' => 28000,
            'is_active' => '1',
        ])->assertRedirect(route('admin.ingredients.index'));

        $this->assertDatabaseHas('ingredients', ['code' => 'TLR-001', 'name' => 'Telur Ayam']);
    }

    public function test_non_admin_cannot_open_user_management(): void
    {
        $this->actingAs($this->user('ahli_gizi'));

        $this->get('/admin/users')->assertForbidden();
    }

    public function test_logout_returns_to_home(): void
    {
        $this->actingAs($this->user('admin'));

        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    private function user(string $role): User
    {
        return User::query()->create([
            'name' => ucfirst($role) . ' Test',
            'email' => $role . '@example.test',
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    /**
     * @return array{0: Category, 1: Unit}
     */
    private function seedMinimalMasterData(): array
    {
        $category = Category::query()->firstOrCreate(['name' => 'Karbohidrat']);
        $unit = Unit::query()->firstOrCreate(['symbol' => 'kg'], ['name' => 'Kilogram']);

        return [$category, $unit];
    }
}
