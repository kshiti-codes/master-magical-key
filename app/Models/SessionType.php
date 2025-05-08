<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionType extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'duration',
        'price',
        'currency',
        'is_active'
    ];
    
    protected $casts = [
        'duration' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the coaches that offer this session type
     */
    public function coaches()
    {
        return $this->belongsToMany(Coach::class, 'coach_session_types');
    }
    
    /**
     * Get the booked sessions of this type
     */
    public function bookedSessions()
    {
        return $this->hasMany(BookedSession::class);
    }
    
    /**
     * Get the formatted price with currency
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2) . ' ' . $this->currency;
    }
}