<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\CoachAvailability;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of coach availabilities.
     */
    public function index(Request $request)
    {
        $coaches = Coach::where('is_active', true)->orderBy('name')->get();
        $selectedCoachId = $request->input('coach_id');
        $selectedDate = $request->input('date'); // Remove the default value
        
        $query = CoachAvailability::query();
        
        // If user is a coach only (not an admin) or specifically viewing their coach data
        if (!auth()->user()->is_admin || ($request->has('coach_id') && auth()->user()->is_coach)) {
            // Get the coach ID - use the requested ID if present, otherwise use the user's coach ID
            $coachId = $selectedCoachId ?: null;
            
            // If coach is also admin and no specific coach ID was provided, show all
            if (auth()->user()->is_admin && !$coachId) {
                // No filtering needed, show all availabilities
            } else {
                // If coach only or specific coach view
                if (!$coachId && auth()->user()->is_coach) {
                    $coach = Coach::where('email', auth()->user()->email)->first();
                    if ($coach) {
                        $coachId = $coach->id;
                        $selectedCoachId = $coachId;
                    }
                }
                
                if ($coachId) {
                    $query->where('coach_id', $coachId);
                }
            }
        } elseif ($selectedCoachId) {
            // Admin filtering by coach ID
            $query->where('coach_id', $selectedCoachId);
        }
        
        if ($selectedDate) {
            $query->whereDate('date', $selectedDate);
        }
        
        $availabilities = $query->orderBy('date')
                            ->orderBy('start_time')
                            ->paginate(20)
                            ->withQueryString();
                            
        return view('admin.availabilities.index', compact('coaches', 'availabilities', 'selectedCoachId', 'selectedDate'));
    }

    /**
     * Show the form for creating a new availability.
     */
    public function create()
    {
        if (auth()->user()->is_admin) {
            // Admin can select any coach
            $coaches = Coach::where('is_active', true)->orderBy('name')->get();
        } else {
            // Coach can only select themselves
            $coach = auth()->user()->coach;
            
            if (!$coach) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Coach profile not found. Please contact an administrator.');
            }
            
            $coaches = collect([$coach]);
        }
        
        return view('admin.availabilities.create', compact('coaches'));
    }

    /**
     * Store a newly created availability.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'coach_id' => 'required|exists:coaches,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,unavailable'
        ]);
        
        // If user is a coach but not an admin, verify they are creating their own availability
        if (!auth()->user()->is_admin) {
            $coach = auth()->user()->coach;
            
            if (!$coach || $coach->id != $validated['coach_id']) {
                return redirect()->route('admin.availabilities.index')
                    ->with('error', 'You can only create availabilities for yourself');
            }
        }
        
        // Check for overlapping availabilities
        $exists = CoachAvailability::where('coach_id', $validated['coach_id'])
            ->where('date', $validated['date'])
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'There is an overlapping availability for this coach at the selected time');
        }
        
        // Create the availability
        CoachAvailability::create($validated);
        
        return redirect()->route('admin.availabilities.index')
            ->with('success', 'Availability created successfully');
    }

    /**
     * Show the form for editing the specified availability.
     */
    public function edit($id)
    {
        $availability = CoachAvailability::findOrFail($id);
    
        // If user is a coach but not an admin, verify they are editing their own availability
        if (!auth()->user()->is_admin) {
            $coach = auth()->user()->coach;
            
            if (!$coach || $coach->id != $availability->coach_id) {
                return redirect()->route('admin.availabilities.index')
                    ->with('error', 'You can only edit your own availabilities');
            }
        }
        
        if (auth()->user()->is_admin) {
            // Admin can select any coach
            $coaches = Coach::where('is_active', true)->orderBy('name')->get();
        } else {
            // Coach can only select themselves
            $coaches = collect([auth()->user()->coach]);
        }
        
        return view('admin.availabilities.edit', compact('availability', 'coaches'));
    }

    /**
     * Update the specified availability.
     */
    public function update(Request $request, $id)
    {
        $availability = CoachAvailability::findOrFail($id);
    
        // If user is a coach but not an admin, verify they are updating their own availability
        if (!auth()->user()->is_admin) {
            $coach = auth()->user()->coach;
            
            if (!$coach || $coach->id != $availability->coach_id) {
                return redirect()->route('admin.availabilities.index')
                    ->with('error', 'You can only update your own availabilities');
            }
        }
        
        // Check if the availability is already booked
        if ($availability->status === 'booked') {
            return redirect()->route('admin.availabilities.index')
                ->with('error', 'Cannot update a booked availability');
        }
        
        $validated = $request->validate([
            'coach_id' => 'required|exists:coaches,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:available,unavailable'
        ]);
        
        // Check for overlapping availabilities (except this one)
        $exists = CoachAvailability::where('coach_id', $validated['coach_id'])
            ->where('date', $validated['date'])
            ->where('id', '!=', $id)
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhere(function($q) use ($validated) {
                        $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                    });
            })
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'There is an overlapping availability for this coach at the selected time');
        }
        
        // Update the availability
        $availability->update($validated);
        
        return redirect()->route('admin.availabilities.index')
            ->with('success', 'Availability updated successfully');
    }

    /**
     * Remove the specified availability.
     */
    public function destroy($id)
    {
        $availability = CoachAvailability::findOrFail($id);
    
        // If user is a coach but not an admin, verify they are deleting their own availability
        if (!auth()->user()->is_admin) {
            $coach = auth()->user()->coach;
            
            if (!$coach || $coach->id != $availability->coach_id) {
                return redirect()->route('admin.availabilities.index')
                    ->with('error', 'You can only delete your own availabilities');
            }
        }
        
        // Check if the availability is already booked
        if ($availability->status === 'booked') {
            return redirect()->route('admin.availabilities.index')
                ->with('error', 'Cannot delete a booked availability');
        }
        
        // Delete the availability
        $availability->delete();
        
        return redirect()->route('admin.availabilities.index')
            ->with('success', 'Availability deleted successfully');
    }
    
    /**
     * Show the form for batch creating availabilities.
     */
    public function batchCreate()
    {
        if (auth()->user()->is_admin) {
            // Admin can select any coach
            $coaches = Coach::where('is_active', true)->orderBy('name')->get();
        } else {
            // Coach can only select themselves
            $coach = auth()->user()->coach;
            
            if (!$coach) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Coach profile not found. Please contact an administrator.');
            }
            
            $coaches = collect([$coach]);
        }
        
        return view('admin.availabilities.batch', compact('coaches'));
    }
    
    /**
     * Store batch created availabilities.
     */
    public function batchStore(Request $request)
    {
        $request->validate([
            'coach_id' => 'required|exists:coaches,id',
            'date_range' => 'required|string',
            'days' => 'required|array',
            'days.*' => 'integer|between:0,6', // 0 = Sunday, 6 = Saturday
            'time_slots' => 'required|array',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time'
        ]);
        
        // If user is a coach but not an admin, verify they are creating their own availabilities
        if (!auth()->user()->is_admin) {
            $coach = auth()->user()->coach;
            
            if (!$coach || $coach->id != $request->coach_id) {
                return redirect()->route('admin.availabilities.index')
                    ->with('error', 'You can only create availabilities for yourself');
            }
        }
        
        // Parse date range - handle different possible formats
        try {
            if (strpos($request->date_range, ' - ') !== false) {
                list($startDate, $endDate) = explode(' - ', $request->date_range);
            } else {
                // If the separator isn't found, assume single day selection
                $startDate = $endDate = $request->date_range;
            }
            
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid date range format: ' . $e->getMessage());
        }
        
        // Create availabilities
        $created = 0;
        $skipped = 0;
        
        try {
            DB::beginTransaction();
            
            // Loop through each day in the date range
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Check if this day of week is included
                if (!in_array($date->dayOfWeek, $request->days)) {
                    continue;
                }
                
                foreach ($request->time_slots as $slot) {
                    // Ensure we have both start and end times
                    if (!isset($slot['start_time']) || !isset($slot['end_time'])) {
                        continue;
                    }
                    
                    // Check for overlapping availabilities
                    $exists = CoachAvailability::where('coach_id', $request->coach_id)
                        ->whereDate('date', $date->format('Y-m-d'))
                        ->where(function($query) use ($slot) {
                            $query->whereBetween('start_time', [$slot['start_time'], $slot['end_time']])
                                ->orWhereBetween('end_time', [$slot['start_time'], $slot['end_time']])
                                ->orWhere(function($q) use ($slot) {
                                    $q->where('start_time', '<=', $slot['start_time'])
                                    ->where('end_time', '>=', $slot['end_time']);
                                });
                        })
                        ->exists();
                        
                    if ($exists) {
                        $skipped++;
                        continue;
                    }
                    
                    // Create the availability
                    CoachAvailability::create([
                        'coach_id' => $request->coach_id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'status' => 'available'
                    ]);
                    
                    $created++;
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.availabilities.index')
                ->with('success', "Created $created availability slots. Skipped $skipped due to overlaps.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create availabilities: ' . $e->getMessage());
        }
    }

    /**
     * Batch delete multiple availabilities.
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:coach_availabilities,id'
        ]);
        
        $selectedIds = $request->selected_ids;
        $deletedCount = 0;
        $errorCount = 0;
        
        // Find availabilities that are not booked
        $availabilities = CoachAvailability::whereIn('id', $selectedIds)
            ->where('status', '!=', 'booked')
            ->get();
        
        foreach ($availabilities as $availability) {
            try {
                $availability->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errorCount++;
            }
        }
        
        // Count items that were booked (and thus not included in the delete operation)
        $bookedCount = count($selectedIds) - count($availabilities);
        
        $message = "{$deletedCount} availability slots deleted successfully.";
        
        if ($bookedCount > 0) {
            $message .= " {$bookedCount} booked slots were skipped.";
        }
        
        if ($errorCount > 0) {
            $message .= " {$errorCount} slots could not be deleted due to errors.";
        }
        
        return redirect()->route('admin.availabilities.index')
            ->with('success', $message);
    }
}