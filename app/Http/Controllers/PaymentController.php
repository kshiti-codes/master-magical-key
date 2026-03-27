<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Purchase;
use App\Models\Cart;
use App\Services\InvoiceService;
use App\Mail\InvoiceEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PaymentController extends Controller
{
    // DISABLED PAYPAL FOR NOW - STRIPE ONLY
    // public function processCart(Request $request)
    // {
    //     try {
    //         // Get user's cart
    //         $cart = Auth::user()->getCart();
            
    //         // Check if cart is null
    //         if (!$cart) {
    //             \Log::error('Cart is null during checkout', [
    //                 'user_id' => Auth::id()
    //             ]);
    //             return redirect()->route('cart.index')
    //                 ->with('error', 'There was an issue with your cart. Please try again.');
    //         }
            
    //         // Check if cart is empty
    //         if ($cart->items->isEmpty()) {
    //             return redirect()->route('cart.index')
    //                 ->with('error', 'Your cart is empty. Please add items before checkout.');
    //         }
            
    //         // Calculate prices including GST (with promo discount)
    //         $subtotal = $cart->subtotal;
    //         $promoDiscount = min($request->session()->get('promo_discount', 0), $subtotal);
    //         $discountedSubtotal = $subtotal - $promoDiscount;
    //         $tax = round($discountedSubtotal * 0.1, 2);
    //         $total = $discountedSubtotal + $tax;
            
    //         // Log cart details for debugging
    //         \Log::info('Cart details before PayPal checkout', [
    //             'cart_id' => $cart->id,
    //             'items_count' => $cart->items->count(),
    //             'subtotal' => $subtotal,
    //             'tax' => $tax,
    //             'total' => $total
    //         ]);
            
    //         // Prepare PayPal items with null checks
    //         $paypalItems = [];
    //         $taxRatio = ($subtotal > 0 && $promoDiscount > 0) ? $discountedSubtotal / $subtotal : 1.0;
    //         $actualTaxTotal = 0;

    //         foreach ($cart->items as $item) {
    //             // Skip any null items
    //             if (!$item) continue;
                
    //             $itemSubtotal = $item->price;
    //             $itemTax = round($itemSubtotal * 0.1 * $taxRatio, 2);
    //             $actualTaxTotal += $itemTax * $item->quantity;
                
    //             $itemName = "Unknown Item";
    //             if($item->item_type === 'product' && $item->product) {
    //                 $itemName = "Product: {$item->product->title}";
    //             } else if ($item->item_type === 'chapter' && $item->chapter) {
    //                 $itemName = "Chapter {$item->chapter->id}: {$item->chapter->title}";
    //             } else if ($item->item_type === 'spell' && $item->spell) {
    //                 $itemName = "Spell: {$item->spell->title}";
    //             } else if ($item->item_type === 'video' && $item->video) {
    //                 $itemName = "Training Video: {$item->video->title}";
    //             }
                
    //             $paypalItems[] = [
    //                 "name" => $itemName,
    //                 "quantity" => "{$item->quantity}",
    //                 "category" => "DIGITAL_GOODS",
    //                 "unit_amount" => [
    //                     "currency_code" => 'AUD',
    //                     "value" => number_format($itemSubtotal, 2, '.', '')
    //                 ],
    //                 "tax" => [
    //                     "currency_code" => 'AUD',
    //                     "value" => number_format($itemTax, 2, '.', '')
    //                 ]
    //             ];
    //         }

    //         // Recalculate tax/total from adjusted per-item taxes so PayPal validation passes
    //         $tax = round($actualTaxTotal, 2);
    //         $total = $discountedSubtotal + $tax;
            
    //         // Check if we have PayPal items
    //         if (empty($paypalItems)) {
    //             \Log::error('No valid PayPal items found in cart', [
    //                 'cart_id' => $cart->id
    //             ]);
    //             return redirect()->route('cart.index')
    //                 ->with('error', 'There was an issue with your cart items. Please try again.');
    //         }
            
    //         // Initialize PayPal with error handling
    //         try {
    //             // Initialize PayPal with detailed error logging
    //             $provider = new PayPalClient;
                
    //             // Log the configuration being used (redact sensitive info)
    //             \Log::info('PayPal configuration', [
    //                 'mode' => config('paypal.mode'),
    //                 'client_id_length' => strlen(config('paypal.live.client_id')),
    //                 'client_secret_length' => strlen(config('paypal.live.client_secret'))
    //             ]);
                
    //             $provider->setApiCredentials(config('paypal'));
                
    //             // Get token with extended error handling
    //             try {
    //                 $paypalToken = $provider->getAccessToken();
    //                 if (!$paypalToken) {
    //                     throw new \Exception('Empty token response from PayPal');
    //                 }
    //                 \Log::info('Successfully obtained PayPal access token');
    //             } catch (\Exception $tokenEx) {
    //                 \Log::error('PayPal token retrieval failed', [
    //                     'error' => $tokenEx->getMessage(),
    //                     'trace' => $tokenEx->getTraceAsString()
    //                 ]);
    //                 throw new \Exception('Failed to get PayPal access token: ' . $tokenEx->getMessage());
    //             }

    //             // Add merchant ID for identification
    //             $merchantId = config('paypal.' . config('paypal.mode') . '.merchant_id');
    //             if ($merchantId) {
    //                 \Log::info('Using PayPal with merchant ID', [
    //                     'merchant_id' => $merchantId,
    //                     'mode' => config('paypal.mode')
    //                 ]);
    //             }
    //         } catch (\Exception $e) {
    //             \Log::error('PayPal authentication error', [
    //                 'error' => $e->getMessage()
    //             ]);
    //             return redirect()->route('cart.checkout')
    //                 ->with('error', 'We couldn\'t connect to PayPal. Please try again later.');
    //         }
            
    //         // Create PayPal order with proper error handling
    //         try {
    //             // Create the order
    //             $response = $provider->createOrder([
    //                 "intent" => "CAPTURE",
    //                 "application_context" => [
    //                     "return_url" => route('payment.success'),
    //                     "cancel_url" => route('payment.cancel'),
    //                     "brand_name" => "Master Magical Key",
    //                     "landing_page" => "BILLING",
    //                     "user_action" => "PAY_NOW",
    //                 ],
    //                 "purchase_units" => [
    //                     [
    //                         "amount" => [
    //                             "currency_code" => 'AUD',
    //                             "value" => number_format($total, 2, '.', ''),
    //                             "breakdown" => [
    //                                 "item_total" => [
    //                                     "currency_code" => 'AUD',
    //                                     "value" => number_format($subtotal, 2, '.', '')
    //                                 ],
    //                                 ...($promoDiscount > 0 ? ["discount" => [
    //                                     "currency_code" => 'AUD',
    //                                     "value" => number_format($promoDiscount, 2, '.', '')
    //                                 ]] : []),
    //                                 "tax_total" => [
    //                                     "currency_code" => 'AUD',
    //                                     "value" => number_format($tax, 2, '.', '')
    //                                 ]
    //                             ]
    //                         ],
    //                         "items" => $paypalItems
    //                     ]
    //                 ]
    //             ]);
                
    //             \Log::info('PayPal order created', [
    //                 'order_id' => $response['id'] ?? 'No ID found',
    //                 'status' => $response['status'] ?? 'No status found'
    //             ]);

    //             // Validate the response structure
    //             if (!isset($response['id']) || empty($response['id'])) {
    //                 throw new \Exception('PayPal order ID not found in response: ' . json_encode($response));
    //             }
                
    //             if (!isset($response['status']) || $response['status'] !== 'CREATED') {
    //                 throw new \Exception('PayPal order not in CREATED status: ' . ($response['status'] ?? 'unknown'));
    //             }
                
    //             // Find the approval URL
    //             $approvalUrl = null;
    //             if (isset($response['links']) && is_array($response['links'])) {
    //                 foreach ($response['links'] as $link) {
    //                     if ($link['rel'] === 'approve') {
    //                         $approvalUrl = $link['href'];
    //                         \Log::info('Found PayPal approval URL', ['url' => $approvalUrl]);
    //                         break;
    //                     }
    //                 }
    //             }
                
    //             if (!$approvalUrl) {
    //                 throw new \Exception('PayPal approval URL not found in response');
    //             }
                
    //             // Store order ID and cart info in session
    //             if (isset($response['id']) && $response['id']) {
    //                 $request->session()->put('paypal_order_id', $response['id']);
    //                 $request->session()->put('purchase_type', 'cart');
    //                 $request->session()->put('cart_id', $cart->id);
    //                 $request->session()->put('subtotal', $subtotal);
    //                 $request->session()->put('tax', $tax);
    //                 $request->session()->put('total', $total);
                    
    //                 // Find and redirect to PayPal approval URL
    //                 $approvalUrl = null;
    //                 foreach ($response['links'] as $link) {
    //                     if ($link['rel'] === 'approve') {
    //                         $approvalUrl = $link['href'];
    //                         break;
    //                     }
    //                 }
                    
    //                 if ($approvalUrl) {
    //                     return redirect()->away($approvalUrl);
    //                 } else {
    //                     throw new \Exception('PayPal approval URL not found in response');
    //                 }
    //             } else {
    //                 throw new \Exception('PayPal order ID not found in response');
    //             }
    //         } catch (\Exception $e) {
    //             \Log::error('PayPal order creation failed', [
    //                 'error' => $e->getMessage(),
    //                 'response' => $response ?? 'No response'
    //             ]);
    //             return redirect()->route('cart.checkout')
    //                 ->with('error', 'We couldn\'t set up your PayPal payment. Please try again.');
    //         }
            
    //     } catch (\Exception $e) {
    //         \Log::error('PayPal cart checkout error', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'user_id' => Auth::id(),
    //             'cart_id' => $cart->id ?? null
    //         ]);
            
    //         return redirect()->route('cart.checkout')
    //             ->with('error', 'Something went wrong with PayPal: ' . $e->getMessage());
    //     }
    // }

    /**
     * Initiate Stripe Checkout Session for cart (guest + auth)
     */
    public function processCartStripe(Request $request)
    {
        // Validate guest fields if not logged in
        if (!Auth::check()) {
            $request->validate([
                'guest_name'  => 'required|string|max:255',
                'guest_email' => 'required|email|max:255',
            ]);
        }

        if (!Auth::check()) {
            $guestEmail = $request->input('guest_email');
            $existingUser = \App\Models\User::where('email', $guestEmail)->first();

            if ($existingUser) {
                $cart = Cart::where('session_id', $request->session()->getId())
                            ->whereNull('user_id')
                            ->first();

                if ($cart) {
                    $alreadyOwned = $cart->items->filter(function($item) use ($existingUser) {
                        return $item->item_type === 'product'
                            && $item->product
                            && $item->product->isPurchasedBy($existingUser->id);
                    });

                    if ($alreadyOwned->isNotEmpty()) {
                        $productTitle = $alreadyOwned->first()->product->title;
                        return redirect()->route('cart.checkout')
                            ->with('error', 
                                "You already own \"{$productTitle}\" with this email. " .
                                "Please log in to access it. " .
                                "Forgot your password? Use the forgot password link on the login page."
                            );
                    }
                }
            }
        }

        try {
            // Resolve cart for both guest and auth
            if (Auth::check()) {
                $cart = Auth::user()->getCart();
            } else {
                $cart = Cart::where('session_id', $request->session()->getId())
                            ->whereNull('user_id')
                            ->first();
            }

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('cart.index')
                    ->with('error', 'Your cart is empty. Please add items before checkout.');
            }

            $subtotal           = $cart->subtotal;
            $promoDiscount      = min($request->session()->get('promo_discount', 0), $subtotal);
            $discountedSubtotal = $subtotal - $promoDiscount;
            $tax                = round($discountedSubtotal * 0.1, 2);
            $total              = $discountedSubtotal + $tax;

            \Log::info('Stripe cart checkout initiated', [
                'cart_id'  => $cart->id,
                'subtotal' => $subtotal,
                'tax'      => $tax,
                'total'    => $total,
                'user_id'  => Auth::id() ?? 'guest',
            ]);

            // Build Stripe line items
            $lineItems = [];
            $taxRatio  = ($subtotal > 0 && $promoDiscount > 0)
                ? (1 - ($promoDiscount / $subtotal))
                : 1;

            foreach ($cart->items as $item) {
                if (!$item || !$item->product) continue;

                $itemPrice   = round($item->price * $taxRatio, 2);
                $itemWithTax = round($itemPrice * 1.1, 2);

                $lineItems[] = [
                    'price_data' => [
                        'currency'     => 'aud',
                        'unit_amount'  => (int) round($itemWithTax * 100),
                        'product_data' => [
                            'name'        => $item->product->title,
                            'description' => $item->product->description ?? null,
                        ],
                    ],
                    'quantity' => $item->quantity,
                ];
            }

            if (empty($lineItems)) {
                return redirect()->route('cart.index')
                    ->with('error', 'No valid items in cart.');
            }

            $customerEmail = Auth::check()
                ? Auth::user()->email
                : $request->input('guest_email');

            Stripe::setApiKey(config('stripe.secret'));

            $session = StripeSession::create([
                'line_items'     => $lineItems,
                'mode'           => 'payment',
                'success_url'    => route('payment.stripeSuccess') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => route('cart.checkout'),
                'customer_email' => $customerEmail,
            ]);

            // Store in session
            $request->session()->put('stripe_session_id', $session->id);
            $request->session()->put('cart_id',   $cart->id);
            $request->session()->put('subtotal',  $subtotal);
            $request->session()->put('tax',       $tax);
            $request->session()->put('total',     $total);

            if (!Auth::check()) {
                $request->session()->put('guest_email', $request->input('guest_email'));
                $request->session()->put('guest_name',  $request->input('guest_name'));
            }

            \Log::info('Stripe session created', ['stripe_session_id' => $session->id]);

            return redirect($session->url);

        } catch (\Exception $e) {
            \Log::error('Stripe cart checkout error', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => Auth::id() ?? 'guest',
            ]);

            return redirect()->route('cart.checkout')
                ->with('error', 'Something went wrong with Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Handle Stripe success callback (guest + auth)
     */
    public function stripeSuccess(Request $request)
    {
        $stripeSessionId = $request->query('session_id')
                        ?: $request->session()->get('stripe_session_id');

        if (!$stripeSessionId) {
            \Log::error('Stripe session ID not found');
            return redirect()->route('products')
                ->with('error', 'Payment information not found.');
        }

        try {
            Stripe::setApiKey(config('stripe.secret'));
            $stripeSession = StripeSession::retrieve($stripeSessionId);

            if ($stripeSession->payment_status !== 'paid') {
                throw new \Exception('Stripe payment not completed. Status: ' . $stripeSession->payment_status);
            }

            $captureId = $stripeSession->payment_intent;
            $currency  = strtoupper($stripeSession->currency);
            $amount    = $stripeSession->amount_total / 100;

            // IDEMPOTENCY: if this payment_intent was already processed, 
            // just redirect to success without reprocessing
            $existingPurchase = \App\Models\Purchase::where('transaction_id', $captureId)->first();
            if ($existingPurchase) {
                \Log::info('Stripe payment already processed, skipping', [
                    'transaction_id' => $captureId,
                    'purchase_id'    => $existingPurchase->id,
                ]);
                $request->session()->forget([
                    'stripe_session_id', 'cart_id', 'subtotal', 'tax', 'total',
                    'promo_code', 'promo_id', 'promo_discount',
                    'guest_email', 'guest_name',
                ]);
                return redirect()->route('products')
                    ->with('success', 'Payment successful! Your purchase is now available.');
            }

            // Get session values — fall back to Stripe amounts if session expired
            $subtotal = $request->session()->get('subtotal');
            $tax      = $request->session()->get('tax');
            $total    = $request->session()->get('total');
            $cartId   = $request->session()->get('cart_id');

            // SESSION EXPIRY FALLBACK: try to recover cart from Stripe customer email
            if (!$cartId) {
                \Log::warning('Cart ID missing from session, attempting recovery', [
                    'stripe_session_id' => $stripeSessionId,
                    'customer_email'    => $stripeSession->customer_details->email ?? 'unknown',
                ]);

                // Try to find cart via the customer email on the Stripe session
                $customerEmail = $stripeSession->customer_details->email ?? null;
                if ($customerEmail) {
                    $user = \App\Models\User::where('email', $customerEmail)->first();
                    if ($user) {
                        $cart = Cart::where('user_id', $user->id)->latest()->first();
                        if ($cart && !$cart->items->isEmpty()) {
                            $cartId = $cart->id;
                        }
                    }
                }

                if (!$cartId) {
                    throw new \Exception('Cart not found and session recovery failed. Please contact support with reference: ' . $captureId);
                }

                // Recalculate totals from the Stripe session amount
                $total    = $amount;
                $tax      = round($amount / 1.1 * 0.1, 2);
                $subtotal = round($amount / 1.1, 2);
            }

            // GUEST: create or find account before processing purchase
            if (!Auth::check()) {
                $guestEmail = $request->session()->get('guest_email')
                        ?? $stripeSession->customer_details->email
                        ?? null;
                $guestName  = $request->session()->get('guest_name')
                        ?? $stripeSession->customer_details->name
                        ?? 'Customer';

                if (!$guestEmail) {
                    throw new \Exception('Guest email not found. Cannot create account.');
                }

                $existingUser = \App\Models\User::where('email', $guestEmail)->first();

                if ($existingUser) {
                    // Account already exists — just log them in, no email sent
                    Auth::login($existingUser);
                    \Log::info('Guest matched existing account', ['email' => $guestEmail]);
                } else {
                    // Create new account
                    $tempPassword = \Illuminate\Support\Str::random(12);
                    $newUser = \App\Models\User::create([
                        'name'             => $guestName,
                        'email'            => $guestEmail,
                        'password'         => bcrypt($tempPassword),
                        'is_guest'         => 1,
                        'password_set'     => 0,
                        'email_verified_at' => now(),
                    ]);

                    Auth::login($newUser);

                    // Send welcome email with temp password
                    try {
                        \Mail::to($guestEmail)->send(
                            new \App\Mail\GuestWelcomeEmail($newUser, $tempPassword)
                        );
                    } catch (\Exception $mailEx) {
                        // Don't fail the purchase if email fails — log it
                        \Log::error('Failed to send guest welcome email', [
                            'email' => $guestEmail,
                            'error' => $mailEx->getMessage(),
                        ]);
                    }

                    \Log::info('Guest account created', ['email' => $guestEmail, 'user_id' => $newUser->id]);
                }

                // Attach the session cart to the now-logged-in user
                $cart = Cart::find($cartId);
                if ($cart && is_null($cart->user_id)) {
                    $cart->update(['user_id' => Auth::id(), 'session_id' => null]);
                }
            }

            \Log::info('Stripe payment captured', [
                'stripe_session_id' => $stripeSessionId,
                'payment_intent'    => $captureId,
                'amount'            => $amount,
                'user_id'           => Auth::id(),
            ]);

            list($purchase, $purchasedItems) = $this->processCartPurchase(
                $cartId, $captureId, $amount, $currency, $subtotal, $tax, $total
            );

            $promoId = $request->session()->get('promo_id');
            if ($promoId) {
                \App\Models\PromoCode::find($promoId)?->increment('used_count');
            }

            $request->session()->forget([
                'stripe_session_id', 'cart_id', 'subtotal', 'tax', 'total',
                'promo_code', 'promo_id', 'promo_discount',
                'guest_email', 'guest_name',
            ]);

            // Get the purchased product slug for direct redirect
            $productItem = collect($purchasedItems)->firstWhere('type', 'product');

            if ($productItem) {
                $product = \App\Models\Product::find($productItem['product_id']);
                if ($product) {
                    return redirect()->route('products.show', $product->slug)
                        ->with('success', '✨ Payment successful! Your product is ready to access.');
                }
            }

            // Fallback to products index if no product found
            return redirect()->route('products')
                ->with('success', '✨ Payment successful! Your purchase is now available.');
        } catch (\Exception $e) {
            \Log::error('Stripe success error', [
                'error'             => $e->getMessage(),
                'trace'             => $e->getTraceAsString(),
                'stripe_session_id' => $stripeSessionId,
            ]);

            return redirect()->route('products')
                ->with('error', 'Error confirming payment: ' . $e->getMessage());
        }
    }
        
    /**
     * Handle success callback from PayPal
     */
    // public function success(Request $request)
    // {
    //     // Get order ID from session
    //     $orderId = $request->session()->get('paypal_order_id');
        
    //     if (!$orderId) {
    //         \Log::error('PayPal order ID not found in session');
    //         return redirect()->route('products')
    //             ->with('error', 'Payment information not found.');
    //     }
        
    //     try {
    //         // Initialize PayPal and capture payment
    //         $provider = new PayPalClient;
    //         $provider->setApiCredentials(config('paypal'));
    //         $provider->getAccessToken();
            
    //         \Log::info('Attempting to capture PayPal payment', ['order_id' => $orderId]);
    //         $response = $provider->capturePaymentOrder($orderId);
            
    //         if (!$response || !isset($response['status']) || $response['status'] !== 'COMPLETED') {
    //             throw new \Exception('Payment not completed. Status: ' . ($response['status'] ?? 'unknown'));
    //         }
            
    //         // Extract payment details
    //         $captureId = $this->extractCaptureId($response);
    //         $amount = $this->extractAmount($response);
    //         $currency = $this->extractCurrency($response);
            
    //         // Get stored values from session
    //         $subtotal = $request->session()->get('subtotal');
    //         $tax = $request->session()->get('tax');
    //         $total = $request->session()->get('total');
    //         $cartId = $request->session()->get('cart_id');
            
    //         if (!$cartId) {
    //             throw new \Exception('Cart ID not found in session');
    //         }
            
    //         // Process cart purchase
    //         list($purchase, $purchasedItems) = $this->processCartPurchase($cartId, $captureId, $amount, $currency, $subtotal, $tax, $total);
            
    //         // Clear session data
    //         // Increment promo used_count if one was applied
    //         $promoId = $request->session()->get('promo_id');
    //         if ($promoId) {
    //             \App\Models\PromoCode::find($promoId)?->increment('used_count');
    //         }

    //         $request->session()->forget([
    //             'paypal_order_id', 'purchase_type', 'product_id', 'chapter_id', 'spell_id', 
    //             'training_video_id', 'cart_id', 'subtotal', 'tax', 'total',
    //             'promo_code', 'promo_id', 'promo_discount'
    //         ]);
            
    //         // Return success view
    //         return redirect()->route('products')->with('success', 'Payment successful! Your purchase is now available.');
            
    //     } catch (\Exception $e) {
    //         \Log::error('PayPal capture error', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'order_id' => $orderId
    //         ]);
            
    //         return redirect()->route('products')
    //             ->with('error', 'An error occurred while processing your payment: ' . $e->getMessage());
    //     }
    // }

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
        // Find cart directly by ID — safe for both guest (now logged in) and auth users
        $cart = Cart::findOrFail($cartId);

        // Verify the cart belongs to the current user (safety check)
        if ($cart->user_id !== Auth::id()) {
            throw new \Exception('Cart ownership mismatch. Cart: ' . $cartId . ' User: ' . Auth::id());
        }

        
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

        Auth::user()->products()->syncWithoutDetaching([$item->product->id]);
        
        // Add to purchased items
        $purchasedItems[] = [
            'product_id' => $item->product->id,
            'title' => $item->product->title,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'type' => 'product'
        ];
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