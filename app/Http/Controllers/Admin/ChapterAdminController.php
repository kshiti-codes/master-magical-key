<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Spell;
use App\Services\ChapterPaginationService;
use App\Helpers\ContentFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChapterAdminController extends Controller
{
    protected $paginationService;
    
    public function __construct(ChapterPaginationService $paginationService)
    {
        $this->middleware(['auth', 'admin']);
        $this->paginationService = $paginationService;
    }

    /**
     * Display a listing of chapters.
     */
    public function index()
    {
        $chapters = Chapter::orderBy('order')->get();
        return view('admin.chapters.index', compact('chapters'));
    }

    /**
     * Show the form for creating a new chapter.
     */
    public function create()
    {
        $spells = Spell::orderBy('title')->get();
        return view('admin.chapters.create', compact('spells'));
    }

    /**
     * Store a newly created chapter in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'preview_content' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'order' => 'required|integer|min:1',
            'is_published' => 'boolean',
            'is_free' => 'boolean',
            'free_spells' => 'nullable|array',
            'free_spells.*' => 'exists:spells,id',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:102400', // Max 100MB
        ]);

        // Begin a database transaction
        try {
            \DB::beginTransaction();
            
            // Log creation attempt
            Log::info('Creating new chapter', [
                'title' => $validated['title'],
                'price' => $validated['price'],
                'has_audio' => $request->hasFile('audio_file')
            ]);

            // Create the chapter
            $chapter = Chapter::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'content' => $validated['content'],
                'preview_content' => $validated['preview_content'] ?? null,
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'order' => $validated['order'],
                'is_published' => $request->has('is_published')? true : false,
                'is_free' => $request->has('is_free')?  true : false,
                'has_audio' => false, // Default, will be updated if audio upload succeeds
            ]);

            // Handle audio file upload if provided
            $audioUploaded = false;
            if ($request->hasFile('audio_file')) {
                $audioFile = $request->file('audio_file');
                
                // Check if the file is valid
                if ($audioFile->isValid()) {
                    $audioUploaded = $this->handleAudioUpload($chapter, $audioFile);
                    
                    if (!$audioUploaded) {
                        Log::warning('Audio upload failed, but continuing with chapter creation', [
                            'chapter_id' => $chapter->id
                        ]);
                    }
                } else {
                    Log::warning('Invalid audio file uploaded', [
                        'original_name' => $audioFile->getClientOriginalName(),
                        'error' => $audioFile->getError()
                    ]);
                }
            }

            // Attach free spells to this chapter if any
            if (!empty($validated['free_spells'])) {
                foreach ($validated['free_spells'] as $spellId) {
                    $chapter->spells()->attach($spellId, ['is_free_with_chapter' => true]);
                }
                
                Log::info('Attached free spells to chapter', [
                    'chapter_id' => $chapter->id,
                    'spell_count' => count($validated['free_spells'])
                ]);
            }

            // Paginate the content
            $pageCount = $this->paginationService->paginateChapter($chapter);
            
            Log::info('Chapter pagination complete', [
                'chapter_id' => $chapter->id,
                'page_count' => $pageCount
            ]);

            // Commit the transaction
            \DB::commit();

            // Build success message
            $successMessage = "Chapter '{$chapter->title}' created successfully and paginated into {$pageCount} pages.";
            if ($request->hasFile('audio_file') && !$audioUploaded) {
                $successMessage .= " However, there was an issue uploading the audio file. Please try uploading it again.";
            }

            return redirect()->route('admin.chapters.index')->with('success', $successMessage);
            
        } catch (\Exception $e) {
            // If something goes wrong, rollback the transaction
            \DB::rollBack();
            
            Log::error('Chapter creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create chapter: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified chapter.
     */
    public function edit(Chapter $chapter)
    {
        $spells = Spell::orderBy('title')->get();
        $freeSpellIds = $chapter->freeSpells()->pluck('spell_id')->toArray();
        
        return view('admin.chapters.edit', compact('chapter', 'spells', 'freeSpellIds'));
    }

    /**
     * Update the specified chapter in storage.
     */
    public function update(Request $request, Chapter $chapter)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|string',
            'preview_content' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'order' => 'required|integer|min:1',
            'is_published' => 'boolean',
            'is_free' => 'boolean',
            'free_spells' => 'nullable|array',
            'free_spells.*' => 'exists:spells,id',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg|max:102400',
        ]);

        // Log creation attempt
        Log::info('Updating new chapter', [
            'title' => $validated['title'],
            'price' => $validated['price'],
            'has_audio' => $request->hasFile('audio_file'),
            'is_published' => $request->has('is_published')? true : false,
            'is_free' => $request->has('is_free')? true : false,
        ]);

        // Update the chapter
        $chapter->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'content' => $validated['content'],
            'preview_content' => $validated['preview_content'] ?? null,
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'order' => $validated['order'],
            'is_published' => $request->has('is_published')? true : false,
            'is_free' => $request->has('is_free')? true : false,
        ]);

        // Handle audio file upload if provided
        if ($request->hasFile('audio_file')) {
            // If there's an existing audio file, remove it first
            if ($chapter->has_audio && $chapter->audio_path) {
                $oldFilePath = public_path($chapter->audio_path);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
            
            // Upload new audio file
            $this->handleAudioUpload($chapter, $request->file('audio_file'));
        }

        // Update free spells for this chapter
        $freeSpells = $validated['free_spells'] ?? [];
        
        // Remove all existing relationships first
        $chapter->spells()->detach();
        
        // Then add the new free spells
        foreach ($freeSpells as $spellId) {
            $chapter->spells()->attach($spellId, ['is_free_with_chapter' => true]);
        }

        // Re-paginate the content
        $pageCount = $this->paginationService->paginateChapter($chapter);

        // Check if images in content need to be updated
        $this->updateContentImages($chapter);

        return redirect()->route('admin.chapters.index')
            ->with('success', "Chapter '{$chapter->title}' updated successfully and paginated into {$pageCount} pages.");
    }

    /**
     * Update image references in chapter content
     * This handles cases where storage links might have changed
     * 
     * @param Chapter $chapter
     * @return void
     */
    private function updateContentImages(Chapter $chapter)
    {
        $content = $chapter->content;
        
        // Find all image references in the content
        preg_match_all('/!\[(.*?)\]\((.*?)\)/s', $content, $matches, PREG_SET_ORDER);
        
        $contentUpdated = false;
        
        foreach ($matches as $match) {
            $altText = $match[1];
            $imageUrl = $match[2];
            
            // Check if parameters are included
            $urlParts = explode('|', $imageUrl);
            $url = $urlParts[0];
            $params = array_slice($urlParts, 1);
            
           if (strpos($url, 'chapter-images/') === 0) {
                // Extract the filename
                $filename = basename($url);
                
                // Create the new URL directly from public path
                $newUrl = asset('chapter-images/' . $filename);
                
                // Rebuild the image reference with parameters if any
                $newImageRef = "![{$altText}]({$newUrl}";
                if (count($params) > 0) {
                    $newImageRef .= '|' . implode('|', $params);
                }
                $newImageRef .= ')';
                
                // Replace the old reference with the new one
                $content = str_replace($match[0], $newImageRef, $content);
                $contentUpdated = true;
            }
        }
        
        // If content was updated, save it back to the chapter
        if ($contentUpdated) {
            $chapter->update(['content' => $content]);
        }
    }

    /**
     * Remove the specified chapter from storage.
     */
    public function destroy(Chapter $chapter)
    {
        // First check if the chapter has any purchases
        $hasPurchases = $chapter->purchaseItems()->exists();
        $userChapters = $chapter->users()->exists();
        
        if ($hasPurchases && $userChapters) {
            return redirect()->route('admin.chapters.index')
                ->with('error', "Cannot delete chapter '{$chapter->title}' because it has been purchased by users.");
        }
        
        // If no purchases, it's safe to delete
        $chapterTitle = $chapter->title;
        
        // Remove associated audio file if any
        if ($chapter->audio_path) {
            Storage::delete($chapter->audio_path);
        }
        
        // Delete all chapter pages
        $chapter->pages()->delete();
        
        // Remove spell associations
        $chapter->spells()->detach();
        
        // Delete the chapter
        $chapter->delete();
        
        return redirect()->route('admin.chapters.index')
            ->with('success', "Chapter '{$chapterTitle}' deleted successfully.");
    }

    /**
     * Preview the chapter with formatting applied.
     */
    public function preview(Request $request)
    {
        $content = $request->input('content');
        $formattedContent = ContentFormatter::format($content);
        
        return response()->json(['formatted_content' => $formattedContent]);
    }

    /**
     * Re-paginate the specified chapter.
     */
    public function paginate(Chapter $chapter)
    {
        $pageCount = $this->paginationService->paginateChapter($chapter);
        
        return redirect()->route('admin.chapters.edit', $chapter)
            ->with('success', "Chapter '{$chapter->title}' has been paginated into {$pageCount} pages.");
    }

    /**
     * Upload and process an image for a chapter.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // Max 5MB
        ]);
        
        $file = $request->file('image');
        $fileName = time() . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        
        // Create directory if it doesn't exist
        $directory = public_path('chapter-images');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Store directly in public/chapter-images
        $relativePath = 'chapter-images/' . $fileName;
        $file->move($directory, $fileName);
        
        // Return the url and markdown code
        $url = asset($relativePath);
        $markdown = "![Image Description]({$url})";
        
        return response()->json([
            'url' => $url,
            'markdown' => $markdown
        ]);
    }

    /**
     * Handle audio file upload for a chapter.
     *
     * @param Chapter $chapter
     * @param \Illuminate\Http\UploadedFile $audioFile
     * @return void
     */
    private function handleAudioUpload(Chapter $chapter, $audioFile)
    {
        // Define storage path - store directly in public/chapter-audio folder
        $fileName = time() . '_' . Str::slug($chapter->title) . '.' . $audioFile->getClientOriginalExtension();
        $relativePath = 'chapter-audio/' . $fileName;
        $fullPath = public_path($relativePath);
        
        // Make sure directory exists
        $directory = public_path('chapter-audio');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Move the file to the public directory
        $audioFile->move($directory, $fileName);
        
        // Get file metadata
        $format = $audioFile->getClientOriginalExtension();
        $duration = $this->getAudioDuration($fullPath);
        
        // Update chapter with audio info - store the relative path
        $chapter->update([
            'audio_path' => $relativePath,
            'audio_format' => $format,
            'audio_duration' => $duration,
            'has_audio' => true
        ]);
    }

    /**
     * Get audio duration in seconds.
     * This is a simplified method - in a production environment, 
     * you might want to use a library like getID3.
     *
     * @param string $filePath
     * @return int
     */
    private function getAudioDuration($filePath)
    {
        // For a more accurate duration detection, you would use getID3 or similar libraries
        // This is a simplified placeholder implementation
        try {
            // If ffmpeg is available on the server
            if (function_exists('exec')) {
                $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filePath);
                $duration = exec($command);
                if (is_numeric($duration)) {
                    return (int)$duration;
                }
            }
        } catch (\Exception $e) {
            // Log the error but continue with default duration
            \Log::error('Error getting audio duration: ' . $e->getMessage());
        }
        
        // Default duration as fallback (5 minutes)
        return 300;
    }
    
    /**
     * Generate sample content with formatting examples.
     */
    public function generateSampleContent()
    {
        $sampleContent = <<<EOT
        # Chapter Title: The Mystical Beginnings

        This is an introduction paragraph that sets the scene for this chapter. You can use **bold text** for emphasis or *italic text* for subtle highlights. The chapter should begin with a compelling hook that draws readers into the mystical world.

        ## The First Gateway

        > Every journey into the mystical begins with a single step through the gateway of consciousness.

        This section introduces the first key concept of the chapter. Here you can explain fundamental principles or share an enlightening story that illustrates the point.

        ### Key Insights

        * The universe speaks in symbols and patterns
        * Consciousness is the foundation of all magic
        * Your intention shapes the outcome of any spell
        * There are no coincidences in the mystical realm

        ## Working with Energy

        When working with magical energy, remember that it follows the path of least resistance, much like water. Here's a simple practice to begin sensing energy:

        1. Find a quiet space where you won't be disturbed
        2. Sit comfortably and begin to focus on your breath
        3. Rub your palms together vigorously for 30 seconds
        4. Slowly separate your hands about 2 inches
        5. Feel the subtle energy pulsing between your palms

        ![Energy Meditation](/chapter-images/sample-meditation.jpg|width=500|height=300|class=center)

        ## Advanced Techniques

        For those ready to delve deeper, the following techniques can amplify your mystical connection:

        ```
        Daily Practice Template:
        - Morning intention setting (5 minutes)
        - Energy circulation visualization (10 minutes)
        - Symbol meditation with your chosen arcana (15 minutes)
        - Evening reflection and gratitude (5 minutes)
        ```

        ### Common Challenges

        Many practitioners encounter blocks or resistance when they first begin this work. This is not only normal but actually a positive sign that energy is starting to move and transform. The discomfort you feel is old patterns dissolving.

        ![Spiritual Transformation](/chapter-images/transformation.jpg|width=400|class=right)

        When you encounter resistance, remember to:

        1. Breathe deeply and center yourself
        2. Acknowledge the resistance without judgment
        3. Invite it to reveal its message to you
        4. Release it with gratitude for its teaching

        ## Connecting to Universal Wisdom

        The final section of this chapter should summarize the key takeaways and prepare the reader for what's coming in the next chapter. You might want to include a simple practice they can use immediately.

        Remember that each chapter should build upon the previous ones, creating a cohesive journey for the reader that progressively deepens their understanding and abilities.

        > "The key to the universe is not found in seeking new landscapes, but in having new eyes." - Marcel Proust
        EOT;

        return response()->json(['sample_content' => $sampleContent]);
    }
}