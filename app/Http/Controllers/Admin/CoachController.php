<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CoachController extends Controller
{
    /**
     * Display a listing of coaches.
     */
    public function index()
    {
        $coaches = Coach::orderBy('name')->get();
        return view('admin.coaches.index', compact('coaches'));
    }

    /**
     * Show the form for creating a new coach.
     */
    public function create()
    {
        return view('admin.coaches.create');
    }

    /**
     * Store a newly created coach.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:coaches,email',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|image|max:2048', // Max 2MB
            'is_active' => 'sometimes|boolean'
        ]);
        
        // Process the profile image if uploaded
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('coaches', 'public');
            $validated['profile_image'] = 'storage/' . $imagePath;
        }
        
        // Set default active status if not provided
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        // Create the coach
        Coach::create($validated);
        
        return redirect()->route('admin.coaches.index')
            ->with('success', 'Coach created successfully');
    }

    /**
     * Display the specified coach.
     */
    public function show(Coach $coach)
    {
        $sessionTypes = $coach->sessionTypes;
        $upcomingSessions = $coach->bookedSessions()
            ->with(['user', 'sessionType'])
            ->where('session_time', '>', now())
            ->orderBy('session_time')
            ->get();
            
        $pastSessions = $coach->bookedSessions()
            ->with(['user', 'sessionType'])
            ->where('session_time', '<', now())
            ->orderBy('session_time', 'desc')
            ->get();
            
        return view('admin.coaches.show', compact('coach', 'sessionTypes', 'upcomingSessions', 'pastSessions'));
    }

    /**
     * Show the form for editing the specified coach.
     */
    public function edit(Coach $coach)
    {
        return view('admin.coaches.edit', compact('coach'));
    }

    /**
     * Update the specified coach.
     */
    public function update(Request $request, Coach $coach)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:coaches,email,' . $coach->id,
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|image|max:2048', // Max 2MB
            'is_active' => 'sometimes|boolean'
        ]);
        
        // Process the profile image if uploaded
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($coach->profile_image) {
                Storage::disk('public')->delete(str_replace('storage/', '', $coach->profile_image));
            }
            
            $imagePath = $request->file('profile_image')->store('coaches', 'public');
            $validated['profile_image'] = 'storage/' . $imagePath;
        }
        
        // Set active status
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        // Update the coach
        $coach->update($validated);
        
        return redirect()->route('admin.coaches.index')
            ->with('success', 'Coach updated successfully');
    }

    /**
     * Remove the specified coach.
     */
    public function destroy(Coach $coach)
    {
        // Check if there are any booked sessions for this coach
        $hasBookings = $coach->bookedSessions()->exists();
        
        if ($hasBookings) {
            return redirect()->route('admin.coaches.index')
                ->with('error', 'Cannot delete coach with existing bookings');
        }
        
        // Delete profile image if exists
        if ($coach->profile_image) {
            Storage::disk('public')->delete(str_replace('storage/', '', $coach->profile_image));
        }
        
        // Delete the coach
        $coach->delete();
        
        return redirect()->route('admin.coaches.index')
            ->with('success', 'Coach deleted successfully');
    }
}