<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingLock extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'coach_availability_id',
        'expires_at'
    ];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    
    /**
     * Get the user who created this lock
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the availability this lock is for
     */
    public function availability()
    {
        return $this->belongsTo(CoachAvailability::class, 'coach_availability_id');
    }
    
    /**
     * Check if this lock is expired
     */
    public function isExpired()
    {
        return now()->gt($this->expires_at);
    }
    
    /**
     * Scope a query to only include active locks
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
    
    /**
     * Create a new lock for an availability slot
     * 
     * @param int $userId
     * @param int $availabilityId
     * @param int $minutesToExpire Default is 10 minutes
     * @return BookingLock
     */
    public static function createLock($userId, $availabilityId, $minutesToExpire = 10)
    {
        // Remove any existing expired locks
        self::where('coach_availability_id', $availabilityId)
            ->where('expires_at', '<=', now())
            ->delete();
        
        // Create new lock
        return self::create([
            'user_id' => $userId,
            'coach_availability_id' => $availabilityId,
            'expires_at' => now()->addMinutes($minutesToExpire)
        ]);
    }
}