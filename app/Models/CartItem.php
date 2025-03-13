<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'chapter_id',
        'spell_id',
        'quantity',
        'price',
    ];

    /**
     * Get the cart that owns the item.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the chapter associated with this cart item.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
    
    /**
     * Get the total price for this cart item.
     */
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the spell associated with this cart item.
     */
    public function spell()
    {
        if ($this->item_type === 'spell') {
            return $this->belongsTo(Spell::class);
        }
        
        return null;
    }

    /**
     * Get the purchasable item (polymorphic relationship).
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