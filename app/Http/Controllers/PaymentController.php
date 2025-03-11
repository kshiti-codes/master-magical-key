<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Purchase;
use App\Services\InvoiceService;
use App\Mail\InvoiceEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentController extends Controller
{
    /**
     * Process payment with PayPal for cart checkout.
     */
    public function processCart(Request $request)
    {
        try {
            // Get user's cart
            $cart = Auth::user()->getCart();
            
            // Check if cart is empty
            if ($cart->items->isEmpty()) {
                return redirect()->route('cart.index')
                    ->with('error', 'Your cart is empty. Please add chapters before checkout.');
            }
            
            // Log the start of payment processing
            \Log::info('Starting PayPal cart checkout process', [
                'user_id' => Auth::id(),
                'cart_items' => $cart->items->count()
            ]);
            
            // Calculate prices including GST
            $subtotal = $cart->subtotal;
            $tax = $cart->tax;
            $total = $cart->total;
            
            // Initialize PayPal
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();
            
            // Log PayPal token status
            \Log::info('PayPal access token', [
                'success' => !empty($paypalToken)
            ]);
            
            // Prepare payment data
            $returnUrl = route('payment.success');
            $cancelUrl = route('payment.cancel');
            
            // Prepare items for PayPal
            $paypalItems = [];
            foreach ($cart->items as $item) {
                $itemSubtotal = $item->price;
                $itemTax = round($itemSubtotal * 0.1, 2);
                
                $paypalItems[] = [
                    "name" => "Chapter {$item->chapter->id}: {$item->chapter->title}",
                    "quantity" => "{$item->quantity}",
                    "category" => "DIGITAL_GOODS",
                    "unit_amount" => [
                        "currency_code" => 'AUD',
                        "value" => $itemSubtotal
                    ],
                    "tax" => [
                        "currency_code" => 'AUD',
                        "value" => $itemTax
                    ]
                ];
            }
            
            // Create PayPal order payload
            $payload = [
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => $returnUrl,
                    "cancel_url" => $cancelUrl,
                    "brand_name" => "Master Magical Key",
                    "landing_page" => "BILLING",
                    "user_action" => "PAY_NOW",
                ],
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => 'AUD',
                            "value" => number_format($total, 2, '.', ''),
                            "breakdown" => [
                                "item_total" => [
                                    "currency_code" => 'AUD',
                                    "value" => number_format($subtotal, 2, '.', '')
                                ],
                                "tax_total" => [
                                    "currency_code" => 'AUD',
                                    "value" => number_format($tax, 2, '.', '')
                                ]
                            ]
                        ],
                        "items" => $paypalItems
                    ]
                ]
            ];
            
            // Log the PayPal order request
            \Log::info('PayPal create order request', [
                'payload' => json_encode($payload)
            ]);
            
            // Create PayPal order
            $response = $provider->createOrder($payload);
            
            // Log the PayPal order response
            \Log::info('PayPal create order response', [
                'response' => json_encode($response)
            ]);
            
            // Store order ID and cart info in session
            if (isset($response['id']) && $response['id']) {
                $request->session()->put('paypal_order_id', $response['id']);
                $request->session()->put('purchase_type', 'cart');
                $request->session()->put('cart_id', $cart->id);
                $request->session()->put('subtotal', $subtotal);
                $request->session()->put('tax', $tax);
                $request->session()->put('total', $total);
                
                // Log session data
                \Log::info('Stored PayPal session data', [
                    'order_id' => $response['id'],
                    'cart_id' => $cart->id
                ]);
                
                // Find and redirect to PayPal approval URL
                $approvalUrl = null;
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approvalUrl = $link['href'];
                        break;
                    }
                }
                
                if ($approvalUrl) {
                    \Log::info('Redirecting to PayPal', [
                        'url' => $approvalUrl
                    ]);
                    return redirect()->away($approvalUrl);
                } else {
                    throw new \Exception('PayPal approval URL not found in response');
                }
            }
            
            // If something went wrong
            throw new \Exception('Failed to create PayPal order: ' . json_encode($response));
            
        } catch (\Exception $e) {
            // Log detailed error information
            \Log::error('PayPal cart checkout error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'cart_id' => $cart->id ?? null
            ]);
            
            return redirect()->route('cart.checkout')
                ->with('error', 'Something went wrong with PayPal: ' . $e->getMessage());
        }
    }
        
    /**
     * Handle success callback from PayPal
     */
    public function success(Request $request)
    {
        // Get order ID from session
        $orderId = $request->session()->get('paypal_order_id');
        
        if (!$orderId) {
            return redirect()->route('chapters.index')
                ->with('error', 'Payment information not found.');
        }
        
        try {
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
                // Get subtotal and tax from session
                $subtotal = $request->session()->get('subtotal');
                $tax = $request->session()->get('tax');
                $total = $request->session()->get('total');

                // Process cart purchase - CONSOLIDATED APPROACH
                $cartId = $request->session()->get('cart_id');
                $cart = Auth::user()->cart()->findOrFail($cartId);
                
                // Create a SINGLE purchase record for the entire order
                $purchase = Purchase::create([
                    'user_id' => Auth::id(),
                    'transaction_id' => $captureId,
                    'amount' => $total, // Total amount from session (all items)
                    'currency' => $currency,
                    'status' => 'completed',
                    'subtotal' => $subtotal, // Subtotal from session
                    'tax' => $tax, // Tax from session
                    'tax_rate' => 10.00,
                    'invoice_number' => $this->generateInvoiceNumber()
                ]);
                
                // Prepare items data for display on success page
                $purchasedItems = [];
                
                // Process each item in the cart - create purchase items, NOT purchases
                foreach ($cart->items as $item) {
                    // Create a purchase item record associated with the main purchase
                    \App\Models\PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'chapter_id' => $item->chapter->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price
                    ]);
                    
                    // Add to purchased items for display
                    $purchasedItems[] = [
                        'chapter_id' => $item->chapter->id,
                        'title' => $item->chapter->title,
                        'price' => $item->price,
                        'quantity' => $item->quantity
                    ];
                    
                    // Grant access to the chapter
                    Auth::user()->chapters()->syncWithoutDetaching([
                        $item->chapter->id => [
                            'last_read_at' => now(),
                            'last_page' => 1,
                        ]
                    ]);
                }
                
                // Generate a SINGLE invoice for the entire purchase
                try {
                    // Generate PDF invoice
                    $invoiceService = new InvoiceService();
                    $pdfData = $invoiceService->generateInvoice($purchase);
                    
                    // Store PDF data directly in the database using a raw query
                    \DB::statement('UPDATE purchases SET invoice_data = ?, emailed_at = NOW() WHERE id = ?', [
                        $pdfData,
                        $purchase->id
                    ]);
                    
                    // Refresh the model to get the updated values
                    $purchase->refresh();
                    
                    // Send ONE email with the consolidated invoice
                    Mail::to($purchase->user->email)
                        ->send(new InvoiceEmail($purchase, $pdfData));
                    
                    // Log success
                    \Log::info("Consolidated invoice generated and emailed for purchase #{$purchase->id}");
                } catch (\Exception $e) {
                    // Log error but continue with checkout process
                    \Log::error("Failed to generate/send consolidated invoice for purchase #{$purchase->id}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                // Clear the cart
                $cart->clear(); 
                    
                
                // Clear the session data
                $request->session()->forget([
                    'paypal_order_id', 
                    'purchase_type', 
                    'chapter_id', 
                    'cart_id',
                    'subtotal',
                    'tax',
                    'total'
                ]);

                // Prepare data for the success page
                 return view('payment.success', compact('purchase', 'subtotal', 'tax', 'purchasedItems', 'total'));
                
                // Redirect to success page - use the last created purchase
                // return view('payment.success', compact('purchase'));
            }
            
            // If payment not completed
            return redirect()->route('chapters.index')
                ->with('error', 'Payment was not successful. Please try again.');
                
        } catch (\Exception $e) {
            \Log::error('PayPal capture error', [
                'error' => $e->getMessage(),
                'order_id' => $orderId
            ]);
            
            return redirect()->route('chapters.index')
                ->with('error', 'An error occurred while processing your payment. Please contact support.');
        }
    }

    /**
     * Generate a unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $timestamp = date('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        
        return "{$prefix}-{$timestamp}-{$random}";
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