<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentController extends Controller
{
    /**
     * Show checkout page
     */
    public function checkout(Chapter $chapter)
    {
        return view('payment.checkout', compact('chapter'));
    }
    
    /**
     * Process payment with PayPal
     */
    public function process(Request $request)
    {
        // Validate request
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
        ]);
        
        $chapter = Chapter::findOrFail($request->chapter_id);
        
        // Initialize PayPal
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        
        // Prepare payment data
        $returnUrl = route('payment.success');
        $cancelUrl = route('payment.cancel');
        
        $items = [
            [
                'name' => "Chapter {$chapter->id}: {$chapter->title}",
                'price' => $chapter->price,
                'desc' => substr($chapter->description, 0, 100),
                'qty' => 1
            ]
        ];
        
        $total = $chapter->price;
        
        // Create PayPal order
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => $returnUrl,
                "cancel_url" => $cancelUrl,
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $chapter->currency ?? 'AUD',
                        "value" => $total,
                        "breakdown" => [
                            "item_total" => [
                                "currency_code" => $chapter->currency ?? 'AUD',
                                "value" => $total
                            ]
                        ]
                    ],
                    "items" => [
                        [
                            "name" => "Chapter {$chapter->id}: {$chapter->title}",
                            "quantity" => "1",
                            "category" => "DIGITAL_GOODS",
                            "unit_amount" => [
                                "currency_code" => $chapter->currency ?? 'AUD',
                                "value" => $chapter->price
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        
        // Store order ID in session
        if (isset($response['id']) && $response['id']) {
            $request->session()->put('paypal_order_id', $response['id']);
            $request->session()->put('chapter_id', $chapter->id);
            
            // Redirect to PayPal checkout
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect($link['href']);
                }
            }
        }
        
        // If something went wrong
        return redirect()->route('chapters.index')
            ->with('error', 'Something went wrong with PayPal. Please try again later.');
    }
    
    /**
     * Handle success callback from PayPal
     */
    public function success(Request $request)
    {
        // Get order ID from session
        $orderId = $request->session()->get('paypal_order_id');
        $chapterId = $request->session()->get('chapter_id');
        
        if (!$orderId || !$chapterId) {
            return redirect()->route('chapters.index')
                ->with('error', 'Payment information not found.');
        }
        
        // Get chapter
        $chapter = Chapter::findOrFail($chapterId);
        
        // Initialize PayPal
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        
        // Capture payment
        $response = $provider->capturePaymentOrder($orderId);
        
        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            // Get transaction details
            $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'];
            $amount = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
            $currency = $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
            
            // Create purchase record
            $purchase = Purchase::create([
                'user_id' => Auth::id(),
                'chapter_id' => $chapter->id,
                'transaction_id' => $captureId,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'completed'
            ]);
            
            // Grant access to the chapter
            Auth::user()->chapters()->syncWithoutDetaching([
                $chapter->id => [
                    'last_read_at' => now(),
                    'last_page' => 1,
                ]
            ]);
            
            // Clear the session data
            $request->session()->forget(['paypal_order_id', 'chapter_id']);
            
            // Redirect to success page
            return view('payment.success', compact('purchase'));
        }
        
        // If payment not completed
        return redirect()->route('chapters.index')
            ->with('error', 'Payment was not successful. Please try again.');
    }
    
    /**
     * Handle payment cancellation
     */
    public function cancel(Request $request)
    {
        $request->session()->forget(['paypal_order_id', 'chapter_id']);
        
        return redirect()->route('chapters.index')
            ->with('info', 'Payment was cancelled.');
    }

    private function getPayPalEnvironment()
    {
        if (app()->environment('production')) {
            return [
                'mode' => 'live',
                'client_id' => config('paypal.live.client_id'),
                'client_secret' => config('paypal.live.client_secret')
            ];
        } else {
            return [
                'mode' => 'sandbox',
                'client_id' => config('paypal.sandbox.client_id'),
                'client_secret' => config('paypal.sandbox.client_secret')
            ];
        }
    }
}