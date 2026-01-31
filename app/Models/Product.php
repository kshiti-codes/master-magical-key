<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'currency',
        'type',
        'pdf_file_path',
        'audio_file_path',
        'popup_text',
        'is_active',
        'sku',
        'slug',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Boot method to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
            if (empty($product->sku)) {
                $product->sku = 'PROD-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('title') && empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
        });
    }

    /**
     * Scope to get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by product type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get all purchase items for this product
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get all cart items for this product
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Check if a user has purchased this product
     */
    public function isPurchasedBy($userId)
    {
        return $this->purchaseItems()
            ->whereHas('purchase', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'completed');
            })
            ->exists();
    }

    /**
     * Get formatted price with currency
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Calculate price with GST (10% for Australia)
     */
    public function getPriceWithGstAttribute()
    {
        $gstRate = 0.10; // 10% GST for Australia
        return $this->price * (1 + $gstRate);
    }

    /**
     * Get formatted price with GST
     */
    public function getFormattedPriceWithGstAttribute()
    {
        return '$' . number_format($this->price_with_gst, 2) . ' ' . $this->currency;
    }

    /**
     * Calculate GST amount
     */
    public function getGstAmountAttribute()
    {
        return $this->price * 0.10;
    }

    /**
     * Check if product has PDF file
     */
    public function hasPdf()
    {
        return !empty($this->pdf_file_path) && file_exists(public_path('app/' . $this->pdf_file_path));
    }

    /**
     * Check if product has audio file
     */
    public function hasAudio()
    {
        return !empty($this->audio_file_path) && file_exists(public_path('app/' . $this->audio_file_path));
    }

    /**
     * Get the full PDF file path
     */
    public function getPdfPath()
    {
        return public_path('app/' . $this->pdf_file_path);
    }

    /**
     * Get the full audio file path
     */
    public function getAudioPath()
    {
        return public_path('app/' . $this->audio_file_path);
    }

    /**
     * Get product image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset($this->image);
        }
        return asset('images/default-product.png'); // fallback image
    }

    /**
     * Get the route key name for model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}