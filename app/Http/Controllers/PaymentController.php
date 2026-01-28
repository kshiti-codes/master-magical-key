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
                if($item->item_type === 'product' && $item->product) {
                    $itemName = "Product: {$item->product->title}";
                } else if ($item->item_type === 'chapter' && $item->chapter) {
                    $itemName = "Chapter {$item->chapter->id}: {$item->chapter->title}";
                } else if ($item->item_type === 'spell' && $item->spell) {
                    $itemName = "Spell: {$item->spell->title}";
                } else if ($item->item_type === 'video' && $item->video) {
                    $itemName = "Training Video: {$item->video->title}";
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
        
        if (!$orderId) {
            \Log::error('PayPal order ID not found in session');
            return redirect()->route('products')
                ->with('error', 'Payment information not found.');
        }
        
        try {
            // Initialize PayPal and capture payment
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            \Log::info('Attempting to capture PayPal payment', ['order_id' => $orderId]);
            $response = $provider->capturePaymentOrder($orderId);
            
            if (!$response || !isset($response['status']) || $response['status'] !== 'COMPLETED') {
                throw new \Exception('Payment not completed. Status: ' . ($response['status'] ?? 'unknown'));
            }
            
            // Extract payment details
            $captureId = $this->extractCaptureId($response);
            $amount = $this->extractAmount($response);
            $currency = $this->extractCurrency($response);
            
            // Get stored values from session
            $subtotal = $request->session()->get('subtotal');
            $tax = $request->session()->get('tax');
            $total = $request->session()->get('total');
            $cartId = $request->session()->get('cart_id');
            
            if (!$cartId) {
                throw new \Exception('Cart ID not found in session');
            }
            
            // Process cart purchase
            list($purchase, $purchasedItems) = $this->processCartPurchase($cartId, $captureId, $amount, $currency, $subtotal, $tax, $total);
            
            // Clear session data
            $request->session()->forget([
                'paypal_order_id', 'purchase_type', 'product_id', 'chapter_id', 'spell_id', 
                'training_video_id', 'cart_id', 'subtotal', 'tax', 'total'
            ]);
            
            // Return success view
            return view('payment.success', compact('purchase', 'subtotal', 'tax', 'purchasedItems', 'total'));
            
        } catch (\Exception $e) {
            \Log::error('PayPal capture error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $orderId
            ]);
            
            return redirect()->route('products')
                ->with('error', 'An error occurred while processing your payment: ' . $e->getMessage());
        }
    }

    /**
     * Extract capture ID from PayPal response
     */
    private function extractCaptureId($response)
    {
        if (!isset($response['purchase_units'][0]['payments']['captures'][0]['id'])) {
            throw new \Exception('Capture ID not found in PayPal response');
        }
        
        return $response['purchase_units'][0]['payments']['captures'][0]['id'];
    }

    /**
     * Extract amount from PayPal response
     */
    private function extractAmount($response)
    {
        if (!isset($response['purchase_units'][0]['payments']['captures'][0]['amount']['value'])) {
            throw new \Exception('Amount not found in PayPal response');
        }
        
        return $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
    }

    /**
     * Extract currency from PayPal response
     */
    private function extractCurrency($response)
    {
        if (!isset($response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'])) {
            throw new \Exception('Currency not found in PayPal response');
        }
        
        return $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
    }

    /**
     * Process cart purchase
     */
    private function processCartPurchase($cartId, $captureId, $amount, $currency, $subtotal, $tax, $total)
    {
        $cart = Auth::user()->cart()->findOrFail($cartId);
        
        // Create purchase record
        $purchase = $this->createPurchaseRecord(Auth::id(), $captureId, $total, $currency, $subtotal, $tax);
        
        // Process each item and build purchasedItems array
        $purchasedItems = [];
        
        foreach ($cart->items as $item) {
            if (!$item) continue;
            if($item->item_type === 'product' && $item->product) {
                $this->processProductCartItem($purchase, $item, $purchasedItems);
            }
            else if ($item->item_type === 'chapter' && $item->chapter) {
                $this->processChapterPurchase($purchase, $item, $purchasedItems);
            } else if ($item->item_type === 'spell' && $item->spell) {
                $this->processSpellCartItem($purchase, $item, $purchasedItems);
            } else if ($item->item_type === 'video' && $item->video) {
                $this->processVideoCartItem($purchase, $item, $purchasedItems);
            }
        }
        
        // Ensure all purchase items have correct item_type
        \App\Models\PurchaseItem::whereNotNull('spell_id')
            ->where('item_type', '!=', 'spell')
            ->update(['item_type' => 'spell']);
        
        // Generate and send invoice
        $this->generateAndSendInvoice($purchase);
        
        // Clear the cart
        $cart->clear();
        
        return [$purchase, $purchasedItems];
    }

    private function processProductCartItem($purchase, $item, &$purchasedItems)
    {
        // Create purchase item
        \App\Models\PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $item->product->id,
            'item_type' => 'product',
            'quantity' => $item->quantity,
            'price' => $item->price
        ]);
        
        // Add to purchased items
        $purchasedItems[] = [
            'product_id' => $item->product->id,
            'title' => $item->product->title,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'type' => 'product'
        ];
        
        // Grant access to product if applicable
        Auth::user()->products()->syncWithoutDetaching([$item->product->id]);
    }

    /**
     * Process chapter purchase from cart
     */
    private function processChapterPurchase($purchase, $item, &$purchasedItems)
    {
        // Create purchase item
        \App\Models\PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'chapter_id' => $item->chapter->id,
            'item_type' => 'chapter',
            'quantity' => $item->quantity,
            'price' => $item->price
        ]);
        
        // Add to purchased items
        $purchasedItems[] = [
            'chapter_id' => $item->chapter->id,
            'title' => $item->chapter->title,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'type' => 'chapter'
        ];
        
        // Grant access to chapter
        Auth::user()->chapters()->syncWithoutDetaching([
            $item->chapter->id => [
                'last_read_at' => now(),
                'last_page' => 1,
            ]
        ]);
    }

    /**
     * Process spell cart item
     */
    private function processSpellCartItem($purchase, $item, &$purchasedItems)
    {
        // Create purchase item
        \App\Models\PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'spell_id' => $item->spell->id,
            'item_type' => 'spell',
            'quantity' => $item->quantity,
            'price' => $item->price
        ]);
        
        // Add to purchased items
        $purchasedItems[] = [
            'spell_id' => $item->spell->id,
            'title' => $item->spell->title,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'type' => 'spell'
        ];
        
        // Grant access
        Auth::user()->grantSpellAccess($item->spell);
    }

    /**
     * Process video cart item
     */
    private function processVideoCartItem($purchase, $item, &$purchasedItems)
    {
        // Create purchase item
        \App\Models\PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'training_video_id' => $item->video->id,
            'item_type' => 'video',
            'quantity' => $item->quantity,
            'price' => $item->price
        ]);
        
        // Add to purchased items
        $purchasedItems[] = [
            'training_video_id' => $item->video->id,
            'title' => $item->video->title,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'type' => 'video'
        ];
        
        // Grant access
        Auth::user()->grantVideoAccess($item->video);
    }

    /**
     * Create purchase record
     */
    private function createPurchaseRecord($userId, $transactionId, $amount, $currency, $subtotal, $tax)
    {
        return Purchase::create([
            'user_id' => $userId,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'completed',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'tax_rate' => 10.00,
            'invoice_number' => $this->generateInvoiceNumber()
        ]);
    }

    /**
     * Generate and send invoice
     */
    private function generateAndSendInvoice($purchase)
    {
        try {
            $invoiceService = new InvoiceService();
            $pdfData = $invoiceService->generateInvoice($purchase);
            
            // Store PDF data
            \DB::statement('UPDATE purchases SET invoice_data = ?, emailed_at = NOW() WHERE id = ?', [
                $pdfData,
                $purchase->id
            ]);
            
            // Refresh model and send email
            $purchase->refresh();
            Mail::to($purchase->user->email)->send(new InvoiceEmail($purchase, $pdfData));
            
            // Mark as emailed
            $purchase->emailed_at = now();
            $purchase->save();
        } catch (\Exception $e) {
            \Log::error("Failed to generate/send invoice for purchase #{$purchase->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
        $request->session()->forget(['paypal_order_id', 'product_id', 'chapter_id', 'spell_id', 'training_video_id', 'cart_id', 'subtotal', 'tax', 'total']);
        
        return redirect()->route('products')
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