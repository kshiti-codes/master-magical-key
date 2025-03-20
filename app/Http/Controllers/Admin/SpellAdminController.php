<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Spell;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SpellAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of spells.
     */
    public function index()
    {
        $spells = Spell::orderBy('order')->get();
        return view('admin.spells.index', compact('spells'));
    }

    /**
     * Show the form for creating a new spell.
     */
    public function create()
    {
        $chapters = Chapter::orderBy('order')->get();
        return view('admin.spells.create', compact('chapters'));
    }

    /**
     * Store a newly created spell in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // Max 10MB PDF
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'order' => 'required|integer|min:1',
            'is_published' => 'boolean',
            'related_chapters' => 'nullable|array',
            'related_chapters.*' => 'exists:chapters,id',
            'free_with_chapters' => 'nullable|array',
            'free_with_chapters.*' => 'exists:chapters,id',
        ]);

        try {
            \DB::beginTransaction();
            
            // Log creation attempt
            Log::info('Creating new spell', [
                'title' => $validated['title'],
                'price' => $validated['price']
            ]);

            // Handle PDF upload
            $pdfFile = $request->file('pdf_file');
            $pdfContent = file_get_contents($pdfFile->getRealPath());

            // Create the spell with PDF content stored directly in the database
            $spell = Spell::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'pdf' => $pdfContent,
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'order' => $validated['order'],
                'is_published' => $request->has('is_published') ? true : false,
            ]);

            // Process chapter relationships
            if (!empty($validated['related_chapters'])) {
                foreach ($validated['related_chapters'] as $chapterId) {
                    // Check if this chapter should provide the spell for free
                    $isFree = in_array($chapterId, $validated['free_with_chapters'] ?? []);
                    
                    // Attach the chapter with the appropriate flag
                    $spell->chapters()->attach($chapterId, ['is_free_with_chapter' => $isFree]);
                }
                
                Log::info('Attached chapters to spell', [
                    'spell_id' => $spell->id,
                    'chapter_count' => count($validated['related_chapters'])
                ]);
            }

            \DB::commit();

            return redirect()->route('admin.spells.index')
                ->with('success', "Spell '{$spell->title}' created successfully.");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            Log::error('Spell creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create spell: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified spell.
     */
    public function edit(Spell $spell)
    {
        $chapters = Chapter::orderBy('order')->get();
        
        // Get related chapter IDs
        $relatedChapterIds = $spell->chapters()->pluck('chapters.id')->toArray();
        
        // Get chapters that provide this spell for free
        $freeChapterIds = $spell->chapters()
            ->wherePivot('is_free_with_chapter', true)
            ->pluck('chapters.id')
            ->toArray();
        
        return view('admin.spells.edit', compact('spell', 'chapters', 'relatedChapterIds', 'freeChapterIds'));
    }

    /**
     * Update the specified spell in storage.
     */
    public function update(Request $request, Spell $spell)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // Max 10MB PDF
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'order' => 'required|integer|min:1',
            'is_published' => 'boolean',
            'related_chapters' => 'nullable|array',
            'related_chapters.*' => 'exists:chapters,id',
            'free_with_chapters' => 'nullable|array',
            'free_with_chapters.*' => 'exists:chapters,id',
        ]);

        try {
            \DB::beginTransaction();
            
            // Update basic spell information
            $spell->title = $validated['title'];
            $spell->description = $validated['description'];
            $spell->price = $validated['price'];
            $spell->currency = $validated['currency'];
            $spell->order = $validated['order'];
            $spell->is_published = $request->has('is_published') ? true : false;
            
            // Handle PDF upload if a new file is provided
            if ($request->hasFile('pdf_file')) {
                $pdfFile = $request->file('pdf_file');
                $pdfContent = file_get_contents($pdfFile->getRealPath());
                $spell->pdf = $pdfContent;
                
                Log::info('Updated PDF for spell', [
                    'spell_id' => $spell->id
                ]);
            }
            
            $spell->save();
            
            // Update chapter relationships
            $spell->chapters()->detach(); // Remove all existing relationships
            
            if (!empty($validated['related_chapters'])) {
                foreach ($validated['related_chapters'] as $chapterId) {
                    // Check if this chapter should provide the spell for free
                    $isFree = in_array($chapterId, $validated['free_with_chapters'] ?? []);
                    
                    // Attach the chapter with the appropriate flag
                    $spell->chapters()->attach($chapterId, ['is_free_with_chapter' => $isFree]);
                }
                
                Log::info('Updated chapter relationships for spell', [
                    'spell_id' => $spell->id,
                    'chapter_count' => count($validated['related_chapters'])
                ]);
            }
            
            \DB::commit();
            
            return redirect()->route('admin.spells.index')
                ->with('success', "Spell '{$spell->title}' updated successfully.");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            
            Log::error('Spell update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update spell: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified spell from storage.
     */
    public function destroy(Spell $spell)
    {
        // First check if the spell has any purchases
        $hasPurchases = $spell->purchaseItems()->exists();
        
        if ($hasPurchases) {
            return redirect()->route('admin.spells.index')
                ->with('error', "Cannot delete spell '{$spell->title}' because it has been purchased by users.");
        }
        
        // If no purchases, it's safe to delete
        $spellTitle = $spell->title;
        
        // Remove chapter associations
        $spell->chapters()->detach();
        
        // Delete the spell
        $spell->delete();
        
        return redirect()->route('admin.spells.index')
            ->with('success', "Spell '{$spellTitle}' deleted successfully.");
    }

    /**
     * Preview a spell PDF
     */
    public function preview(Spell $spell)
    {
        if (!$spell->pdf) {
            return redirect()->back()->with('error', 'No PDF available for this spell.');
        }
        
        return response($spell->pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . Str::slug($spell->title) . '.pdf"');
    }
}