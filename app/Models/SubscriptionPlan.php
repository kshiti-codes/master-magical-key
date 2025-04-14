<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SubscriptionPlan extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'billing_interval',
        'is_active',
        'is_lifetime',
        'available_until'
    ];
    
    protected $casts = [
        'available_until' => 'datetime',
        'is_active' => 'boolean',
        'is_lifetime' => 'boolean',
    ];
    
    /**
     * Check if the plan is still available for purchase
     */
    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }
        
        if ($this->available_until && Carbon::now()->gt($this->available_until)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2) . ' ' . $this->currency;
    }
    
    /**
     * Get the billing description
     */
    public function getBillingDescriptionAttribute()
    {
        if ($this->is_lifetime) {
            return 'One-time payment (Lifetime access)';
        }
        
        if ($this->billing_interval == 'month') {
            return 'Monthly subscription';
        } elseif ($this->billing_interval == 'year') {
            return 'Annual subscription';
        }
        
        return 'Subscription';
    }
    
    /**
     * Get users subscribed to this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
    
    /**
     * Format availability date range
     */
    public function getAvailabilityRangeAttribute()
    {
        if (!$this->available_until) {
            return 'Always available';
        }
        
        return 'Available until ' . $this->available_until->format('F j, Y');
    }
}