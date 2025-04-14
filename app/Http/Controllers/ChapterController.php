<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Services\ChapterPaginationService;
use App\Http\Controllers\SubscriptionController;
use App\Models\ChapterPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterController extends Controller
{
    protected $paginationService;
    
    public function __construct(ChapterPaginationService $paginationService)
    {
        $this->paginationService = $paginationService;
    }

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

        // Get subscription plans for the modal
        $subscriptionPlans = SubscriptionController::getPlansForModal();
        
        return view('chapters.index', compact('chapters', 'cartItemCount', 'chaptersInCart', 'subscriptionPlans'));
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

    /**
     * Update chapter content and paginate it
     */
    public function update(Request $request, Chapter $chapter)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            // Other validation rules...
        ]);
        
        // Update the chapter
        $chapter->update($request->all());
        
        // Paginate the content
        $pageCount = $this->paginationService->paginateChapter($chapter);
        return true;
        
        // return redirect()->route('admin.chapters.edit', $chapter)
        //     ->with('success', "Chapter updated and paginated into {$pageCount} pages.");
    }

    public function getPages(Chapter $chapter, Request $request)
    {
        // Check if the chapter is accessible
        if (!$chapter->isPurchased()) {
            return response()->json([
                'error' => 'You do not have access to this chapter.'
            ], 403);
        }
        
        // Get requested page numbers
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 5);
        
        // Check if pages exist for this chapter
        $pagesCount = $chapter->pages()->count();
        
        // If no pages exist, paginate the chapter now
        if ($pagesCount === 0) {
            $paginationService = app(ChapterPaginationService::class);
            $paginationService->paginateChapter($chapter);
        }
        
        // Get the pages
        $pages = $chapter->pages()
            ->where('page_number', '>=', $page)
            ->where('page_number', '<', $page + $perPage)
            ->orderBy('page_number')
            ->get();
        
        // Get total pages count
        $totalPages = $chapter->pages()->count();
        
        // Get next chapter information
        $nextChapter = Chapter::where('order', '>', $chapter->order)
                            ->where('is_published', true)
                            ->orderBy('order')
                            ->first();
        
        $nextChapterInfo = null;
        if ($nextChapter) {
            $nextChapterInfo = [
                'id' => $nextChapter->id,
                'title' => $nextChapter->title,
                'description' => $nextChapter->description,
                'price' => $nextChapter->price,
                'currency' => $nextChapter->currency,
                'is_free' => $nextChapter->isFree(),
                'is_purchased' => $nextChapter->isPurchased(),
                'purchase_url' => route('chapters.show', $nextChapter->id)
            ];
        }
        
        // Format response
        $response = [
            'chapter_id' => $chapter->id,
            'title' => $chapter->title,
            'total_pages' => $totalPages,
            'current_page' => (int)$page,
            'pages' => $pages->map(function($page) {
                return [
                    'page_number' => $page->page_number,
                    'content' => $page->formatted_content
                ];
            }),
            'next_chapter' => $nextChapterInfo,
            'audio' => null
        ];
        
        // Add audio information if available
        if ($chapter->has_audio && $chapter->audio_path) {
            $response['audio'] = [
                'available' => true,
                'path' => asset($chapter->audio_path),
                'format' => $chapter->audio_format,
                'duration' => $chapter->audio_duration
            ];
        }
        
        return response()->json($response);
    }

    /**
     * Get audio for a chapter
     *
     * @param Chapter $chapter
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAudio(Chapter $chapter)
    {
        // Check if user has purchased this chapter if it's not free
        if ($chapter->price > 0 && !$chapter->isPurchased()) {
            return response()->json([
                'error' => 'Unauthorized access'
            ], 403);
        }
        
        // Check if chapter has audio
        if (!$chapter->has_audio || !$chapter->audio_path) {
            return response()->json([
                'error' => 'No audio available for this chapter'
            ], 404);
        }
        
        // Return audio information
        return response()->json([
            'chapter_id' => $chapter->id,
            'title' => $chapter->title,
            'audio' => [
                'path' => asset($chapter->audio_path),
                'format' => $chapter->audio_format,
                'duration' => $chapter->audio_duration
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Create the chapter
        $chapter = Chapter::create($request->validated());
        
        // Paginate the content
        $paginationService = new ChapterPaginationService();
        $paginationService->paginateChapter($chapter);
        
        return redirect()->route('chapters.index')
            ->with('success', 'Chapter created successfully');
    }

}