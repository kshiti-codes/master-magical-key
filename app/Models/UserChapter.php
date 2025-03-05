<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChapter extends Model
{
    use HasFactory;

    protected $table = 'user_chapters';

    protected $fillable = [
        'user_id',
        'chapter_id',
        'last_read_at',
        'last_page'
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];
}