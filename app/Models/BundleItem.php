<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundleItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'bundle_id',
        'item_type',
        'item_id'
    ];
    
    /**
     * Get the bundle this item belongs to.
     */
    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }
    
    /**
     * Get the related chapter if this is a chapter item.
     */
    public function chapter()
    {
        if ($this->item_type === 'chapter') {
            return $this->belongsTo(Chapter::class, 'item_id');
        }
        
        return null;
    }
    
    /**
     * Get the related spell if this is a spell item.
     */
    public function spell()
    {
        if ($this->item_type === 'spell') {
            return $this->belongsTo(Spell::class, 'item_id');
        }
        
        return null;
    }
}