<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content',
        'preview_content',
        'price',
        'currency',
        'order',
        'is_published',
        'is_free',
        // Audio fields
        'audio_path',
        'audio_format',
        'audio_duration',
        'has_audio',
        'audio_timestamps'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_free' => 'boolean',
        'has_audio' => 'boolean',
        'audio_timestamps' => 'array',
    ];

    /**
     * Check if the chapter is free
     */
    public function isFree()
    {
        return $this->is_free || $this->price == 0;
    }

    /**
     * Check if the chapter is purchased by the current user
     */
    public function isPurchased()
    {
        // Free chapters are accessible to everyone
        if ($this->isFree()) {
            return true;
        }

        if (!Auth::check()) {
            return false;
        }
        
        $isPurchased = \App\Models\PurchaseItem::where('chapter_id', $this->id)
        ->whereHas('purchase', function($query) {
            $query->where('user_id', Auth::id())
                  ->where('status', 'completed');
        })
        ->exists();

        $isUserChapter = \App\Models\UserChapter::where('chapter_id', $this->id)
        ->where('user_id', Auth::id())
        ->exists();

        $hasLifetimeSubscription = \App\Models\User::where('id', Auth::id())
        ->whereHas('subscriptions', function($query) {
            $query->where('status', 'active')
                  ->where('subscription_plan_id', function($subQuery) {
                      $subQuery->select('id')
                               ->from('subscription_plans')
                               ->where('is_lifetime', true);
                  });
        })
        ->exists();

        $hasActiveSubscription = \App\Models\User::where('id', Auth::id())
        ->whereHas('subscriptions', function($query) {
            $query->where('status', 'active')
                  ->where(function($subQuery) {
                      $subQuery->whereNull('end_date')
                               ->orWhere('end_date', '>', now());
                  });
        })
        ->exists();
        
        return ($isPurchased && $isUserChapter) || $hasLifetimeSubscription || $hasActiveSubscription;
    }
    
    /**
     * Get purchase items associated with this chapter.
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get purchases that include this chapter.
     */
    public function purchases()
    {
        return $this->hasManyThrough(Purchase::class, PurchaseItem::class, 'chapter_id', 'id', 'id', 'purchase_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_chapters')
            ->withPivot('last_read_at', 'last_page')
            ->withTimestamps();
    }

    /**
     * Get the pages for the chapter.
     */
    public function pages()
    {
        return $this->hasMany(ChapterPage::class)->orderBy('page_number');
    }

    /**
     * Get the spells associated with this chapter.
     */
    public function spells()
    {
        return $this->belongsToMany(Spell::class)
            ->withPivot('is_free_with_chapter')
            ->withTimestamps();
    }

    /**
     * Get the free spells associated with this chapter.
     */
    public function freeSpells()
    {
        return $this->belongsToMany(Spell::class)
            ->withPivot('is_free_with_chapter')
            ->wherePivot('is_free_with_chapter', true)
            ->withTimestamps();
    }

    /**
     * Get the premium spells associated with this chapter.
     */
    public function premiumSpells()
    {
        return $this->belongsToMany(Spell::class)
            ->withPivot('is_free_with_chapter')
            ->wherePivot('is_free_with_chapter', false)
            ->withTimestamps();
    }

    /**
     * Get the formatted audio path for the player
     */
    public function getAudioUrlAttribute()
    {
        if (!$this->has_audio || !$this->audio_path) {
            return null;
        }
        
        return asset('storage/' . $this->audio_path);
    }
}