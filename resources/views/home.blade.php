@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/magical-door.css') }}" rel="stylesheet">
<link href="{{ asset('css/components/home.css') }}" rel="stylesheet">
<link href="{{ asset('css/components/digital-book.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('js/components/magical-door.js') }}" defer></script>
<script src="{{ asset('js/components/digital-book.js') }}" defer></script>
<script>
    // Pass chapters data to JavaScript
    window.bookChapters = [
        @foreach($chapters as $chapter)
        {
            id: {{ $chapter->id }},
            title: "{{ $chapter->title }}",
            previewContent: "{{ $chapter->preview_content ?? 'This chapter contains sacred wisdom about the universe and your connection to it.' }}",
            @if($chapter->isPurchased())
            fullContent: {!! json_encode($chapter->content) !!}, // Full content for purchased chapters
            @endif
            price: {{ $chapter->price }},
            isPurchased: {{ $chapter->isPurchased() ? 'true' : 'false' }},
            readUrl: "{{ route('chapters.read', $chapter->id) }}",
            purchaseUrl: "{{ route('chapters.index', $chapter->id) }}"
        },
        @endforeach
    ];
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we need to open a specific chapter
        const urlParams = new URLSearchParams(window.location.search);
        const openChapter = urlParams.get('open_chapter');
        
        if (openChapter) {
            // First wait for the book to be initialized
            const checkBookInit = setInterval(function() {
                // Check if the openSpecificChapter function is available
                if (window.openSpecificChapter) {
                    clearInterval(checkBookInit);
                    
                    // Open the magical door
                    const magicalDoor = document.getElementById('magicalDoor');
                    if (magicalDoor) {
                        magicalDoor.classList.add('open');
                        
                        setTimeout(() => {
                            document.body.classList.add('door-opened');
                            const titleContainer = document.querySelector('.title-container');
                            if (titleContainer) {
                                titleContainer.classList.add('title-exit');
                            }
                            
                            // Show book content
                            const doorContent = document.getElementById('doorContent');
                            if (doorContent) {
                                doorContent.classList.add('visible');
                                
                                // Navigate to the specific chapter
                                setTimeout(() => {
                                    // Try to navigate to the chapter
                                        const navigated = window.openSpecificChapter(openChapter);
                                        
                                        if (!navigated) {
                                            console.error('Could not navigate to chapter', openChapter);
                                    }
                                }, 800); // Give time for book to render
                            }
                        }, 500);
                    }
                }
            }, 100); // Check every 100ms
        }
    });
</script>
@endpush

@section('content')
<div class="home-container">
    <!-- Hero Section with Title -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center mb-4 title-container">
                    <h1 class="mystical-title">MASTER MAGICAL KEY</h1>
                    <h2 class="mystical-subtitle">TO THE UNIVERSE</h2>
                </div>
            </div>
            
            <!-- Magical Door (Main Feature) -->
            <div class="door-container">
                <div class="magical-door-wrapper">
                    <div class="magical-door-arch"></div>
                    <div class="magical-door" id="magicalDoor">
                        <div class="door-handle"></div>
                        <div class="door-decorations">
                            <div class="door-arch-decoration"></div>
                        </div>
                    </div>
                    <div class="door-lantern door-lantern-left"></div>
                    <div class="door-lantern door-lantern-right"></div>
                    <div class="door-plants"></div>
                    <div class="door-plants door-plants-right"></div>
                    <div class="magical-door-glow"></div>
                    <div class="magical-door-particles" id="doorParticles"></div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Digital Book Interface (Hidden initially) -->
    <div class="door-content" id="doorContent">
        <div class="digital-book-container">
            <div class="digital-book" id="digitalBook">
                <!-- Mobile Header (for mobile only) -->
                <div class="mobile-header">
                    <button class="close-button" id="mobileCloseBtn">×</button>
                </div>
                <div class="book-content-wrapper">
                    <button id="closeBookBtn" class="close-book-btn"><i class="fas fa-times"></i></button>
                
                    <div class="book-open" id="bookContainer">
                        <!-- Left Page -->
                        <div class="book-left-page" id="leftPage">
                            <!-- Content will be loaded by JS -->
                        </div>
                        
                        <!-- Right Page -->
                        <div class="book-right-page" id="rightPage">
                            <!-- Content will be loaded by JS -->
                        </div>
                        
                        <!-- Book Spine -->
                        <div class="book-spine"></div>
                        
                        <!-- Page Turner (for animation) -->
                        <div class="page-turner" id="pageTurner">
                            <div class="page-turner-front" id="turnerFront">
                                <!-- Content copied during page turn -->
                            </div>
                            <div class="page-turner-back" id="turnerBack">
                                <!-- Content copied during page turn -->
                            </div>
                        </div>
                        
                        
                    </div>
                    <!-- Navigation Buttons -->
                    <button class="page-turn-btn prev-page" id="prevBtn">&lt;</button>
                    <button class="page-turn-btn next-page" id="nextBtn">&gt;</button>
                    <!-- Mobile Navigation -->
                    <div class="mobile-navigation">
                        <button class="nav-button" id="mobilePrevBtn">&lt;</button>
                        <div class="page-indicator" id="mobilePageIndicator">Page 1/10</div>
                        <button class="nav-button" id="mobileNextBtn">&gt;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection