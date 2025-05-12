<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'email',
        'bio',
        'profile_image',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the session types this coach offers
     */
    public function sessionTypes()
    {
        return $this->belongsToMany(SessionType::class, 'coach_session_types');
    }
    
    /**
     * Get the availabilities for this coach
     */
    public function availabilities()
    {
        return $this->hasMany(CoachAvailability::class);
    }
    
    /**
     * Get the booked sessions for this coach
     */
    public function bookedSessions()
    {
        return $this->hasMany(BookedSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get available time slots for a specific date
     */
    public function getAvailableSlots($date)
    {
        return $this->availabilities()
            ->where('date', $date)
            ->where('status', 'available')
            ->orderBy('start_time')
            ->get();
    }
}