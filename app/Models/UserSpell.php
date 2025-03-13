<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSpell extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'spell_id',
        'purchased_at',
        'last_downloaded_at',
        'download_count'
    ];
    
    protected $casts = [
        'purchased_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
    ];
    
    /**
     * Get the user that owns this spell.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the spell.
     */
    public function spell()
    {
        return $this->belongsTo(Spell::class);
    }
    
    /**
     * Record a download of this spell.
     */
    public function recordDownload()
    {
        $this->download_count++;
        $this->last_downloaded_at = now();
        $this->save();
        
        return $this;
    }
}