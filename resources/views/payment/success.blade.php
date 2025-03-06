@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/payment.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // If navigating to home with open_chapter param
    const readNowBtn = document.querySelector('.btn-read');
    
    if (readNowBtn) {
        readNowBtn.addEventListener('click', function(e) {
            // Add a flag to indicate we should refresh the content
            const currentUrl = new URL(this.href);
            currentUrl.searchParams.append('refresh_content', 'true');
            this.href = currentUrl.toString();
        });
    }
});
</script>
@endpush

@section('content')
<div class="payment-container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="success-title">Payment Successful!</h1>
        
        <div class="success-details">
            <p>Thank you for your purchase of Chapter {{ $purchase->chapter->id }}: {{ $purchase->chapter->title }}</p>
            <p class="success-amount">${{ number_format($purchase->amount, 2) }} {{ $purchase->currency }}</p>
            <p class="transaction-id">Transaction ID: {{ $purchase->transaction_id }}</p>
        </div>
        
        <div class="success-actions">
            <a href="{{ route('home', ['open_chapter' => $purchase->chapter_id]) }}" class="btn-read">
                Read Chapter Now
            </a>
            
            <a href="{{ route('chapters.index') }}" class="btn-chapters">
                Back to Chapters
            </a>
        </div>
    </div>
</div>
@endsection