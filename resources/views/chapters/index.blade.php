@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/chapters.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="chapters-container">
    <h1 class="chapters-title">Chapters</h1>
    
    <!-- Desktop View - Grid Layout -->
    <div class="chapters-grid desktop-only">
        @foreach($chapters as $chapter)
            <div class="chapter-card">
                <h2 class="chapter-title">Chapter {{ $chapter->id }}</h2>
                <p class="chapter-description">{{ $chapter->description }}</p>
                <p class="chapter-price">${{ number_format($chapter->price, 2) }} AUD</p>
                
                @if($chapter->isPurchased())
                    <a href="{{ route('chapters.read', $chapter->id) }}" class="btn btn-portal">Read Now</a>
                @else
                    <a href="{{ route('chapters.purchase', $chapter->id) }}" class="btn btn-portal">Purchase</a>
                @endif
            </div>
        @endforeach
    </div>
    
    <!-- Mobile View - List Layout -->
    <div class="chapters-list mobile-only">
        @foreach($chapters as $chapter)
            <div class="chapter-list-item">
                <div class="chapter-info">
                    <h2 class="chapter-title">Chapter {{ $chapter->id }}</h2>
                    <p class="chapter-price">${{ number_format($chapter->price, 2) }} AUD</p>
                </div>
                
                @if($chapter->isPurchased())
                    <a href="{{ route('chapters.read', $chapter->id) }}" class="btn-portal">Read Now</a>
                @else
                    <a href="{{ route('chapters.purchase', $chapter->id) }}" class="btn-portal">Purchase</a>
                @endif
            </div>
        @endforeach
        
        @if(count($chapters) > 4)
            <div class="chapter-list-item more-chapters">
                <p>...</p>
            </div>
        @endif
    </div>
</div>
@endsection