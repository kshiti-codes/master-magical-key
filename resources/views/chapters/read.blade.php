@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/chapters.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    // Track reading progress
    document.addEventListener('DOMContentLoaded', function() {
        let lastPosition = 0;
        const saveProgressInterval = 30000; // Save every 30 seconds
        
        // Function to save reading progress
        function saveProgress() {
            const scrollPosition = window.scrollY;
            // Only save if position changed significantly
            if (Math.abs(scrollPosition - lastPosition) > 100) {
                lastPosition = scrollPosition;
                
                // Calculate page based on content height
                const contentHeight = document.querySelector('.chapter-content').scrollHeight;
                const viewportHeight = window.innerHeight;
                const totalPages = Math.ceil(contentHeight / viewportHeight);
                const currentPage = Math.max(1, Math.ceil((scrollPosition / contentHeight) * totalPages));
                
                // Save progress via AJAX
                fetch('{{ route('chapters.progress', $chapter->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        page: currentPage
                    })
                });
            }
        }
        
        // Set interval to periodically save progress
        setInterval(saveProgress, saveProgressInterval);
        
        // Also save on page leave
        window.addEventListener('beforeunload', saveProgress);
    });
</script>
@endpush

@section('content')
<div class="chapter-read-container">
    <div class="chapter-navigation">
        <a href="{{ route('chapters.index') }}" class="back-to-chapters">
            <i class="fas fa-arrow-left"></i> Chapters
        </a>
        <div class="chapter-info">
            <h1 class="reading-chapter-title">Chapter {{ $chapter->id }}: {{ $chapter->title }}</h1>
        </div>
    </div>
    
    <div class="chapter-content-container">
        <div class="chapter-content">
            @if($chapter->content)
                {!! nl2br(e($chapter->content)) !!}
            @else
                <p>Chapter content is being prepared. Please check back soon.</p>
            @endif
        </div>
    </div>
</div>
@endsection