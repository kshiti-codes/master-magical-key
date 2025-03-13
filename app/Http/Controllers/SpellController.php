<?php

namespace App\Http\Controllers;

use App\Models\Spell;
use App\Models\Chapter;
use App\Models\UserSpell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class SpellController extends Controller
{
    /**
     * Display a listing of the spells.
     */
    public function index()
    {
        $spells = Spell::where('is_published', true)
            ->orderBy('order')
            ->get();
        
        $cartItemCount = 0;
        $spellsInCart = [];
        $userSpells = [];
        
        if (Auth::check()) {
            $cart = Auth::user()->getCart();
            $cartItemCount = $cart->itemCount;
            
            // Get IDs of spells already in cart
            $spellsInCart = $cart->items->where('item_type', 'spell')->pluck('spell_id')->toArray();
            
            // Get IDs of spells the user already owns
            $userSpells = Auth::user()->spells()->pluck('spells.id')->toArray();
        }
        
        return view('spells.index', compact('spells', 'cartItemCount', 'spellsInCart', 'userSpells'));
    }

    /**
     * Display the specified spell.
     */
    public function show(Spell $spell)
    {
        // Check related chapters for context
        $relatedChapters = $spell->chapters;
        
        // Check if the user has access to this spell through chapter ownership
        $hasAccessThroughChapter = $spell->isAvailableThroughChapter();
        
        // Check if user directly owns this spell
        $isOwned = $spell->isOwned();
        
        return view('spells.show', compact('spell', 'relatedChapters', 'hasAccessThroughChapter', 'isOwned'));
    }
    
    /**
     * Show the download page for a spell.
     */
    public function download(Spell $spell)
    {
        // Check if user can access this spell
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        
        // Check if user has direct access
        $hasDirectAccess = $user->spells()->where('spell_id', $spell->id)->exists();
        
        // Check if user has access through chapter
        $hasChapterAccess = false;
        if (!$hasDirectAccess) {
            // Get all chapters that include this spell for free
            $chapterIds = $spell->chapters()
                ->where('is_free_with_chapter', true)
                ->pluck('chapters.id');
            
            $hasChapterAccess = $user->chapters()
                ->whereIn('chapter_id', $chapterIds)
                ->exists();
        }
        
        if (!$hasDirectAccess && !$hasChapterAccess) {
            return redirect()->route('spells.show', $spell)
                ->with('error', 'You do not have access to download this spell.');
        }
        
        // Record download if user has direct access
        if ($hasDirectAccess) {
            $userSpell = UserSpell::where('user_id', Auth::id())
                ->where('spell_id', $spell->id)
                ->first();

            if ($userSpell) {
                $userSpell->recordDownload();
            }
        }
        
        // Return PDF as download
        return Response::make($spell->pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->sanitizeFilename($spell->title) . '.pdf"'
        ]);
    }
    
    /**
     * Display spell inline (for preview).
     */
    public function preview(Spell $spell)
    {
        // Only allow access to published spells
        if (!$spell->is_published) {
            abort(404);
        }
        
        // Return PDF for display in browser
        return Response::make($spell->pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $this->sanitizeFilename($spell->title) . '.pdf"'
        ]);
    }
    
    /**
     * Sanitize a filename to be safe for downloads.
     */
    private function sanitizeFilename($filename)
    {
        // Remove any potentially dangerous characters
        $filename = preg_replace('/[^\w\s.-]/', '', $filename);
        
        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);
        
        // Ensure the filename isn't too long
        if (strlen($filename) > 50) {
            $filename = substr($filename, 0, 50);
        }
        
        return $filename;
    }
}