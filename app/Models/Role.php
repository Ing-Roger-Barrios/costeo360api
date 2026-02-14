<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Scope para roles activos
    public function scopeActive($query)
    {
        return $query->whereHas('users', function ($q) {
            $q->where('active', true);
        });
    }
}
