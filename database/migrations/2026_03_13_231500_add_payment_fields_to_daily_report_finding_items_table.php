<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_report_finding_items', function (Blueprint $table): void {
            $table->string('payment_status', 30)->default('neplaceno')->index();
            $table->string('payment_method', 50)->nullable()->index();
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            $table->text('unpaid_reason')->nullable();
        });

        DB::table('daily_report_finding_items')->update([
            'payment_status' => 'placeno',
            'payment_method' => 'nepoznato',
            'paid_amount' => DB::raw('COALESCE(total_price, 0)'),
            'remaining_amount' => 0,
            'unpaid_reason' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_finding_items', function (Blueprint $table): void {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['payment_method']);
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'paid_amount',
                'remaining_amount',
                'unpaid_reason',
            ]);
        });
    }
};

