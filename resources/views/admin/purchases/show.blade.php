@extends('layouts.admin')

@section('title', 'Purchase Details')

@push('styles')
<link href="{{ asset('css/admin/admin-purchase-report.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.purchases.index') }}" class="btn-admin-secondary mr-3">
            <i class="fas fa-arrow-left"></i> Back to Purchases
        </a>
        <h1 class="admin-page-title mb-0">Purchase Details</h1>
    </div>
    
    <div class="admin-card">
        <div class="purchase-header">
            <div>
                <span class="purchase-id">Invoice #{{ $purchase->invoice_number }}</span>
                <span class="purchase-status status-{{ $purchase->status }}">
                    {{ ucfirst($purchase->status) }}
                </span>
            </div>
            <div>
                <a href="{{ route('invoices.view', $purchase) }}" class="btn-admin-secondary" target="_blank">
                    <i class="fas fa-file-pdf"></i> View Invoice
                </a>
            </div>
        </div>
        
        <div class="purchase-details">
            <!-- Transaction Details -->
            <div class="detail-block">
                <h3>Transaction Details</h3>
                <div class="detail-row">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">{{ $purchase->created_at->format('M d, Y H:i:s') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Transaction ID</div>
                    <div class="detail-value">{{ $purchase->transaction_id }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Payment Method</div>
                    <div class="detail-value">PayPal</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Currency</div>
                    <div class="detail-value">{{ $purchase->currency }}</div>
                </div>
            </div>
            
            <!-- Customer Information -->
            <div class="detail-block">
                <h3>Customer Information</h3>
                <div class="detail-row">
                    <div class="detail-label">Name</div>
                    <div class="detail-value">{{ $purchase->user->name }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">{{ $purchase->user->email }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Customer Since</div>
                    <div class="detail-value">{{ $purchase->user->created_at->format('M d, Y') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Total Orders</div>
                    <div class="detail-value">
                        {{ $purchase->user->purchases()->where('status', 'completed')->count() }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Purchased Items -->
        <div class="admin-card-title">Purchased Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                    <tr>
                        <td>
                            @if($item->item_type === 'chapter' && $item->chapter)
                                <span class="item-type-badge item-chapter">Chapter</span>
                                Chapter {{ $item->chapter->order }}: {{ $item->chapter->title }}
                            @elseif($item->item_type === 'spell' && $item->spell)
                                <span class="item-type-badge item-spell">Spell</span>
                                {{ $item->spell->title }}
                            @else
                                Unknown Item
                            @endif
                        </td>
                        <td class="text-right">${{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Subtotal</td>
                    <td class="text-right">${{ number_format($purchase->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Tax ({{ $purchase->tax_rate }}%)</td>
                    <td class="text-right">${{ number_format($purchase->tax, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Total</td>
                    <td class="text-right">${{ number_format($purchase->amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        
        <!-- Admin Actions -->
        <div class="admin-buttons">
            <div>
                <a href="{{ route('admin.purchases.index') }}" class="btn-admin-secondary">
                    Back to Purchases
                </a>
            </div>
            
        </div>
    </div>
@endsection