<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->invoice_number }}</td>
                    <td>{{ $purchase->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        {{ $purchase->user->name }}<br>
                        <small>{{ $purchase->user->email }}</small>
                    </td>
                    <td>
                        @php $itemCount = $purchase->items->count(); @endphp
                        @foreach($purchase->items->take(2) as $item)
                            @if($item->item_type === 'chapter' && $item->chapter)
                                <div>Chapter {{ $item->chapter->id }}: {{ Str::limit($item->chapter->title, 20) }}</div>
                            @elseif($item->item_type === 'spell' && $item->spell)
                                <div>Spell: {{ Str::limit($item->spell->title, 20) }}</div>
                            @endif
                        @endforeach
                        @if($itemCount > 2)
                            <div>... and {{ $itemCount - 2 }} more</div>
                        @endif
                    </td>
                    <td>${{ number_format($purchase->amount, 2) }}</td>
                    <td>
                        <span class="status-badge status-{{ $purchase->status }}">
                            {{ ucfirst($purchase->status) }}
                        </span>
                    </td>
                    <td class="actions-cell">
                        <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn-admin-secondary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No purchases found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $purchases->appends(request()->except('page'))->links() }}
</div>