@extends('layouts.admin')

@section('title', 'Purchase Details')

@push('styles')
<style>
    .purchase-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .purchase-id {
        font-size: 1.2rem;
        color: #d8b5ff;
    }
    
    .purchase-status {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin-left: 10px;
    }
    
    .status-completed {
        background: rgba(40, 167, 69, 0.2);
        color: #a0ffa0;
    }
    
    .status-pending {
        background: rgba(255, 193, 7, 0.2);
        color: #ffe0a0;
    }
    
    .status-failed {
        background: rgba(220, 53, 69, 0.2);
        color: #ffa0a0;
    }
    
    .purchase-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .detail-block {
        background: rgba(15, 15, 35, 0.6);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .detail-block h3 {
        color: #d8b5ff;
        font-size: 1.1rem;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
        padding-bottom: 10px;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .detail-label {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .detail-value {
        color: white;
        font-weight: 500;
    }
    
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    
    .items-table th {
        background: rgba(30, 30, 70, 0.6);
        color: #d8b5ff;
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .items-table td {
        padding: 12px 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .items-table tr:last-child td {
        border-bottom: none;
    }
    
    .items-table tfoot {
        font-weight: bold;
    }
    
    .items-table tfoot td {
        padding-top: 15px;
    }
    
    .items-table .text-right {
        text-align: right;
    }
    
    .admin-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    
    .item-type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        margin-right: 5px;
    }
    
    .item-chapter {
        background: rgba(138, 43, 226, 0.2);
        color: #d8b5ff;
    }
    
    .item-spell {
        background: rgba(0, 128, 128, 0.2);
        color: #a0ffd8;
    }
</style>
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