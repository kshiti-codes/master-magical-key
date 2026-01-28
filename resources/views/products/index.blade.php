@extends('layouts.app')

@push('styles')
<style>
    .products-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
        position: relative;
        z-index: 1;
    }
    
    .products-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: 15px;
        letter-spacing: 4px;
        text-shadow: 0 0 20px rgba(138, 43, 226, 0.9);
        animation: glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes glow {
        from {
            text-shadow: 0 0 20px rgba(138, 43, 226, 0.7), 0 0 30px rgba(138, 43, 226, 0.5);
        }
        to {
            text-shadow: 0 0 30px rgba(138, 43, 226, 0.9), 0 0 40px rgba(138, 43, 226, 0.7);
        }
    }
    
    .products-subtitle {
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 40px;
        font-size: 1.1rem;
        letter-spacing: 2px;
    }
    
    .alert {
        max-width: 800px;
        margin: 0 auto 30px;
        padding: 15px 20px;
        border-radius: 10px;
        text-align: center;
    }
    
    .alert-success {
        background: rgba(0, 128, 0, 0.2);
        border: 1px solid rgba(0, 255, 0, 0.4);
        color: #90EE90;
    }
    
    .alert-info {
        background: rgba(0, 123, 255, 0.2);
        border: 1px solid rgba(0, 123, 255, 0.4);
        color: #87CEEB;
    }
    
    /* Desktop View - Grid Layout */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }
    
    .product-card {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 15px;
        padding: 25px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
        backdrop-filter: blur(10px);
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 40px rgba(138, 43, 226, 0.5);
        border-color: rgba(138, 43, 226, 0.8);
    }
    
    .product-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 150%;
        height: 150%;
        background: radial-gradient(circle, rgba(138, 43, 226, 0.1) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.4s ease;
        pointer-events: none;
    }
    
    .product-card:hover::before {
        opacity: 1;
    }
    
    .product-image-container {
        width: 100%;
        height: 200px;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 20px;
        background: rgba(138, 43, 226, 0.1);
        border: 1px solid rgba(138, 43, 226, 0.3);
        position: relative;
    }
    
    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.1);
    }
    
    .no-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        color: rgba(255, 255, 255, 0.5);
    }
    
    .no-image-placeholder i {
        font-size: 3rem;
        margin-bottom: 10px;
    }
    
    .product-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(138, 43, 226, 0.9);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: bold;
        letter-spacing: 1px;
        z-index: 2;
    }
    
    .product-owned-badge {
        background: rgba(0, 200, 0, 0.9);
    }
    
    .product-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.2rem;
        letter-spacing: 1px;
        margin-bottom: 15px;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    
    .product-description {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
        text-align: center;
        min-height: 80px;
    }
    
    .product-price-container {
        text-align: center;
        margin-bottom: 20px;
        padding: 10px;
        background: rgba(138, 43, 226, 0.1);
        border-radius: 10px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .product-price {
        font-size: 1.2rem;
        color: #fff;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .product-price-gst {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.6);
    }
    
    .product-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
    }
    
    .btn-view-product {
        flex: 1;
        background: rgba(138, 43, 226, 0.6);
        color: white;
        border: 1px solid rgba(138, 43, 226, 0.8);
        padding: 12px 20px;
        border-radius: 30px;
        cursor: pointer;
        pointer-events: auto;
        font-size: 1rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 500;
        letter-spacing: 1px;
    }
    
    .btn-view-product:hover {
        background: rgba(138, 43, 226, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(138, 43, 226, 0.5);
        text-decoration: none;
        color: white;
    }
    
    /* Form button styling */
    form button.btn-view-product {
        font-family: inherit;
    }
    
    .btn-add-cart {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 12px 20px;
        border-radius: 30px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .btn-add-cart:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
    }
    
    .btn-disabled {
        background: rgba(100, 100, 100, 0.3);
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .btn-disabled:hover {
        transform: none;
        box-shadow: none;
    }
    
    .no-products-message {
        text-align: center;
        padding: 60px 20px;
        background: rgba(10, 10, 30, 0.8);
        border-radius: 15px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
    }
    
    .no-products-message p {
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.2rem;
        margin: 0;
    }
    
    /* Mobile View - List Layout */
    .products-list {
        display: none;
    }
    
    .product-list-item {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 20px rgba(138, 43, 226, 0.3);
        backdrop-filter: blur(10px);
    }
    
    .product-info {
        margin-bottom: 15px;
    }
    
    .product-list-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.3rem;
        margin-bottom: 10px;
    }
    
    .product-list-price {
        font-size: 1.5rem;
        color: #fff;
        font-weight: bold;
    }
    
    .product-actions-mobile {
        display: flex;
        gap: 10px;
    }
    
    /* Pagination */
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 40px;
    }
    
    .pagination {
        display: flex;
        gap: 10px;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .pagination li a,
    .pagination li span {
        display: inline-block;
        padding: 10px 15px;
        background: rgba(138, 43, 226, 0.3);
        color: white;
        border-radius: 8px;
        border: 1px solid rgba(138, 43, 226, 0.5);
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .pagination li a:hover {
        background: rgba(138, 43, 226, 0.6);
        transform: translateY(-2px);
    }
    
    .pagination li.active span {
        background: rgba(138, 43, 226, 0.8);
        border-color: rgba(138, 43, 226, 0.9);
    }
    
    .pagination li.disabled span {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Responsive Design */
    .desktop-only {
        display: grid;
    }
    
    .mobile-only {
        display: none;
    }
    
    @media (max-width: 768px) {
        .desktop-only {
            display: none;
        }
        
        .mobile-only {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
        }
        
        .products-title {
            font-size: 1rem;
        }
        
        .products-subtitle {
            font-size: 1rem;
        }
        
        .products-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .product-list-title {
            font-size: 0.85rem;
        }

        .product-list-price {
            font-size: 0.85rem;
        }
        
        .btn-view-product {
            font-size: 0.85rem;
            padding: 8px 15px;
        }

        .btn-add-cart {
            font-size: 0.85rem;
            padding: 8px 15px;
        }
    }

    .floating-cart-button {
        position: fixed;
        bottom: 60px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(to right, #4b0082, #9400d3);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 15px rgba(138, 43, 226, 0.5);
        transition: all 0.3s ease;
        z-index: 100;
        text-decoration: none;
    }
    
    .floating-cart-button:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(138, 43, 226, 0.7);
        color: white;
    }

    .cart-items-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ff3366;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 12px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
</style>
@endpush

@section('content')
<div class="products-container">
    <h1 class="products-title">✨ The Master Magical Keys ✨</h1>
    <p class="products-subtitle">Transform Your Reality with Divine Intelligence</p>
    
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    
    @if(session('info'))
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
        </div>
    @endif
    
    <!-- Desktop View - Grid Layout -->
    <div class="products-grid desktop-only">
        @foreach($products as $product)
            <div class="product-card">
                @php
                    $hasPurchased = false;
                    if (Auth::check()) {
                        $hasPurchased = $product->isPurchasedBy(Auth::id());
                    }
                @endphp
                
                @if($hasPurchased)
                    <span class="product-badge product-owned-badge">
                        <i class="fas fa-check-circle"></i> OWNED
                    </span>
                @elseif($product->price == 0)
                    <span class="product-badge" style="background: rgba(0, 200, 0, 0.9);">
                        <i class="fas fa-gift"></i> FREE
                    </span>
                @else
                    <span class="product-badge">
                        {{ strtoupper(str_replace('_', ' ', $product->type)) }}
                    </span>
                @endif
                
                <div class="product-image-container">
                    @if($product->image)
                        <img src="{{ $product->image_url }}" alt="{{ $product->title }}" class="product-image">
                    @else
                        <div class="no-image-placeholder">
                            <i class="fas fa-key"></i>
                            <span>{{ $product->title }}</span>
                        </div>
                    @endif
                </div>
                
                <h2 class="product-title">{{ $product->title }}</h2>
                
                <div class="product-price-container">
                    <div class="product-price">${{ number_format($product->price, 2) }} AUD</div>
                    @if($product->price > 0)
                        <div class="product-price-gst">
                            <i class="fas fa-info-circle"></i> 
                            ${{ number_format($product->price_with_gst, 2) }} AUD inc. GST
                        </div>
                    @else
                        <div class="product-price-gst">
                            <i class="fas fa-gift"></i> 
                            No payment required
                        </div>
                    @endif
                </div>
                
                @if($hasPurchased || $product->price == 0)
                    <div class="product-actions">
                        <a href="{{ route('products.show', $product->slug) }}" class="btn-view-product">
                            <i class="fas fa-download"></i> Access
                        </a>
                    </div>
                @else
                    <div class="product-actions">
                        @auth
                            <form action="{{ route('products.add-to-cart', $product->slug) }}" method="POST" style="flex: 1;">
                                @csrf
                                <button type="submit" 
                                        class="btn-view-product {{ in_array($product->id, $productsInCart ?? []) ? 'btn-disabled' : '' }}" 
                                        style="width: 100%; border: none;"
                                        {{ in_array($product->id, $productsInCart ?? []) ? 'disabled' : '' }}>
                                    <i class="fas {{ in_array($product->id, $productsInCart ?? []) ? 'fa-check' : 'fa-cart-plus' }}"></i> 
                                    {{ in_array($product->id, $productsInCart ?? []) ? 'Added' : 'Add to Cart' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-view-product">
                                <i class="fas fa-sign-in-alt"></i> Login to Purchase
                            </a>
                        @endauth
                    </div>
                @endif
            </div>
        @endforeach
        
        @if(count($products) == 0)
            <div class="no-products-message" style="grid-column: 1 / -1;">
                <i class="fas fa-key" style="font-size: 3rem; color: rgba(138, 43, 226, 0.7); margin-bottom: 20px;"></i>
                <p>No products available at this time. Check back soon for magical additions.</p>
            </div>
        @endif
    </div>
    
    <!-- Mobile View - List Layout -->
    <div class="products-list mobile-only">
        @foreach($products as $product)
            <div class="product-list-item">
                @php
                    $hasPurchased = false;
                    if (Auth::check()) {
                        $hasPurchased = $product->isPurchasedBy(Auth::id());
                    }
                @endphp
                
                <div class="product-info">
                    <h2 class="product-list-title">
                        {{ $product->title }}
                        @if($hasPurchased)
                            <span class="product-badge product-owned-badge" style="font-size: 0.6rem; vertical-align: middle;">
                                <i class="fas fa-check-circle"></i> Owned
                            </span>
                        @elseif($product->price == 0)
                            <span class="product-badge" style="font-size: 0.6rem; vertical-align: middle; background: rgba(0, 200, 0, 0.9);">
                                <i class="fas fa-gift"></i> FREE
                            </span>
                        @endif
                    </h2>
                    <p class="product-list-price">${{ number_format($product->price, 2) }} AUD</p>
                    @if($product->price > 0)
                        <p style="color: rgba(255, 255, 255, 0.6); font-size: 0.75rem; margin-top: 2px;">
                            <i class="fas fa-info-circle"></i> ${{ number_format($product->price_with_gst, 2) }} AUD inc. GST
                        </p>
                    @else
                        <p style="color: rgba(0, 255, 0, 0.7); font-size: 0.75rem; margin-top: 2px;">
                            <i class="fas fa-gift"></i> No payment required
                        </p>
                    @endif
                </div>
                
                @if($hasPurchased || $product->price == 0)
                    <div class="product-actions-mobile">
                        <a href="{{ route('products.show', $product->slug) }}" class="btn-view-product" style="width: 100%;">
                            <i class="fas fa-download"></i> Access
                        </a>
                    </div>
                @else
                    <div class="product-actions-mobile">
                        @auth
                            <form action="{{ route('products.add-to-cart', $product->slug) }}" method="POST" style="width: 100%;">
                                @csrf
                                <button type="submit" 
                                        class="btn-view-product {{ in_array($product->id, $productsInCart ?? []) ? 'btn-disabled' : '' }}" 
                                        style="width: 100%; border: none; font-size: 1rem;"
                                        {{ in_array($product->id, $productsInCart ?? []) ? 'disabled' : '' }}>
                                    <i class="fas {{ in_array($product->id, $productsInCart ?? []) ? 'fa-check' : 'fa-cart-plus' }}"></i> 
                                    {{ in_array($product->id, $productsInCart ?? []) ? 'Added' : 'Add to Cart' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-view-product" style="width: 100%;">
                                <i class="fas fa-sign-in-alt"></i> Login to Purchase
                            </a>
                        @endauth
                    </div>
                @endif
            </div>
        @endforeach
        
        @if(count($products) == 0)
            <div class="no-products-message">
                <i class="fas fa-key" style="font-size: 3rem; color: rgba(138, 43, 226, 0.7); margin-bottom: 20px;"></i>
                <p>No products available at this time. Check back soon for magical additions.</p>
            </div>
        @endif
    </div>
    
    <!-- Pagination -->
    @if($products->hasPages())
        <div class="pagination-container">
            {{ $products->links() }}
        </div>
    @endif

    <!-- Floating Cart Button -->
    @if(isset($cartItemCount) && $cartItemCount > 0)
    <a href="{{ route('cart.index') }}" class="floating-cart-button">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-items-count">{{ $cartItemCount }}</span>
    </a>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Disable form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        // Disable add-to-cart buttons after submission to prevent double-clicks
        const addToCartForms = document.querySelectorAll('.add-to-cart-form');
                
        addToCartForms.forEach(form => {
            form.addEventListener('submit', function() {
                const button = this.querySelector('button');
                button.disabled = true;
                button.classList.add('btn-disabled');
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            });
        });
    });
</script>
@endpush