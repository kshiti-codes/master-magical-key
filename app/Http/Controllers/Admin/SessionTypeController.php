<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionType;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionTypeController extends Controller
{
    /**
     * Display a listing of session types.
     */
    public function index()
    {
        $sessionTypes = SessionType::orderBy('name')->get();
        return view('admin.session-types.index', compact('sessionTypes'));
    }

    /**
     * Show the form for creating a new session type.
     */
    public function create()
    {
        // We don't need coaches for the create view based on our current design
        return view('admin.session-types.create');
    }

    /**
     * Store a newly created session type.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:15', // Minimum 15 minutes
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'sometimes|boolean'
        ]);
        
        // Set default active status if not provided
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        // Create the session type
        SessionType::create($validated);
        
        return redirect()->route('admin.session-types.index')
            ->with('success', 'Session type created successfully');
    }

    /**
     * Display the specified session type.
     */
    public function show(SessionType $sessionType)
    {
        $coaches = $sessionType->coaches;
        $bookedSessions = $sessionType->bookedSessions()
            ->with(['user', 'coach'])
            ->orderBy('session_time', 'desc')
            ->get();
            
        return view('admin.session-types.show', compact('sessionType', 'coaches', 'bookedSessions'));
    }

    /**
     * Show the form for editing the specified session type.
     */
    public function edit(SessionType $sessionType)
    {
        return view('admin.session-types.edit', compact('sessionType'));
    }

    /**
     * Update the specified session type.
     */
    public function update(Request $request, SessionType $sessionType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:15', // Minimum 15 minutes
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'is_active' => 'sometimes|boolean'
        ]);
        
        // Set active status
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        // Update the session type
        $sessionType->update($validated);
        
        return redirect()->route('admin.session-types.index')
            ->with('success', 'Session type updated successfully');
    }

    /**
     * Remove the specified session type.
     */
    public function destroy(Request $request, SessionType $sessionType)
    {
        // Check if there are any booked sessions for this type
        $hasBookings = $sessionType->bookedSessions()->exists();
        
        if ($hasBookings) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete session type '{$sessionType->name}' because it has existing bookings."
                ]);
            }
            
            return redirect()->route('admin.session-types.index')
                ->with('error', "Cannot delete session type '{$sessionType->name}' because it has existing bookings.");
        }
        
        try {
            // Store session type name for the success message
            $sessionTypeName = $sessionType->name;
            
            // Detach any associated coaches first
            $sessionType->coaches()->detach();
            
            // Delete the session type
            $sessionType->delete();
            
            // Log the deletion
            \Log::info('Session type deleted by admin', [
                'admin_id' => auth()->id(),
                'session_type_id' => $sessionType->id,
                'session_type_name' => $sessionTypeName
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Session type '{$sessionTypeName}' deleted successfully."
                ]);
            }
            
            return redirect()->route('admin.session-types.index')
                ->with('success', "Session type '{$sessionTypeName}' deleted successfully.");
                
        } catch (\Exception $e) {
            \Log::error('Error deleting session type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_type_id' => $sessionType->id
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to delete session type: {$e->getMessage()}"
                ]);
            }
            
            return redirect()->route('admin.session-types.index')
                ->with('error', "Failed to delete session type: {$e->getMessage()}");
        }
    }
    
    /**
     * Show the form for managing coaches for this session type
     */
    public function manageCoaches($id)
    {
        $sessionType = SessionType::findOrFail($id);
        $coaches = Coach::orderBy('name')->get();
        $assignedCoachIds = $sessionType->coaches->pluck('id')->toArray();
        
        return view('admin.session-types.coaches', compact('sessionType', 'coaches', 'assignedCoachIds'));
    }
    
    /**
     * Update the coaches for this session type
     */
    public function updateCoaches(Request $request, $id)
    {
        $request->validate([
            'coach_ids' => 'nullable|array',
            'coach_ids.*' => 'exists:coaches,id'
        ]);
        
        $sessionType = SessionType::findOrFail($id);
        
        // Sync the coaches
        $sessionType->coaches()->sync($request->coach_ids ?? []);
        
        return redirect()->route('admin.session-types.show', $sessionType->id)
            ->with('success', 'Coaches updated successfully');
    }
}