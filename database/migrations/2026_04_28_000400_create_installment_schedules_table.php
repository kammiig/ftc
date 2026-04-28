<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('installment_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('installment_number');
            $table->date('due_date')->index();
            $table->decimal('due_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('remaining_amount', 14, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->date('paid_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['installment_sale_id', 'installment_number'], 'sale_installment_unique');
            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_schedules');
    }
};
