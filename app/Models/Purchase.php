<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'invoice_number',
        'subtotal',
        'tax',
        'tax_rate',
        'invoice_data',
        'emailed_at'
    ];

    protected $casts = [
        'emailed_at' => 'datetime',
    ];

    // Exclude binary data from JSON serialization
    protected $hidden = [
        'invoice_data'
    ];

    // Override the attribute setter for invoice_data
    public function setInvoiceDataAttribute($value)
    {
        $this->attributes['invoice_data'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this purchase.
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get the chapters purchased.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Get the subscription plan purchased.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get the training video.
     */
    public function trainingVideo()
    {
        return $this->belongsTo(TrainingVideo::class);
    }

    /**
     * Get the booked session.
     */
    public function bookedSession()
    {
        return $this->belongsTo(BookedSession::class);
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 1;
        
        if ($lastInvoice) {
            // Extract the number portion and increment
            $parts = explode('-', $lastInvoice->invoice_number);
            $lastNumber = (int) substr(end($parts), -4);
            $nextNumber = $lastNumber + 1;
        }
        
        // Format: INV-YYYYMM-0001
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $nextNumber);
    }
    
    /**
     * Get the invoice PDF data.
     */
    public function getInvoicePdf()
    {
        return $this->invoice_data;
    }
}