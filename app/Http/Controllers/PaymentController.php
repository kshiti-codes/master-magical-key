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
    public function processCart(Request $request)
    {
        try {
            // Get user's cart
            $cart = Auth::user()->getCart();
            
            // Check if cart is null
            if (!$cart) {
                \Log::error('Cart is null during checkout', [
                    'user_id' => Auth::id()
                ]);
                return redirect()->route('cart.index')
                    ->with('error', 'There was an issue with your cart. Please try again.');
            }
            
            // Check if cart is empty
            if ($cart->items->isEmpty()) {
                return redirect()->route('cart.index')
                    ->with('error', 'Your cart is empty. Please add items before checkout.');
            }
            
            // Calculate prices including GST
            $subtotal = $cart->subtotal;
            $tax = $cart->tax;
            $total = $cart->total;
            
            // Log cart details for debugging
            \Log::info('Cart details before PayPal checkout', [
                'cart_id' => $cart->id,
                'items_count' => $cart->items->count(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ]);
            
            // Prepare PayPal items with null checks
            $paypalItems = [];
            foreach ($cart->items as $item) {
                // Skip any null items
                if (!$item) continue;
                
                $itemSubtotal = $item->price;
                $itemTax = round($itemSubtotal * 0.1, 2);
                
                $itemName = "Unknown Item";
                if ($item->item_type === 'chapter' && $item->chapter) {
                    $itemName = "Chapter {$item->chapter->id}: {$item->chapter->title}";
                } else if ($item->item_type === 'spell' && $item->spell) {
                    $itemName = "Spell: {$item->spell->title}";
                }
                
                $paypalItems[] = [
                    "name" => $itemName,
                    "quantity" => "{$item->quantity}",
                    "category" => "DIGITAL_GOODS",
                    "unit_amount" => [
                        "currency_code" => 'AUD',
                        "value" => number_format($itemSubtotal, 2, '.', '')
                    ],
                    "tax" => [
                        "currency_code" => 'AUD',
                        "value" => number_format($itemTax, 2, '.', '')
                    ]
                ];
            }
            
            // Check if we have PayPal items
            if (empty($paypalItems)) {
                \Log::error('No valid PayPal items found in cart', [
                    'cart_id' => $cart->id
                ]);
                return redirect()->route('cart.index')
                    ->with('error', 'There was an issue with your cart items. Please try again.');
            }
            
            // Initialize PayPal with error handling
            try {
                // Initialize PayPal with detailed error logging
                $provider = new PayPalClient;
                
                // Log the configuration being used (redact sensitive info)
                \Log::info('PayPal configuration', [
                    'mode' => config('paypal.mode'),
                    'client_id_length' => strlen(config('paypal.live.client_id')),
                    'client_secret_length' => strlen(config('paypal.live.client_secret'))
                ]);
                
                $provider->setApiCredentials(config('paypal'));
                
                // Get token with extended error handling
                try {
                    $paypalToken = $provider->getAccessToken();
                    if (!$paypalToken) {
                        throw new \Exception('Empty token response from PayPal');
                    }
                    \Log::info('Successfully obtained PayPal access token');
                } catch (\Exception $tokenEx) {
                    \Log::error('PayPal token retrieval failed', [
                        'error' => $tokenEx->getMessage(),
                        'trace' => $tokenEx->getTraceAsString()
                    ]);
                    throw new \Exception('Failed to get PayPal access token: ' . $tokenEx->getMessage());
                }

                // Add merchant ID for identification
                $merchantId = config('paypal.' . config('paypal.mode') . '.merchant_id');
                if ($merchantId) {
                    \Log::info('Using PayPal with merchant ID', [
                        'merchant_id' => $merchantId,
                        'mode' => config('paypal.mode')
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('PayPal authentication error', [
                    'error' => $e->getMessage()
                ]);
                return redirect()->route('cart.checkout')
                    ->with('error', 'We couldn\'t connect to PayPal. Please try again later.');
            }
            
            // Create PayPal order with proper error handling
            try {
                // Create the order
                $response = $provider->createOrder([
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('payment.success'),
                        "cancel_url" => route('payment.cancel'),
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
                ]);
                
                \Log::info('PayPal order created', [
                    'order_id' => $response['id'] ?? 'No ID found',
                    'status' => $response['status'] ?? 'No status found'
                ]);

                // Validate the response structure
                if (!isset($response['id']) || empty($response['id'])) {
                    throw new \Exception('PayPal order ID not found in response: ' . json_encode($response));
                }
                
                if (!isset($response['status']) || $response['status'] !== 'CREATED') {
                    throw new \Exception('PayPal order not in CREATED status: ' . ($response['status'] ?? 'unknown'));
                }
                
                // Find the approval URL
                $approvalUrl = null;
                if (isset($response['links']) && is_array($response['links'])) {
                    foreach ($response['links'] as $link) {
                        if ($link['rel'] === 'approve') {
                            $approvalUrl = $link['href'];
                            \Log::info('Found PayPal approval URL', ['url' => $approvalUrl]);
                            break;
                        }
                    }
                }
                
                if (!$approvalUrl) {
                    throw new \Exception('PayPal approval URL not found in response');
                }
                
                // Store order ID and cart info in session
                if (isset($response['id']) && $response['id']) {
                    $request->session()->put('paypal_order_id', $response['id']);
                    $request->session()->put('purchase_type', 'cart');
                    $request->session()->put('cart_id', $cart->id);
                    $request->session()->put('subtotal', $subtotal);
                    $request->session()->put('tax', $tax);
                    $request->session()->put('total', $total);
                    
                    // Find and redirect to PayPal approval URL
                    $approvalUrl = null;
                    foreach ($response['links'] as $link) {
                        if ($link['rel'] === 'approve') {
                            $approvalUrl = $link['href'];
                            break;
                        }
                    }
                    
                    if ($approvalUrl) {
                        return redirect()->away($approvalUrl);
                    } else {
                        throw new \Exception('PayPal approval URL not found in response');
                    }
                } else {
                    throw new \Exception('PayPal order ID not found in response');
                }
            } catch (\Exception $e) {
                \Log::error('PayPal order creation failed', [
                    'error' => $e->getMessage(),
                    'response' => $response ?? 'No response'
                ]);
                return redirect()->route('cart.checkout')
                    ->with('error', 'We couldn\'t set up your PayPal payment. Please try again.');
            }
            
        } catch (\Exception $e) {
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
        $purchaseType = $request->session()->get('purchase_type', 'single');
        
        if (!$orderId) {
            \Log::error('PayPal order ID not found in session');
            return redirect()->route('chapters.index')
                ->with('error', 'Payment information not found.');
        }
        
        try {
            // Initialize PayPal
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // Log the order ID we're about to capture
            \Log::info('Attempting to capture PayPal payment', [
                'order_id' => $orderId
            ]);
            
            // Capture payment
            $response = $provider->capturePaymentOrder($orderId);
            
            // Log the full response for debugging
            \Log::info('PayPal capture response', [
                'response' => $response
            ]);
            
            if (!$response) {
                throw new \Exception('Empty response from PayPal capture');
            }
            
            if (!isset($response['status'])) {
                throw new \Exception('Status not found in PayPal response');
            }
            
            if ($response['status'] === 'COMPLETED') {
                // Safely access purchase_units array
                if (!isset($response['purchase_units']) || 
                    !is_array($response['purchase_units']) || 
                    empty($response['purchase_units'])) {
                    throw new \Exception('Purchase units not found in PayPal response');
                }
                
                // Safely access payments array
                $purchaseUnit = $response['purchase_units'][0];
                if (!isset($purchaseUnit['payments']) || 
                    !isset($purchaseUnit['payments']['captures']) || 
                    !is_array($purchaseUnit['payments']['captures']) || 
                    empty($purchaseUnit['payments']['captures'])) {
                    throw new \Exception('Captures not found in PayPal response');
                }
                
                // Safely get capture details
                $capture = $purchaseUnit['payments']['captures'][0];
                $captureId = $capture['id'] ?? null;
                $amount = $capture['amount']['value'] ?? null;
                $currency = $capture['amount']['currency_code'] ?? null;
                
                if (!$captureId || !$amount || !$currency) {
                    throw new \Exception('Missing essential capture details in PayPal response');
                }
                
                // Log successful capture
                \Log::info('Payment capture successful', [
                    'capture_id' => $captureId,
                    'amount' => $amount,
                    'currency' => $currency
                ]);
                
                // Get subtotal and tax from session
                $subtotal = $request->session()->get('subtotal');
                $tax = $request->session()->get('tax');
                $total = $request->session()->get('total');

                // Initialize purchasedItems array
                $purchasedItems = [];
                $purchase = null;

                if ($purchaseType === 'spell') {
                    // Process single spell purchase
                    $spellId = $request->session()->get('spell_id');
                    if (!$spellId) {
                        throw new \Exception('Spell ID not found in session');
                    }
                    
                    $spell = Spell::find($spellId);
                    if (!$spell) {
                        throw new \Exception('Spell not found with ID: ' . $spellId);
                    }
                    
                    // Create purchase record
                    $purchase = Purchase::create([
                        'user_id' => Auth::id(),
                        'transaction_id' => $captureId,
                        'amount' => $amount,
                        'currency' => $currency,
                        'status' => 'completed',
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'tax_rate' => 10.00, // 10% GST
                        'invoice_number' => $this->generateInvoiceNumber()
                    ]);

                    // Create purchase item for the spell
                    \App\Models\PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'spell_id' => $spell->id,
                        'item_type' => 'spell',
                        'quantity' => 1,
                        'price' => $spell->price
                    ]);

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
                    } catch (\Exception $e) {
                        // Log error but continue with checkout process
                        \Log::error("Failed to generate/send invoice for spell purchase #{$purchase->id}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                    
                    // Grant access to the spell
                    Auth::user()->grantSpellAccess($spell);

                    // Set purchased items for view
                    $purchasedItems = [
                        [
                            'spell_id' => $spell->id,
                            'title' => $spell->title,
                            'price' => $spell->price,
                            'quantity' => 1,
                            'type' => 'spell'
                        ]
                    ];
                    
                } else {
                    // Process cart purchase
                    $cartId = $request->session()->get('cart_id');
                    
                    if (!$cartId) {
                        throw new \Exception('Cart ID not found in session');
                    }
                    
                    \Log::info('Processing cart purchase', [
                        'cart_id' => $cartId,
                        'user_id' => Auth::id()
                    ]);
                    
                    $cart = Auth::user()->cart()->find($cartId);
                    
                    if (!$cart) {
                        throw new \Exception('Cart not found with ID: ' . $cartId);
                    }
                    
                    // Create a SINGLE purchase record for the entire order
                    $purchase = Purchase::create([
                        'user_id' => Auth::id(),
                        'transaction_id' => $captureId,
                        'amount' => $total,
                        'currency' => $currency,
                        'status' => 'completed',
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'tax_rate' => 10.00,
                        'invoice_number' => $this->generateInvoiceNumber()
                    ]);
                    
                    \Log::info('Purchase record created', [
                        'purchase_id' => $purchase->id
                    ]);
                    
                    // Process each item in the cart
                    $purchasedItems = [];
                    
                    foreach ($cart->items as $item) {
                        if (!$item) continue;
                        
                        \Log::info('Processing cart item', [
                            'item_id' => $item->id,
                            'item_type' => $item->item_type ?? 'unknown'
                        ]);
                        
                        if ($item->item_type === 'chapter' && $item->chapter) {
                            // Create a purchase item record for chapter
                            \App\Models\PurchaseItem::create([
                                'purchase_id' => $purchase->id,
                                'chapter_id' => $item->chapter->id,
                                'item_type' => 'chapter',
                                'quantity' => $item->quantity,
                                'price' => $item->price
                            ]);
                            
                            // Add to purchased items for display
                            $purchasedItems[] = [
                                'chapter_id' => $item->chapter->id,
                                'title' => $item->chapter->title,
                                'price' => $item->price,
                                'quantity' => $item->quantity,
                                'type' => 'chapter'
                            ];
                            
                            // Grant access to the chapter
                            Auth::user()->chapters()->syncWithoutDetaching([
                                $item->chapter->id => [
                                    'last_read_at' => now(),
                                    'last_page' => 1,
                                ]
                            ]);
                            
                            // Also grant access to any free spells that come with this chapter
                            $freeSpells = $item->chapter->freeSpells ?? collect();
                            foreach ($freeSpells as $spell) {
                                Auth::user()->grantSpellAccess($spell);
                                
                                // Add free spells to purchased items for display
                                $purchasedItems[] = [
                                    'spell_id' => $spell->id,
                                    'title' => $spell->title,
                                    'price' => 0, // Free with chapter
                                    'quantity' => 1,
                                    'type' => 'spell',
                                    'free_with_chapter' => true,
                                    'chapter_id' => $item->chapter->id
                                ];
                            }
                        } else if ($item->item_type === 'spell' && $item->spell) {
                            // Create a purchase item record for spell
                            \App\Models\PurchaseItem::create([
                                'purchase_id' => $purchase->id,
                                'spell_id' => $item->spell->id,
                                'item_type' => 'spell',
                                'quantity' => $item->quantity,
                                'price' => $item->price
                            ]);
                            
                            // Add to purchased items for display
                            $purchasedItems[] = [
                                'spell_id' => $item->spell->id,
                                'title' => $item->spell->title,
                                'price' => $item->price,
                                'quantity' => $item->quantity,
                                'type' => 'spell'
                            ];
                            
                            // Grant access to the spell
                            Auth::user()->grantSpellAccess($item->spell);
                        }
                    }
                    \App\Models\PurchaseItem::whereNotNull('spell_id')
                        ->where('item_type', '!=', 'spell')
                        ->update(['item_type' => 'spell']);
                    // Generate a SINGLE invoice for the entire purchase
                    try {
                        // Generate PDF invoice
                        $invoiceService = new InvoiceService();
                        $pdfData = $invoiceService->generateInvoice($purchase);
                        
                        // Store PDF data
                        \DB::statement('UPDATE purchases SET invoice_data = ?, emailed_at = NOW() WHERE id = ?', [
                            $pdfData,
                            $purchase->id
                        ]);
                        
                        // Refresh the model to get the updated values
                        $purchase->refresh();
                        
                        // Send ONE email with the consolidated invoice
                        Mail::to($purchase->user->email)
                            ->send(new InvoiceEmail($purchase, $pdfData));
                            
                        // Mark as emailed
                        $purchase->emailed_at = now();
                        $purchase->save();
                    } catch (\Exception $e) {
                        // Log error but continue with checkout process
                        \Log::error("Failed to generate/send consolidated invoice for purchase #{$purchase->id}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                    
                    // Clear the cart
                    $cart->clear();
                }
                
                // Clear the session data
                $request->session()->forget([
                    'paypal_order_id', 
                    'purchase_type', 
                    'chapter_id',
                    'spell_id',
                    'cart_id',
                    'subtotal',
                    'tax',
                    'total'
                ]);
                
                \Log::info('Ready to render success page', [
                    'purchase_id' => $purchase->id ?? 'No purchase ID',
                    'items_count' => count($purchasedItems)
                ]);
                
                // Ensure variables are set before passing to view
                $subtotal = $subtotal ?? 0;
                $tax = $tax ?? 0;
                $total = $total ?? 0;
                
                // Return success view
                return view('payment.success', compact('purchase', 'subtotal', 'tax', 'purchasedItems', 'total'));
            }
            
            // If payment not completed
            throw new \Exception('Payment was not completed. Status: ' . ($response['status'] ?? 'unknown'));
            
        } catch (\Exception $e) {
            \Log::error('PayPal capture error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId
            ]);
            
            return redirect()->route('chapters.index')
                ->with('error', 'An error occurred while processing your payment: ' . $e->getMessage());
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
                'client_secret' => config('paypal.live.client_secret'),
                'merchant_id' => config('paypal.live.merchant_id')
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