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
        Schema::create('daily_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')
                ->constrained('daily_reports')
                ->cascadeOnDelete();
            $table->string('patient_full_name');
            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();
            $table->foreignId('doctor_id')
                ->nullable()
                ->constrained('staff_members')
                ->nullOnDelete();
            $table->decimal('item_price', 10, 2)->default(0);
            $table->string('payment_status', 30)->default('neplaceno')->index();
            $table->string('payment_method', 30)->nullable();
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            $table->text('unpaid_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('entered_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['daily_report_id', 'doctor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_items');
    }
};
