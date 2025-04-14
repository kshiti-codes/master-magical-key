<?php

namespace App\Http\Controllers;

use App\Models\Spell;
use App\Models\Chapter;
use App\Models\UserSpell;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $user = Auth::user();
        
        if (Auth::check()) {
            $cart = Auth::user()->getCart();
            $cartItemCount = $cart->itemCount;
            
            // Get IDs of spells already in cart
            $spellsInCart = $cart->items->where('item_type', 'spell')->pluck('spell_id')->toArray();

            $hasLifetime = Auth::user()->hasLifetimeSubscription();
            $hasActiveSubscription = Auth::user()->hasActiveSubscription();
            
            if($hasLifetime || $hasActiveSubscription) {
                // If user has a lifetime subscription or an active subscription, they can access all spells
                $userSpells = Spell::pluck('id')->toArray();
            } else {
                // Otherwise, get IDs of spells the user has purchased
                $userSpells = Auth::user()->spells()->pluck('spells.id')->toArray();
            }
        }

        // Get subscription plans for the modal
        $subscriptionPlans = SubscriptionController::getPlansForModal();
        
        return view('spells.index', compact('spells', 'cartItemCount', 'spellsInCart', 'userSpells', 'user', 'subscriptionPlans'));
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
        
        // Check if pdf_path exists and the file exists
        if (empty($spell->pdf_path) || !file_exists(public_path($spell->pdf_path))) {
            return redirect()->route('spells.show', $spell)
                ->with('error', 'The spell PDF file is not available. Please contact support.');
        }
        
        // Return PDF as download - using the file path now
        return Response::download(
            public_path($spell->pdf_path),
            $this->sanitizeFilename($spell->title) . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
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
        
        // Check if pdf_path exists and the file exists
        if (empty($spell->pdf_path) || !file_exists(public_path($spell->pdf_path))) {
            return redirect()->route('spells.index')
                ->with('error', 'The spell preview is not available.');
        }
        
        // Return PDF for display in browser - using the file path now
        return Response::file(
            public_path($spell->pdf_path),
            ['Content-Type' => 'application/pdf']
        );
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
    
    /**
     * Upload a spell PDF file.
     */
    public function uploadPdf(Request $request, Spell $spell)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // max 10MB
        ]);
        
        // Check if file was uploaded properly
        if ($request->file('pdf_file')->isValid()) {
            // Generate a filename based on the spell title
            $fileName = Str::slug($spell->title) . '-' . time() . '.pdf';
            $path = 'spell-pdf/' . $fileName;
            
            // Create directory if it doesn't exist
            $directory = public_path('spell-pdf');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Move the uploaded file
            $request->file('pdf_file')->move($directory, $fileName);
            
            // Delete the old file if it exists
            if ($spell->pdf_path && file_exists(public_path($spell->pdf_path))) {
                unlink(public_path($spell->pdf_path));
            }
            
            // Update the spell record
            $spell->update(['pdf_path' => $path]);
            
            return redirect()->back()->with('success', 'Spell PDF uploaded successfully.');
        }
        
        return redirect()->back()->with('error', 'Failed to upload PDF file.');
    }
}