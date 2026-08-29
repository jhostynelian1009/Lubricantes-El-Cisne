<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('sku', 60)->unique();
            $table->string('barcode', 80)->nullable()->unique();
            $table->string('name', 180);
            $table->text('description')->nullable();
            $table->string('unit', 30);
            $table->decimal('current_stock', 14, 3)->default(0);
            $table->decimal('minimum_stock', 14, 3)->default(0);
            $table->decimal('last_cost', 14, 2)->default(0);
            $table->decimal('sale_price', 14, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['active', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
