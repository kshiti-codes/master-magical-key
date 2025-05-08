<?php

namespace App\Http\Controllers;

use App\Models\SessionType;
use App\Models\Coach;
use App\Models\CoachAvailability;
use App\Models\BookedSession;
use App\Models\BookingLock;
use App\Models\Purchase;
use App\Models\User;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SessionBookedMail;
use Carbon\Carbon;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class SessionBookingController extends Controller
{
    /**
     * Display a listing of the user's booked sessions.
     */
    public function index()
    {
        $upcomingSessions = BookedSession::where('user_id', Auth::id())
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('session_time', '>', now())
            ->orderBy('session_time')
            ->get();
            
        $pastSessions = BookedSession::where('user_id', Auth::id())
            ->whereIn('status', ['completed', 'cancelled', 'refunded'])
            ->orWhere(function($query) {
                $query->where('user_id', Auth::id())
                      ->where('session_time', '<', now());
            })
            ->orderBy('session_time', 'desc')
            ->get();
            
        return view('sessions.index', compact('upcomingSessions', 'pastSessions'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create()
    {
        $sessionTypes = SessionType::where('is_active', true)->get();
        return view('sessions.create', compact('sessionTypes'));
    }
    
    /**
     * Get coaches available for a selected session type
     */
    public function getCoaches(Request $request)
    {
        $request->validate([
            'session_type_id' => 'required|exists:session_types,id',
        ]);
        
        $sessionType = SessionType::findOrFail($request->session_type_id);
        $coaches = $sessionType->coaches()->where('is_active', true)->get();
        
        return response()->json([
            'coaches' => $coaches->map(function($coach) {
                return [
                    'id' => $coach->id,
                    'name' => $coach->name,
                    'bio' => $coach->bio,
                    'profile_image' => $coach->profile_image ? asset($coach->profile_image) : null
                ];
            })
        ]);
    }
    
    /**
     * Get available dates for a selected coach and session type
     */
    public function getAvailableDates(Request $request)
    {
        $request->validate([
            'session_type_id' => 'required|exists:session_types,id',
            'coach_id' => 'required|exists:coaches,id',
        ]);
        
        // Get the coach and session type
        $coach = Coach::findOrFail($request->coach_id);
        $sessionType = SessionType::findOrFail($request->session_type_id);
        
        // Get all available dates (next 30 days)
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);
        
        $availableDates = CoachAvailability::where('coach_id', $coach->id)
            ->where('status', 'available')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->select(DB::raw('DISTINCT date'))
            ->orderBy('date')
            ->get()
            ->map(function($availability) {
                return [
                    'date' => $availability->date->format('Y-m-d'),
                    'formatted_date' => $availability->date->format('l, F j, Y')
                ];
            });
            
        return response()->json([
            'dates' => $availableDates
        ]);
    }
    
    /**
     * Get available time slots for a selected date
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'session_type_id' => 'required|exists:session_types,id',
            'coach_id' => 'required|exists:coaches,id',
            'date' => 'required|date_format:Y-m-d',
        ]);
        
        // Get the coach, session type, and date
        $coach = Coach::findOrFail($request->coach_id);
        $sessionType = SessionType::findOrFail($request->session_type_id);
        
        // Parse date with explicit handling
        $date = Carbon::parse($request->date)->startOfDay();
        
        \Log::info('Looking for available slots:', [
            'coach_id' => $coach->id,
            'session_type_id' => $sessionType->id,
            'date' => $date->toDateString(),
            'required_duration' => $sessionType->duration
        ]);
        
        // Get raw data for debugging
        $rawSlots = CoachAvailability::where('coach_id', $coach->id)
            ->whereDate('date', '=', $date->toDateString())
            ->get();
        
        \Log::info('Found ' . $rawSlots->count() . ' total slots for date');
        
        // Get available time slots with more explicit query
        $availableSlots = CoachAvailability::where('coach_id', $coach->id)
            ->whereDate('date', '=', $date->toDateString())
            ->where('status', 'available')
            ->orderBy('start_time')
            ->get()
            ->map(function($slot) {
                return [
                    'id' => $slot->id,
                    'start_time' => Carbon::parse($slot->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($slot->end_time)->format('H:i'),
                    'formatted_time' => Carbon::parse($slot->start_time)->format('g:i A') . ' - ' . 
                                    Carbon::parse($slot->end_time)->format('g:i A')
                ];
            });
        
        \Log::info('Returning ' . $availableSlots->count() . ' available slots after filtering');
        
        return response()->json([
            'slots' => $availableSlots
        ]);
    }
    
    /**
     * Lock a time slot for booking
     */
    public function lockTimeSlot(Request $request)
    {
        $request->validate([
            'availability_id' => 'required|exists:coach_availabilities,id',
        ]);
        
        // Get the availability
        $availability = CoachAvailability::findOrFail($request->availability_id);
        
        // Check if the slot is still available
        if ($availability->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'This time slot is no longer available'
            ], 400);
        }
        
        // Check if the slot is locked by someone else
        if ($availability->isLocked()) {
            $lock = $availability->bookingLock;
            
            // If locked by the current user, extend the lock
            if ($lock->user_id === Auth::id()) {
                $lock->update([
                    'expires_at' => now()->addMinutes(10)
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Lock extended',
                    'lock_id' => $lock->id,
                    'expires_at' => $lock->expires_at->toIso8601String()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'This time slot is currently being booked by another user'
            ], 400);
        }
        
        // Create a new lock
        $lock = BookingLock::createLock(Auth::id(), $availability->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Time slot locked for booking',
            'lock_id' => $lock->id,
            'expires_at' => $lock->expires_at->toIso8601String()
        ]);
    }

    /**
     * Prepare a session for booking (before payment)
     */
    public function prepare(Request $request)
    {
        $request->validate([
            'session_type_id' => 'required|exists:session_types,id',
            'coach_id' => 'required|exists:coaches,id',
            'availability_id' => 'required|exists:coach_availabilities,id',
        ]);
        
        // Get the resources
        $sessionType = SessionType::findOrFail($request->session_type_id);
        $coach = Coach::findOrFail($request->coach_id);
        $availability = CoachAvailability::findOrFail($request->availability_id);
        
        // Verify the availability is for the correct coach
        if ($availability->coach_id !== $coach->id) {
            return redirect()->route('sessions.create')
                ->with('error', 'Invalid availability selected');
        }
        
        // Verify the availability is still available
        if ($availability->status !== 'available') {
            return redirect()->route('sessions.create')
                ->with('error', 'This time slot is no longer available');
        }
        
        // Check if the slot is locked by someone else
        if ($availability->isLocked() && $availability->bookingLock->user_id !== Auth::id()) {
            return redirect()->route('sessions.create')
                ->with('error', 'This time slot is currently being booked by another user');
        }
        
        try {
            // Extend or create the booking lock
            if ($availability->isLocked() && $availability->bookingLock->user_id === Auth::id()) {
                // Extend existing lock
                $availability->bookingLock->update([
                    'expires_at' => now()->addMinutes(15) // Extended for payment process
                ]);
            } else {
                // Create a new lock with longer expiry time for payment
                BookingLock::create([
                    'user_id' => Auth::id(),
                    'coach_availability_id' => $availability->id,
                    'expires_at' => now()->addMinutes(15) // 15 minutes for payment
                ]);
            }
            
            // Calculate session time and price
            $sessionTime = Carbon::parse($availability->date->format('Y-m-d') . ' ' . $availability->start_time);
            $price = $sessionType->price;
            
            // Store booking info in session for payment
            session([
                'booking_info' => [
                    'user_id' => Auth::id(),
                    'coach_id' => $coach->id,
                    'session_type_id' => $sessionType->id,
                    'coach_availability_id' => $availability->id,
                    'session_time' => $sessionTime->toDateTimeString(),
                    'duration' => $sessionType->duration,
                    'amount' => $price,
                    'currency' => $sessionType->currency
                ]
            ]);
            
            // Redirect to payment page
            return redirect()->route('sessions.payment');
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to prepare session booking', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'availability_id' => $availability->id
            ]);
            
            return redirect()->route('sessions.create')
                ->with('error', 'Failed to prepare booking. Please try again.');
        }
    }

    /**
     * Show payment page for session booking
     */
    public function payment($id = null)
    {
        // Check if booking info exists in session
        if (!session()->has('booking_info')) {
            return redirect()->route('sessions.create')
                ->with('error', 'Your booking session has expired. Please try again.');
        }
        
        $bookingInfo = session('booking_info');
        
        // Get required models
        $sessionType = SessionType::findOrFail($bookingInfo['session_type_id']);
        $coach = Coach::findOrFail($bookingInfo['coach_id']);
        //calculate amount paid with GST
        $amountPaid = $bookingInfo['amount'] * 1.1; // Base amount before GST
        
        // Prepare display data
        $bookingData = (object) [
            'sessionType' => $sessionType,
            'coach' => $coach,
            'session_time' => Carbon::parse($bookingInfo['session_time']),
            'duration' => $bookingInfo['duration'],
            'amount_paid' => $amountPaid,
            'currency' => $bookingInfo['currency']
        ];
        
        return view('sessions.payment', ['bookedSession' => $bookingData]);
    }

    /**
     * Process the payment for a booking
     */
    public function processPayment(Request $request)
    {
        // Check if booking info exists in session
        if (!session()->has('booking_info')) {
            return redirect()->route('sessions.create')
                ->with('error', 'Your booking session has expired. Please try again.');
        }
        
        $bookingInfo = session('booking_info');
        
        // Calculate the proper amounts including GST
        $total = $bookingInfo['amount'] * 1.1; // Total amount including GST
        $subtotal = $total / 1.1; // Base amount before GST
        $tax = $total - $subtotal; // GST amount
        
        try {
            // Initialize PayPal
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // Create order with proper GST calculation
            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('sessions.store'),
                    "cancel_url" => route('sessions.cancel-payment'),
                    "brand_name" => config('app.name'),
                    "landing_page" => "BILLING",
                    "user_action" => "PAY_NOW",
                ],
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => $bookingInfo['currency'],
                            "value" => number_format($total, 2, '.', ''),
                            "breakdown" => [
                                "item_total" => [
                                    "currency_code" => $bookingInfo['currency'],
                                    "value" => number_format($subtotal, 2, '.', '')
                                ],
                                "tax_total" => [
                                    "currency_code" => $bookingInfo['currency'],
                                    "value" => number_format($tax, 2, '.', '')
                                ]
                            ]
                        ],
                        "items" => [
                            [
                                "name" => SessionType::find($bookingInfo['session_type_id'])->name,
                                "description" => "Session with " . Coach::find($bookingInfo['coach_id'])->name,
                                "quantity" => "1",
                                "category" => "DIGITAL_GOODS",
                                "unit_amount" => [
                                    "currency_code" => $bookingInfo['currency'],
                                    "value" => number_format($subtotal, 2, '.', '')
                                ],
                                "tax" => [
                                    "currency_code" => $bookingInfo['currency'],
                                    "value" => number_format($tax, 2, '.', '')
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
            
            if (isset($response['id']) && $response['id']) {
                // Store the PayPal order ID in the session for capture later
                session(['paypal_order_id' => $response['id']]);
                
                // Find and redirect to PayPal approval URL
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return redirect()->away($link['href']);
                    }
                }
            }
            
            // Something went wrong
            return redirect()->route('sessions.payment')
                ->with('error', 'Unable to process payment. Please try again.');
                
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to process payment', [
                'error' => $e->getMessage(),
                'booking_info' => $bookingInfo
            ]);
            
            return redirect()->route('sessions.payment')
                ->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the payment process
     */
    public function cancelPayment()
    {
        // Get booking info from session
        $bookingInfo = session('booking_info');
        
        // Clean up the booking lock if it exists
        if ($bookingInfo) {
            try {
                $availability = CoachAvailability::find($bookingInfo['coach_availability_id']);
                if ($availability && $availability->bookingLock) {
                    $availability->bookingLock->delete();
                }
            } catch (\Exception $e) {
                \Log::error('Error cleaning up booking lock', [
                    'error' => $e->getMessage(),
                    'availability_id' => $bookingInfo['coach_availability_id'] ?? null
                ]);
            }
        }
        
        // Clear session data
        session()->forget(['booking_info', 'paypal_order_id']);
        
        return redirect()->route('sessions.index')
            ->with('info', 'Payment cancelled. Your booking has been cancelled.');
    }

    /**
     * Store a session booking after successful payment
     */
    public function store()
    {
        // Check if booking info exists in session
        if (!session()->has('booking_info') || !session()->has('paypal_order_id')) {
            return redirect()->route('sessions.create')
                ->with('error', 'Your booking session has expired. Please try again.');
        }
        
        $bookingInfo = session('booking_info');
        $paypalOrderId = session('paypal_order_id');
        
        try {
            // Initialize PayPal
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // Capture payment
            $response = $provider->capturePaymentOrder($paypalOrderId);
            
            if (!isset($response['status']) || $response['status'] !== 'COMPLETED') {
                throw new \Exception('Payment failed or was not completed.');
            }
            
            // Extract transaction ID
            $transactionId = $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalOrderId;
            
            // Create the booking with the transaction details
            $result = DB::transaction(function() use ($bookingInfo, $transactionId) {
                // Get the availability
                $availability = CoachAvailability::findOrFail($bookingInfo['coach_availability_id']);
                
                // Create the booking
                $bookedSession = BookedSession::create([
                    'user_id' => $bookingInfo['user_id'],
                    'coach_id' => $bookingInfo['coach_id'],
                    'session_type_id' => $bookingInfo['session_type_id'],
                    'coach_availability_id' => $bookingInfo['coach_availability_id'],
                    'session_time' => $bookingInfo['session_time'],
                    'duration' => $bookingInfo['duration'],
                    'status' => 'pending', // Pending coach confirmation
                    'amount_paid' => $bookingInfo['amount'] * 1.1, // Amount including GST
                    'transaction_id' => $transactionId
                ]);
                
                // Mark the availability as booked
                $availability->update(['status' => 'booked']);
                
                // Remove the lock
                if ($availability->bookingLock) {
                    $availability->bookingLock->delete();
                }
                
                // Create purchase record
                $purchase = $this->createPurchaseRecord($bookedSession, $transactionId);
                
                return [
                    'bookedSession' => $bookedSession,
                    'purchase' => $purchase
                ];
            });
            
            $bookedSession = $result['bookedSession'];
            $purchase = $result['purchase'];
            
            // Send confirmation emails
            $this->sendBookingEmails($bookedSession);
            
            // Clear session data
            session()->forget(['booking_info', 'paypal_order_id']);
            
            // Redirect to confirmation page
            return redirect()->route('sessions.confirmation', $bookedSession->id)
                ->with('success', 'Your session has been booked successfully!');
            
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to complete session booking after payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'booking_info' => $bookingInfo,
                'paypal_order_id' => $paypalOrderId
            ]);
            
            return redirect()->route('sessions.index')
                ->with('error', 'There was an error processing your booking: ' . $e->getMessage());
        }
    }

    /**
     * Send booking confirmation emails to coach and user
     */
    private function sendBookingEmails(BookedSession $bookedSession)
    {
        try {
            // Send email to coach
            Mail::to($bookedSession->coach->email)
                ->send(new SessionBookedMail($bookedSession, 'coach'));
            
            // Send email to user
            Mail::to($bookedSession->user->email)
                ->send(new SessionBookedMail($bookedSession, 'user'));
            
            // Log successful email sending
            \Log::info('Session booking emails sent', [
                'session_id' => $bookedSession->id,
                'user_id' => $bookedSession->user_id,
                'coach_id' => $bookedSession->coach_id
            ]);
        } catch (\Exception $e) {
            // Log email error but continue with the process
            \Log::error('Failed to send session booking emails', [
                'error' => $e->getMessage(),
                'session_id' => $bookedSession->id
            ]);
        }
    }
    
    /**
     * Show the confirmation page for a completed booking
     */
    public function confirmation($id)
    {
        $bookedSession = BookedSession::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
            
        // Get the PayPal order ID from session
        $paypalOrderId = session('paypal_order_id');
        // Create purchase record
        $purchase = $this->createPurchaseRecord($bookedSession);
        $coach = $bookedSession->coach;
        $user = $bookedSession->user;
        // Send email to coach
        Mail::to($coach->email)->send(new SessionBookedMail($bookedSession, 'coach'));
            
        // Send email to user
        Mail::to($user->email)->send(new SessionBookedMail($bookedSession, 'user'));
        
        // If there's a PayPal order ID, process the payment
        if ($paypalOrderId && $bookedSession->status === 'pending') {
            try {
                // Initialize PayPal
                $provider = new PayPalClient;
                $provider->setApiCredentials(config('paypal'));
                $provider->getAccessToken();
                
                // Capture payment
                $response = $provider->capturePaymentOrder($paypalOrderId);
                
                if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                    // Payment successful, update booking status
                    $bookedSession->update([
                        'status' => 'confirmed',
                        'transaction_id' => $response['id'] ?? $paypalOrderId
                    ]);
                    
                    // Clear the session data
                    session()->forget(['paypal_order_id', 'booked_session_id']);
                    
                    // Send confirmation email or notification (would be implemented separately)
                }
            } catch (\Exception $e) {
                // Log the error
                \Log::error('Failed to capture payment', [
                    'error' => $e->getMessage(),
                    'paypal_order_id' => $paypalOrderId,
                    'booked_session_id' => $bookedSession->id
                ]);
            }
        }
        
        return view('sessions.confirmation', compact('bookedSession'));
    }

    /**
     * Display the specified booking.
     */
    public function show($id)
    {
        $bookedSession = BookedSession::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
            
        return view('sessions.show', compact('bookedSession'));
    }
    
    /**
     * Join a meeting for a booked session
     */
    public function joinMeeting($id)
    {
        $bookedSession = BookedSession::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'confirmed')
            ->firstOrFail();
            
        // Check if the meeting can be joined now
        if (!$bookedSession->canJoinNow()) {
            return redirect()->route('sessions.show', $bookedSession->id)
                ->with('error', 'You can only join the meeting 10 minutes before the scheduled time');
        }
        
        // Check if meeting link exists
        if (!$bookedSession->meeting_link) {
            return redirect()->route('sessions.show', $bookedSession->id)
                ->with('error', 'Meeting link is not available yet');
        }
        
        // Redirect to the meeting link
        return redirect()->away($bookedSession->meeting_link);
    }

    /**
     * Create a purchase record for a session booking
     */
    private function createPurchaseRecord(BookedSession $bookedSession)
    {
        // Calculate tax (assuming 10% GST)
        $subtotal = $bookedSession->amount_paid / 1.1; // Remove GST from total
        $tax = $bookedSession->amount_paid - $subtotal; // Calculate GST amount
        
        // Create the purchase record
        $purchase = Purchase::create([
            'user_id' => $bookedSession->user_id,
            'transaction_id' => $bookedSession->transaction_id ?? 'SESSION-' . $bookedSession->id,
            'amount' => $bookedSession->amount_paid,
            'currency' => $bookedSession->sessionType->currency,
            'status' => 'completed', // Sessions are paid for immediately
            'subtotal' => $subtotal,
            'tax' => $tax,
            'tax_rate' => 10.00,
        ]);
        
        // Create the purchase item record
        $purchaseItem = \App\Models\PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'booked_session_id' => $bookedSession->id,
            'item_type' => 'session',
            'quantity' => 1,
            'price' => $subtotal, // Price before tax
        ]);
        
        // Update booked session with purchase reference
        $bookedSession->update([
            'purchase_id' => $purchase->id
        ]);
        
        return $purchase;
    }
}