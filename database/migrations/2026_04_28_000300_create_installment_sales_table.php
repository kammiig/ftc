<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_sales', function (Blueprint $table): void {
            $table->id();
            $table->string('account_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->decimal('product_cost_price', 14, 2)->default(0);
            $table->decimal('installment_sale_price', 14, 2)->default(0);
            $table->decimal('advance_payment', 14, 2)->default(0);
            $table->decimal('remaining_balance', 14, 2)->default(0);
            $table->unsignedInteger('installments_count')->default(1);
            $table->decimal('monthly_installment_amount', 14, 2)->default(0);
            $table->date('installment_start_date');
            $table->unsignedTinyInteger('monthly_due_day')->default(1);
            $table->decimal('total_paid', 14, 2)->default(0);
            $table->decimal('pending_balance', 14, 2)->default(0);
            $table->decimal('profit_amount', 14, 2)->default(0);
            $table->string('status')->default('active')->index();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['installment_start_date', 'monthly_due_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_sales');
    }
};
