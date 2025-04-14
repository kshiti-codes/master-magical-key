<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TrainingVideo extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'description',
        'video_path',
        'thumbnail_path',
        'duration',
        'price',
        'currency',
        'is_published',
        'order_sequence'
    ];
    
    protected $casts = [
        'is_published' => 'boolean',
    ];
    
    /**
     * Get the users who have access to this video
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_videos')
            ->withPivot('purchased_at', 'last_watched_at', 'watch_count')
            ->withTimestamps();
    }
    
    /**
     * Get the formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2) . ' ' . $this->currency;
    }
    
    /**
     * Get the formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return 'Unknown duration';
        }
        
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        
        return $minutes . ':' . str_pad($seconds, 2, '0', STR_PAD_LEFT);
    }
    
    /**
     * Check if the current user has access to this video
     */
    public function isAccessible()
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        
        // Check if the user has directly purchased this video
        $hasDirectAccess = $user->videos()->where('training_videos.id', $this->id)->exists();
        
        if ($hasDirectAccess) {
            return true;
        }
        
        // Check if the user has a lifetime subscription which grants access to all videos
        $hasLifetimeSubscription = $user->subscriptions()
            ->whereHas('plan', function($query) {
                $query->where('is_lifetime', true);
            })
            ->where('status', 'active')
            ->exists();
        
        return $hasLifetimeSubscription;
    }
    
    /**
     * Check if the video is free for the current user
     */
    public function isFreeForUser()
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        
        // Check if the user has a lifetime subscription
        $hasLifetimeSubscription = $user->subscriptions()
            ->whereHas('plan', function($query) {
                $query->where('is_lifetime', true);
            })
            ->where('status', 'active')
            ->exists();
        
        return $hasLifetimeSubscription;
    }
}