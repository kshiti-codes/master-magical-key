@extends('layouts.admin')

@section('title', 'Promo Codes')

@section('content')
<div class="admin-header">
    <h1 class="admin-page-title">Promo Codes</h1>
    <a href="{{ route('admin.promo-codes.create') }}" class="btn-admin-primary">
        <i class="fas fa-plus"></i> Create Promo Code
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">All Promo Codes</h2>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Min Order</th>
                    <th>Uses</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promoCodes as $promo)
                <tr>
                    <td><strong style="color:#d8b5ff; letter-spacing:1px;">{{ $promo->code }}</strong></td>
                    <td>
                        @if($promo->discount_type === 'percentage')
                            {{ $promo->discount_value }}%
                        @else
                            ${{ number_format($promo->discount_value, 2) }} off
                        @endif
                    </td>
                    <td>
                        {{ $promo->min_order_amount ? '$' . number_format($promo->min_order_amount, 2) : '—' }}
                    </td>
                    <td>
                        {{ $promo->used_count }}{{ $promo->max_uses ? ' / ' . $promo->max_uses : '' }}
                    </td>
                    <td>
                        <form action="{{ route('admin.promo-codes.toggle', $promo) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="status-toggle"
                                    style="background:{{ $promo->is_active ? 'rgba(0,200,100,0.2)' : 'rgba(200,50,50,0.2)' }}; color:{{ $promo->is_active ? '#a0ffd8' : '#ff8a8a' }}; border:1px solid {{ $promo->is_active ? 'rgba(0,200,100,0.4)' : 'rgba(200,50,50,0.4)' }}; padding:4px 12px; border-radius:15px; cursor:pointer; font-size:0.8rem;">
                                {{ $promo->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        {{ $promo->expires_at ? $promo->expires_at->format('M d, Y') : '—' }}
                    </td>
                    <td>{{ $promo->created_at->format('M d, Y') }}</td>
                    <td class="actions-cell">
                        <form action="{{ route('admin.promo-codes.destroy', $promo) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete promo code {{ $promo->code }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-admin-secondary" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding:3rem; color:rgba(255,255,255,0.5);">
                        <i class="fas fa-tag" style="font-size:3rem; margin-bottom:1rem; opacity:0.3;"></i>
                        <p>No promo codes yet</p>
                        <a href="{{ route('admin.promo-codes.create') }}" class="btn-admin-primary" style="margin-top:1rem;">
                            <i class="fas fa-plus"></i> Create Your First Promo Code
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
    .actions-cell { white-space: nowrap; }
    .actions-cell form { margin: 0; }
    .status-toggle:hover { opacity: 0.8; }
</style>
@endpush