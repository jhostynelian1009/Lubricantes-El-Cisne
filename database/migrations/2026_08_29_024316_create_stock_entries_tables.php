<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->string('number', 60)->nullable()->unique();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->restrictOnDelete();
            $table->date('entry_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_entry_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_entry_id')->constrained('stock_entries')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('product_sku', 60);
            $table->string('product_name', 180);
            $table->string('unit', 30);
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();

            $table->unique(['stock_entry_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_entry_details');
        Schema::dropIfExists('stock_entries');
    }
};
