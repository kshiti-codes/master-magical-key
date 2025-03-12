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
        
        return $isPurchased;
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
}