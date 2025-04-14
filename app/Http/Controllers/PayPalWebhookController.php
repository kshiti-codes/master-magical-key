<?php

namespace App\Http\Controllers;

use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayPalWebhookController extends Controller
{
    /**
     * Handle PayPal webhook events
     */
    public function handleWebhook(Request $request)
    {
        // Get the webhook payload
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;
        
        Log::info('PayPal webhook received', [
            'event_type' => $eventType,
            'webhook_id' => $payload['id'] ?? 'unknown'
        ]);
        
        // Verify the webhook signature (optional but recommended)
        if (!$this->verifyWebhookSignature($request)) {
            Log::warning('Invalid PayPal webhook signature', [
                'headers' => $request->headers->all(),
                'payload' => $payload
            ]);
            return response('Webhook signature verification failed', 400);
        }
        
        // Process based on event type
        try {
            switch ($eventType) {
                case 'PAYMENT.SALE.COMPLETED':
                    return $this->handlePaymentSuccess($payload);
                
                case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                    return $this->handlePaymentFailure($payload);
                
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    return $this->handleSubscriptionCancellation($payload);
                
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                    return $this->handleSubscriptionSuspension($payload);
                
                case 'BILLING.SUBSCRIPTION.UPDATED':
                    return $this->handleSubscriptionUpdate($payload);
                
                case 'BILLING.SUBSCRIPTION.RENEWED':
                    return $this->handleSubscriptionRenewed($payload);
                
                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    return $this->handleSubscriptionExpired($payload);
                
                default:
                    Log::info('Unhandled PayPal webhook event type', ['event_type' => $eventType]);
                    return response('Webhook received but not processed', 200);
            }
        } catch (\Exception $e) {
            Log::error('Error processing PayPal webhook', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Error processing webhook: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Handle successful payment event
     */
    private function handlePaymentSuccess($payload)
    {
        // Extract resource data
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['billing_agreement_id'] ?? null;
        
        if (!$subscriptionId) {
            Log::warning('PayPal payment success webhook missing subscription ID', [
                'payload' => $payload
            ]);
            return response('Missing subscription ID', 400);
        }
        
        // Find the subscription in our database
        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if (!$subscription) {
            Log::warning('PayPal subscription not found in database', [
                'paypal_subscription_id' => $subscriptionId
            ]);
            return response('Subscription not found', 404);
        }
        
        // Update subscription status if needed
        if ($subscription->status !== 'active') {
            $subscription->status = 'active';
        }
        
        // Update next billing date
        $billingPeriod = $subscription->plan->billing_interval;
        $nextBillingDate = null;
        
        if ($billingPeriod === 'month') {
            $nextBillingDate = Carbon::now()->addMonth();
        } elseif ($billingPeriod === 'year') {
            $nextBillingDate = Carbon::now()->addYear();
        }
        
        if ($nextBillingDate) {
            $subscription->next_billing_date = $nextBillingDate;
            $subscription->end_date = $nextBillingDate;
        }
        
        // Save transaction details
        $transaction = $resource['id'] ?? null;
        if ($transaction) {
            $subscription->transaction_id = $transaction;
        }
        
        $subscription->save();
        
        Log::info('Subscription payment successful', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'next_billing_date' => $nextBillingDate ? $nextBillingDate->format('Y-m-d') : null
        ]);
        
        return response('Webhook processed successfully', 200);
    }
    
    /**
     * Handle payment failure event
     */
    private function handlePaymentFailure($payload)
    {
        // Extract resource data
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;
        $paymentId = $resource['id'] ?? null;
        
        if (!$subscriptionId) {
            Log::warning('PayPal payment failure webhook missing subscription ID', [
                'payload' => $payload
            ]);
            return response('Missing subscription ID', 400);
        }
        
        // Find the subscription in our database
        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if (!$subscription) {
            Log::warning('PayPal subscription not found in database', [
                'paypal_subscription_id' => $subscriptionId
            ]);
            return response('Subscription not found', 404);
        }
        
        // Update subscription status to indicate payment failure
        $subscription->status = 'payment_failed';
        
        // Increment failed payment count if you have this field
        if (isset($subscription->failed_payment_count)) {
            $subscription->failed_payment_count += 1;
        }
        
        // Set a grace period ending date (e.g., 7 days from now)
        $subscription->grace_period_ends_at = Carbon::now()->addDays(7);
        
        // Record the last failed payment ID if you have this field
        if (isset($subscription->last_failed_payment_id)) {
            $subscription->last_failed_payment_id = $paymentId;
        }
        
        $subscription->save();
        
        Log::info('Subscription payment failed', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'failed_count' => $subscription->failed_payment_count ?? 1
        ]);
        
        // Notify the user about the payment failure
        try {
            $user = $subscription->user;
            Mail::to($user->email)->send(new PaymentFailureNotification($subscription));
            
            // Or using a notification class
            // $user->notify(new SubscriptionPaymentFailed($subscription));
        } catch (\Exception $e) {
            Log::error('Failed to send payment failure notification', [
                'error' => $e->getMessage(),
                'user_id' => $subscription->user_id
            ]);
        }

        
        // Notify admin
        try {
            // Mail::to(config('app.admin_email'))->send(new AdminPaymentFailureAlert($subscription));
        } catch (\Exception $e) {
            Log::error('Failed to send admin payment failure alert', [
                'error' => $e->getMessage()
            ]);
        }
        
        return response('Webhook processed successfully', 200);
    }
    
    /**
     * Handle subscription cancellation event
     */
    private function handleSubscriptionCancellation($payload)
    {
        // Extract resource data
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;
        
        if (!$subscriptionId) {
            Log::warning('PayPal cancellation webhook missing subscription ID', [
                'payload' => $payload
            ]);
            return response('Missing subscription ID', 400);
        }
        
        // Find the subscription in our database
        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if (!$subscription) {
            Log::warning('PayPal subscription not found in database', [
                'paypal_subscription_id' => $subscriptionId
            ]);
            return response('Subscription not found', 404);
        }
        
        // Update subscription status
        $subscription->status = 'canceled';
        $subscription->save();
        
        Log::info('Subscription cancelled', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id
        ]);
        
        return response('Webhook processed successfully', 200);
    }
    
    /**
     * Handle subscription suspension event
     */
    private function handleSubscriptionSuspension($payload)
    {
        // Extract resource data
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;
        
        if (!$subscriptionId) {
            Log::warning('PayPal suspension webhook missing subscription ID', [
                'payload' => $payload
            ]);
            return response('Missing subscription ID', 400);
        }
        
        // Find the subscription in our database
        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if (!$subscription) {
            Log::warning('PayPal subscription not found in database', [
                'paypal_subscription_id' => $subscriptionId
            ]);
            return response('Subscription not found', 404);
        }
        
        // Update subscription status
        $subscription->status = 'suspended';
        $subscription->save();
        
        Log::info('Subscription suspended', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id
        ]);
        
        return response('Webhook processed successfully', 200);
    }
    
    /**
     * Handle subscription update event
     */
    private function handleSubscriptionUpdate($payload)
    {
        // Extract resource data
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;
        
        if (!$subscriptionId) {
            Log::warning('PayPal update webhook missing subscription ID', [
                'payload' => $payload
            ]);
            return response('Missing subscription ID', 400);
        }
        
        // Find the subscription in our database
        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if (!$subscription) {
            Log::warning('PayPal subscription not found in database', [
                'paypal_subscription_id' => $subscriptionId
            ]);
            return response('Subscription not found', 404);
        }
        
        // Update subscription details if needed
        // Depending on what changed, you might need to update different fields
        
        Log::info('Subscription updated', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'payload' => $payload
        ]);
        
        return response('Webhook processed successfully', 200);
    }
    
    /**
     * Handle subscription renewed event
     */
    private function handleSubscriptionRenewed($payload)
    {
        // Extract resource data
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;
        
        if (!$subscriptionId) {
            Log::warning('PayPal renewal webhook missing subscription ID', [
                'payload' => $payload
            ]);
            return response('Missing subscription ID', 400);
        }
        
        // Find the subscription in our database
        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if (!$subscription) {
            Log::warning('PayPal subscription not found in database', [
                'paypal_subscription_id' => $subscriptionId
            ]);
            return response('Subscription not found', 404);
        }
        
        // Update next billing date
        $billingPeriod = $subscription->plan->billing_interval;
        $nextBillingDate = null;
        
        if ($billingPeriod === 'month') {
            $nextBillingDate = Carbon::now()->addMonth();
        } elseif ($billingPeriod === 'year') {
            $nextBillingDate = Carbon::now()->addYear();
        }
        
        if ($nextBillingDate) {
            $subscription->next_billing_date = $nextBillingDate;
            $subscription->end_date = $nextBillingDate;
        }
        
        // Ensure status is active
        $subscription->status = 'active';
        $subscription->save();
        
        Log::info('Subscription renewed', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'next_billing_date' => $nextBillingDate ? $nextBillingDate->format('Y-m-d') : null
        ]);
        
        return response('Webhook processed successfully', 200);
    }
    
    /**
     * Handle subscription expired event
     */
    private function handleSubscriptionExpired($payload)
    {
        // Extract resource data
        $resource = $payload['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;
        
        if (!$subscriptionId) {
            Log::warning('PayPal expiration webhook missing subscription ID', [
                'payload' => $payload
            ]);
            return response('Missing subscription ID', 400);
        }
        
        // Find the subscription in our database
        $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
        
        if (!$subscription) {
            Log::warning('PayPal subscription not found in database', [
                'paypal_subscription_id' => $subscriptionId
            ]);
            return response('Subscription not found', 404);
        }
        
        // Update subscription status
        $subscription->status = 'expired';
        $subscription->save();
        
        Log::info('Subscription expired', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id
        ]);
        
        return response('Webhook processed successfully', 200);
    }
    
    /**
     * Verify webhook signature (recommended for security)
     */
    private function verifyWebhookSignature(Request $request)
    {
        try {
            $webhookId = config('paypal.webhook_id');
            
            // For PayPal SDK implementation:
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            $result = $provider->verifyWebhookSignature([
                'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url' => $request->header('PAYPAL-CERT-URL'),
                'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id' => $webhookId,
                'webhook_event' => $request->all()
            ]);
            
            Log::info('Webhook signature verification result', ['result' => $result]);
            
            // Check if verification was successful
            return isset($result['verification_status']) && $result['verification_status'] === 'SUCCESS';
            
        } catch (\Exception $e) {
            Log::error('Error verifying webhook signature', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}