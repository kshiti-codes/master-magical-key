<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'chapter_id',
        'spell_id',
        'subscription_plan_id',
        'training_video_id',
        'item_type',
        'quantity',
        'price',
    ];

    /**
     * Get the purchase that owns the item.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the chapter associated with this purchase item.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
    
    /**
     * Get the total price for this purchase item.
     */
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the spell associated with this purchase item.
     */
    public function spell()
    {
        if ($this->item_type === 'spell') {
            return $this->belongsTo(Spell::class);
        }
        
        return null;
    }

    /**
     * Get the subscription plan
     */
    public function subscriptionPlan()
    {
        if ($this->item_type === 'subscription') {
            return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
        }
        
        return null;
    }

    /**
     * Get the video associated with this purchase item.
     */
    public function video()
    {
        if ($this->item_type === 'video') {
            return $this->belongsTo(TrainingVideo::class, 'video_id');
        }
        
        return null;
    }

    /**
     * Get the purchased item (polymorphic relationship).
     */
    public function purchasable()
    {
        if ($this->item_type === 'chapter') {
            return $this->belongsTo(Chapter::class, 'chapter_id');
        } elseif ($this->item_type === 'spell') {
            return $this->belongsTo(Spell::class, 'spell_id');
        } elseif ($this->item_type === 'video') {
            return $this->belongsTo(TrainingVideo::class, 'video_id');
        }
        
        return null;
    }

    /**
     * Get the item name.
     */
    public function getItemNameAttribute()
    {
        if ($this->item_type === 'chapter' && $this->chapter) {
            return "Chapter {$this->chapter->id}: {$this->chapter->title}";
        } elseif ($this->item_type === 'spell' && $this->spell) {
            return "Spell: {$this->spell->title}";
        } elseif ($this->item_type === 'subscription') {
            return $this->description ?? 'Subscription Plan';
        } elseif ($this->item_type === 'video' && $this->video) {
            return "Training Video: {$this->video->title}";
        }
        
        return 'Unknown Item';
    }
}