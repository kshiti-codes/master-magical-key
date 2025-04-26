<?php

namespace App\Http\Controllers;

use App\Models\TrainingVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class TrainingVideoController extends Controller
{
    /**
     * Display a listing of training videos
     */
    public function index()
    {
        $videos = TrainingVideo::where('is_published', true)
                ->orderBy('order_sequence')
                ->get();
        
        // Check if user has lifetime subscription
        $hasLifetimeSubscription = Auth::check() ? Auth::user()->hasLifetimeSubscription() : false;
        
        // Get IDs of videos that user has purchased individually
        $purchasedVideoIds = [];
        if (Auth::check()) {
            $purchasedVideoIds = Auth::user()->videos()->pluck('training_videos.id')->toArray();
        }
        
        // Get cart item count for floating cart button
        $cartItemCount = 0;
        if (Auth::check()) {
            $cart = Auth::user()->getCart();
            $cartItemCount = $cart ? $cart->itemCount : 0;
        }
        
        return view('videos.index', compact('videos', 'hasLifetimeSubscription', 'purchasedVideoIds', 'cartItemCount'));
    }
    
    
    /**
     * Display details for a specific video
     */
    public function show(TrainingVideo $video)
    {
        // Check if video is published
        if (!$video->is_published) {
            return redirect()->route('videos.index')
                ->with('error', 'This video is not available.');
        }
        
        // Check if user has access to this video
        $hasAccess = $video->isAccessible();
        
        // Check if user has lifetime subscription (videos are free)
        $isFreeForUser = $video->isFreeForUser();
        
        // Get cart item count for floating cart button
        $cartItemCount = 0;
        if (Auth::check()) {
            $cart = Auth::user()->getCart();
            $cartItemCount = $cart ? $cart->itemCount : 0;
        }
        
        return view('videos.show', compact('video', 'hasAccess', 'isFreeForUser', 'cartItemCount'));
    }

    /**
     * Add a video to the cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:training_videos,id',
        ]);

        $video = TrainingVideo::findOrFail($request->video_id);
        $cart = Auth::user()->getCart();
        
        // Check if user already has access to this video
        if ($video->isAccessible()) {
            return back()->with('info', 'You already have access to this video.');
        }

        // Check if video is already in cart
        $existingCartItem = $cart->items()->where('training_video_id', $video->id)
                                ->where('item_type', 'video')
                                ->first();
        
        if (!$existingCartItem) {
            // Add to cart
            $cart->items()->create([
                'training_video_id' => $video->id,
                'item_type' => 'video',
                'quantity' => 1,
                'price' => $video->price
            ]);
        }
        
        // Determine if we should redirect to cart or back to the videos page
        if ($request->buy_now) {
            return redirect()->route('cart.checkout');
        }
        
        return back()->with('success', 'Video added to your cart!');
    }
    
    /**
     * Process video purchase
     */
    public function purchase(Request $request)
    {
        // Validate the request
        $request->validate([
            'video_id' => 'required|exists:training_videos,id',
        ]);
        
        // Get the video from the database
        $video = TrainingVideo::findOrFail($request->video_id);
        
        // Check if video is published
        if (!$video->is_published) {
            return redirect()->route('videos.index')
                ->with('error', 'This video is not available for purchase.');
        }
        
        // Check if user already has access
        if ($video->isAccessible()) {
            return redirect()->route('videos.show', $video)
                ->with('info', 'You already have access to this video.');
        }
        
        // Check if this is a "buy now" request
        if ($request->has('buy_now')) {
            // Add to cart and redirect to checkout
            Auth::user()->getCart()->addVideo($video);
            return redirect()->route('cart.checkout');
        }
        
        // Store video ID in session
        $request->session()->put('video_id', $video->id);
        
        try {
            // Initialize PayPal API
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();
            
            if (!$paypalToken) {
                throw new \Exception('Failed to get PayPal access token');
            }
            
            $response = $provider->createOrder([
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('videos.purchase.success'),
                    "cancel_url" => route('videos.purchase.cancel'),
                    "brand_name" => "Master Magical Key",
                    "landing_page" => "BILLING",
                    "user_action" => "PAY_NOW",
                ],
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => $video->currency,
                            "value" => number_format($video->price, 2, '.', '')
                        ],
                        "description" => "Access to training video: " . $video->title
                    ]
                ]
            ]);
            
            if (isset($response['id']) && $response['id']) {
                // Store PayPal order ID
                $request->session()->put('paypal_order_id', $response['id']);
                
                // Find and redirect to PayPal approval URL
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        return redirect()->away($link['href']);
                    }
                }
            }
            
            // If we get here, something went wrong
            return redirect()->route('videos.show', $video)
                ->with('error', 'Could not process the payment. Please try again later.');
                
        } catch (\Exception $e) {
            \Log::error('Video purchase error', [
                'error' => $e->getMessage(),
                'video_id' => $video->id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('videos.show', $video)
                ->with('error', 'There was an error processing your payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle successful video purchase
     */
    public function purchaseSuccess(Request $request)
    {
        $videoId = $request->session()->get('video_id');
        $video = TrainingVideo::findOrFail($videoId);
        
        try {
            // Initialize PayPal API
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            
            // Handle one-time payment success
            $orderId = $request->session()->get('paypal_order_id');
            $response = $provider->capturePaymentOrder($orderId);
            
            // Check if payment is completed
            if ($response['status'] === 'COMPLETED') {
                // Grant access to the video
                Auth::user()->grantVideoAccess($video);
                
                // Create a purchase record in your system
                $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
                $amount = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null;
                
                // Create a purchase record
                $purchase = \App\Models\Purchase::create([
                    'user_id' => Auth::id(),
                    'transaction_id' => $captureId,
                    'amount' => $amount,
                    'currency' => $video->currency,
                    'status' => 'completed',
                    'subtotal' => $amount / 1.1, // Assuming 10% tax
                    'tax' => $amount - ($amount / 1.1),
                    'tax_rate' => 10.00, // 10% GST
                    'invoice_number' => \App\Models\Purchase::generateInvoiceNumber()
                ]);
                
                // Create purchase item for the video
                \App\Models\PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_type' => 'video',
                    'quantity' => 1,
                    'price' => $video->price
                ]);
                
                // Clear session data
                $request->session()->forget(['video_id', 'paypal_order_id']);
                
                return redirect()->route('videos.watch', $video)
                    ->with('success', 'Your purchase was successful! You now have access to this video.');
            }
            
            // If we get here, the payment wasn't completed
            return redirect()->route('videos.show', $video)
                ->with('error', 'There was an issue with your payment. Please contact support.');
                
        } catch (\Exception $e) {
            \Log::error('Video purchase success callback error', [
                'error' => $e->getMessage(),
                'video_id' => $videoId,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('videos.show', $video)
                ->with('error', 'There was an error processing your payment: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle cancelled video purchase
     */
    public function purchaseCancel(Request $request)
    {
        $videoId = $request->session()->get('video_id');
        $request->session()->forget(['video_id', 'paypal_order_id']);
        
        if ($videoId) {
            return redirect()->route('videos.show', $videoId)
                ->with('info', 'You have cancelled the purchase process.');
        }
        
        return redirect()->route('videos.index')
            ->with('info', 'You have cancelled the purchase process.');
    }
    
    /**
     * Watch a video
     */
    public function watch(TrainingVideo $video)
    {
        // Check if video is published
        if (!$video->is_published) {
            return redirect()->route('videos.index')
                ->with('error', 'This video is not available.');
        }
        
        // Check if user has access to this video
        if (!$video->isAccessible()) {
            return redirect()->route('videos.show', $video)
                ->with('error', 'You do not have access to this video. Please purchase it first.');
        }
        
        // Update watch statistics
        $watchCount = 0;
        if (Auth::check()) {
            $userVideo = Auth::user()->videos()->where('training_videos.id', $video->id)->first();
            
            if ($userVideo) {
                $watchCount = $userVideo->pivot->watch_count + 1;
                
                Auth::user()->videos()->updateExistingPivot($video->id, [
                    'last_watched_at' => now(),
                    'watch_count' => $watchCount
                ]);
            }
        }
        
        // Process the video URL to get a direct embedable URL
        $embedUrl = null;
        
        // Handle Google Drive links
        if (strpos($video->video_path, 'drive.google.com') !== false) {
            // Extract the file ID from various Google Drive URL formats
            $fileId = null;
            
            // Format: drive.google.com/file/d/{fileId}/view
            if (preg_match('/\/file\/d\/([^\/\?]+)/', $video->video_path, $matches)) {
                $fileId = $matches[1];
            }
            // Format: drive.google.com/open?id={fileId}
            else if (preg_match('/[?&]id=([^&]+)/', $video->video_path, $matches)) {
                $fileId = $matches[1];
            }
            
            if ($fileId) {
                // Use the embed URL format for Google Drive
                $embedUrl = "https://drive.google.com/file/d/{$fileId}/preview";
                
                // Log successful URL conversion
                \Log::info('Generated Google Drive embed URL', [
                    'video_id' => $video->id,
                    'original_path' => $video->video_path,
                    'embed_url' => $embedUrl
                ]);
            } else {
                \Log::warning('Could not extract Google Drive file ID', [
                    'video_path' => $video->video_path
                ]);
            }
        } else {
            // For non-Google Drive videos, use the original URL
            $embedUrl = $video->video_path;
        }
        
        return view('videos.watch', compact('video', 'watchCount', 'embedUrl'));
    }

    /**
     * Generate the appropriate embed URL for the video source
     */
    private function getEmbedUrl($url)
    {
        // Handle Google Drive URLs
        if (strpos($url, 'drive.google.com') !== false) {
            $fileId = $this->extractGoogleDriveFileId($url);
            if ($fileId) {
                // Create embed URL - using the direct embed format
                return "https://drive.google.com/file/d/{$fileId}/preview";
            }
        } 
        
        // For other video sources, return the original URL
        return $url;
    }

    /**
     * Download a video
     */
    public function download(TrainingVideo $video)
    {
        // Check if video is published
        if (!$video->is_published) {
            return redirect()->route('videos.index')
                ->with('error', 'This video is not available.');
        }
        
        // Check if user has access to this video
        if (!$video->isAccessible()) {
            return redirect()->route('videos.show', $video)
                ->with('error', 'You do not have access to this video. Please purchase it first.');
        }
        
        // For Google Drive URLs, create a direct download URL
        if (strpos($video->video_path, 'drive.google.com') !== false) {
            $fileId = $this->extractGoogleDriveFileId($video->video_path);
            if ($fileId) {
                // Use the direct download URL with 'export=download' parameter
                $downloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}";
                return redirect()->away($downloadUrl);
            }
        }
        
        // For other URLs, simply redirect to the original URL
        return redirect()->away($video->video_path);
    }

    /**
     * Extract Google Drive file ID from various URL formats
     */
    private function extractGoogleDriveFileId($url)
    {
        // Format: drive.google.com/file/d/{fileId}/view
        if (preg_match('/\/file\/d\/([^\/\?]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Format: drive.google.com/open?id={fileId}
        if (preg_match('/[?&]id=([^&]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Format: docs.google.com/uc?id={fileId}
        if (preg_match('/uc\?.*id=([^&]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}