@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/chapters.css') }}" rel="stylesheet">
<style>
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
    
    .btn-disabled {
        background: rgba(138, 43, 226, 0.2) !important;
        border-color: rgba(138, 43, 226, 0.3) !important;
        cursor: not-allowed !important;
        transform: none !important;
        box-shadow: none !important;
    }
</style>
@endpush

@section('content')
<div class="chapters-container">
    <h1 class="chapters-title">Chapters</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif
    
    <!-- Desktop View - Grid Layout -->
    <div class="chapters-grid desktop-only">
        @foreach($chapters as $chapter)
            <div class="chapter-card">
                <h2 class="chapter-title">Chapter {{ $chapter->order }}</h2>
                <p class="chapter-description">{{ $chapter->description }}</p>
                @if($chapter->isFree())
                    <p class="chapter-price"><span class="free-badge">Free</span></p>
                @else
                    <p class="chapter-price">${{ number_format($chapter->price, 2) }} AUD</p>
                @endif
                
                @if($chapter->isPurchased())
                    <a href="{{ route('chapters.read', $chapter->id) }}" class="btn btn-portal">Read Now</a>
                @else
                    <div class="chapter-actions">
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="btn btn-portal btn-buy-now btn-sm">Buy Now</button>
                        </form>
                        
                        <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form">
                            @csrf
                            <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                            <button type="submit" class="btn btn-add-cart btn-sm {{ in_array($chapter->id, $chaptersInCart ?? []) ? 'btn-disabled' : '' }}" 
                                    {{ in_array($chapter->id, $chaptersInCart ?? []) ? 'disabled' : '' }}>
                                <i class="fas {{ in_array($chapter->id, $chaptersInCart ?? []) ? 'fa-check' : 'fa-cart-plus' }}"></i> 
                                {{ in_array($chapter->id, $chaptersInCart ?? []) ? 'Added' : 'Add to Cart' }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    
    <!-- Mobile View - List Layout -->
    <div class="chapters-list mobile-only">
        @foreach($chapters as $chapter)
            <div class="chapter-list-item">
                <div class="chapter-info">
                    <h2 class="chapter-title">Chapter {{ $chapter->order }}</h2>
                    @if($chapter->isFree())
                        <p class="chapter-price"><span class="free-badge">Free</span></p>
                    @else
                        <p class="chapter-price">${{ number_format($chapter->price, 2) }} AUD</p>
                    @endif
                </div>
                
                @if($chapter->isPurchased())
                    <a href="{{ route('chapters.read', $chapter->id) }}" class="btn-portal btn-sm">Read Now</a>
                @else
                    <div class="chapter-actions-mobile">
                        <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form">
                            @csrf
                            <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                            <button type="submit" class="btn-add-cart-mobile {{ in_array($chapter->id, $chaptersInCart ?? []) ? 'btn-disabled' : '' }}"
                                    {{ in_array($chapter->id, $chaptersInCart ?? []) ? 'disabled' : '' }}>
                                <i class="fas {{ in_array($chapter->id, $chaptersInCart ?? []) ? 'fa-check' : 'fa-cart-plus' }}"></i>
                            </button>
                        </form>
                        
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="btn-portal btn-buy-now btn-sm">Buy</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    
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