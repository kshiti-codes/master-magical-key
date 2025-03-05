@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/chapters.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="chapters-container">
    <div class="chapter-purchase-container">
        <h1 class="chapters-title">Purchase Chapter</h1>
        
        <div class="chapter-preview-card">
            <h2 class="chapter-title">Chapter {{ $chapter->id }}</h2>
            <p class="chapter-description">{{ $chapter->description }}</p>
            
            <div class="chapter-preview">
                <p>{{ $chapter->preview_content ?? 'Preview this chapter before purchase.' }}</p>
            </div>
            
            <div class="purchase-details">
                <p class="chapter-price">${{ number_format($chapter->price, 2) }} AUD</p>
                
                <!-- PayPal Button (for demo) -->
                <form action="{{ route('payment.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                    <button type="submit" class="btn btn-portal">Pay with PayPal</button>
                </form>
            </div>
        </div>
        
        <div class="back-link">
            <a href="{{ route('chapters.index') }}" class="mystic-link">
                <i class="fas fa-arrow-left"></i> Back to Chapters
            </a>
        </div>
    </div>
</div>
@endsection