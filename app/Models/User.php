<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'can_submit_report',
        'can_change_submitter',
        'phone',
    ];

    protected $attributes = [
        'role' => 'medicinska_sestra',
        'is_active' => true,
        'can_submit_report' => true,
        'can_change_submitter' => false,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'can_submit_report' => 'boolean',
            'can_change_submitter' => 'boolean',
        ];
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'user_location')
            ->withTimestamps();
    }

    public function createdReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'created_by_user_id');
    }

    public function submittedReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'submitted_by_user_id');
    }

    public function editedReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'last_edited_by_user_id');
    }

    public function enteredReportItems(): HasMany
    {
        return $this->hasMany(DailyReportItem::class, 'entered_by_user_id');
    }

    public function enteredFindingItems(): HasMany
    {
        return $this->hasMany(DailyReportFindingItem::class, 'entered_by_user_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * @param  array<int, string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
