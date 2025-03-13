@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/payment.css') }}" rel="stylesheet">
<style>
    .success-icon {
        font-size: 5rem;
        color: #4BB543;
        margin-bottom: 20px;
        animation: pulse 2s ease-in-out;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .success-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        font-size: 2rem;
        margin-bottom: 30px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .purchase-items-table {
        width: 100%;
        margin-bottom: 20px;
        border-collapse: collapse;
    }
    
    .purchase-items-table th {
        text-align: left;
        padding: 12px 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
        color: #d8b5ff;
    }
    
    .purchase-items-table td {
        padding: 12px 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .purchase-items-table tr:last-child td {
        border-bottom: none;
    }
    
    .purchase-items-table .text-right {
        text-align: right;
    }
    
    .item-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        margin-bottom: 5px;
        text-align: left;
    }
    
    .purchase-summary {
        margin: 25px 0;
        background: rgba(10, 10, 30, 0.5);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .summary-row:last-child {
        border-bottom: none;
    }
    
    .summary-row.total {
        margin-top: 10px;
        padding-top: 15px;
        border-top: 1px solid rgba(138, 43, 226, 0.3);
        font-weight: bold;
        color: #d8b5ff;
        font-size: 1.2rem;
    }
    
    .transaction-details {
        background: rgba(10, 10, 30, 0.5);
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 25px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        text-align: center;
    }
    
    .transaction-id {
        color: #d8b5ff;
        font-family: 'Courier New', monospace;
        letter-spacing: 1px;
    }
    
    .item-type-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        margin-right: 8px;
        vertical-align: middle;
    }
    
    .item-type-chapter {
        background: rgba(138, 43, 226, 0.3);
        color: #d8b5ff;
    }
    
    .item-type-spell {
        background: rgba(0, 128, 128, 0.3);
        color: #a0ffd8;
    }
    
    .free-badge {
        background: rgba(0, 128, 0, 0.3);
        color: #a0ffa0;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-left: 5px;
    }
    
    .order-section {
        margin-bottom: 30px;
    }
    
    .order-section-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.3rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
</style>
@endpush

@section('content')
<div class="payment-container fade-transition">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="success-title">Payment Successful!</h1>
        
        <div class="transaction-details">
            <p>Transaction Date: {{ $purchase->created_at->format('F j, Y, g:i a') }}</p>
            <p class="transaction-id">Transaction ID: {{ $purchase->transaction_id }}</p>
        </div>
        
        <!-- Purchased Items -->
        @php
            $chapterItems = collect($purchasedItems)->where('type', 'chapter');
            $spellItems = collect($purchasedItems)->where('type', 'spell');
        @endphp
        
        @if($chapterItems->count() > 0)
            <div class="order-section">
                <h3 class="order-section-title">Purchased Chapters</h3>
                <table class="purchase-items-table">
                    <thead>
                        <tr>
                            <th>Chapter</th>
                            <th class="text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chapterItems as $item)
                            <tr>
                                <td>
                                    <div class="item-title">
                                        <span class="item-type-badge item-type-chapter">Chapter</span>
                                        Chapter {{ $item['chapter_id'] }}: {{ $item['title'] }}
                                    </div>
                                    @if(isset($item['quantity']) && $item['quantity'] > 1)
                                        <div class="item-quantity">Qty: {{ $item['quantity'] }}</div>
                                    @endif
                                </td>
                                <td class="text-right">${{ number_format($item['price'] * ($item['quantity'] ?? 1), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        
        @if($spellItems->count() > 0)
            <div class="order-section">
                <h3 class="order-section-title">Purchased Spells</h3>
                <table class="purchase-items-table">
                    <thead>
                        <tr>
                            <th>Spell</th>
                            <th class="text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($spellItems as $item)
                            <tr>
                                <td>
                                    <div class="item-title">
                                        <span class="item-type-badge item-type-spell">Spell</span>
                                        {{ $item['title'] }}
                                        @if(isset($item['free_with_chapter']) && $item['free_with_chapter'])
                                            <span class="free-badge">Free with Chapter</span>
                                        @endif
                                    </div>
                                    @if(isset($item['quantity']) && $item['quantity'] > 1)
                                        <div class="item-quantity">Qty: {{ $item['quantity'] }}</div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if(isset($item['free_with_chapter']) && $item['free_with_chapter'])
                                        <span style="color: #a0ffa0;">Free</span>
                                    @else
                                        ${{ number_format($item['price'] * ($item['quantity'] ?? 1), 2) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        
        <!-- Purchase Summary -->
        <div class="purchase-summary">
            <div class="summary-row">
                <div>Subtotal:</div>
                <div>${{ number_format($subtotal ?? ($purchase->amount / 1.1), 2) }}</div>
            </div>
            
            <div class="summary-row">
                <div>GST (10%):</div>
                <div>${{ number_format($tax ?? ($purchase->amount - $purchase->amount / 1.1), 2) }}</div>
            </div>
            
            <div class="summary-row total">
                <div>Total:</div>
                <div>${{ number_format($total ?? $purchase->amount, 2) }} {{ $purchase->currency }}</div>
            </div>
        </div>
        
        <div class="success-actions">
            @if($chapterItems->count() > 0)
                <a href="{{ route('home') }}" class="btn-read">
                    Open Digital Book
                </a>
            @endif
            
            @if($spellItems->count() > 0)
                <a href="{{ route('spells.index') }}" class="btn-read">
                    View My Spells
                </a>
            @endif
            
            <a href="{{ route('chapters.index') }}" class="btn-chapters">
                Browse More Chapters
            </a>
            
            <a href="{{ route('spells.index') }}" class="btn-chapters">
                Browse More Spells
            </a>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <p>An invoice has been sent to your email address.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Create success particles
        const container = document.querySelector('.success-card');
        if (!container) return;
        
        // Create particle container
        const particlesContainer = document.createElement('div');
        particlesContainer.className = 'success-particles';
        particlesContainer.style.position = 'absolute';
        particlesContainer.style.top = '0';
        particlesContainer.style.left = '0';
        particlesContainer.style.width = '100%';
        particlesContainer.style.height = '100%';
        particlesContainer.style.pointerEvents = 'none';
        particlesContainer.style.zIndex = '-1';
        container.appendChild(particlesContainer);
        
        // Generate particles
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.style.position = 'absolute';
            particle.style.width = `${3 + Math.random() * 5}px`;
            particle.style.height = particle.style.width;
            particle.style.backgroundColor = `hsl(${120 + Math.random() * 60}, 70%, 60%)`;
            particle.style.borderRadius = '50%';
            particle.style.opacity = '0';
            
            // Random position
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            
            // Animation
            particle.style.animation = `fadeInOut ${3 + Math.random() * 2}s infinite`;
            particle.style.animationDelay = `${Math.random() * 2}s`;
            
            particlesContainer.appendChild(particle);
        }
        
        // Add keyframes if not already in stylesheet
        if (!document.getElementById('success-animation-style')) {
            const style = document.createElement('style');
            style.id = 'success-animation-style';
            style.textContent = `
                @keyframes fadeInOut {
                    0% { transform: translateY(0) scale(1); opacity: 0; }
                    20% { opacity: 0.7; }
                    80% { opacity: 0.7; }
                    100% { transform: translateY(-50px) scale(0.5); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    });
</script>
@endpush