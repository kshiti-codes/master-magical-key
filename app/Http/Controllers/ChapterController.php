<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterController extends Controller
{
    public function index()
    {
        $chapters = Chapter::where('is_published', true)
            ->orderBy('order')
            ->get();
        
        $cartItemCount = 0;
        $chaptersInCart = [];
        
        if (Auth::check()) {
            $cart = Auth::user()->getCart();
            $cartItemCount = $cart->itemCount;
            
            // Get IDs of chapters already in cart
            $chaptersInCart = $cart->items->pluck('chapter_id')->toArray();
        }
        
        return view('chapters.index', compact('chapters', 'cartItemCount', 'chaptersInCart'));
    }

    public function show(Chapter $chapter)
    {
        return view('chapters.show', compact('chapter'));
    }

    public function read(Chapter $chapter)
    {
        // Check if user has purchased this chapter
        if (!$chapter->isPurchased()) {
            return redirect()->route('home');
        }
        
        // Redirect to home page with chapter parameter
        return redirect()->route('home', ['open_chapter' => $chapter->id]);
    }
    
    public function saveProgress(Request $request, Chapter $chapter)
    {
        // Validate request
        $request->validate([
            'page' => 'required|integer|min:1',
        ]);
        
        // Save the user's reading progress
        Auth::user()->chapters()->syncWithoutDetaching([
            $chapter->id => [
                'last_read_at' => now(),
                'last_page' => $request->page,
            ]
        ]);
        
        return response()->json(['success' => true]);
    }
}