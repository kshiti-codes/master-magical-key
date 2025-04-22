<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailCampaignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_campaign_id',
        'user_id',
        'email',
        'sent',
        'sent_at',
    ];

    protected $casts = [
        'sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the campaign this log belongs to
     */
    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    /**
     * Get the user this log belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}