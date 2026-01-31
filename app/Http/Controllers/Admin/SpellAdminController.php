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
        // Set is_active default
        if (!$request->has('is_active')) {
            $request->merge(['is_active' => false]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'type' => 'required|in:digital_download,course,session,subscription,video,other',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200',
            'audio_file' => 'nullable|file|mimes:mp3,wav,m4a,ogg|max:102400',
            'popup_text' => 'nullable|string',
            'is_active' => 'boolean',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'remove_pdf' => 'boolean',
            'remove_audio' => 'boolean',
            'remove_image' => 'boolean',
        ]);

        // Update product data
        $product->update($validated);

        // Handle PDF removal/update
        if ($request->boolean('remove_pdf') && $product->pdf_file_path) {
            // Delete old PDF from SECURE storage
            $oldPdfPath = storage_path('app/' . $product->pdf_file_path);
            if (file_exists($oldPdfPath)) {
                unlink($oldPdfPath);
            }
            $product->update(['pdf_file_path' => null]);
            
        } elseif ($request->hasFile('pdf_file') && $request->file('pdf_file')->isValid()) {
            $pdfFile = $request->file('pdf_file');
            
            // Delete old PDF if exists
            if ($product->pdf_file_path) {
                $oldPdfPath = storage_path('app/' . $product->pdf_file_path);
                if (file_exists($oldPdfPath)) {
                    unlink($oldPdfPath);
                }
            }
            
            // Generate a filename
            $fileName = Str::slug($product->title) . '-' . time() . '.pdf';
            $relativePath = 'products/pdfs/' . $fileName;
            
            // SECURE STORAGE: Create directory in storage/app
            $directory = storage_path('app/products/pdfs');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Move the uploaded file to SECURE storage
            $pdfFile->move($directory, $fileName);
            
            // Update the product with the PDF path
            $product->update(['pdf_file_path' => $relativePath]);
        }

        // Handle audio removal/update
        if ($request->boolean('remove_audio') && $product->audio_file_path) {
            // Delete old audio from SECURE storage
            $oldAudioPath = storage_path('app/' . $product->audio_file_path);
            if (file_exists($oldAudioPath)) {
                unlink($oldAudioPath);
            }
            $product->update(['audio_file_path' => null]);
            
        } elseif ($request->hasFile('audio_file') && $request->file('audio_file')->isValid()) {
            $audioFile = $request->file('audio_file');
            
            // Delete old audio if exists
            if ($product->audio_file_path) {
                $oldAudioPath = storage_path('app/' . $product->audio_file_path);
                if (file_exists($oldAudioPath)) {
                    unlink($oldAudioPath);
                }
            }
            
            // Generate a filename
            $audioExtension = $audioFile->getClientOriginalExtension();
            $audioFileName = Str::slug($product->title) . '-' . time() . '.' . $audioExtension;
            $audioRelativePath = 'products/audio/' . $audioFileName;
            
            // SECURE STORAGE: Create directory in storage/app
            $audioDirectory = storage_path('app/products/audio');
            if (!file_exists($audioDirectory)) {
                mkdir($audioDirectory, 0755, true);
            }
            
            // Move the uploaded file to SECURE storage
            $audioFile->move($audioDirectory, $audioFileName);
            
            // Update the product with the audio path
            $product->update(['audio_file_path' => $audioRelativePath]);
        }

        // Handle image removal/update
        if ($request->boolean('remove_image') && $product->image) {
            // Delete old image from PUBLIC storage
            $oldImagePath = storage_path('app/public/' . $product->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
            $product->update(['image' => null]);
            
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imageFile = $request->file('image');
            
            // Delete old image if exists
            if ($product->image) {
                $oldImagePath = storage_path('app/public/' . $product->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            // Generate a filename
            $imageExtension = $imageFile->getClientOriginalExtension();
            $imageFileName = Str::slug($product->title) . '-' . time() . '.' . $imageExtension;
            $imageRelativePath = 'products/images/' . $imageFileName;
            
            // PUBLIC STORAGE: Images can be in public
            $imageDirectory = storage_path('app/public/products/images');
            if (!file_exists($imageDirectory)) {
                mkdir($imageDirectory, 0755, true);
            }
            
            // Move the uploaded file
            $imageFile->move($imageDirectory, $imageFileName);
            
            // Update the product with the image path
            $product->update(['image' => $imageRelativePath]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
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