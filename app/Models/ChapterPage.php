<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'page_number',
        'content',
        'formatted_content'
    ];

    /**
     * Get the chapter that owns the page.
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}