<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptionExtension extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_subscription_id',
        'transaction_id',
        'amount_paid',
        'original_end_date',
        'new_end_date',
        'extended_at'
    ];
    
    protected $casts = [
        'original_end_date' => 'datetime',
        'new_end_date' => 'datetime',
        'extended_at' => 'datetime',
    ];
    
    /**
     * Get the subscription that was extended
     */
    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }
}