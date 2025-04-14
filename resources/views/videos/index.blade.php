@extends('layouts.app')

@push('styles')
<style>
    .videos-container {
        max-width: 1100px;
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
        height: 60px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }
    
    .video-access {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .video-price {
        font-size: 1.2rem;
        font-weight: 500;
        color: #d8b5ff;
    }
    
    .free-label {
        background: linear-gradient(to right, #00b09b, #96c93d);
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    
    .owned-label {
        background: linear-gradient(to right, #4b0082, #9400d3);
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
    
    .video-action {
        margin-top: 15px;
        text-align: center;
    }
    
    .btn-view-video {
        background: linear-gradient(to right, #4b0082, #9400d3);
        border: none;
        color: white;
        padding: 8px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
        width: 100%;
        display: inline-block;
    }
    
    .btn-view-video:hover {
        background: linear-gradient(to right, #9400d3, #4b0082);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(75, 0, 130, 0.3);
        color: white;
    }
    
    .no-videos {
        text-align: center;
        padding: 50px 20px;
        background: rgba(10, 10, 30, 0.5);
        border-radius: 10px;
        color: rgba(255, 255, 255, 0.8);
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
    }
</style>
@endpush

@section('content')
<div class="videos-container">
    <h1 class="videos-title">Training Videos</h1>
    
    <p class="videos-subtitle">Enhance your mystical journey with practical training</p>
    
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
    @if($hasLifetimeSubscription)
        <div class="lifetime-notice">
            <h2 class="lifetime-notice-title">Lifetime Subscription Benefit</h2>
            <p class="lifetime-notice-text">As a lifetime subscriber, you have free access to all training videos, including future releases. Enjoy your unlimited learning journey!</p>
        </div>
    @endif
    
    <!-- Video Grid -->
    @if(count($videos) > 0)
        <div class="videos-grid">
            @foreach($videos as $video)
                <div class="video-card">
                    <div class="video-thumbnail">
                        @if($video->thumbnail_path)
                            <img src="{{ asset('storage/' . $video->thumbnail_path) }}" alt="{{ $video->title }}">
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
                            {{ Str::limit($video->description, 100) }}
                        </div>
                        
                        <div class="video-access">
                            @if(in_array($video->id, $purchasedVideoIds) || $hasLifetimeSubscription)
                                <div class="video-price">
                                    <span class="owned-label"><i class="fas fa-check"></i> Owned</span>
                                </div>
                            @elseif($hasLifetimeSubscription)
                                <div class="video-price">
                                    <span class="free-label"><i class="fas fa-gift"></i> Free</span>
                                </div>
                            @else
                                <div class="video-price">
                                    {{ $video->formatted_price }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="video-action">
                            @if(in_array($video->id, $purchasedVideoIds) || $hasLifetimeSubscription)
                                <a href="{{ route('videos.watch', $video) }}" class="btn-view-video">
                                    <i class="fas fa-play"></i> Watch Now
                                </a>
                            @else
                                <a href="{{ route('videos.show', $video) }}" class="btn-view-video">
                                    <i class="fas fa-info-circle"></i> View Details
                                </a>
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
</div>
@endsection