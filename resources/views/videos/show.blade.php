@extends('layouts.app')

@push('styles')
<style>
    .video-detail-container {
        max-width: 1100px;
        margin: 80px auto;
        padding: 0 20px;
    }
    
    .video-card {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(138, 43, 226, 0.3);
        margin-top: 30px;
    }
    
    .video-header {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        padding: 30px;
    }
    
    .video-thumbnail-large {
        width: 100%;
        max-width: 400px;
        height: 225px;
        background-color: #1a1a3a;
        border-radius: 8px;
        overflow: hidden;
        position: relative;
        border: 1px solid rgba(138, 43, 226, 0.4);
    }
    
    .video-thumbnail-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .video-play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80px;
        height: 80px;
        background: rgba(138, 43, 226, 0.7);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 2rem;
        transition: all 0.3s ease;
        cursor: pointer;
        opacity: 0.8;
    }
    
    .video-play-btn:hover {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1.1);
        background: rgba(138, 43, 226, 0.9);
    }
    
    .video-duration-large {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    
    .video-info {
        flex: 1;
        min-width: 300px;
    }
    
    .video-title-large {
        font-family: 'Cinzel', serif;
        color: white;
        font-size: 2rem;
        margin-bottom: 15px;
        line-height: 1.2;
        text-align: left;
    }
    
    .video-description-full {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 20px;
        line-height: 1.6;
    }
    
    .video-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .meta-item i {
        color: #d8b5ff;
    }
    
    .video-price-large {
        font-size: 1.8rem;
        font-weight: 500;
        color: #d8b5ff;
        margin-bottom: 20px;
    }
    
    .price-label {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.7);
        margin-right: 10px;
    }
    
    .free-with-subscription {
        display: inline-block;
        background: linear-gradient(to right, #00b09b, #96c93d);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin-left: 10px;
        vertical-align: middle;
    }
    
    .owned-badge {
        display: inline-block;
        background: linear-gradient(to right, #4b0082, #9400d3);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin-left: 10px;
        vertical-align: middle;
    }
    
    .video-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .btn-purchase, .btn-watch, .btn-add-cart {
        padding: 12px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 160px;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .btn-purchase {
        background: linear-gradient(to right, #8a2be2, #4b0082);
        color: white;
        border: none;
        cursor: pointer;
    }
    
    .btn-purchase:hover {
        background: linear-gradient(to right, #9400d3, #8a2be2);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(75, 0, 130, 0.3);
        color: white;
    }
    
    .btn-watch {
        background: linear-gradient(to right, #4b0082, #9400d3);
        color: white;
        border: none;
    }
    
    .btn-watch:hover {
        background: linear-gradient(to right, #9400d3, #4b0082);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(75, 0, 130, 0.3);
        color: white;
    }
    
    .btn-add-cart {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(138, 43, 226, 0.4);
    }
    
    .btn-add-cart:hover {
        background: rgba(138, 43, 226, 0.2);
        color: white;
    }
    
    .video-content {
        padding: 0 30px 30px 30px;
    }
    
    .content-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.3rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .content-section {
        margin-bottom: 30px;
    }
    
    .feature-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .feature-item {
        padding: 10px 0;
        display: flex;
        align-items: start;
        gap: 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.15);
    }
    
    .feature-item:last-child {
        border-bottom: none;
    }
    
    .feature-icon {
        color: #d8b5ff;
        font-size: 1.1rem;
        margin-top: 3px;
    }
    
    .feature-text {
        flex: 1;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .subscription-promo {
        background: rgba(15, 15, 40, 0.6);
        border-radius: 8px;
        padding: 20px;
        margin-top: 30px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        text-align: center;
    }
    
    .promo-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.2rem;
        margin-bottom: 10px;
    }
    
    .promo-text {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 15px;
    }
    
    .btn-view-plans {
        display: inline-block;
        background: linear-gradient(to right, #8a2be2, #4b0082);
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-view-plans:hover {
        background: linear-gradient(to right, #9400d3, #8a2be2);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(75, 0, 130, 0.3);
        color: white;
    }
    
    .back-link {
        display: inline-block;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    @media (max-width: 767px) {
        .video-header {
            flex-direction: column;
            padding: 20px;
        }
        
        .video-thumbnail-large {
            max-width: none;
            width: 100%;
        }
        
        .video-title-large {
            font-size: 1.6rem;
        }
        
        .video-actions {
            flex-direction: column;
        }
        
        .btn-purchase, .btn-watch, .btn-add-cart {
            width: 100%;
        }
        
        .video-content {
            padding: 0 20px 20px 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="video-detail-container">
    <div class="back-link">
        <a href="{{ route('videos.index') }}" class="mystic-link">
            <i class="fas fa-arrow-left"></i> Back to Training Videos
        </a>
    </div>
    <div class="video-card">
        <div class="video-header">
            <div class="video-thumbnail-large">
                @if($video->thumbnail_path)
                    <img src="{{ $video->thumbnail_path }}" alt="{{ $video->title }}">
                @else
                    <div style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background: #1a1a3a;">
                        <i class="fas fa-video" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                    </div>
                @endif
                
                <div class="video-play-btn">
                    <i class="fas fa-play"></i>
                </div>
                
                @if($video->duration)
                    <div class="video-duration-large">{{ $video->formatted_duration }}</div>
                @endif
            </div>
            
            <div class="video-info">
                <h1 class="video-title-large">{{ $video->title }}</h1>
                
                <div class="video-meta">
                    @if($video->duration)
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>{{ $video->formatted_duration }}</span>
                        </div>
                    @endif
                    
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Added {{ $video->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                
                <div class="video-price-large">
                    <span class="price-label">Price:</span>
                    @if($hasAccess)
                        <span>{{ $video->formatted_price }}</span>
                        <span class="owned-badge"><i class="fas fa-check"></i> Owned</span>
                    @elseif($isFreeForUser)
                        <span class="free-with-subscription">
                            <i class="fas fa-gift"></i> Free with your subscription
                        </span>
                    @else
                        <span>{{ $video->formatted_price }}</span>
                    @endif
                </div>
                
                <div class="video-actions">
                    @if($hasAccess || $isFreeForUser)
                        <a href="{{ route('videos.watch', $video) }}" class="btn-watch">
                            <i class="fas fa-play"></i> Watch Now
                        </a>
                    @else
                        <form action="{{ route('videos.purchase') }}" method="POST">
                            @csrf
                            <input type="hidden" name="video_id" value="{{ $video->id }}">
                            <input type="hidden" name="buy_now" value="1">
                            <button type="submit" class="btn-purchase">
                                <i class="fas fa-credit-card"></i> Buy Now
                            </button>
                        </form>
                        
                        <form action="{{ route('videos.add-to-cart') }}" method="POST">
                            @csrf
                            <input type="hidden" name="video_id" value="{{ $video->id }}">
                            <button type="submit" class="btn-add-cart">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="video-content">
            <div class="content-section">
                <h2 class="content-title">About This Video</h2>
                <div class="video-description-full">
                    {!! nl2br(e($video->description)) !!}
                </div>
            </div>
        </div>
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
        // Handle thumbnail from Google Drive if needed
        const thumbnail = document.querySelector('.video-thumbnail-large img');
        
        if (thumbnail && thumbnail.src.includes('drive.google.com')) {
            // Extract file ID
            let fileId = null;
            const url = new URL(thumbnail.src);
            
            if (thumbnail.src.includes('/file/d/')) {
                const pathParts = url.pathname.split('/');
                const fileIndex = pathParts.indexOf('d') + 1;
                if (fileIndex < pathParts.length) {
                    fileId = pathParts[fileIndex];
                }
            } else if (url.searchParams.has('id')) {
                fileId = url.searchParams.get('id');
            }
            
            if (fileId) {
                // Create direct thumbnail URL (works for images shared publicly)
                thumbnail.src = `https://drive.google.com/thumbnail?id=${fileId}&sz=w800`;
            }
        }
        
        // Handle play button click
        const playBtn = document.querySelector('.video-play-btn');
        if (playBtn) {
            playBtn.addEventListener('click', function() {
                // Check if the user has access
                const hasAccess = {{ $hasAccess || $isFreeForUser ? 'true' : 'false' }};
                
                if (hasAccess) {
                    // Redirect to watch page
                    window.location.href = "{{ route('videos.watch', $video) }}";
                } else {
                    // Show purchase options
                    alert('Please purchase this video or subscribe to watch.');
                }
            });
        }
    });
</script>
@endpush