<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'start_date',
        'end_date',
        'next_billing_date',
        'paypal_subscription_id',
        'transaction_id',
        'amount_paid'
    ];
    
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_billing_date' => 'datetime',
    ];
    
    /**
     * Get the user who owns the subscription
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the subscription plan
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
    
    /**
     * Check if the subscription is active
     */
    public function isActive()
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        // Lifetime subscriptions are always active
        if ($this->plan->is_lifetime) {
            return true;
        }
        
        // Check if the subscription has ended
        if ($this->end_date && Carbon::now()->gt($this->end_date)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if the subscription is a lifetime subscription
     */
    public function isLifetime()
    {
        return $this->plan->is_lifetime;
    }
    
    /**
     * Cancel the subscription
     */
    public function cancel()
    {
        if ($this->plan->is_lifetime) {
            // Cannot cancel lifetime subscriptions
            return false;
        }
        
        $this->status = 'canceled';
        $this->end_date = $this->next_billing_date; // Will be active until the next billing date
        $this->save();
        
        return true;
    }
    
    /**
     * Get subscription status text
     */
    public function getStatusTextAttribute()
    {
        if ($this->status === 'active') {
            if ($this->plan->is_lifetime) {
                return 'Lifetime Access';
            } else {
                return 'Active';
            }
        } elseif ($this->status === 'canceled') {
            return 'Canceled';
        } elseif ($this->status === 'expired') {
            return 'Expired';
        }
        
        return ucfirst($this->status);
    }
}