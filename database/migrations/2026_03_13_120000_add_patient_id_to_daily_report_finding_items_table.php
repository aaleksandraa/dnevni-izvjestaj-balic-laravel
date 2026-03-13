<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_report_finding_items', function (Blueprint $table): void {
            $table->foreignId('patient_id')
                ->nullable()
                ->after('finding_id')
                ->constrained('patients')
                ->nullOnDelete();

            $table->index(['daily_report_id', 'patient_id'], 'drfi_report_patient_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_finding_items', function (Blueprint $table): void {
            $table->dropIndex('drfi_report_patient_index');
            $table->dropConstrainedForeignId('patient_id');
        });
    }
};

