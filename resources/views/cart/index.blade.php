@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/cart.css') }}" rel="stylesheet">
<style>
    .cart-item {
        position: relative;
        transition: all 0.3s ease;
    }
    
    .remove-btn {
        background: rgba(255, 75, 75, 0.2);
        border: none;
        border-radius: 50%;
        color: #ff4b4b;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .remove-btn:hover {
        background: rgba(255, 75, 75, 0.4);
        transform: scale(1.1);
    }
    
    .remove-confirmation {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(10, 10, 30, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        border-radius: 8px;
        z-index: 10;
    }
    
    .remove-confirmation.active {
        opacity: 1;
        visibility: visible;
    }
    
    .confirmation-buttons {
        display: flex;
        gap: 10px;
    }
    
    .btn-confirm-remove {
        background: #ff4b4b;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .btn-cancel-remove {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
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
    
    .cart-section-divider {
        border-top: 1px dashed rgba(138, 43, 226, 0.3);
        margin: 20px 0;
        padding-top: 15px;
    }
    
    .cart-section-title {
        color: #d8b5ff;
        margin-bottom: 15px;
        font-size: 1.1rem;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="cart-container fade-transition">
    <h1 class="cart-title">Your Mystical Cart</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if($cart->items->isEmpty())
        <div class="cart-empty">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>Your cart is empty</h2>
            <p>Explore the mystical chapters and spells to begin your cosmic journey.</p>
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
                <a href="{{ route('chapters.index') }}" class="btn-checkout">Browse Chapters</a>
                <a href="{{ route('spells.index') }}" class="btn-checkout">Browse Spells</a>
            </div>
        </div>
    @else
        <div class="cart-items">
            <!-- Chapter Items -->
            @php
                $chapterItems = $cart->items->where('item_type', 'chapter');
                $spellItems = $cart->items->where('item_type', 'spell');
            @endphp
            
            @if($chapterItems->count() > 0)
                <h3 class="cart-section-title">Chapters</h3>
                
                @foreach($chapterItems as $item)
                    <div class="cart-item" id="cart-item-{{ $item->id }}">
                        <div class="item-details">
                            <h3 class="item-title">
                                @if($item->item_type == 'chapter' && $item->chapter)
                                    <span class="item-type-badge item-type-chapter">Chapter</span>
                                    Chapter {{ $item->chapter->order }}: {{ $item->chapter->title }}
                                @elseif($item->item_type == 'spell' && $item->spell)
                                    <span class="item-type-badge item-type-spell">Spell</span>
                                    {{ $item->spell->title }}
                                @else
                                    <span class="item-type-badge">Unknown Item</span>
                                @endif
                            </h3>
                            <p class="item-description">
                                @if($item->item_type == 'chapter' && $item->chapter)
                                    {{ Str::limit($item->chapter->description, 100) }}
                                @elseif($item->item_type == 'spell' && $item->spell)
                                    {{ Str::limit($item->spell->description, 100) }}
                                @else
                                    No description available
                                @endif
                            </p>
                        </div>
                        
                        <div class="item-price">${{ number_format($item->total, 2) }}</div>
                        
                        <div class="item-remove">
                            <button type="button" class="remove-btn" onclick="showRemoveConfirmation('{{ $item->id }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        
                        <!-- Remove Confirmation Overlay -->
                        <div class="remove-confirmation" id="remove-confirmation-{{ $item->id }}">
                            <div class="confirm-content">
                                <p>Remove this chapter from your cart?</p>
                                <div class="confirmation-buttons">
                                    <form action="{{ route('cart.remove') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <button type="submit" class="btn-confirm-remove">Remove</button>
                                    </form>
                                    <button type="button" class="btn-cancel-remove" onclick="hideRemoveConfirmation('{{ $item->id }}')">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
            
            <!-- Spell Items -->
            @if($spellItems->count() > 0)
                @if($chapterItems->count() > 0)
                    <div class="cart-section-divider"></div>
                @endif
                
                <h3 class="cart-section-title">Spells</h3>
                
                @foreach($spellItems as $item)
                    <div class="cart-item" id="cart-item-{{ $item->id }}">
                        <div class="item-details">
                            <h3 class="item-title">
                                <span class="item-type-badge item-type-spell">Spell</span>
                                {{ $item->spell->title }}
                            </h3>
                            <p class="item-description">{{ Str::limit($item->spell->description, 100) }}</p>
                        </div>
                        
                        <div class="item-price">${{ number_format($item->total, 2) }}</div>
                        
                        <div class="item-remove">
                            <button type="button" class="remove-btn" onclick="showRemoveConfirmation('{{ $item->id }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        
                        <!-- Remove Confirmation Overlay -->
                        <div class="remove-confirmation" id="remove-confirmation-{{ $item->id }}">
                            <div class="confirm-content">
                                <p>Remove this spell from your cart?</p>
                                <div class="confirmation-buttons">
                                    <form action="{{ route('cart.remove') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <button type="submit" class="btn-confirm-remove">Remove</button>
                                    </form>
                                    <button type="button" class="btn-cancel-remove" onclick="hideRemoveConfirmation('{{ $item->id }}')">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        
        <div class="cart-summary">
            <h3 class="summary-title">Order Summary</h3>
            
            <div class="summary-row">
                <div class="summary-label">Subtotal</div>
                <div class="summary-value">${{ number_format($cart->subtotal, 2) }}</div>
            </div>
            
            <div class="summary-row">
                <div class="summary-label">GST (10%)</div>
                <div class="summary-value">${{ number_format($cart->tax, 2) }}</div>
            </div>
            
            <div class="summary-row total">
                <div class="summary-label">Total</div>
                <div class="summary-value">${{ number_format($cart->total, 2) }}</div>
            </div>
            
            <div class="cart-actions">
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('chapters.index') }}" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i> Chapters
                    </a>
                    <a href="{{ route('spells.index') }}" class="continue-shopping">
                        <i class="fas fa-magic"></i> Spells
                    </a>
                </div>
                
                <a href="{{ route('cart.checkout') }}" class="btn-checkout">
                    Proceed to Checkout
                </a>
            </div>
            
            <form action="{{ route('cart.clear') }}" method="POST" class="text-center">
                @csrf
                <button type="submit" class="btn-clear-cart">Clear Cart</button>
            </form>
            
            <div class="tax-note">
                * All prices include 10% GST for Australian customers
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Remove confirmation functions
    function showRemoveConfirmation(itemId) {
        const confirmation = document.getElementById(`remove-confirmation-${itemId}`);
        confirmation.classList.add('active');
    }
    
    function hideRemoveConfirmation(itemId) {
        const confirmation = document.getElementById(`remove-confirmation-${itemId}`);
        confirmation.classList.remove('active');
    }
</script>
@endpush
@endsection