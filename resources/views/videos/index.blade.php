@extends('layouts.app')

@push('styles')
<style>
    .videos-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .videos-title {
        text-align: center;
        color: #fff;
        font-family: 'Cinzel', serif;
        margin-bottom: 20px;
        font-size: 2.2rem;
        letter-spacing: 2px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .videos-subtitle {
        text-align: center;
        color: #d8b5ff;
        margin-bottom: 40px;
        font-size: 1.2rem;
    }
    
    .lifetime-notice {
        background: rgba(10, 10, 30, 0.6);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        text-align: center;
    }
    
    .lifetime-notice-title {
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1.3rem;
        margin-bottom: 10px;
    }
    
    .lifetime-notice-text {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .videos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
    
    .video-card {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        border: 1px solid rgba(138, 43, 226, 0.3);
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .video-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(138, 43, 226, 0.3);
        border-color: rgba(138, 43, 226, 0.6);
    }
    
    .video-thumbnail {
        width: 100%;
        height: 180px;
        background-color: #1a1a3a;
        position: relative;
        overflow: hidden;
    }
    
    .video-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.5s ease;
    }
    
    .video-card:hover .video-thumbnail img {
        transform: scale(1.05);
    }
    
    .video-duration {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
    }
    
    .video-play-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        background: rgba(138, 43, 226, 0.7);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        color: white;
        font-size: 1.5rem;
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    .video-card:hover .video-play-icon {
        opacity: 1;
    }
    
    .video-details {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .video-title {
        color: white;
        font-family: 'Cinzel', serif;
        font-size: 1.2rem;
        margin-bottom: 10px;
    }
    
    .video-description {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        margin-bottom: 15px;
        flex-grow: 1;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }
    
    .video-access {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .video-price {
        font-size: 1.2rem;
        font-weight: 500;
        color: #d8b5ff;
    }
    
    .free-label {
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
    
    .owned-label {
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
    
    .video-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-view-video, .btn-buy-now, .btn-add-cart {
        padding: 8px 10px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
        text-align: center;
        font-size: 0.9rem;
    }
    
    .btn-view-video {
        background: linear-gradient(to right, #4b0082, #9400d3);
        border: none;
        color: white;
    }
    
    .btn-view-video:hover {
        background: linear-gradient(to right, #9400d3, #4b0082);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(75, 0, 130, 0.3);
        color: white;
    }
    
    .btn-buy-now {
        background: linear-gradient(to right, #8a2be2, #4b0082);
        color: white;
    }
    
    .btn-buy-now:hover {
        background: linear-gradient(to right, #9400d3, #8a2be2);
        color: white;
        transform: translateY(-2px);
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
    
    .no-videos {
        text-align: center;
        padding: 50px 20px;
        background: rgba(10, 10, 30, 0.5);
        border-radius: 10px;
        color: rgba(255, 255, 255, 0.8);
    }

    /* Floating Cart Button styles */
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
    
    @media (max-width: 767px) {
        .videos-title {
            font-size: 1.8rem;
        }
        
        .videos-subtitle {
            font-size: 1rem;
            margin-bottom: 30px;
        }
        
        .videos-grid {
            grid-template-columns: 1fr;
        }
        
        .video-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="videos-container">
    <h1 class="videos-title">Training Videos</h1>
    
    <p class="videos-subtitle">Enhance your mystical journey with practical guidance and advanced techniques</p>
    
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
    
    <!-- Lifetime Subscription Notice -->
    @if(isset($hasLifetimeSubscription) && $hasLifetimeSubscription)
        <div class="lifetime-notice">
            <h2 class="lifetime-notice-title">Lifetime Subscription Benefit</h2>
            <p class="lifetime-notice-text">As a lifetime subscriber, you have free access to all training videos, including future releases. Enjoy your unlimited learning journey!</p>
        </div>
    @endif
    
    <!-- Video Grid -->
    @if(isset($videos) && count($videos) > 0)
        <div class="videos-grid">
            @foreach($videos as $video)
                <div class="video-card">
                    <div class="video-thumbnail">
                        @if($video->thumbnail_path)
                            <img src="{{ $video->thumbnail_path }}" alt="{{ $video->title }}">
                        @else
                            <div style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background: #1a1a3a;">
                                <i class="fas fa-video" style="font-size: 2rem; color: rgba(255,255,255,0.3);"></i>
                            </div>
                        @endif
                        
                        @if($video->duration)
                            <div class="video-duration">{{ $video->formatted_duration }}</div>
                        @endif
                        
                        <div class="video-play-icon">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                    
                    <div class="video-details">
                        <h3 class="video-title">{{ $video->title }}</h3>
                        
                        <div class="video-description">
                            {{ Str::limit($video->description, 120) }}
                        </div>
                        
                        <div class="video-access">
                            @if(isset($purchasedVideoIds) && in_array($video->id, $purchasedVideoIds) || (isset($hasLifetimeSubscription) && $hasLifetimeSubscription))
                                <div class="video-price">
                                    <span class="owned-label"><i class="fas fa-check"></i> Owned</span>
                                </div>
                            @elseif(isset($hasLifetimeSubscription) && $hasLifetimeSubscription)
                                <div class="video-price">
                                    <span class="free-label"><i class="fas fa-gift"></i> Free</span>
                                </div>
                            @else
                                <div class="video-price">
                                    {{ $video->formatted_price }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="video-actions">
                            @if(isset($purchasedVideoIds) && in_array($video->id, $purchasedVideoIds) || (isset($hasLifetimeSubscription) && $hasLifetimeSubscription))
                                <a href="{{ route('videos.watch', $video) }}" class="btn-view-video">
                                    <i class="fas fa-play"></i> Watch Now
                                </a>
                            @else
                                <a href="{{ route('videos.show', $video) }}" class="btn-view-video">
                                    <i class="fas fa-info-circle"></i> Details
                                </a>
                                
                                <form action="{{ route('videos.purchase') }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <input type="hidden" name="video_id" value="{{ $video->id }}">
                                    <input type="hidden" name="buy_now" value="1">
                                    <button type="submit" class="btn-buy-now" style="width: 100%;">
                                        Buy Now
                                    </button>
                                </form>
                                
                                <form action="{{ route('videos.add-to-cart') }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <input type="hidden" name="video_id" value="{{ $video->id }}">
                                    <button type="submit" class="btn-add-cart" style="width: 100%;">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-videos">
            <i class="fas fa-film" style="font-size: 3rem; margin-bottom: 20px; color: rgba(138, 43, 226, 0.5);"></i>
            <h3>No videos available yet</h3>
            <p>Check back soon for training videos to enhance your mystical journey.</p>
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
        // Handle video thumbnails from Google Drive if needed
        const thumbnails = document.querySelectorAll('.video-thumbnail img');
        
        thumbnails.forEach(img => {
            // Check if the image source is a Google Drive link
            if (img.src.includes('drive.google.com')) {
                // Extract file ID
                let fileId = null;
                const url = new URL(img.src);
                
                if (img.src.includes('/file/d/')) {
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
                    img.src = `https://drive.google.com/thumbnail?id=${fileId}&sz=w500`;
                }
            }
        });
    });
</script>
@endpush