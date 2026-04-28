<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_guarantors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('full_name')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('cnic')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('relationship')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('cnic_front_path')->nullable();
            $table->string('cnic_back_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'position']);
            $table->index(['phone', 'cnic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_guarantors');
    }
};
