<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DailyReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'patient_id',
        'patient_full_name',
        'is_new_patient',
        'service_id',
        'doctor_id',
        'item_price',
        'payment_status',
        'payment_method',
        'paid_amount',
        'remaining_amount',
        'unpaid_reason',
        'notes',
        'entered_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'item_price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'is_new_patient' => 'boolean',
        ];
    }

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'doctor_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }
}
