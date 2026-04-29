<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('payments', 'receipt_pdf_path')) {
                $table->string('receipt_pdf_path')->nullable()->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'receipt_pdf_path')) {
                $table->dropColumn('receipt_pdf_path');
            }
        });
    }
};
