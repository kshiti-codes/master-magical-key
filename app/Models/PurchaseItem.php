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
     * Get the purchased item (polymorphic relationship).
     */
    public function purchasable()
    {
        if ($this->item_type === 'chapter') {
            return $this->belongsTo(Chapter::class, 'chapter_id');
        } elseif ($this->item_type === 'spell') {
            return $this->belongsTo(Spell::class, 'spell_id');
        }
        
        return null;
    }

    /**
     * Get the item name.
     */
    public function getItemNameAttribute()
    {
        if ($this->item_type === 'chapter') {
            return $this->chapter ? "Chapter {$this->chapter->id}: {$this->chapter->title}" : 'Unknown Chapter';
        } elseif ($this->item_type === 'spell') {
            return $this->spell ? "Spell: {$this->spell->title}" : 'Unknown Spell';
        }
        
        return 'Unknown Item';
    }
}