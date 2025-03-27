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

            // Create the spell with PDF content stored directly in the database
            $spell = Spell::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'order' => $validated['order'],
                'is_published' => $request->has('is_published') ? true : false,
                'pdf_path' => null,
            ]);

            // Handle PDF file upload
            if ($request->hasFile('pdf_file') && $request->file('pdf_file')->isValid()) {
                $pdfFile = $request->file('pdf_file');
                
                // Generate a filename
                $fileName = Str::slug($spell->title) . '-' . time() . '.' . $pdfFile->getClientOriginalExtension();
                $path = 'spell-pdf/' . $fileName;
                
                // Create directory if it doesn't exist
                $directory = public_path('spell-pdf');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Move the uploaded file
                $pdfFile->move($directory, $fileName);
                
                // Update the spell with the PDF path
                $spell->update(['pdf_path' => $path]);
            }

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
            
            // Update the spell
            $spell->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'order' => $validated['order'],
                'is_published' => $request->has('is_published') ? true : false,
                // pdf_path is updated separately if a new file is uploaded
            ]);

            // Handle PDF file upload if provided
            if ($request->hasFile('pdf_file') && $request->file('pdf_file')->isValid()) {
                $pdfFile = $request->file('pdf_file');
                
                // Generate a filename
                $fileName = Str::slug($spell->title) . '-' . time() . '.' . $pdfFile->getClientOriginalExtension();
                $path = 'spell-pdf/' . $fileName;
                
                // Create directory if it doesn't exist
                $directory = public_path('spell-pdf');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Delete old file if it exists
                if ($spell->pdf_path && file_exists(public_path($spell->pdf_path))) {
                    unlink(public_path($spell->pdf_path));
                }
                
                // Move the uploaded file
                $pdfFile->move($directory, $fileName);
                
                // Update the spell with the new PDF path
                $spell->update(['pdf_path' => $path]);
            }
            
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
        
        try {
            // Delete PDF file if it exists
            if ($spell->pdf_path && file_exists(public_path($spell->pdf_path))) {
                unlink(public_path($spell->pdf_path));
            }
            
            // Remove chapter associations
            $spell->chapters()->detach();
            
            // Delete the spell
            $spell->delete();
            
            return redirect()->route('admin.spells.index')
                ->with('success', "Spell '{$spell->title}' deleted successfully.");
                
        } catch (\Exception $e) {
            Log::error('Spell deletion failed', [
                'error' => $e->getMessage(),
                'spell_id' => $spell->id
            ]);
            
            return redirect()->route('admin.spells.index')
                ->with('error', 'Failed to delete spell: ' . $e->getMessage());
        }
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