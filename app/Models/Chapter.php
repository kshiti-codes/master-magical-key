<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content',
        'preview_content',
        'price',
        'currency',
        'order',
        'is_published'
    ];

    // Check if the chapter is purchased by the current user
    public function isPurchased()
    {
        if (!Auth::check()) {
            return false;
        }
        
        return Purchase::where('user_id', Auth::id())
            ->where('chapter_id', $this->id)
            ->where('status', 'completed')
            ->exists();
    }
    
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_chapters')
            ->withPivot('last_read_at', 'last_page')
            ->withTimestamps();
    }
}