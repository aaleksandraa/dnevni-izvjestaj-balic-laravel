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
        Schema::create('report_email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('report_type', 20)->index();
            $table->string('email');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['report_type', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_email_settings');
    }
};
