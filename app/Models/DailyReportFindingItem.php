<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DailyReportFindingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'finding_id',
        'patient_id',
        'quantity',
        'unit_price',
        'total_price',
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
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_user_id');
    }
}
