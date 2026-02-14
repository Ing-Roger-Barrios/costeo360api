<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'license_id',
        'payment_id',
        'status',
        'amount',
        'currency',
        'payment_method',
        'payment_data',
        'paid_at'
    ];

    protected $casts = [
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
