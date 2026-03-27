<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Chapter;
use App\Models\CartItem;
use App\Models\Spell;
use App\Models\PromoCode;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Resolve cart for both guests and logged-in users.
     */
    private function resolveCart(Request $request): Cart
    {
        if (Auth::check()) {
            return Auth::user()->getCart();
        }

        $sessionId = $request->session()->getId();
        return Cart::firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null]
        );
    }

    /**
     * Display the cart.
     */
    public function index(Request $request)
    {
        $cart = $this->resolveCart($request);
        $cart->load(['items']);
        return view('cart.index', compact('cart'));
    }

    /**
     * Add a product to the cart (guest + auth).
     */
    public function addProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'integer|min:1|max:10',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart    = $this->resolveCart($request);

        // If logged in, check ownership
        if (Auth::check() && Auth::user()->products->contains($product->id)) {
            return back()->with('info', 'You already own this product.');
        }

        $existing = $cart->items()->where('product_id', $product->id)->first();
        if (!$existing) {
            $cart->items()->create([
                'product_id' => $product->id,
                'item_type'  => 'product',
                'quantity'   => $request->quantity ?? 1,
                'price'      => $product->price,
            ]);
        }

        if ($request->buy_now) {
            return redirect()->route('cart.checkout');
        }

        return back()->with('success', 'Added to your cart!');
    }

    /**
     * Add a chapter to the cart.
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'quantity'   => 'integer|min:1|max:10',
        ]);

        $chapter = Chapter::findOrFail($request->chapter_id);
        $cart    = $this->resolveCart($request);

        if (Auth::check() && $chapter->isPurchased()) {
            return back()->with('info', 'You already own this chapter.');
        }

        $existing = $cart->items()->where('chapter_id', $chapter->id)->first();
        if (!$existing) {
            $cart->addItem($chapter, $request->quantity ?? 1);

            if ($chapter->freeSpells()->exists()) {
                $cart->addFreeSpells($chapter->freeSpells()->get());
            }
        }

        if ($request->buy_now) {
            return redirect()->route('cart.checkout');
        }

        return back()->with('success', 'Chapter added to your cart!');
    }

    /**
     * Add a spell to the cart.
     */
    public function addSpell(Request $request)
    {
        $request->validate([
            'spell_id' => 'required|exists:spells,id',
            'quantity' => 'integer|min:1|max:10',
        ]);

        $spell = Spell::findOrFail($request->spell_id);
        $cart  = $this->resolveCart($request);

        if (Auth::check() && Auth::user()->hasSpell($spell)) {
            return back()->with('info', 'You already own this spell.');
        }

        $existing = $cart->items()->where('spell_id', $spell->id)->first();
        if (!$existing) {
            $cart->addSpell($spell, $request->quantity ?? 1);
        }

        CartItem::whereNotNull('spell_id')
            ->where('item_type', '!=', 'spell')
            ->update(['item_type' => 'spell']);

        if ($request->buy_now) {
            return redirect()->route('cart.checkout');
        }

        return back()->with('success', 'Spell added to your cart!');
    }

    /**
     * Update cart item quantity.
     */
    public function updateItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity'     => 'required|integer|min:1|max:10',
        ]);

        $cart     = $this->resolveCart($request);
        $cartItem = $cart->items()->findOrFail($request->cart_item_id);
        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        $cart = $this->resolveCart($request);
        $cart->removeItem($request->cart_item_id);

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Apply a promo code.
     */
    public function applyPromoCode(Request $request)
    {
        $code  = strtoupper(trim($request->input('promo_code', '')));
        $promo = PromoCode::where('code', $code)->first();

        if (!$promo || !$promo->isValid()) {
            return back()->with('promo_error', 'Invalid or expired promo code.');
        }

        $cart     = $this->resolveCart($request);
        $subtotal = $cart->subtotal;

        if ($promo->min_order_amount && $subtotal < $promo->min_order_amount) {
            return back()->with('promo_error', 'Minimum order of $' . number_format($promo->min_order_amount, 2) . ' required.');
        }

        $discount = $promo->calculateDiscount($subtotal);
        $request->session()->put('promo_code', $promo->code);
        $request->session()->put('promo_id', $promo->id);
        $request->session()->put('promo_discount', $discount);

        return back()->with('promo_success', 'Promo code applied!');
    }

    /**
     * Remove the applied promo code.
     */
    public function removePromoCode(Request $request)
    {
        $request->session()->forget(['promo_code', 'promo_id', 'promo_discount']);
        return back();
    }

    /**
     * Clear the entire cart.
     */
    public function clear(Request $request)
    {
        $cart = $this->resolveCart($request);
        $cart->clear();
        return back()->with('success', 'Cart cleared successfully.');
    }

    /**
     * Proceed to checkout.
     */
    public function checkout(Request $request)
    {
        $cart = $this->resolveCart($request);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $chapterItems = $cart->items->where('item_type', 'chapter');
        $spellItems   = $cart->items->where('item_type', 'spell');
        $productItems = $cart->items->where('item_type', 'product');

        return view('cart.checkout', compact('cart', 'chapterItems', 'spellItems', 'productItems'));
    }
}