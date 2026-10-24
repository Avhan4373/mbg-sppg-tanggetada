<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ForecastController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ProductionPlanController;
use App\Http\Controllers\Admin\StockTransactionController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PredictionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PredictionController::class, 'index'])->name('home');
Route::post('/prediksi', [PredictionController::class, 'store'])->name('predictions.store');
Route::get('/prediksi/{forecastRun}', [PredictionController::class, 'show'])->name('predictions.show');
Route::get('/panduan', [PageController::class, 'guide'])->name('guide');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('ingredients', IngredientController::class)->except(['show'])
        ->parameters(['ingredients' => 'ingredient']);
    Route::resource('categories', CategoryController::class)->except(['show'])
        ->parameters(['categories' => 'category']);
    Route::resource('units', UnitController::class)->except(['show'])
        ->parameters(['units' => 'unit']);
    Route::resource('suppliers', SupplierController::class)->except(['show'])
        ->parameters(['suppliers' => 'supplier']);
    Route::resource('stock-transactions', StockTransactionController::class)->except(['show'])
        ->parameters(['stock-transactions' => 'stockTransaction']);
    Route::resource('menus', MenuController::class)->except(['show'])
        ->parameters(['menus' => 'menu']);
    Route::resource('production-plans', ProductionPlanController::class)->except(['show'])
        ->parameters(['production-plans' => 'productionPlan']);
    Route::resource('users', UserController::class)->except(['show'])
        ->parameters(['users' => 'user']);

    Route::get('forecasts', [ForecastController::class, 'index'])->name('forecasts.index');
    Route::delete('forecasts/{forecastRun}', [ForecastController::class, 'destroy'])->name('forecasts.destroy');
});
