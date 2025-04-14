@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/spells.css') }}" rel="stylesheet">
<link href="{{ asset('css/components/subscription-modal.css') }}" rel="stylesheet">
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
    
    .spell-owned-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0, 180, 0, 0.7);
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        z-index: 5;
    }
    
    .spell-status-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: bold;
        z-index: 5;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .free-with-chapter {
        background: rgba(0, 128, 0, 0.5);
        color: white;
    }
    
    .locked-spell {
        background: rgba(255, 0, 0, 0.5);
        color: white;
    }
    
    .unlocked-spell {
        background: rgba(0, 128, 0, 0.7);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="spells-container">
    <h1 class="spells-title">Mystical Spells</h1>
    
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
    <div class="spells-grid desktop-only">
        @foreach($spells as $spell)
            <div class="spell-card">
                @php
                    // Check if the spell is part of any chapter
                    $freeWithChapter = $spell->chapters()->where('is_free_with_chapter', true)->exists();
                    
                    // Check if user has unlocked via chapter purchase
                    $unlockedViaChapter = $spell->isAvailableThroughChapter();
                @endphp
                
                @if(in_array($spell->id, $userSpells ?? []))
                    <span class="spell-owned-badge">
                        <i class="fas fa-check-circle"></i> Owned
                    </span>
                @endif
                
                @if($freeWithChapter && !$unlockedViaChapter && !in_array($spell->id, $userSpells ?? []))
                    <span class="spell-status-badge free-with-chapter">
                        <i class="fas fa-gift"></i> Free with Chapter
                    </span>
                @endif
                
                <h2 class="spell-title">{{ $spell->title }}</h2>
                <p class="spell-price">${{ number_format($spell->price, 2) }} AUD</p>
                
                @if(in_array($spell->id, $userSpells ?? []) || $unlockedViaChapter)
                    <!-- User has access - show download button -->
                    <a href="{{ route('spells.download', $spell->id) }}" class="btn btn-portal">
                        <i class="fas fa-download"></i> Download Spell
                    </a>
                @else
                    <!-- User doesn't have access - show purchase options -->
                    <div class="spell-actions">
                        <a href="{{ route('spells.show', $spell->id) }}" class="btn btn-sm" title="View Details" style="color: white;">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form action="{{ route('cart.addSpell') }}" method="POST">
                            @csrf
                            <input type="hidden" name="spell_id" value="{{ $spell->id }}">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="btn btn-buy-now btn-sm" style="width: 90%;">Buy Now</button>
                        </form>
                        
                        <form action="{{ route('cart.addSpell') }}" method="POST" class="add-to-cart-form">
                            @csrf
                            <input type="hidden" name="spell_id" value="{{ $spell->id }}">
                            <button type="submit" class="btn btn-add-cart btn-sm {{ in_array($spell->id, $spellsInCart ?? []) ? 'btn-disabled' : '' }}" 
                                    {{ in_array($spell->id, $spellsInCart ?? []) ? 'disabled' : '' }}  style="width: 90%;">
                                <i class="fas {{ in_array($spell->id, $spellsInCart ?? []) ? 'fa-check' : 'fa-cart-plus' }}"></i> 
                                {{ in_array($spell->id, $spellsInCart ?? []) ? 'Added' : 'Add to Cart' }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
        
        @if(count($spells) == 0)
            <div class="no-spells-message">
                <p>No spells available at this time. Check back soon for mystical additions.</p>
            </div>
        @endif
    </div>
    
    <!-- Mobile View - List Layout -->
    <div class="spells-list mobile-only">
        @foreach($spells as $spell)
            @php
                // Check if the spell is part of any chapter
                $freeWithChapter = $spell->chapters()->where('is_free_with_chapter', true)->exists();
                
                // Check if user has unlocked via chapter purchase
                $unlockedViaChapter = $spell->isAvailableThroughChapter();
            @endphp
            
            <div class="spell-list-item">
                <div class="spell-info">
                    <h2 class="spell-title">
                        {{ $spell->title }}
                        @if(in_array($spell->id, $userSpells ?? []))
                            <span class="badge bg-success" style="font-size: 0.6rem; vertical-align: middle;">Owned</span>
                        @elseif($unlockedViaChapter)
                            <span class="badge bg-success" style="font-size: 0.6rem; vertical-align: middle;">Unlocked</span>
                        @elseif($freeWithChapter)
                            <span class="badge bg-danger" style="font-size: 0.6rem; vertical-align: middle;">Locked</span>
                        @endif
                        
                        @if($freeWithChapter && !$unlockedViaChapter && !in_array($spell->id, $userSpells ?? []))
                            <span class="badge bg-info" style="font-size: 0.6rem; vertical-align: middle;">Free with Chapter</span>
                        @endif
                    </h2>
                    <p class="spell-price">${{ number_format($spell->price, 2) }} AUD</p>
                </div>
                
                @if(in_array($spell->id, $userSpells ?? []) || $unlockedViaChapter)
                    <a href="{{ route('spells.download', $spell->id) }}" class="btn-portal btn-sm" style="width:30%;">Download</a>
                @else
                    <div class="spell-actions-mobile">
                        <form action="{{ route('cart.addSpell') }}" method="POST" class="add-to-cart-form">
                            @csrf
                            <input type="hidden" name="spell_id" value="{{ $spell->id }}">
                            <button type="submit" class="btn-add-cart-mobile {{ in_array($spell->id, $spellsInCart ?? []) ? 'btn-disabled' : '' }}"
                                    {{ in_array($spell->id, $spellsInCart ?? []) ? 'disabled' : '' }}>
                                <i class="fas {{ in_array($spell->id, $spellsInCart ?? []) ? 'fa-check' : 'fa-cart-plus' }}"></i>
                            </button>
                        </form>
                        
                        <form action="{{ route('cart.addSpell') }}" method="POST">
                            @csrf
                            <input type="hidden" name="spell_id" value="{{ $spell->id }}">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="btn-portal btn-buy-now btn-sm" style="width:100% !importanant;">Buy</button>
                        </form>
                    </div>
                @endif
                <a href="{{ route('spells.show', $spell->id) }}" class="spell-details-link" style="margin-left:0.5rem;"><i class="fas fa-eye"></i></a>
            </div>
        @endforeach
        
        @if(count($spells) == 0)
            <div class="no-spells-message-mobile">
                <p>No spells available at this time. Check back soon.</p>
            </div>
        @endif
    </div>
    
    <!-- Floating Cart Button -->
    @if(isset($cartItemCount) && $cartItemCount > 0)
    <a href="{{ route('cart.index') }}" class="floating-cart-button">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-items-count">{{ $cartItemCount }}</span>
    </a>
    @endif

    @include('partials.subscription-modal')
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
<script src="{{ asset('js/components/subscription-modal.js') }}" defer></script>
@endpush