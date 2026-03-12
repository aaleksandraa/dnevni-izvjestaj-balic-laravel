<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'config_key',
        'config_value',
    ];

    protected function casts(): array
    {
        return [
            'config_value' => 'array',
        ];
    }
}
