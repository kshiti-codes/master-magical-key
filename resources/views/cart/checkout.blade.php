@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/cart.css') }}" rel="stylesheet">
<link href="{{ asset('css/components/payment.css') }}" rel="stylesheet">
<style>
    .checkout-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    
    .checkout-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(138, 43, 226, 0.2);
        color: rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        transition: all 0.3s ease;
    }
    
    .step-label {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
        transition: all 0.3s ease;
    }
    
    .checkout-step.active .step-number {
        background: rgba(138, 43, 226, 0.7);
        color: white;
        box-shadow: 0 0 15px rgba(138, 43, 226, 0.5);
    }
    
    .checkout-step.active .step-label {
        color: white;
        font-weight: 500;
    }
    
    .checkout-step.completed .step-number {
        background: #4BB543;
        color: white;
        border-color: #4BB543;
    }
    
    .steps-line {
        position: absolute;
        top: 20px;
        left: 70px;
        right: 70px;
        height: 2px;
        background: rgba(138, 43, 226, 0.3);
        z-index: 1;
    }
    
    .order-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .order-table th {
        background: rgba(138, 43, 226, 0.2);
        color: white;
        text-align: left;
        padding: 5px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.5);
        font-family: 'Cinzel', serif;
        font-weight: normal;
    }
    
    .order-table td {
        padding: 5px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
        color: rgba(255, 255, 255, 0.8);
    }
    
    .order-table tr:last-child td {
        border-bottom: none;
    }
    
    .order-table .item-title {
        color: white;
        font-family: 'Cinzel', serif;
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
    
    .order-table .item-description {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.6);
    }
    
    .order-table .text-right {
        text-align: right;
    }
    
    .order-table .text-center {
        text-align: center;
    }
    
    .price-summary-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .price-summary-table .label {
        text-align: right;
        width: 70%;
    }
    
    .price-summary-table .value {
        text-align: right;
        width: 30%;
        color: white;
    }
    
    .price-summary-table .subtotal-row td {
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
        padding-bottom: 5px;
    }
    
    .price-summary-table .tax-row td {
        padding-top: 5px;
    }
    
    .price-summary-table .total-row {
        font-size: 1.2rem;
        color: #d8b5ff;
    }
    
    .price-summary-table .total-row td {
        padding-top: 5px;
    }
    
    .payment-section {
        background: rgba(10, 10, 30, 0.6);
        border-radius: 10px;
        padding: 25px;
        margin-top: 30px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .payment-section h3 {
        font-family: 'Cinzel', serif;
        text-align: center;
        margin-bottom: 20px;
        color: white;
    }
    
    .btn-paypal {
        display: block;
        margin: 0 auto;
    }
    
    .order-section {
        margin-bottom: 30px;
    }
    
    .order-section-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.3rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .item-type-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        margin-right: 8px;
        vertical-align: middle;
    }
    
    .item-type-chapter {
        background: rgba(138, 43, 226, 0.3);
        color: #d8b5ff;
    }
    
    .item-type-spell {
        background: rgba(0, 128, 128, 0.3);
        color: #a0ffd8;
    }
    
    @media (max-width: 767px) {
        .steps-line {
            left: 30px;
            right: 30px;
        }
        
        .step-label {
            font-size: 0.8rem;
        }
        
        .order-table th:nth-child(3), 
        .order-table td:nth-child(3) {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="payment-container fade-transition">
    <div class="checkout-card">
        <h1 class="checkout-title">Complete Your Purchase</h1>
        
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="steps-line"></div>
            
            <div class="checkout-step completed">
                <div class="step-number">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-label">Cart</div>
            </div>
            
            <div class="checkout-step active">
                <div class="step-number">2</div>
                <div class="step-label">Checkout</div>
            </div>
            
            <div class="checkout-step">
                <div class="step-number">3</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>
        
        <!-- Order Items -->
        @php
            $chapterItems = $cart->items->where('item_type', 'chapter');
            $spellItems = $cart->items->where('item_type', 'spell');
        @endphp
        
        @if($chapterItems->count() > 0)
            <div class="order-section">
                <h3 class="order-section-title">Chapters</h3>
                
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Chapter</th>
                            <th class="text-center">Qty</th>
                            <th>Price</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chapterItems as $item)
                        <tr>
                            <td>
                                <div class="item-title">
                                    <span class="item-type-badge item-type-chapter">Chapter</span>
                                    Chapter {{ $item->chapter->id }}: {{ $item->chapter->title }}
                                </div>
                                <div class="item-description">{{ Str::limit($item->chapter->description, 80) }}</div>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td class="text-right">${{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        
        @if($spellItems->count() > 0)
            <div class="order-section">
                <h3 class="order-section-title">Spells</h3>
                
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Spell</th>
                            <th class="text-center">Qty</th>
                            <th>Price</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($spellItems as $item)
                        <tr>
                            <td>
                                <div class="item-title">
                                    <span class="item-type-badge item-type-spell">Spell</span>
                                    {{ $item->spell->title }}
                                </div>
                                <div class="item-description">{{ Str::limit($item->spell->description, 80) }}</div>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td class="text-right">${{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        
        <!-- Price Summary -->
        <table class="price-summary-table">
            <tr class="subtotal-row">
                <td class="label">Subtotal:</td>
                <td class="value">${{ number_format($cart->subtotal, 2) }}</td>
            </tr>
            <tr class="tax-row">
                <td class="label">GST (10%):</td>
                <td class="value">${{ number_format($cart->tax, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Total:</td>
                <td class="value">${{ number_format($cart->total, 2) }} AUD</td>
            </tr>
        </table>
        
        <!-- Payment Section -->
        <div class="payment-section">
            <h3>Complete Your Order</h3>
            
            <div class="secure-payment-note text-center mb-4">
                <i class="fas fa-lock"></i> Secure Payment with PayPal
            </div>
            
            <form action="{{ route('payment.processCart') }}" method="POST" id="checkout-form">
                @csrf
                <button type="submit" class="btn-paypal">
                    <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png" alt="Check out with PayPal">
                </button>
            </form>
            
            <div class="payment-note text-center mt-4">
                <p>You will be redirected to PayPal to complete your payment.</p>
                <p>After payment, you'll have immediate access to all purchased items.</p>
            </div>
        </div>
        
        <div class="back-link mt-4">
            <a href="{{ route('cart.index') }}" class="mystic-link">
                <i class="fas fa-arrow-left"></i> Back to Cart
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/components/checkout.js') }}" defer></script>
@endpush