<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CoachAvailability extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'coach_id',
        'date',
        'start_time',
        'end_time',
        'status'
    ];
    
    protected $casts = [
        'date' => 'date',
    ];
    
    /**
     * Get the coach this availability belongs to
     */
    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }
    
    /**
     * Get the booking lock for this availability if it exists
     */
    public function bookingLock()
    {
        return $this->hasOne(BookingLock::class);
    }
    
    /**
     * Get the booked session for this availability if it exists
     */
    public function bookedSession()
    {
        return $this->hasOne(BookedSession::class);
    }
    
    /**
     * Check if this availability is locked
     */
    public function isLocked()
    {
        return $this->bookingLock()
            ->where('expires_at', '>', now())
            ->exists();
    }
    
    /**
     * Format the time range for display
     */
    public function getTimeRangeAttribute()
    {
        $start = Carbon::parse($this->start_time)->format('g:i A');
        $end = Carbon::parse($this->end_time)->format('g:i A');
        return $start . ' - ' . $end;
    }
    
    /**
     * Format the date for display
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('l, F j, Y');
    }
    
    /**
     * Scope a query to only include available time slots
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}