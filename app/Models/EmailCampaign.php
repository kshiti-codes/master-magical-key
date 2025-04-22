<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'content',
        'segment_conditions',
        'status',
        'sent_at',
        'total_recipients'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the campaign logs
     */
    public function logs()
    {
        return $this->hasMany(EmailCampaignLog::class);
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        switch ($this->status) {
            case 'draft':
                return '<span class="status-badge status-draft">Draft</span>';
            case 'sending':
                return '<span class="status-badge status-sending">Sending</span>';
            case 'sent':
                return '<span class="status-badge status-sent">Sent</span>';
            default:
                return '<span class="status-badge">' . ucfirst($this->status) . '</span>';
        }
    }

    /**
     * Get formatted sent date
     */
    public function getFormattedSentDateAttribute()
    {
        if (!$this->sent_at) {
            return 'Not sent yet';
        }

        return $this->sent_at->format('M d, Y h:i A');
    }

    /**
     * Get formatted segment name
     */
    public function getSegmentNameAttribute()
    {
        if (!$this->segment_conditions) {
            return 'All Users';
        }

        switch ($this->segment_conditions) {
            case 'has_purchases':
                return 'Users Who Made Purchases';
            case 'no_purchases':
                return 'Users With No Purchases';
            case 'active_subscribers':
                return 'Active Subscribers';
            case 'expired_subscribers':
                return 'Expired Subscribers';
            case 'lifetime_subscribers':
                return 'Lifetime Subscribers';
            case 'non_subscribers':
                return 'Non-Subscribers';
            case 'free_content_only':
                return 'Users With Only Free Content';
            case 'recent_signup':
                return 'Recent Signups (Last 7 Days)';
            case 'chapter_owners':
                return 'Chapter Owners';
            case 'spell_owners':
                return 'Spell Owners';
            default:
                return ucfirst(str_replace('_', ' ', $this->segment_conditions));
        }
    }
}