<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable()->index();
            $table->string('brand_model')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->decimal('cost_price', 14, 2)->default(0);
            $table->decimal('cash_sale_price', 14, 2)->default(0);
            $table->decimal('installment_sale_price', 14, 2)->default(0);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status')->default('available')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
