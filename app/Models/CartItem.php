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
}