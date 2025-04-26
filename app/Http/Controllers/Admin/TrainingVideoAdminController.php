<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrainingVideoAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of the training videos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $videos = TrainingVideo::orderBy('order_sequence')->get();
        return view('admin.videos.index', compact('videos'));
    }

    /**
     * Show the form for creating a new training video.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.videos.create');
    }

    /**
     * Store a newly created training video in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_path' => 'required|url',
            'thumbnail_path' => 'nullable|url',
            'duration' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'order_sequence' => 'required|integer|min:0',
            'is_published' => 'sometimes',
        ]);

        try {
            // Create training video
            $video = TrainingVideo::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'video_path' => $validated['video_path'],
                'thumbnail_path' => $validated['thumbnail_path'],
                'duration' => $validated['duration'],
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'order_sequence' => $validated['order_sequence'],
                'is_published' => $request->has('is_published'),
            ]);

            Log::info('Training video created', [
                'id' => $video->id,
                'title' => $video->title
            ]);

            return redirect()->route('admin.videos.index')
                ->with('success', "Training video '{$video->title}' created successfully.");

        } catch (\Exception $e) {
            Log::error('Error creating training video', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create training video: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified training video.
     *
     * @param  \App\Models\TrainingVideo  $video
     * @return \Illuminate\Http\Response
     */
    public function show(TrainingVideo $video)
    {
        $purchasedCount = $video->users()->count();
        $viewCount = $video->users()->sum('watch_count');
        
        return view('admin.videos.show', compact('video', 'purchasedCount', 'viewCount'));
    }

    /**
     * Show the form for editing the specified training video.
     *
     * @param  \App\Models\TrainingVideo  $video
     * @return \Illuminate\Http\Response
     */
    public function edit(TrainingVideo $video)
    {
        return view('admin.videos.edit', compact('video'));
    }

    /**
     * Update the specified training video in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TrainingVideo  $video
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TrainingVideo $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_path' => 'required|url',
            'thumbnail_path' => 'nullable|url',
            'duration' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'order_sequence' => 'required|integer|min:0',
            'is_published' => 'sometimes',
        ]);

        try {
            // Update training video
            $video->update([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'video_path' => $validated['video_path'],
                'thumbnail_path' => $validated['thumbnail_path'],
                'duration' => $validated['duration'],
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'order_sequence' => $validated['order_sequence'],
                'is_published' => $request->has('is_published'),
            ]);

            Log::info('Training video updated', [
                'id' => $video->id,
                'title' => $video->title
            ]);

            return redirect()->route('admin.videos.index')
                ->with('success', "Training video '{$video->title}' updated successfully.");

        } catch (\Exception $e) {
            Log::error('Error updating training video', [
                'error' => $e->getMessage(),
                'video_id' => $video->id
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update training video: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified training video from storage.
     *
     * @param  \App\Models\TrainingVideo  $video
     * @return \Illuminate\Http\Response
     */
    public function destroy(TrainingVideo $video)
    {
        // Check if the video has any users (purchased)
        $hasPurchases = $video->users()->exists();
        
        if ($hasPurchases) {
            return redirect()->route('admin.videos.index')
                ->with('error', "Cannot delete video '{$video->title}' because it has been purchased by users.");
        }
        
        try {
            $videoTitle = $video->title;
            $video->delete();
            
            Log::info('Training video deleted', [
                'id' => $video->id,
                'title' => $videoTitle
            ]);
            
            return redirect()->route('admin.videos.index')
                ->with('success', "Training video '{$videoTitle}' deleted successfully.");
                
        } catch (\Exception $e) {
            Log::error('Error deleting training video', [
                'error' => $e->getMessage(),
                'video_id' => $video->id
            ]);
            
            return redirect()->route('admin.videos.index')
                ->with('error', 'Failed to delete training video: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle published status for the video
     *
     * @param  \App\Models\TrainingVideo  $video
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(TrainingVideo $video)
    {
        $video->is_published = !$video->is_published;
        $video->save();
        
        $statusText = $video->is_published ? 'published' : 'unpublished';
        
        return redirect()->route('admin.videos.index')
            ->with('success', "Training video '{$video->title}' {$statusText} successfully.");
    }
}