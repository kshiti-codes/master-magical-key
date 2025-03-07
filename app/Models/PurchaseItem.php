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
}