<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'is_active'
    ];
    
    /**
     * Get the bundle items.
     */
    public function items()
    {
        return $this->hasMany(BundleItem::class);
    }
    
    /**
     * Get all chapters included in this bundle.
     */
    public function chapters()
    {
        return $this->hasManyThrough(
            Chapter::class,
            BundleItem::class,
            'bundle_id',
            'id',
            'id',
            'item_id'
        )->where('bundle_items.item_type', 'chapter');
    }
    
    /**
     * Get all spells included in this bundle.
     */
    public function spells()
    {
        return $this->hasManyThrough(
            Spell::class,
            BundleItem::class,
            'bundle_id',
            'id',
            'id',
            'item_id'
        )->where('bundle_items.item_type', 'spell');
    }
    
    /**
     * Check if the bundle includes all chapters.
     */
    public function includesAllChapters()
    {
        return $this->items()->where('item_type', 'all_chapters')->exists();
    }
    
    /**
     * Check if the bundle includes all spells.
     */
    public function includesAllSpells()
    {
        return $this->items()->where('item_type', 'all_spells')->exists();
    }
}