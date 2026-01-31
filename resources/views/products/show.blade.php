@extends('layouts.app')

@push('styles')
<style>
    .product-detail-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        position: relative;
        z-index: 1;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        margin-bottom: 30px;
        transition: all 0.3s ease;
        font-family: 'Rajdhani', sans-serif;
    }
    
    .back-link:hover {
        color: #d8b5ff;
        transform: translateX(-5px);
        text-decoration: none;
    }
    
    .product-detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 40px;
    }
    
    /* Authorization Popup Modal */
    .auth-popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(10px);
    }
    
    .auth-popup-overlay.active {
        display: flex;
    }
    
    .auth-popup-content {
        background: rgba(10, 10, 30, 0.95);
        border: 2px solid rgba(138, 43, 226, 0.8);
        border-radius: 20px;
        padding: 20px;
        max-width: 800px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 0 50px rgba(138, 43, 226, 0.8);
        position: relative;
        animation: popupFadeIn 0.4s ease-out;
    }
    
    @keyframes popupFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .auth-popup-content::-webkit-scrollbar {
        width: 10px;
    }
    
    .auth-popup-content::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 10px;
    }
    
    .auth-popup-content::-webkit-scrollbar-thumb {
        background: rgba(138, 43, 226, 0.6);
        border-radius: 10px;
    }
    
    .popup-close {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 2rem;
        color: rgba(255, 255, 255, 0.7);
        cursor: pointer;
        transition: all 0.3s ease;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }
    
    .popup-close:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }
    
    .popup-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        font-size: 14px;
        text-align: center;
        margin-bottom: 10px;
        letter-spacing: 3px;
        text-shadow: 0 0 20px rgba(138, 43, 226, 0.9);
    }
    
    .popup-text {
        color: rgba(255, 255, 255, 0.95);
        line-height: 1.25;
        font-size: 12px;
        margin-bottom: 20px;
        text-align: center;
        white-space: pre-wrap;
    }
    
    .popup-continue-btn {
        width: 100%;
        background: rgba(138, 43, 226, 0.7);
        color: white;
        border: 2px solid rgba(138, 43, 226, 0.9);
        padding: 5px;
        border-radius: 50px;
        cursor: pointer;
        font-size: 14px;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .popup-continue-btn:hover {
        background: rgba(138, 43, 226, 0.9);
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(138, 43, 226, 0.7);
    }
    
    /* PDF Viewer Container */
    .pdf-viewer-container {
        background: rgba(10, 10, 30, 0.95);
        border-radius: 15px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
        position: relative;
    }
    
    .pdf-viewer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: rgba(138, 43, 226, 0.2);
        border-radius: 10px;
    }
   
    .title-audio-container {
        display: flex;
        gap: 20px;
        align-items: center;
        margin-bottom: 30px;
    }

    .pdf-viewer-title {
        width: 50%;
        background: rgba(10, 10, 30, 0.95);
        border-radius: 15px;
        padding: 10px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
        position: relative;
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 14px;
        text-align: center;
        letter-spacing: 2px;
    }
    /* Audio Player */
    audio {
        width: 50%;
        height: 40px;
        outline: none;
    }
    
    .btn-download {
        background: rgba(0, 200, 0, 0.6);
        color: white;
        border: 2px solid rgba(0, 200, 0, 0.8);
        padding: 12px 25px;
        border-radius: 30px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 600;
        letter-spacing: 1px;
    }
    
    .btn-download:hover {
        background: rgba(0, 200, 0, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0, 200, 0, 0.5);
        text-decoration: none;
        color: white;
    }
    
    .pdf-iframe-wrapper {
        width: 100%;
        height: 800px;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        border: 2px solid rgba(138, 43, 226, 0.3);
    }
    
    .pdf-iframe-wrapper iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .product-detail-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .product-title {
            font-size: 1.8rem;
        }
        
        .product-price {
            font-size: 2rem;
        }
        
        .auth-popup-content {
            padding: 30px 20px;
            max-width: 90%;
            margin: 20px;
        }
        
        .popup-title {
            font-size: 1.5rem;
        }
        
        .popup-text {
            font-size: 0.85rem;
        }
        
        .pdf-iframe-wrapper {
            height: 500px;
        }
    }
</style>
@endpush

@section('content')
<div class="product-detail-container">
    <a href="{{ route('products') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
    
    @if($hasPurchased || $product->price == 0)
        <div class="title-audio-container">
            <div class="pdf-viewer-title">
                {{ $product->title }}
            </div>
            @if($product->audio_file_path)
            <audio controls>   
                <source src="{{ route('products.download-audio', $product->slug) }}" type="audio/mp3">
                    Your browser does not support the audio element.
            </audio>
            @endif   
        </div>
        @if($product->pdf_file_path)
        <div class="pdf-iframe-wrapper">
            <iframe src="{{ route('products.download-pdf', $product->slug) }}#toolbar=1" type="application/pdf"></iframe>
        </div>
        @else
        <div class="no-image-placeholder" style="margin-top: 20px;">
            <i class="fas fa-file-pdf"></i>
            <div>No PDF available for this product.</div>
        </div>
        @endif
    @endif
</div>

<!-- Authorization Popup Modal -->
@if($hasPurchased || $product->price == 0)
<div class="auth-popup-overlay active" id="authPopup">
    <div class="auth-popup-content">
        <span class="popup-close" onclick="closeAuthPopup()">&times;</span>
        <h2 class="popup-title">✨ Authorization & Alignment ✨</h2>
        <div class="popup-text">{{ $product->popup_text ?? 'Welcome! Click continue to access your product.' }}</div>
        <button type="button" class="popup-continue-btn" onclick="continueToProduct()">
            <i class="fas fa-key"></i> Continue
        </button>
    </div>
</div>
@endif

@push('scripts')
<script>
function showAuthPopup() {
    document.getElementById('authPopup').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeAuthPopup() {
    document.getElementById('authPopup').classList.remove('active');
    document.body.style.overflow = '';
}

function continueToProduct() {
    closeAuthPopup();
    // PDF is already loaded, just remove fixed positioning
    document.getElementById('pdfViewerContainer').style.position = 'relative';
    document.getElementById('pdfViewerContainer').style.zIndex = '1';
    
    // Smooth scroll to PDF viewer
    setTimeout(() => {
        document.getElementById('pdfViewerContainer').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }, 300);
}

// Close popup when clicking outside
document.getElementById('authPopup')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAuthPopup();
    }
});

// Close popup with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAuthPopup();
    }
});
</script>
@endpush
@endsection