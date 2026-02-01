<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromoCodeAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $promoCodes = PromoCode::orderBy('created_at', 'desc')->get();
        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function create()
    {
        return view('admin.promo-codes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                => 'required|string|max:255|unique:promo_codes',
            'discount_type'       => 'required|in:percentage,fixed',
            'discount_value'      => 'required|numeric|min:0.01',
            'min_order_amount'    => 'nullable|numeric|min:0',
            'max_uses'            => 'nullable|integer|min:1',
            'expires_at'          => 'nullable|date|after:now',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        PromoCode::create($validated);

        return redirect()->route('admin.promo-codes.index')
            ->with('success', 'Promo code created successfully.');
    }

    public function toggle(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => !$promoCode->is_active]);
        return back();
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();
        return redirect()->route('admin.promo-codes.index')
            ->with('success', 'Promo code deleted.');
    }
}