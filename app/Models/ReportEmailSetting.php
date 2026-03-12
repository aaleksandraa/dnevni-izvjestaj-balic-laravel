<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportEmailSetting extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    public const REPORT_TYPES = [
        'daily',
        'weekly',
        'monthly',
    ];

    protected $fillable = [
        'report_type',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
