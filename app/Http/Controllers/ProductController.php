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
        $products = Product::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        return view('admin.products.create');
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        // Generate slug if empty
        if (empty($request->slug)) {
            $request->merge(['slug' => Str::slug($request->title)]);
        }

        // Set is_active default
        if (!$request->has('is_active')) {
            $request->merge(['is_active' => false]);
        }

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
            'sku' => 'nullable|string|unique:products,sku',
            'slug' => 'nullable|string|unique:products,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        // Create product first
        $product = Product::create($validated);

        // Handle PDF file upload - USING WORKING SPELL METHOD
        if ($request->hasFile('pdf_file') && $request->file('pdf_file')->isValid()) {
            $pdfFile = $request->file('pdf_file');
            
            // Generate a filename
            $fileName = Str::slug($product->title) . '-' . time() . '.pdf';
            $relativePath = 'products/pdfs/' . $fileName;
            
            // SECURE STORAGE: Create directory in storage/app (not public)
            $directory = storage_path('app/products/pdfs');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Move the uploaded file to SECURE storage
            $pdfFile->move($directory, $fileName);
            
            // Update the product with the PDF path
            $product->update(['pdf_file_path' => $relativePath]);
        }

        // Handle audio file upload
        if ($request->hasFile('audio_file') && $request->file('audio_file')->isValid()) {
            $audioFile = $request->file('audio_file');
            
            // Generate a filename
            $audioExtension = $audioFile->getClientOriginalExtension();
            $audioFileName = Str::slug($product->title) . '-' . time() . '.' . $audioExtension;
            $audioRelativePath = 'products/audio/' . $audioFileName;
            
            // SECURE STORAGE: Create directory in storage/app
            $audioDirectory = storage_path('app/products/audio');
            if (!file_exists($audioDirectory)) {
                mkdir($audioDirectory, 0755, true);
            }
            
            // Move the uploaded file to SECURE storage
            $audioFile->move($audioDirectory, $audioFileName);
            
            // Update the product with the audio path
            $product->update(['audio_file_path' => $audioRelativePath]);
        }

        // Handle image upload (images can be public)
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imageFile = $request->file('image');
            
            // Generate a filename
            $imageExtension = $imageFile->getClientOriginalExtension();
            $imageFileName = Str::slug($product->title) . '-' . time() . '.' . $imageExtension;
            $imageRelativePath = 'products/images/' . $imageFileName;
            
            // PUBLIC STORAGE: Images can be in public
            $imageDirectory = storage_path('app/public/products/images');
            if (!file_exists($imageDirectory)) {
                mkdir($imageDirectory, 0755, true);
            }
            
            // Move the uploaded file
            $imageFile->move($imageDirectory, $imageFileName);
            
            // Update the product with the image path
            $product->update(['image' => $imageRelativePath]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Show the form for editing a product
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        // Set is_active default
        if (!$request->has('is_active')) {
            $request->merge(['is_active' => false]);
        }

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

        // Update product data
        $product->update($validated);

        // Handle PDF removal/update
        if ($request->boolean('remove_pdf') && $product->pdf_file_path) {
            // Delete old PDF from SECURE storage
            $oldPdfPath = storage_path('app/' . $product->pdf_file_path);
            if (file_exists($oldPdfPath)) {
                unlink($oldPdfPath);
            }
            $product->update(['pdf_file_path' => null]);
            
        } elseif ($request->hasFile('pdf_file') && $request->file('pdf_file')->isValid()) {
            $pdfFile = $request->file('pdf_file');
            
            // Delete old PDF if exists
            if ($product->pdf_file_path) {
                $oldPdfPath = storage_path('app/' . $product->pdf_file_path);
                if (file_exists($oldPdfPath)) {
                    unlink($oldPdfPath);
                }
            }
            
            // Generate a filename
            $fileName = Str::slug($product->title) . '-' . time() . '.pdf';
            $relativePath = 'products/pdfs/' . $fileName;
            
            // SECURE STORAGE: Create directory in storage/app
            $directory = storage_path('app/products/pdfs');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Move the uploaded file to SECURE storage
            $pdfFile->move($directory, $fileName);
            
            // Update the product with the PDF path
            $product->update(['pdf_file_path' => $relativePath]);
        }

        // Handle audio removal/update
        if ($request->boolean('remove_audio') && $product->audio_file_path) {
            // Delete old audio from SECURE storage
            $oldAudioPath = storage_path('app/' . $product->audio_file_path);
            if (file_exists($oldAudioPath)) {
                unlink($oldAudioPath);
            }
            $product->update(['audio_file_path' => null]);
            
        } elseif ($request->hasFile('audio_file') && $request->file('audio_file')->isValid()) {
            $audioFile = $request->file('audio_file');
            
            // Delete old audio if exists
            if ($product->audio_file_path) {
                $oldAudioPath = storage_path('app/' . $product->audio_file_path);
                if (file_exists($oldAudioPath)) {
                    unlink($oldAudioPath);
                }
            }
            
            // Generate a filename
            $audioExtension = $audioFile->getClientOriginalExtension();
            $audioFileName = Str::slug($product->title) . '-' . time() . '.' . $audioExtension;
            $audioRelativePath = 'products/audio/' . $audioFileName;
            
            // SECURE STORAGE: Create directory in storage/app
            $audioDirectory = storage_path('app/products/audio');
            if (!file_exists($audioDirectory)) {
                mkdir($audioDirectory, 0755, true);
            }
            
            // Move the uploaded file to SECURE storage
            $audioFile->move($audioDirectory, $audioFileName);
            
            // Update the product with the audio path
            $product->update(['audio_file_path' => $audioRelativePath]);
        }

        // Handle image removal/update
        if ($request->boolean('remove_image') && $product->image) {
            // Delete old image from PUBLIC storage
            $oldImagePath = storage_path('app/public/' . $product->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
            $product->update(['image' => null]);
            
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imageFile = $request->file('image');
            
            // Delete old image if exists
            if ($product->image) {
                $oldImagePath = storage_path('app/public/' . $product->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            // Generate a filename
            $imageExtension = $imageFile->getClientOriginalExtension();
            $imageFileName = Str::slug($product->title) . '-' . time() . '.' . $imageExtension;
            $imageRelativePath = 'products/images/' . $imageFileName;
            
            // PUBLIC STORAGE: Images can be in public
            $imageDirectory = storage_path('app/public/products/images');
            if (!file_exists($imageDirectory)) {
                mkdir($imageDirectory, 0755, true);
            }
            
            // Move the uploaded file
            $imageFile->move($imageDirectory, $imageFileName);
            
            // Update the product with the image path
            $product->update(['image' => $imageRelativePath]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
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
        $product->is_active = !$product->is_active;
        $product->save();

        $status = $product->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Product {$status} successfully!");
    }
}