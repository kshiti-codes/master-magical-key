<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of active products (Customer View)
     */
    public function index()
    {
        $products = Product::active()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Get products in user's cart for showing "Added" state
        $cartItemCount = 0;
        $productsInCart = [];
        if (Auth::check()) {
            $cart = Auth::user()->getCart();
            $cartItemCount = $cart->itemCount;
            if ($cart) {
                $productsInCart = $cart->items()
                    ->whereNotNull('product_id')
                    ->pluck('product_id')
                    ->toArray();
            }
        }

        return view('products.index', compact('products', 'cartItemCount', 'productsInCart'));
    }

    /**
     * Display a single product (Customer View)
     */
    public function show(Product $product)
    {
        // Check if product is active
        if (!$product->is_active) {
            abort(404, 'Product not found');
        }

        $hasPurchased = false;
        $inCart = false;
        
        if (Auth::check()) {
            $hasPurchased = $product->isPurchasedBy(Auth::id());
            
            // Check if product is in cart
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $inCart = $cart->items()
                    ->where('product_id', $product->id)
                    ->exists();
            }
        }

        return view('products.show', compact('product', 'hasPurchased', 'inCart'));
    }

    /**
     * Add product to cart
     */
    public function addToCart(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'integer|min:1|max:10'
        ]);

        $quantity = $request->input('quantity', 1);

        // Check if product is active
        if (!$product->is_active) {
            return back()->with('error', 'This product is no longer available.');
        }

        // Get or create cart
        $cart = Cart::firstOrCreate(
            ['user_id' => Auth::id()],
            ['session_id' => session()->getId()]
        );

        // Check if product already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'item_type' => 'product',
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Product added to cart!');
    }

    /**
     * Download PDF file (requires purchase verification)
     */
    public function downloadPdf(Product $product)
    {
        // Check if user owns product or it's free
        if (!$product->isPurchasedBy(Auth::id()) && $product->price > 0.00) {
            abort(403, 'You must purchase this product to access it.');
        }
        
        // Check if PDF exists
        // if (!$product->hasPdf()) {
        //     abort(404, 'PDF file not found.');
        // }
        
        // Return the PDF file for viewing (not download)
        return response()->file(storage_path('app/' . $product->pdf_file_path));
    }

    /**
     * Download audio file (requires purchase verification)
     */
    public function downloadAudio(Product $product)
    {
        // Check if user has purchased this product
        if (!$product->isPurchasedBy(Auth::id()) && $product->price > 0.00) {
            abort(403, 'You must purchase this product to download it.');
        }

        // $filePath = $product->getAudioPath();
        // $fileName = Str::slug($product->title) . '-audio.' . pathinfo($filePath, PATHINFO_EXTENSION);

        return response()->file(storage_path('app/' . $product->audio_file_path));
    }

    /**
     * Check if product has a PDF file
     */
    public function hasPdf()
    {
        return !empty($this->pdf_file_path) && Storage::exists($this->pdf_file_path);
    }

    /**
     * Check if product has an audio file
     */
    public function hasAudio()
    {
        return !empty($this->audio_file_path) && Storage::exists($this->audio_file_path);
    }

    /**
     * Show user's purchased products
     */
    public function myProducts()
    {
        $purchases = Purchase::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        $products = collect();
        foreach ($purchases as $purchase) {
            foreach ($purchase->items as $item) {
                if ($item->product) {
                    $products->push($item->product);
                }
            }
        }

        $products = $products->unique('id');

        return view('products.my-products', compact('products'));
    }

    // ==========================================
    // ADMIN FUNCTIONS
    // ==========================================

    /**
     * Display admin products list
     */
    public function adminIndex()
    {
        $this->authorize('admin'); // Ensure only admins can access

        $products = Product::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $this->authorize('admin');

        return view('admin.products.create');
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $this->authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'type' => 'required|in:digital_download,course,session,subscription,video,other',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200', // 50MB max
            'audio_file' => 'nullable|file|mimes:mp3,wav,m4a,ogg|max:102400', // 100MB max
            'popup_text' => 'nullable|string',
            'is_active' => 'boolean',
            'sku' => 'nullable|string|unique:products,sku',
            'slug' => 'nullable|string|unique:products,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
        ]);

        // Handle PDF upload
        if ($request->hasFile('pdf_file')) {
            $pdfPath = $request->file('pdf_file')->store('products/pdfs', 'local');
            $validated['pdf_file_path'] = $pdfPath;
        }

        // Handle audio upload
        if ($request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('products/audio', 'local');
            $validated['audio_file_path'] = $audioPath;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products/images', 'public');
            $validated['image'] = $imagePath;
        }

        $product = Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Show the form for editing a product
     */
    public function edit(Product $product)
    {
        $this->authorize('admin');

        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $this->authorize('admin');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'type' => 'required|in:digital_download,course,session,subscription,video,other',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200',
            'audio_file' => 'nullable|file|mimes:mp3,wav,m4a,ogg|max:102400',
            'popup_text' => 'nullable|string',
            'is_active' => 'boolean',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'remove_pdf' => 'boolean',
            'remove_audio' => 'boolean',
            'remove_image' => 'boolean',
        ]);

        // Handle PDF removal/update
        if ($request->boolean('remove_pdf') && $product->pdf_file_path) {
            Storage::disk('local')->delete($product->pdf_file_path);
            $validated['pdf_file_path'] = null;
        } elseif ($request->hasFile('pdf_file')) {
            // Delete old PDF if exists
            if ($product->pdf_file_path) {
                Storage::disk('local')->delete($product->pdf_file_path);
            }
            $pdfPath = $request->file('pdf_file')->store('products/pdfs', 'local');
            $validated['pdf_file_path'] = $pdfPath;
        }

        // Handle audio removal/update
        if ($request->boolean('remove_audio') && $product->audio_file_path) {
            Storage::disk('local')->delete($product->audio_file_path);
            $validated['audio_file_path'] = null;
        } elseif ($request->hasFile('audio_file')) {
            // Delete old audio if exists
            if ($product->audio_file_path) {
                Storage::disk('local')->delete($product->audio_file_path);
            }
            $audioPath = $request->file('audio_file')->store('products/audio', 'local');
            $validated['audio_file_path'] = $audioPath;
        }

        // Handle image removal/update
        if ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $validated['image'] = null;
        } elseif ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products/images', 'public');
            $validated['image'] = $imagePath;
        }

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        $this->authorize('admin');

        // Delete associated files
        if ($product->pdf_file_path) {
            Storage::disk('local')->delete($product->pdf_file_path);
        }
        if ($product->audio_file_path) {
            Storage::disk('local')->delete($product->audio_file_path);
        }
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * Toggle product active status
     */
    public function toggleActive(Product $product)
    {
        $this->authorize('admin');

        $product->is_active = !$product->is_active;
        $product->save();

        $status = $product->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Product {$status} successfully!");
    }
}