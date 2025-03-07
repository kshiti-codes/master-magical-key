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
}