<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Spell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display the cart.
     */
    public function index()
    {
        $cart = Auth::user()->getCart();

        // Eager load relationships
        // $cart->load(['items.chapter', 'items.spell']);

        return view('cart.index', compact('cart'));
    }

    /**
     * Add an item to the cart.
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'quantity' => 'integer|min:1|max:10',
        ]);

        $chapter = Chapter::findOrFail($request->chapter_id);
        $cart = Auth::user()->getCart();
        
        // Check if the user already owns this chapter
        if ($chapter->isPurchased()) {
            return back()->with('info', 'You already own this chapter.');
        }

        $existingCartItem = $cart->items()->where('chapter_id', $chapter->id)->first();
        if(empty($existingCartItem)) {
            // Add to cart
            $cart->addItem($chapter, $request->quantity ?? 1);
        }

        // Determine if we should redirect to cart or checkout
        if ($request->buy_now) {
            return redirect()->route('cart.checkout');
        }
        
        return back()->with('success', 'Chapter added to your cart!');
    }

    public function addSpell(Request $request)
    {
        $request->validate([
            'spell_id' => 'required|exists:spells,id',
            'quantity' => 'integer|min:1|max:10',
        ]);

        $spell = Spell::findOrFail($request->spell_id);
        $cart = Auth::user()->getCart();
        
        // Check if the user already owns this spell
        if (Auth::user()->hasSpell($spell)) {
            return back()->with('info', 'You already own this spell.');
        }

        // Add to cart - make sure this is calling the correct addSpell method
        $cart->addSpell($spell, $request->quantity ?? 1);

        // Find all cart items with spell_id but incorrect item_type
        $itemsToFix = \App\Models\CartItem::whereNotNull('spell_id')
        ->where('item_type', '!=', 'spell')
        ->update(['item_type' => 'spell']);

        // Determine if we should redirect to cart or checkout
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
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cart = Auth::user()->getCart();
        $cartItem = $cart->items()->findOrFail($request->cart_item_id);
        
        $cartItem->update([
            'quantity' => $request->quantity
        ]);

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

        $cart = Auth::user()->getCart();
        $cart->removeItem($request->cart_item_id);

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        $cart = Auth::user()->getCart();
        $cart->clear();

        return back()->with('success', 'Cart cleared successfully.');
    }

    /**
     * Proceed to checkout.
     */
    public function checkout()
    {
        $cart = Auth::user()->getCart();
        
        // Check if cart is empty
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Please add chapters before checkout.');
        }

        // Group items by type for display
        $chapterItems = $cart->items->where('item_type', 'chapter');
        $spellItems = $cart->items->where('item_type', 'spell');
        
        return view('cart.checkout', compact('cart', 'chapterItems', 'spellItems'));
    }
}