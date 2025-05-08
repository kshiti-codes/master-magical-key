<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookedSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class BookedSessionController extends Controller
{
    /**
     * Display a listing of booked sessions.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $coachId = $request->input('coach_id');
        
        $query = BookedSession::with(['user', 'coach', 'sessionType']);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($coachId) {
            $query->where('coach_id', $coachId);
        }
        
        // Upcoming sessions first, then past sessions by descending date
        $query->orderByRaw("
            CASE WHEN session_time > ? THEN 0 ELSE 1 END ASC,
            CASE WHEN session_time > ? THEN session_time ELSE session_time END DESC
        ", [now(), now()]);
        
        $bookedSessions = $query->paginate(20)->withQueryString();
        
        // Get coaches for filter dropdown
        $coaches = \App\Models\Coach::orderBy('name')->get();
        
        return view('admin.booked-sessions.index', compact('bookedSessions', 'coaches', 'status', 'coachId'));
    }

    /**
     * Display the specified booked session.
     */
    public function show($id)
    {
        $bookedSession = BookedSession::with(['user', 'coach', 'sessionType'])->findOrFail($id);
        return view('admin.booked-sessions.show', compact('bookedSession'));
    }
    
    /**
     * Approve a pending booking.
     */
    public function approve(Request $request, $id)
    {
        $bookedSession = BookedSession::findOrFail($id);
        
        if ($bookedSession->status !== 'pending') {
            return redirect()->route('admin.booked-sessions.show', $id)
                ->with('error', 'Only pending sessions can be approved');
        }
        
        // Update the status
        $bookedSession->update([
            'status' => 'confirmed'
        ]);
        
        // Notify the user (would be implemented separately)
        // Mail::to($bookedSession->user->email)->send(new SessionApproved($bookedSession));
        
        return redirect()->route('admin.booked-sessions.show', $id)
            ->with('success', 'Session has been approved');
    }
    
    /**
     * Reject a pending booking.
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string'
        ]);
        
        $bookedSession = BookedSession::findOrFail($id);
        
        if ($bookedSession->status !== 'pending') {
            return redirect()->route('admin.booked-sessions.show', $id)
                ->with('error', 'Only pending sessions can be rejected');
        }
        
        // Update the status
        $bookedSession->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason
        ]);
        
        // Free up the availability slot
        $bookedSession->availability->update([
            'status' => 'available'
        ]);
        
        // Notify the user (would be implemented separately)
        // Mail::to($bookedSession->user->email)->send(new SessionRejected($bookedSession));
        
        return redirect()->route('admin.booked-sessions.show', $id)
            ->with('success', 'Session has been rejected');
    }
    
    /**
     * Cancel a confirmed booking.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string'
        ]);
        
        $bookedSession = BookedSession::findOrFail($id);
        
        if ($bookedSession->status !== 'confirmed') {
            return redirect()->route('admin.booked-sessions.show', $id)
                ->with('error', 'Only confirmed sessions can be cancelled');
        }
        
        // Update the status
        $bookedSession->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason
        ]);
        
        // Free up the availability slot
        $bookedSession->availability->update([
            'status' => 'available'
        ]);
        
        // Notify the user (would be implemented separately)
        // Mail::to($bookedSession->user->email)->send(new SessionCancelled($bookedSession));
        
        return redirect()->route('admin.booked-sessions.show', $id)
            ->with('success', 'Session has been cancelled');
    }
    
    /**
     * Update the meeting link for a booked session.
     */
    public function updateMeetingLink(Request $request, $id)
    {
        $request->validate([
            'meeting_link' => 'required|url'
        ]);
        
        $bookedSession = BookedSession::findOrFail($id);
        
        if ($bookedSession->status !== 'confirmed') {
            return redirect()->route('admin.booked-sessions.show', $id)
                ->with('error', 'Meeting link can only be set for confirmed sessions');
        }
        
        // Update the meeting link
        $bookedSession->update([
            'meeting_link' => $request->meeting_link
        ]);
        
        // Notify the user (would be implemented separately)
        // Mail::to($bookedSession->user->email)->send(new MeetingLinkUpdated($bookedSession));
        
        return redirect()->route('admin.booked-sessions.show', $id)
            ->with('success', 'Meeting link has been updated');
    }
    
    /**
     * Mark a session as completed.
     */
    public function markComplete($id)
    {
        $bookedSession = BookedSession::findOrFail($id);
        
        if ($bookedSession->status !== 'confirmed') {
            return redirect()->route('admin.booked-sessions.show', $id)
                ->with('error', 'Only confirmed sessions can be marked as completed');
        }
        
        // Check if the session has already passed
        if ($bookedSession->session_time->gt(now())) {
            return redirect()->route('admin.booked-sessions.show', $id)
                ->with('error', 'Cannot mark a future session as completed');
        }
        
        // Update the status
        $bookedSession->update([
            'status' => 'completed'
        ]);
        
        return redirect()->route('admin.booked-sessions.show', $id)
            ->with('success', 'Session has been marked as completed');
    }
}