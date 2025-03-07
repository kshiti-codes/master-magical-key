<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in the cart.
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the total price of all items in the cart (before tax).
     */
    public function getSubtotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Get the GST amount (10% of subtotal).
     */
    public function getTaxAttribute()
    {
        return round($this->subtotal * 0.1, 2);
    }

    /**
     * Get the total price including GST.
     */
    public function getTotalAttribute()
    {
        return $this->subtotal + $this->tax;
    }

    /**
     * Add a chapter to the cart.
     */
    public function addItem(Chapter $chapter, $quantity = 1)
    {
        // Check if chapter already in cart
        $existingItem = $this->items()->where('chapter_id', $chapter->id)->first();

        if ($existingItem) {
            // Update quantity
            $existingItem->update([
                'quantity' => $existingItem->quantity + $quantity
            ]);
            return $existingItem;
        }

        // Add new item
        return $this->items()->create([
            'chapter_id' => $chapter->id,
            'quantity' => $quantity,
            'price' => $chapter->price
        ]);
    }

    /**
     * Remove a chapter from the cart.
     */
    public function removeItem($cartItemId)
    {
        $item = $this->items()->find($cartItemId);
        if ($item) {
            $item->delete();
            return true;
        }
        return false;
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getItemCountAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Clear all items from the cart.
     */
    public function clear()
    {
        return $this->items()->delete();
    }
}