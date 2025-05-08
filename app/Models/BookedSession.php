<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookedSession extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'coach_id',
        'session_type_id',
        'coach_availability_id',
        'session_time',
        'duration',
        'meeting_link',
        'status',
        'cancellation_reason',
        'transaction_id',
        'amount_paid'
    ];
    
    protected $casts = [
        'session_time' => 'datetime',
        'duration' => 'integer',
        'amount_paid' => 'decimal:2',
    ];
    
    /**
     * Get the user who booked this session
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the coach for this session
     */
    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }
    
    /**
     * Get the session type
     */
    public function sessionType()
    {
        return $this->belongsTo(SessionType::class);
    }
    
    /**
     * Get the availability record associated with this booking
     */
    public function availability()
    {
        return $this->belongsTo(CoachAvailability::class, 'coach_availability_id');
    }

    /**
     * Get the purchase associated with this session
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
    
    /**
     * Format the session time for display
     */
    public function getFormattedSessionTimeAttribute()
    {
        return $this->session_time->format('l, F j, Y g:i A');
    }
    
    /**
     * Check if the session can be joined now (within 10 mins of start time)
     */
    public function canJoinNow()
    {
        $now = Carbon::now();
        $startWindow = Carbon::parse($this->session_time)->subMinutes(10);
        $endTime = Carbon::parse($this->session_time)->addMinutes($this->duration);
        
        return $now->between($startWindow, $endTime) && $this->status === 'confirmed';
    }
    
    /**
     * Get the formatted status for display
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'confirmed' => 'success',
            'completed' => 'primary',
            'cancelled' => 'danger',
            'refunded' => 'info'
        ];
        
        $color = $colors[$this->status] ?? 'secondary';
        
        return '<span class="badge bg-' . $color . '">' . ucfirst($this->status) . '</span>';
    }
    
    /**
     * Get the end time of the session
     */
    public function getEndTimeAttribute()
    {
        return $this->session_time->copy()->addMinutes($this->duration);
    }
}