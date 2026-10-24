<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 20)->unique();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('minimum_stock', 12, 3)->default(0);
            $table->unsignedSmallInteger('lead_time_days')->default(1);
            $table->unsignedSmallInteger('shelf_life_days')->nullable();
            $table->decimal('purchase_price', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->decimal('quantity', 12, 3);
            $table->date('transaction_date');
            $table->string('batch_number', 50)->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['ingredient_id', 'transaction_date']);
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_per_portion', 12, 4);
            $table->timestamps();
            $table->unique(['menu_id', 'ingredient_id']);
        });

        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->date('production_date')->index();
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('portions');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('forecast_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('method', 30)->default('holt');
            $table->string('period_type', 20)->default('weekly');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('horizon');
            $table->unsignedSmallInteger('training_periods')->default(0);
            $table->unsignedSmallInteger('validation_periods')->default(0);
            $table->decimal('alpha', 5, 4);
            $table->decimal('beta', 5, 4);
            $table->decimal('mape', 8, 4)->nullable();
            $table->decimal('mae', 12, 3)->nullable();
            $table->decimal('rmse', 12, 3)->nullable();
            $table->decimal('service_level', 5, 4)->default(0.95);
            $table->decimal('current_stock', 12, 3)->default(0);
            $table->decimal('on_order', 12, 3)->default(0);
            $table->decimal('safety_stock', 12, 3)->default(0);
            $table->decimal('reorder_point', 12, 3)->default(0);
            $table->decimal('recommended_purchase', 12, 3)->default(0);
            $table->string('status', 20)->default('completed');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('forecast_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_run_id')->constrained()->cascadeOnDelete();
            $table->date('period_date');
            $table->decimal('actual', 12, 3)->nullable();
            $table->decimal('level', 14, 4);
            $table->decimal('trend', 14, 4);
            $table->decimal('forecast', 14, 4);
            $table->decimal('error', 14, 4)->nullable();
            $table->boolean('is_future')->default(false);
            $table->timestamps();
            $table->index(['forecast_run_id', 'period_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_values');
        Schema::dropIfExists('forecast_runs');
        Schema::dropIfExists('production_plans');
        Schema::dropIfExists('menu_ingredients');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('units');
        Schema::dropIfExists('categories');
    }
};
