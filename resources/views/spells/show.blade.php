@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/spells.css') }}" rel="stylesheet">
<style>
    /* Additional styles specific to the spell detail page */
    .spell-detail-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .spell-title {
        text-align: center;
        color: #fff;
        font-family: 'Cinzel', serif;
        margin-bottom: 20px;
        font-size: 2rem;
        letter-spacing: 2px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .spell-detail-card {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 10px;
        padding: 30px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
        margin-bottom: 30px;
    }
    
    .spell-description {
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.8;
        font-size: 1.1rem;
        margin-bottom: 30px;
    }
    
    .spell-price-section {
        text-align: center;
        padding: 20px;
        background: rgba(10, 10, 30, 0.5);
        border-radius: 8px;
        margin: 30px 0;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .spell-price {
        font-size: 1.6rem;
        color: #d8b5ff;
        margin-bottom: 20px;
    }
    
    .spell-actions {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
    }
    
    .spell-actions form {
        width: 40%;
    }
    
    .btn-portal, .btn-add-cart {
        width: 100%;
    }
    
    .related-chapters {
        margin-top: 40px;
    }
    
    .related-chapters-title {
        color: #fff;
        font-family: 'Cinzel', serif;
        font-size: 1.4rem;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .chapters-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
    
    .chapter-card {
        background: rgba(10, 10, 30, 0.6);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .chapter-title {
        color: #fff;
        font-family: 'Cinzel', serif;
        margin-bottom: 10px;
        font-size: 1.2rem;
    }
    
    .chapter-brief {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 15px;
        font-size: 0.9rem;
    }
    
    .free-with-chapter {
        background: rgba(0, 128, 0, 0.2);
        color: #a0ffa0;
        padding: 8px 15px;
        border-radius: 20px;
        text-align: center;
        margin-top: 10px;
        font-size: 0.9rem;
        display: inline-block;
    }
    
    .access-note {
        background: rgba(138, 43, 226, 0.1);
        padding: 15px 20px;
        border-radius: 8px;
        text-align: center;
        margin: 30px 0;
        line-height: 1.6;
    }
    
    .unlock-note {
        background: rgba(0, 128, 0, 0.1);
        padding: 15px 20px;
        border-radius: 8px;
        text-align: center;
        margin: 30px 0;
        line-height: 1.6;
        border: 1px dashed rgba(0, 128, 0, 0.3);
    }
    
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        margin-bottom: 15px;
    }
    
    .badge-free {
        background: rgba(0, 128, 0, 0.3);
        color: #a0ffa0;
    }
    
    .badge-owned {
        background: rgba(0, 128, 0, 0.5);
        color: white;
    }
    
    .btn-back {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
        color: #d8b5ff;
        text-decoration: none;
        padding: 5px 0;
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        color: #fff;
        text-shadow: 0 0 10px rgba(138, 43, 226, 0.7);
    }
    
    .btn-back i {
        margin-right: 5px;
    }
    
    @media (max-width: 767px) {
        .spell-title {
            font-size: 1.6rem;
        }
        
        .spell-detail-card {
            padding: 20px;
        }
        
        .spell-actions {
            flex-direction: column;
            gap: 10px;
        }
        
        .spell-actions form {
            width: 100%;
        }
        
        .chapters-list {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="spell-detail-container">
    <a href="{{ route('spells.index') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back to Spells
    </a>

    <h1 class="spell-title">{{ $spell->title }}</h1>
    
    <div class="spell-detail-card">
        @if($isOwned)
            <div class="badge badge-owned">
                <i class="fas fa-check-circle"></i> You Own This Spell
            </div>
        @elseif($hasAccessThroughChapter)
            <div class="badge badge-free">
                <i class="fas fa-unlock"></i> Free with Purchased Chapter
            </div>
        @endif
        
        <div class="spell-description">
            {!! nl2br(e($spell->description)) !!}
        </div>
        
        <!-- Conditional display based on ownership status -->
        @if($isOwned || $hasAccessThroughChapter)
            <div class="unlock-note">
                <i class="fas fa-magic" style="font-size: 2rem; margin-bottom: 15px; color: #a0ffa0;"></i>
                <h3>You have access to this spell!</h3>
                <p>Download this mystical spell to enhance your journey</p>
                <a href="{{ route('spells.download', $spell->id) }}" class="btn btn-portal" style="margin-top: 15px;">
                    <i class="fas fa-download"></i> Download Spell
                </a>
            </div>
        @else
            <div class="spell-price-section">
                <p class="spell-price">${{ number_format($spell->price, 2) }} AUD</p>
                <div class="spell-actions">
                    <form action="{{ route('cart.addSpell') }}" method="POST">
                        @csrf
                        <input type="hidden" name="spell_id" value="{{ $spell->id }}">
                        <input type="hidden" name="buy_now" value="1">
                        <button type="submit" class="btn btn-portal">Buy Now</button>
                    </form>
                    
                    <form action="{{ route('cart.addSpell') }}" method="POST">
                        @csrf
                        <input type="hidden" name="spell_id" value="{{ $spell->id }}">
                        <button type="submit" class="btn btn-add-cart">Add to Cart</button>
                    </form>
                </div>
            </div>
            
            @if(count($relatedChapters) > 0)
                <div class="access-note">
                    <p>This spell is also available for free when you purchase select chapters.</p>
                </div>
            @endif
        @endif
    </div>
    
    @if(count($relatedChapters) > 0)
        <div class="related-chapters">
            <h2 class="related-chapters-title">Chapters Containing This Spell</h2>
            <div class="chapters-list">
                @foreach($relatedChapters as $chapter)
                    <div class="chapter-card">
                        <h3 class="chapter-title">Chapter {{ $chapter->id }}: {{ $chapter->title }}</h3>
                        <p class="chapter-brief">{{ \Illuminate\Support\Str::limit($chapter->description, 120) }}</p>
                        
                        <p class="chapter-price">${{ number_format($chapter->price, 2) }} AUD</p>
                        
                        @if($chapter->pivot->is_free_with_chapter)
                            <div class="free-with-chapter">
                                <i class="fas fa-gift"></i> Spell included free with this chapter
                            </div>
                        @endif
                        
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="{{ route('chapters.show', $chapter->id) }}" class="btn btn-portal btn-sm">View Chapter</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection