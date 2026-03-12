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
        Schema::create('daily_report_finding_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')
                ->constrained('daily_reports')
                ->cascadeOnDelete();
            $table->foreignId('finding_id')
                ->nullable()
                ->constrained('findings')
                ->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('entered_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_finding_items');
    }
};
