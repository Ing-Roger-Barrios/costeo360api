<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_key',
        'type',
        'start_date',
        'end_date',
        'is_active',
        'is_paid',
        'amount',
        'currency'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'amount' => 'decimal:2'
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('is_paid', true)
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    });
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    // Métodos útiles
    public function isValid()
    {
        if (!$this->is_active || !$this->is_paid) {
            return false;
        }

        if ($this->end_date === null) {
            return true; // Licencia vitalicia
        }

        return $this->end_date >= now();
    }

    public function getTypeLabel()
    {
        $labels = [
            'monthly' => 'Mensual',
            'yearly' => 'Anual', 
            'lifetime' => 'Vitalicia'
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getDaysRemaining()
    {
        if ($this->end_date === null) {
            return 'Vitalicia';
        }
        
        $now = Carbon::now();
        if ($this->end_date->lt($now)) {
            return 0;
        }
        
        return $this->end_date->diffInDays($now);
    }
}
