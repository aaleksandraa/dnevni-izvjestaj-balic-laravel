<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'date_of_birth',
        'phone',
        'email',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function dailyReportItems(): HasMany
    {
        return $this->hasMany(DailyReportItem::class);
    }

    public function dailyReportFindingItems(): HasMany
    {
        return $this->hasMany(DailyReportFindingItem::class);
    }
}
