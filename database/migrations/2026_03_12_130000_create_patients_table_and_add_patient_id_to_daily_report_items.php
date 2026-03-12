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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->index();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('daily_report_items', function (Blueprint $table): void {
            $table->foreignId('patient_id')
                ->nullable()
                ->after('daily_report_id')
                ->constrained('patients')
                ->nullOnDelete();
            $table->index(['daily_report_id', 'patient_id']);
        });

        $now = now();
        $patientIdByName = [];

        $items = DB::table('daily_report_items')
            ->select(['id', 'patient_full_name'])
            ->whereNull('patient_id')
            ->get();

        foreach ($items as $item) {
            $name = trim((string) ($item->patient_full_name ?? ''));

            if ($name === '') {
                continue;
            }

            if (! isset($patientIdByName[$name])) {
                $existingPatientId = DB::table('patients')
                    ->where('full_name', $name)
                    ->value('id');

                if ($existingPatientId) {
                    $patientIdByName[$name] = (int) $existingPatientId;
                } else {
                    $patientIdByName[$name] = (int) DB::table('patients')->insertGetId([
                        'full_name' => $name,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('daily_report_items')
                ->where('id', $item->id)
                ->update([
                    'patient_id' => $patientIdByName[$name],
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_items', function (Blueprint $table): void {
            $table->dropIndex(['daily_report_id', 'patient_id']);
            $table->dropConstrainedForeignId('patient_id');
        });

        Schema::dropIfExists('patients');
    }
};
