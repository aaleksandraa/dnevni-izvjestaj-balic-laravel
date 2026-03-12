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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('medicinska_sestra')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('can_submit_report')->default(true);
            $table->boolean('can_change_submitter')->default(false);
            $table->string('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'is_active',
                'can_submit_report',
                'can_change_submitter',
                'phone',
            ]);
        });
    }
};
