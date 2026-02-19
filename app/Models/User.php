<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'department_id',
        'superior_id',
        'company_name',
        'phone_number',
        'otp_code',
        'otp_expires_at',
        'ceo_id',
    ];

    /**
     * Role Constants
     */
    public const ROLE_CEO = 'ceo';
    public const ROLE_DIRECTOR = 'director';
    public const ROLE_MANAJER = 'manajer';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_STAFF = 'staff';

    /**
     * Check if user is CEO
     */
    public function isCeo(): bool
    {
        return $this->role === self::ROLE_CEO;
    }

    /**
     * Check if user is Director
     */
    public function isDirector(): bool
    {
        return $this->role === self::ROLE_DIRECTOR;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function superior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'superior_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'superior_id');
    }


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
        ];
    }
}
