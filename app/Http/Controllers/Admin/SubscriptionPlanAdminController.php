<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionPlanAdminController extends Controller
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
     * Display a listing of subscription plans.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('price', 'desc')->get();
        
        // Get active subscribers count for each plan
        foreach ($plans as $plan) {
            $plan->active_subscribers_count = UserSubscription::where('subscription_plan_id', $plan->id)
                ->where('status', 'active')
                ->count();
        }
        
        return view('admin.subscriptions.index', compact('plans'));
    }
    
    /**
     * Show the form for creating a new subscription plan.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.subscriptions.create');
    }
    
    /**
     * Store a newly created subscription plan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_interval' => 'required_if:is_lifetime,0|in:month,year',
            'is_active' => 'boolean',
            'is_lifetime' => 'boolean',
            'available_until' => 'nullable|date',
        ]);
        
        try {
            // Parse date if provided
            if (!empty($validated['available_until'])) {
                $validated['available_until'] = Carbon::parse($validated['available_until']);
            }
            
            // Set billing interval to null for lifetime plans
            if ($request->has('is_lifetime') && $request->is_lifetime) {
                $validated['billing_interval'] = null;
            }
            
            // Create subscription plan
            $plan = SubscriptionPlan::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'billing_interval' => $validated['billing_interval'] ?? null,
                'is_active' => $request->has('is_active') ? true : false,
                'is_lifetime' => $request->has('is_lifetime') ? true : false,
                'available_until' => $validated['available_until'] ?? null,
            ]);
            
            Log::info('Subscription plan created', [
                'id' => $plan->id,
                'name' => $plan->name,
                'is_lifetime' => $plan->is_lifetime
            ]);
            
            return redirect()->route('admin.subscriptions.index')
                ->with('success', "Subscription plan '{$plan->name}' created successfully.");
                
        } catch (\Exception $e) {
            Log::error('Error creating subscription plan', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create subscription plan: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified subscription plan.
     *
     * @param  \App\Models\SubscriptionPlan  $plan
     * @return \Illuminate\Http\Response
     */
    public function show(SubscriptionPlan $plan)
    {
        // Get active subscribers
        $subscribers = UserSubscription::where('subscription_plan_id', $plan->id)
            ->where('status', 'active')
            ->with('user')
            ->paginate(10);
            
        // Get stats
        $stats = [
            'total_subscribers' => UserSubscription::where('subscription_plan_id', $plan->id)->count(),
            'active_subscribers' => UserSubscription::where('subscription_plan_id', $plan->id)
                ->where('status', 'active')
                ->count(),
            'canceled_subscribers' => UserSubscription::where('subscription_plan_id', $plan->id)
                ->where('status', 'canceled')
                ->count(),
            'total_revenue' => UserSubscription::where('subscription_plan_id', $plan->id)
                ->sum('amount_paid')
        ];
        
        return view('admin.subscriptions.show', compact('plan', 'subscribers', 'stats'));
    }
    
    /**
     * Show the form for editing the specified subscription plan.
     *
     * @param  \App\Models\SubscriptionPlan  $plan
     * @return \Illuminate\Http\Response
     */
    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.subscriptions.edit', compact('plan'));
    }
    
    /**
     * Update the specified subscription plan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SubscriptionPlan  $plan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_interval' => 'nullable|in:month,year',
            'is_active' => 'sometimes',
            'is_lifetime' => 'sometimes',
            'available_until' => 'nullable|date',
        ]);
        
        try {
            // Parse date if provided
            if (!empty($validated['available_until'])) {
                $validated['available_until'] = Carbon::parse($validated['available_until']);
            }
            
            // Check if the plan has active subscribers before changing critical fields
            $hasActiveSubscribers = UserSubscription::where('subscription_plan_id', $plan->id)
                ->where('status', 'active')
                ->exists();
                
            // If changing from non-lifetime to lifetime (or vice versa) with active subscribers, prevent change
            if ($hasActiveSubscribers && $plan->is_lifetime !== (bool)$request->has('is_lifetime')) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Cannot change plan type (lifetime/recurring) while it has active subscribers.');
            }
            
            // Update subscription plan
            $plan->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'currency' => $validated['currency'],
                'billing_interval' => $request->has('is_lifetime') ? 'one-time' : $validated['billing_interval'],
                'is_active' => $request->has('is_active'),
                'is_lifetime' => $request->has('is_lifetime'),
                'available_until' => $validated['available_until'] ?? null,
            ]);
            
            Log::info('Subscription plan updated', [
                'id' => $plan->id,
                'name' => $plan->name
            ]);
            
            return redirect()->route('admin.subscriptions.index')
                ->with('success', "Subscription plan '{$plan->name}' updated successfully.");
                
        } catch (\Exception $e) {
            Log::error('Error updating subscription plan', [
                'error' => $e->getMessage(),
                'plan_id' => $plan->id
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update subscription plan: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified subscription plan from storage.
     *
     * @param  \App\Models\SubscriptionPlan  $plan
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubscriptionPlan $plan)
    {
        // Check if the plan has any subscriptions
        $hasSubscriptions = UserSubscription::where('subscription_plan_id', $plan->id)->exists();
        
        if ($hasSubscriptions) {
            return redirect()->route('admin.subscriptions.index')
                ->with('error', "Cannot delete plan '{$plan->name}' because it has subscribers. Consider marking it as inactive instead.");
        }
        
        try {
            $planName = $plan->name;
            $plan->delete();
            
            Log::info('Subscription plan deleted', [
                'id' => $plan->id,
                'name' => $planName
            ]);
            
            return redirect()->route('admin.subscriptions.index')
                ->with('success', "Subscription plan '{$planName}' deleted successfully.");
                
        } catch (\Exception $e) {
            Log::error('Error deleting subscription plan', [
                'error' => $e->getMessage(),
                'plan_id' => $plan->id
            ]);
            
            return redirect()->route('admin.subscriptions.index')
                ->with('error', 'Failed to delete subscription plan: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle plan active status
     */
    public function toggleStatus(SubscriptionPlan $plan)
    {
        $plan->is_active = !$plan->is_active;
        $plan->save();
        
        $statusText = $plan->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Subscription plan '{$plan->name}' {$statusText} successfully.");
    }
    
    /**
     * Show subscription analytics
     */
    public function analytics()
    {
        // Get all subscription plans
        $plans = SubscriptionPlan::all();
        
        // Calculate monthly subscription growth
        $monthlyGrowth = [];
        $currentYear = Carbon::now()->year;
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($currentYear, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($currentYear, $month, 1)->endOfMonth();
            
            // New subscriptions started in this month
            $newSubs = UserSubscription::whereBetween('start_date', [$startDate, $endDate])
                ->count();
                
            // Subscriptions canceled in this month
            $canceledSubs = UserSubscription::where('status', 'canceled')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();
                
            $monthlyGrowth[] = [
                'month' => $startDate->format('F'),
                'new' => $newSubs,
                'canceled' => $canceledSubs,
                'net' => $newSubs - $canceledSubs
            ];
        }
        
        // Get lifetime vs recurring statistics
        $lifetimeSubs = UserSubscription::whereHas('plan', function($query) {
            $query->where('is_lifetime', true);
        })->count();
        
        $recurringSubs = UserSubscription::whereHas('plan', function($query) {
            $query->where('is_lifetime', false);
        })->count();
        
        // Revenue statistics
        $totalRevenue = UserSubscription::sum('amount_paid');
        $lifetimeRevenue = UserSubscription::whereHas('plan', function($query) {
            $query->where('is_lifetime', true);
        })->sum('amount_paid');
        
        $recurringRevenue = UserSubscription::whereHas('plan', function($query) {
            $query->where('is_lifetime', false);
        })->sum('amount_paid');
        
        // Revenue for each plan
        $planRevenue = [];
        foreach ($plans as $plan) {
            $planRevenue[] = [
                'name' => $plan->name,
                'revenue' => UserSubscription::where('subscription_plan_id', $plan->id)
                    ->sum('amount_paid')
            ];
        }
        
        return view('admin.subscriptions.analytics', compact(
            'plans', 
            'monthlyGrowth', 
            'lifetimeSubs', 
            'recurringSubs',
            'totalRevenue',
            'lifetimeRevenue',
            'recurringRevenue',
            'planRevenue'
        ));
    }
}