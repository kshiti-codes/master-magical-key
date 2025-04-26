@extends('layouts.app')

@push('styles')
<style>
    .video-watch-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .video-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        font-size: 2rem;
        margin-bottom: 20px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
        text-align: center;
    }
    
    /* Video player container */
    .video-player-container {
        position: relative;
        width: 100%;
        background: #000;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(138, 43, 226, 0.4);
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Direct iframe embed */
    .video-iframe-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }
    
    .video-iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }
    
    /* This overlay specifically covers the top-right corner where the "Open" button appears */
    .iframe-open-blocker {
        position: absolute;
        top: 0;
        right: 0;
        width: 40px;
        height: 40px;
        background-color: transparent;
        z-index: 10;
        cursor: default;
        pointer-events: all;
    }
    
    /* Video info section */
    .video-info {
        background: rgba(10, 10, 30, 0.7);
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .video-description {
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    .video-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 15px;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .meta-item i {
        color: #d8b5ff;
    }
    
    .video-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .btn-download {
        background: linear-gradient(to right, #2c3e50, #4b6cb7);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-download:hover {
        background: linear-gradient(to right, #4b6cb7, #2c3e50);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(75, 108, 183, 0.3);
        color: white;
    }
    
    .btn-back {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 1px solid rgba(138, 43, 226, 0.4);
        padding: 10px 20px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        background: rgba(138, 43, 226, 0.2);
        color: white;
    }
    
    /* Fullscreen button styling */
    .fullscreen-btn {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: background 0.3s ease;
        z-index: 5;
    }
    
    .fullscreen-btn:hover {
        background: rgba(138, 43, 226, 0.8);
    }
    
    @media (max-width: 767px) {
        .video-title {
            font-size: 1.5rem;
        }
        
        .video-actions {
            flex-direction: column;
        }
        
        .btn-download, .btn-back {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="video-watch-container">
    <h1 class="video-title">{{ $video->title }}</h1>
    
    <div class="video-player-container" id="videoPlayerContainer">
        <div class="video-iframe-wrapper">
            <!-- Google Drive Embed -->
            @if(isset($embedUrl) && $embedUrl)
                <iframe 
                    src="{{ $embedUrl }}" 
                    class="video-iframe" 
                    frameborder="0" 
                    allowfullscreen
                    id="videoIframe"
                ></iframe>
                <!-- This div blocks the "Open" button in the top right corner -->
                <div class="iframe-open-blocker" id="openBlocker"></div>
            @else
                <div class="fallback-message" style="color: white; text-align: center;">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: rgba(138, 43, 226, 0.6); margin-bottom: 15px;"></i>
                    <p>Video playback unavailable.</p>
                </div>
            @endif
        </div>
        
        <!-- Custom fullscreen button (outside iframe) -->
        <button class="fullscreen-btn" id="fullscreenBtn">
            <i class="fas fa-expand"></i>
        </button>
    </div>
    
    <div class="video-info">
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
            
            <div class="meta-item">
                <i class="fas fa-eye"></i>
                <span>Views: {{ $watchCount ?? 0 }}</span>
            </div>
        </div>
        
        <div class="video-description">
            {!! nl2br(e($video->description)) !!}
        </div>
        
        <div class="video-actions">
            <a href="{{ route('videos.download', $video) }}" class="btn-download">
                <i class="fas fa-download"></i> Download Video
            </a>
            
            <a href="{{ route('videos.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Videos
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const videoContainer = document.getElementById('videoPlayerContainer');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const openBlocker = document.getElementById('openBlocker');
    const videoIframe = document.getElementById('videoIframe');
    
    // Initial positioning of the open blocker over the "Open" button
    if (openBlocker) {
        // Make sure the blocker is properly positioned
        openBlocker.style.right = '0';
        openBlocker.style.top = '0';
        
        // Create a larger overlapping div to prevent accidental clicks
        const overlayDiv = document.createElement('div');
        overlayDiv.style.position = 'absolute';
        overlayDiv.style.top = '0';
        overlayDiv.style.right = '0';
        overlayDiv.style.width = '75px';
        overlayDiv.style.height = '75px';
        overlayDiv.style.zIndex = '9';
        overlayDiv.style.pointerEvents = 'all';
        
        // Add additional inner elements to better block clicks
        const innerDiv = document.createElement('div');
        innerDiv.style.position = 'absolute';
        innerDiv.style.top = '0';
        innerDiv.style.right = '0';
        innerDiv.style.width = '40px';
        innerDiv.style.height = '40px';
        innerDiv.style.background = 'transparent';
        
        // Append elements
        overlayDiv.appendChild(innerDiv);
        videoContainer.appendChild(overlayDiv);
    }
    
    // Fullscreen functionality
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                videoContainer.requestFullscreen().catch(err => {
                    console.log('Error attempting to enable fullscreen:', err);
                });
            }
        });
    }
    
    // Track video progress in localStorage
    const videoId = '{{ $video->id }}';
    const storageKey = `video_progress_${videoId}`;
    
    // Check for existing progress
    const savedProgress = localStorage.getItem(storageKey);
    if (savedProgress) {
        console.log('Resuming video from position:', savedProgress);
        // Note: We can't reliably seek in Google Drive iframes, 
        // but we'll keep the progress tracking for future improvements
    }
    
    // Save progress when the page is unloaded
    window.addEventListener('beforeunload', function() {
        // We can't accurately get the current time from the iframe,
        // but we're keeping this structure for future enhancements
        localStorage.setItem(storageKey, '0'); // Placeholder
    });
    
    // Additional event listeners for future iframe communication
    window.addEventListener('message', function(event) {
        // For future iframe integrations that support postMessage API
        try {
            const data = JSON.parse(event.data);
            // Handle potential messages from the iframe
        } catch (e) {
            // Ignore parsing errors for messages we don't understand
        }
    });
});
</script>
@endpush