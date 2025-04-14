<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\TrainingVideo;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\InvoiceService;
use App\Mail\InvoiceEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Display available subscription plans
     */
    public function index()
    {
        // Get active subscription plans
        $plans = SubscriptionPlan::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('available_until')
                      ->orWhere('available_until', '>', Carbon::now());
            })
            ->get();
            
        // Get user's subscriptions
        $userSubscriptions = Auth::check() ? Auth::user()->subscriptions : collect();
        $hasLifetimeSubscription = Auth::check() ? Auth::user()->hasLifetimeSubscription() : false;
        
        return view('subscriptions.index', compact(
            'plans', 
            'userSubscriptions',
            'hasLifetimeSubscription'
        ));
    }
    
    /**
     * Show details for a subscription plan
     */
    public function show(SubscriptionPlan $plan)
    {
        // Check if plan is available
        if (!$plan->isAvailable()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'This subscription plan is no longer available.');
        }
        
        // Check if user already has an active subscription
        if (Auth::check()) {
            $user = Auth::user();
            
            // If user has a lifetime subscription, redirect with message
            if ($user->hasLifetimeSubscription()) {
                return redirect()->route('subscriptions.index')
                    ->with('info', 'You already have a lifetime subscription that grants access to all content.');
            }
            
            // If user already has this plan, redirect to subscription management
            $hasThisPlan = $user->subscriptions()
                ->where('subscription_plan_id', $plan->id)
                ->where('status', 'active')
                ->exists();
                
            if ($hasThisPlan) {
                return redirect()->route('subscription.manage')
                    ->with('info', 'You already have an active subscription to this plan.');
            }
        }
        
        return view('subscriptions.show', compact('plan'));
    }
    
    /**
     * Process subscription purchase
     */
    public function purchase(Request $request, SubscriptionPlan $plan)
    {
        // Check if plan is available
        if (!$plan->isAvailable()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'This subscription plan is no longer available.');
        }
        
        // Store plan info in session
        $request->session()->put('subscription_plan_id', $plan->id);
        
        // Check for active or canceled-but-still-valid subscriptions
        $user = Auth::user();
        $existingSubscription = $user->subscriptions()
            ->where('subscription_plan_id', $plan->id)
            ->where(function ($query) {
                // Either active or canceled but still within valid period
                $query->where('status', 'active')
                    ->orWhere(function ($query) {
                        $query->where('status', 'canceled')
                                ->where('end_date', '>', now());
                    });
            })
            ->first();
        
        if ($existingSubscription) {
            // Offer to extend instead of creating a new subscription
            return view('subscriptions.extend', [
                'existingSubscription' => $existingSubscription,
                'plan' => $plan
            ]);
        }
        
        try {
            // Initialize PayPal API
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();
            
            if (!$paypalToken) {
                throw new \Exception('Failed to get PayPal access token');
            }
            
            // For lifetime plan (one-time payment)
            if ($plan->is_lifetime) {
                $response = $this->createOneTimePayment($provider, $plan);
            } else {
                // For recurring subscription
                $response = $this->createSubscription($provider, $plan);
            }
            
            if (isset($response['id']) && $response['id']) {
                // Store PayPal order/subscription ID
                if ($plan->is_lifetime) {
                    $request->session()->put('paypal_order_id', $response['id']);
                } else {
                    $request->session()->put('paypal_subscription_id', $response['id']);
                }
                
                // Find and redirect to PayPal approval URL
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return redirect()->away($link['href']);
                    }
                }

                return redirect()->route('subscriptions.show', $plan)
                    ->with('error', 'Could not find PayPal approval link. Please try again later.');
            }
            
            // If we get here, something went wrong
            return redirect()->route('subscriptions.show', $plan)
                ->with('error', 'Could not process the subscription. Please try again later.');
                
        } catch (\Exception $e) {
            \Log::error('Subscription purchase error', [
                'error' => $e->getMessage(),
                'plan_id' => $plan->id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('subscriptions.show', $plan)
                ->with('error', 'There was an error processing your payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Create a one-time payment for lifetime subscription
     */
    private function createOneTimePayment($provider, $plan)
    {
        $amountWithTax = $plan->price * 1.1; // Assuming 10% tax
        return $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('subscription.success'),
                "cancel_url" => route('subscription.cancel'),
                "brand_name" => "Master Magical Key",
                "landing_page" => "BILLING",
                "user_action" => "PAY_NOW",
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $plan->currency,
                        "value" => number_format($amountWithTax, 2, '.', '')
                    ],
                    "description" => "Lifetime access to Master Magical Key"
                ]
            ]
        ]);
    }

    /**
     * Create a product in PayPal for subscription
     */
    private function createProduct($provider, $plan)
    {
        try {
            // Log attempt to create product
            Log::info('Attempting to create PayPal product', [
                'plan_name' => $plan->name,
                'plan_id' => $plan->id
            ]);

            $productData = [
                'name' => $plan->name,
                'description' => $plan->description,
                'type' => 'SERVICE'
            ];
            
            // Log the payload
            Log::debug('PayPal product creation payload', $productData);
            
            $response = $provider->createProduct($productData);
            
            // Log successful response
            Log::info('PayPal product created successfully', [
                'product_id' => $response['id'] ?? 'ID not found',
                'plan_id' => $plan->id
            ]);
            
            return $response;
        } catch (\Exception $e) {
            // Enhanced error logging
            Log::error('Failed to create PayPal product', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'plan_id' => $plan->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if we got a response from PayPal
            if (method_exists($e, 'getResponse')) {
                $responseBody = $e->getResponse()->getBody();
                Log::error('PayPal API error response', [
                    'response_body' => (string)$responseBody
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Create a subscription plan in PayPal
     */
    private function createSubscriptionPlan($provider, $productId, $plan)
    {
        $amountWithTax = $plan->price * 1.1; // Assuming 10% tax
        try {
            // Log attempt to create subscription plan
            Log::info('Attempting to create PayPal billing plan', [
                'product_id' => $productId,
                'plan_name' => $plan->name,
                'plan_id' => $plan->id
            ]);
            
            $planData = [
                'product_id' => $productId,
                'name' => $plan->name,
                'billing_cycles' => [
                    [
                        'frequency' => [
                            'interval_unit' => strtoupper($plan->billing_interval),
                            'interval_count' => 1
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0, // Infinite
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => number_format($amountWithTax, 2, '.', ''),
                                'currency_code' => $plan->currency
                            ]
                        ]
                    ]
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee' => [
                        'value' => '0',
                        'currency_code' => $plan->currency
                    ],
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3
                ]
            ];
            
            // Log the payload
            Log::debug('PayPal billing plan creation payload', $planData);
            
            $response = $provider->createPlan($planData);
            
            // Log successful response
            Log::info('PayPal billing plan created successfully', [
                'plan_id' => $response['id'] ?? 'ID not found',
                'db_plan_id' => $plan->id
            ]);
            
            return $response;
        } catch (\Exception $e) {
            // Enhanced error logging
            Log::error('Failed to create PayPal billing plan', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'product_id' => $productId,
                'plan_id' => $plan->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if we got a response from PayPal
            if (method_exists($e, 'getResponse')) {
                $responseBody = $e->getResponse()->getBody();
                Log::error('PayPal API error response', [
                    'response_body' => (string)$responseBody
                ]);
            }
            
            throw $e;
        }
    }
    
    /**
     * Create a recurring subscription
     */
    private function createSubscription($provider, $plan)
    {
        try {
            // First, create a product in PayPal
            $productResponse = $this->createProduct($provider, $plan);
            
            // The error is here - productResponse might not have an 'id'
            // Let's handle it gracefully
            if (!isset($productResponse['id'])) {
                Log::error('Invalid product response from PayPal', [
                    'response' => $productResponse
                ]);
                throw new \Exception('Failed to create product in PayPal: No product ID returned');
            }
            
            // Create a billing plan using the product
            $planResponse = $this->createSubscriptionPlan($provider, $productResponse['id'], $plan);
            
            // Again, check if we got a valid ID
            if (!isset($planResponse['id'])) {
                Log::error('Invalid billing plan response from PayPal', [
                    'response' => $planResponse
                ]);
                throw new \Exception('Failed to create billing plan in PayPal: No plan ID returned');
            }
            
            // Log attempt to create subscription
            Log::info('Attempting to create PayPal subscription', [
                'paypal_plan_id' => $planResponse['id'],
                'db_plan_id' => $plan->id
            ]);
            
            $subscriptionData = [
                'plan_id' => $planResponse['id'],
                'application_context' => [
                    'brand_name' => 'Master Magical Key',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => route('subscription.success'),
                    'cancel_url' => route('subscription.cancel')
                ],
                'subscriber' => [
                    'name' => [
                        'given_name' => Auth::user()->name,
                        'surname' => '' // You may need to add a surname field to your users table
                    ],
                    'email_address' => Auth::user()->email
                ]
            ];
            
            // Log the payload
            Log::debug('PayPal subscription creation payload', $subscriptionData);
            
            $response = $provider->createSubscription($subscriptionData);
            
            // Log successful response
            Log::info('PayPal subscription created successfully', [
                'subscription_id' => $response['id'] ?? 'ID not found',
                'db_plan_id' => $plan->id
            ]);
            
            return $response;
        } catch (\Exception $e) {
            // Enhanced error logging
            Log::error('Failed to create PayPal subscription', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'plan_id' => $plan->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if we got a response from PayPal
            if (method_exists($e, 'getResponse')) {
                $responseBody = $e->getResponse()->getBody();
                Log::error('PayPal API error response', [
                    'response_body' => (string)$responseBody
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Handle extension for canceled subscriptions that still have active access
     */
    public function extend(Request $request, UserSubscription $subscription)
    {
        // Verify the subscription belongs to the current user
        if ($subscription->user_id !== Auth::id()) {
            return redirect()->route('subscriptions.manage')
                ->with('error', 'You do not have permission to modify this subscription.');
        }
        
        // Check if subscription is eligible for extension (must be canceled but not expired)
        if ($subscription->status !== 'canceled' || 
            !$subscription->end_date || 
            $subscription->end_date->isPast()) {
            return redirect()->route('subscriptions.manage')
                ->with('error', 'This subscription is not eligible for extension.');
        }
        
        try {
            
            // Initialize PayPal API
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // Get the plan
            $plan = $subscription->plan;
            
            // Set the start date to one day after the current subscription ends
            // This ensures no double billing for the same period
            $startDate = $subscription->end_date->addDay()->format('Y-m-d\TH:i:s\Z');
            
            // Create a billing plan with the first payment set to happen on the start date
            $planResponse = $provider->createProduct([
                'name' => $plan->name,
                'description' => $plan->description,
                'type' => 'SERVICE'
            ]);
            
            if (!isset($planResponse['id'])) {
                throw new \Exception('Failed to create product in PayPal');
            }
            $amountWithTax = $plan->price * 1.1; // Assuming 10% tax
            $billingPlanResponse = $provider->createPlan([
                'product_id' => $planResponse['id'],
                'name' => $plan->name,
                'billing_cycles' => [
                    [
                        'frequency' => [
                            'interval_unit' => strtoupper($plan->billing_interval),
                            'interval_count' => 1
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0, // Infinite
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => number_format($amountWithTax, 2, '.', ''),
                                'currency_code' => $plan->currency
                            ]
                        ]
                    ]
                ],
                'payment_preferences' => [
                    'auto_bill_outstanding' => true,
                    'setup_fee' => [
                        'value' => '0',
                        'currency_code' => $plan->currency
                    ],
                    'setup_fee_failure_action' => 'CONTINUE',
                    'payment_failure_threshold' => 3
                ],
                'start_date' => $startDate // Set the start date to the day after current subscription ends
            ]);
            
            if (!isset($billingPlanResponse['id'])) {
                throw new \Exception('Failed to create billing plan in PayPal');
            }
            
            // Create subscription with the new plan
            $response = $provider->createSubscription([
                'plan_id' => $billingPlanResponse['id'],
                'application_context' => [
                    'brand_name' => 'Master Magical Key',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => route('subscription.extend.success'),
                    'cancel_url' => route('subscription.extend.cancel')
                ],
                'subscriber' => [
                    'name' => [
                        'given_name' => Auth::user()->name,
                        'surname' => '' // Or add a surname field to users table
                    ],
                    'email_address' => Auth::user()->email
                ],
                'start_time' => $startDate // Set when the subscription should start
            ]);
            
            if (!isset($response['id'])) {
                throw new \Exception('Failed to create subscription in PayPal');
            }
            
            // Store the new subscription ID in session
            $request->session()->put('paypal_subscription_id', $response['id']);
            $request->session()->put('old_subscription_id', $subscription->id);
            $request->session()->put('subscription_start_date', $startDate);
            
            // Find and redirect to PayPal approval URL
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect()->away($link['href']);
                }
            }
            
            return redirect()->route('subscription.manage')
                ->with('error', 'Could not process the extension. Please try again later.');
        } catch (\Exception $e) {
            Log::error('Subscription extension error', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('subscription.manage')
                ->with('error', 'There was an error processing your extension: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful subscription extension
     */
    public function extendSuccess(Request $request)
    {
        $paypalSubId = $request->session()->get('paypal_subscription_id');
        $oldSubId = $request->session()->get('old_subscription_id');
        $startDate = $request->session()->get('subscription_start_date');
        
        if (!$paypalSubId || !$oldSubId || !$startDate) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Extension information not found.');
        }
        
        try {
            // Get old subscription
            $oldSubscription = UserSubscription::find($oldSubId);
            if (!$oldSubscription || $oldSubscription->user_id !== Auth::id()) {
                throw new \Exception('Invalid subscription.');
            }
            
            // Update the old subscription record to use the new PayPal subscription ID
            // This reuses the existing database record instead of creating a new one
            $oldSubscription->paypal_subscription_id = $paypalSubId;
            $oldSubscription->status = 'active';  // Reactivate the subscription
            $oldSubscription->next_billing_date = Carbon::parse($startDate);  // Set to the new start date
            $oldSubscription->save();
            
            // Clear session data
            $request->session()->forget(['paypal_subscription_id', 'old_subscription_id', 'subscription_start_date']);
            
            return redirect()->route('subscription.manage')
                ->with('success', 'Your subscription has been reactivated and will continue after your current paid period ends on ' . 
                    $oldSubscription->end_date->format('F j, Y') . '.');
        } catch (\Exception $e) {
            Log::error('Subscription extension success error', [
                'error' => $e->getMessage(),
                'paypal_subscription_id' => $paypalSubId,
                'old_subscription_id' => $oldSubId
            ]);
            
            return redirect()->route('subscription.manage')
                ->with('error', 'There was an error finalizing your subscription: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle successful subscription
     */
    public function success(Request $request)
    {
        Log::info('Subscription success callback', [
            'request' => $request->all(),
            'user_id' => Auth::id()
        ]);
        $planId = $request->session()->get('subscription_plan_id');
        $plan = SubscriptionPlan::findOrFail($planId);
        
        try {
            // Initialize PayPal API
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            if ($plan->is_lifetime) {
                // Handle one-time payment success
                $orderId = $request->session()->get('paypal_order_id');
                $response = $provider->capturePaymentOrder($orderId);
                
                // Check if payment is completed
                if ($response['status'] === 'COMPLETED') {
                    // Get payment details
                    $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
                    $amount = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null;
                    $currency = $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? null;
                    // Create subscription record
                    $subscription = UserSubscription::create([
                        'user_id' => Auth::id(),
                        'subscription_plan_id' => $plan->id,
                        'status' => 'active',
                        'start_date' => Carbon::now(),
                        'end_date' => null, // Lifetime subscription never ends
                        'next_billing_date' => null, // No billing for lifetime
                        'transaction_id' => $captureId,
                        'amount_paid' => $amount
                    ]);

                    // Create a purchase record for tracking and invoicing
                    $purchase = $this->createPurchaseRecord($subscription, $captureId, $amount, $currency);
                    
                    // Generate and send invoice
                    $this->generateAndSendInvoice($purchase);
                    
                    // Grant access to all training videos for lifetime subscribers
                    $this->grantAccessToAllVideos(Auth::user());
                    
                    // Clear session data
                    $request->session()->forget(['subscription_plan_id', 'paypal_order_id']);
                    
                    return redirect()->route('subscriptions.thankyou', $subscription)
                        ->with('success', 'Your lifetime subscription has been activated successfully!');
                }
            } else {
                // Handle recurring subscription success
                $subscriptionId = $request->session()->get('paypal_subscription_id');
                $response = $provider->showSubscriptionDetails($subscriptionId);
                $amount = $response['billing_info']['last_payment']['amount']['value'] ?? null;
                $currency = $response['billing_info']['last_payment']['amount']['currency_code'] ?? null;
                
                if ($response['status'] === 'ACTIVE') {
                    // Calculate end date for monthly subscription (for display purposes)
                    $endDate = null;
                    if ($plan->billing_interval === 'month') {
                        $endDate = Carbon::now()->addMonth();
                    } elseif ($plan->billing_interval === 'year') {
                        $endDate = Carbon::now()->addYear();
                    }
                    
                    // Create subscription record
                    $subscription = UserSubscription::create([
                        'user_id' => Auth::id(),
                        'subscription_plan_id' => $plan->id,
                        'status' => 'active',
                        'start_date' => Carbon::now(),
                        'end_date' => $endDate,
                        'next_billing_date' => $endDate, // Will be renewed
                        'paypal_subscription_id' => $subscriptionId,
                        'amount_paid' => $amount
                    ]);

                    // Create a purchase record for tracking and invoicing
                    $purchase = $this->createPurchaseRecord($subscription, $subscriptionId, $amount, $plan->currency);
                    
                    // Generate and send invoice
                    $this->generateAndSendInvoice($purchase); 
                    
                    // Clear session data
                    $request->session()->forget(['subscription_plan_id', 'paypal_subscription_id']);
                    
                    return redirect()->route('subscriptions.thankyou', $subscription)
                        ->with('success', 'Your subscription has been activated successfully!');
                }
            }
            
            // If we get here, the subscription wasn't completed
            return redirect()->route('subscriptions.index')
                ->with('error', 'There was an issue with your subscription. Please contact support.');
                
        } catch (\Exception $e) {
            \Log::error('Subscription success callback error', [
                'error' => $e->getMessage(),
                'plan_id' => $planId,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('subscriptions.index')
                ->with('error', 'There was an error processing your subscription: ' . $e->getMessage());
        }
    }
    
    /**
     * Grant access to all training videos for lifetime subscribers
     */
    private function grantAccessToAllVideos($user)
    {
        $videos = TrainingVideo::where('is_published', true)->get();
        
        foreach ($videos as $video) {
            $user->grantVideoAccess($video);
        }
    }
    
    /**
     * Handle cancelled subscription
     */
    public function cancel(Request $request)
    {
        $request->session()->forget(['subscription_plan_id', 'paypal_order_id', 'paypal_subscription_id']);
        
        return redirect()->route('subscriptions.index')
            ->with('info', 'You have cancelled the subscription process.');
    }
    
    /**
     * Show thank you page after successful subscription
     */
    public function thankyou(UserSubscription $subscription)
    {
        // Check if this subscription belongs to the current user
        if ($subscription->user_id !== Auth::id()) {
            return redirect()->route('subscriptions.index');
        }
        
        return view('subscriptions.thankyou', compact('subscription'));
    }
    
    /**
     * Show subscription management page
     */
    public function manage()
    {
        $subscriptions = Auth::user()->subscriptions()->with('plan')->latest()->get();
        
        // Check if user has active lifetime subscription
        $hasLifetimeSubscription = Auth::user()->hasLifetimeSubscription();
        
        return view('subscriptions.manage', compact('subscriptions', 'hasLifetimeSubscription'));
    }
    
    /**
     * Cancel a subscription
     */
    public function cancelSubscription(Request $request, UserSubscription $subscription)
    {
        // Check if this subscription belongs to the current user
        if ($subscription->user_id !== Auth::id()) {
            return redirect()->route('subscription.manage')
                ->with('error', 'You do not have permission to cancel this subscription.');
        }
        
        // Cannot cancel lifetime subscriptions
        if ($subscription->plan->is_lifetime) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Lifetime subscriptions cannot be cancelled.');
        }
        
        try {
            // Initialize PayPal API
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // Cancel subscription in PayPal
            if ($subscription->paypal_subscription_id) {
                $response = $provider->cancelSubscription(
                    $subscription->paypal_subscription_id, 
                    'Cancelled by user request'
                );
            }
            
            // Update local subscription record
            $subscription->status = 'canceled';
            $subscription->end_date = $subscription->next_billing_date; // Will remain active until the end of the current billing period
            $subscription->save();
            
            return redirect()->route('subscription.manage')
                ->with('success', 'Your subscription has been cancelled. You will continue to have access until ' . 
                    $subscription->end_date->format('F j, Y') . '.');
                
        } catch (\Exception $e) {
            \Log::error('Subscription cancellation error', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('subscription.manage')
                ->with('error', 'There was an error cancelling your subscription: ' . $e->getMessage());
        }
    }

    /**
     * Create a purchase record for subscription
     */
    private function createPurchaseRecord($subscription, $transactionId, $amount, $currency)
    {
        // Calculate tax (assuming 10% tax rate)
        $subtotal = round($amount / 1.1, 2);
        $tax = round($amount - $subtotal, 2);
        
        // Create purchase record
        $purchase = Purchase::create([
            'user_id' => $subscription->user_id,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'completed',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'tax_rate' => 10.00, // 10% GST
            'invoice_number' => Purchase::generateInvoiceNumber()
        ]);
        
        // Create purchase item for the subscription
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'subscription_plan_id' => $subscription->plan->id,
            'item_type' => 'subscription',
            'quantity' => 1,
            'price' => $subtotal,
        ]);
        Log::info('Purchase item created', [
            'purchase_id' => $purchase->id,
            'subscription_plan_id' => $subscription->plan->id,
            'user_id' => $subscription->user_id
        ]);
        // Update purchase items to ensure correct item type
        \App\Models\PurchaseItem::whereNotNull('subscription_plan_id')
            ->where('item_type', '!=', 'subscription')
            ->update(['item_type' => 'subscription']);
        
        return $purchase;
    }
    
    /**
     * Generate and send invoice to user's email
     */
    private function generateAndSendInvoice($purchase)
    {
        try {
            // Generate PDF invoice
            $invoiceService = new InvoiceService();
            $pdfData = $invoiceService->generateInvoice($purchase);
            
            // Store PDF data directly in the database
            \DB::statement('UPDATE purchases SET invoice_data = ?, emailed_at = NOW() WHERE id = ?', [
                $pdfData,
                $purchase->id
            ]);
            
            // Refresh the model to get the updated values
            $purchase->refresh();
            
            // Send email with invoice
            Mail::to($purchase->user->email)
                ->send(new InvoiceEmail($purchase, $pdfData));
            
            // Mark as emailed
            $purchase->emailed_at = now();
            $purchase->save();
            
            Log::info('Subscription invoice sent', [
                'purchase_id' => $purchase->id,
                'user_id' => $purchase->user_id,
                'invoice_number' => $purchase->invoice_number
            ]);
            
        } catch (\Exception $e) {
            // Log error but continue with checkout process
            Log::error("Failed to generate/send invoice for subscription purchase #{$purchase->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    } 

    /**
     * Get subscription plans for modal
     */
    public static function getPlansForModal()
    {
        // Get active plans, limit to 3 for the modal
        return SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->limit(3)
            ->get();
    }
}