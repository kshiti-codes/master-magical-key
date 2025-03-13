<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function chapters()
    {
        return $this->belongsToMany(Chapter::class, 'user_chapters')
            ->withPivot('last_read_at', 'last_page')
            ->withTimestamps();
    }

    public function hasPurchased(Chapter $chapter)
    {
        return $this->purchases()->where('chapter_id', $chapter->id)
            ->where('status', 'completed')->exists();
    }

    /**
     * Get the user's active cart.
     */
    public function cart()
    {
        return $this->hasOne(Cart::class)->latest();
    }

    /**
     * Get or create a cart for the user.
     */
    public function getCart()
    {
        $cart = $this->cart;
        
        if (!$cart) {
            $cart = $this->cart()->create([
                'user_id' => $this->id
            ]);
        }
        
        return $cart;
    }

    /**
     * Get the spells owned by the user.
     */
    public function spells()
    {
        return $this->belongsToMany(Spell::class, 'user_spells')
            ->withPivot('purchased_at', 'last_downloaded_at', 'download_count')
            ->withTimestamps();
    }

    /**
     * Check if user has access to a specific spell
     */
    public function hasSpell(Spell $spell)
    {
        // Direct purchase check
        $directPurchase = $this->spells()->where('spell_id', $spell->id)->exists();
        
        if ($directPurchase) {
            return true;
        }
        
        // Check if user has a chapter that includes this spell for free
        $chapterIds = $spell->chapters()
            ->where('is_free_with_chapter', true)
            ->pluck('chapters.id');
        
        return $this->chapters()->whereIn('chapter_id', $chapterIds)->exists();
    }

    /**
     * Grant access to a spell for this user
     */
    public function grantSpellAccess(Spell $spell)
    {
        // Only add if not already owned
        if (!$this->hasSpell($spell)) {
            $this->spells()->attach($spell->id, [
                'purchased_at' => now()
            ]);
        }
        
        return $this;
    }
}