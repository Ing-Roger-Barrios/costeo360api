<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use  HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'email_verified',
        'active',
        'preferences'
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
            'email_verified' => 'boolean',
            'active' => 'boolean',
            'preferences' => 'array'
        ];
    }
    // Relaciones
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // Verificar si tiene un rol específico
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return !!$role->intersect($this->roles)->count();
    }

    // Verificar si es administrador
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    // Scope para usuarios activos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function activeLicense()
    {
        return $this->licenses()->active()->first();
    }

    public function hasValidLicense()
    {
        return $this->activeLicense() !== null;
    }
}
