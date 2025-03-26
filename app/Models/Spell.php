<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Spell extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'description',
        'pdf_path',
        'price',
        'currency',
        'is_published',
        'order'
    ];
    
    /**
     * Get the chapters that include this spell.
     */
    public function chapters()
    {
        return $this->belongsToMany(Chapter::class)
            ->withPivot('is_free_with_chapter')
            ->withTimestamps();
    }
    
    /**
     * Get the users who have access to this spell.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_spells')
            ->withPivot('purchased_at', 'last_downloaded_at', 'download_count')
            ->withTimestamps();
    }
    
    /**
     * Get the purchase items for this spell.
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    
    /**
     * Get the cart items for this spell.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    
    /**
     * Get the bundles that include this spell.
     */
    public function bundles()
    {
        return $this->hasManyThrough(
            Bundle::class,
            BundleItem::class,
            'item_id',
            'id',
            'id',
            'bundle_id'
        )->where('bundle_items.item_type', 'spell');
    }
    
    /**
     * Check if the spell is owned by the current user
     */
    public function isOwned()
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Auth::user()->spells()->where('spell_id', $this->id)->exists();
    }
    
    /**
     * Check if the spell is available for free with a chapter the user owns
     */
    public function isAvailableThroughChapter()
    {
        if (!Auth::check()) {
            return false;
        }
        
        // Get all chapters that include this spell for free
        $chapterIds = $this->chapters()
            ->where('is_free_with_chapter', true)
            ->pluck('chapters.id');
        
        // Check if user owns any of these chapters
        return Auth::user()->chapters()
            ->whereIn('chapter_id', $chapterIds)
            ->exists();
    }

    // Add accessor methods
    public function getPdfAttribute()
    {
        if (!$this->pdf_path) {
            return null;
        }
        
        $path = public_path($this->pdf_path);
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        
        return null;
    }
}