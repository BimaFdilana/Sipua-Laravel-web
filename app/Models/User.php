<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_PENGUSUL = 'pengusul';

    public const ROLE_PERENCANAAN = 'perencanaan';

    public const ROLE_WADIR = 'wadir';

    public const ROLE_SPI = 'spi';

    public const ROLE_DIREKTUR = 'direktur';

    /** Kode peran => label tampilan. */
    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_PENGUSUL => 'Pengusul',
        self::ROLE_PERENCANAAN => 'Perencanaan',
        self::ROLE_WADIR => 'Wadir',
        self::ROLE_SPI => 'SPI',
        self::ROLE_DIREKTUR => 'Direktur',
    ];

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
        'unit_id',
        'nama_lengkap',
        'nip',
        'jabatan',
        'no_hp',
        'is_active',
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
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }
}
