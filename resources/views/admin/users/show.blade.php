@extends('layouts.admin')

@section('title', 'User Profile')

@section('content')
<h1 class="admin-page-title">User Profile</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">{{ $user->name }}</h2>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-admin-secondary">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-lg-8" style="display: flex; gap:1rem;">
            <div class="col-lg-4" style="width: 50%;">
                <div class="user-info-card p-4 h-100" style="background: rgba(10, 10, 30, 0.4); border-radius: 8px;">
                    <h3 style="color: #d8b5ff; font-size: 1.2rem; margin-bottom: 15px;">Account Information</h3>
                    
                    <div class="user-info-item mb-3">
                        <label class="text-muted d-block">Email:</label>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                    
                    <div class="user-info-item mb-3">
                        <label class="text-muted d-block">Member Since:</label>
                        <div class="info-value">{{ $user->created_at->format('F j, Y') }}</div>
                    </div>
                    
                    <div class="user-info-item mb-3">
                        <label class="text-muted d-block">User Role:</label>
                        <div class="info-value">
                            <span class="badge" style="background-color: {{ $user->is_admin ? 'rgba(138, 43, 226, 0.7)' : 'rgba(0, 123, 255, 0.7)' }};">
                                {{ $user->is_admin ? 'Administrator' : 'Customer' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4" style="width: 50%;">
                <div class="user-stats-card p-4 h-100" style="background: rgba(10, 10, 30, 0.4); border-radius: 8px;">
                    <h3 style="color: #d8b5ff; font-size: 1.2rem; margin-bottom: 15px;">Purchase Statistics</h3>
                    
                    <div class="stat-item mb-3 d-flex justify-content-between">
                        <span>Total Purchases:</span>
                        <strong>{{ $purchases->total() }}</strong>
                    </div>
                    
                    <div class="stat-item mb-3 d-flex justify-content-between">
                        <span>Total Chapters Owned:</span>
                        <strong>{{ $ownedChapters->count() }}</strong>
                    </div>
                    
                    <div class="stat-item mb-3 d-flex justify-content-between">
                        <span>Total Spells Owned:</span>
                        <strong>{{ $ownedSpells->count() }}</strong>
                    </div>
                    
                    <div class="stat-item mb-3 d-flex justify-content-between">
                        <span>Total Spent:</span>
                        <strong>${{ number_format($purchases->sum('amount'), 2) }}</strong>
                    </div>
                    
                    <div class="stat-item mb-3">
                        <span class="d-block mb-2">Recent Purchase:</span>
                        <strong>
                            @if($purchases->count() > 0)
                                {{ $purchases->first()->created_at->format('F j, Y') }} - ${{ number_format($purchases->first()->amount, 2) }}
                            @else
                                No purchases yet
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Owned Content -->
    <div class="admin-card mb-4">
        <h3 class="admin-card-title">Manage Content Access</h3>
        
        <form action="{{ route('admin.users.update-content', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h4 style="color: #d8b5ff; font-size: 1.1rem; margin-bottom: 15px;">Owned Chapters</h4>
                    <div style="max-height: 250px; overflow-y: auto; padding: 10px; background: rgba(10, 10, 30, 0.3); border-radius: 5px;">
                        @if(App\Models\Chapter::count() > 0)
                            @foreach(App\Models\Chapter::orderBy('order')->get() as $chapter)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="chapter_ids[]" value="{{ $chapter->id }}" id="chapter-{{ $chapter->id }}" 
                                          {{ $ownedChapters->contains($chapter) ? 'checked' : '' }}>
                                    <label class="form-check-label d-block" for="chapter-{{ $chapter->id }}" style="margin-left: 1.5rem;">
                                        Chapter {{ $chapter->order }}: {{ $chapter->title }}
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <p>No chapters available</p>
                        @endif
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <h4 style="color: #d8b5ff; font-size: 1.1rem; margin-bottom: 15px;">Owned Spells</h4>
                    <div style="max-height: 250px; overflow-y: auto; padding: 10px; background: rgba(10, 10, 30, 0.3); border-radius: 5px;">
                        @if(App\Models\Spell::count() > 0)
                            @foreach(App\Models\Spell::orderBy('title')->get() as $spell)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="spell_ids[]" value="{{ $spell->id }}" id="spell-{{ $spell->id }}" 
                                          {{ $ownedSpells->contains($spell) ? 'checked' : '' }}>
                                    <label class="form-check-label d-block" for="spell-{{ $spell->id }}" style="margin-left: 1.5rem;">
                                        {{ $spell->title }}
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <p>No spells available</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="admin-form-actions">
                <button type="submit" class="btn-admin-primary">Update Content Access</button>
            </div>
        </form>
    </div>
    
    <!-- Purchase History -->
    <h3 class="admin-card-title">Purchase History</h3>
    
    @if($purchases->count() > 0)
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->id }}</td>
                        <td>{{ $purchase->created_at->format('M d, Y') }}</td>
                        <td>${{ number_format($purchase->amount, 2) }}</td>
                        <td>
                            <button type="button" class="btn-admin-secondary view-items" data-purchase-id="{{ $purchase->id }}">
                                View Items ({{ $purchase->items->count() }})
                            </button>
                            
                            <!-- Items Details Modal -->
                            <div class="items-details" id="items-{{ $purchase->id }}" style="display: none;">
                                <div class="items-list p-3 my-2" style="background: rgba(10, 10, 30, 0.3); border-radius: 5px;">
                                    <h5 style="color: #d8b5ff; font-size: 1rem; margin-bottom: 10px;">Purchase Items</h5>
                                    <ul style="list-style-type: none; padding-left: 0;">
                                        @foreach($purchase->items as $item)
                                            <li class="mb-2">
                                                @if($item->item_type == 'chapter' && $item->chapter)
                                                    <i class="fas fa-book"></i> Chapter {{ $item->chapter->order }}: {{ $item->chapter->title }}
                                                @elseif($item->item_type == 'spell' && $item->spell)
                                                    <i class="fas fa-magic"></i> Spell: {{ $item->spell->title }}
                                                @else
                                                    <i class="fas fa-question-circle"></i> Unknown Item
                                                @endif
                                                <span class="text-muted">({{ $item->quantity }} x ${{ number_format($item->price, 2) }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background-color: {{ $purchase->status === 'completed' ? 'rgba(40, 167, 69, 0.7)' : 'rgba(255, 193, 7, 0.7)' }};">
                                {{ ucfirst($purchase->status) }}
                            </span>
                        </td>
                        <td>
                            @if($purchase->invoice_data)
                            <a href="{{ route('invoices.view', $purchase) }}" target="_blank" class="btn-admin-secondary" title="View Invoice">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                            @else
                            <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $purchases->links() }}
        </div>
    @else
        <div class="alert text-center" style="background: rgba(10, 10, 30, 0.3); padding: 20px; border-radius: 5px;">
            <i class="fas fa-shopping-cart fa-2x mb-3" style="color: rgba(138, 43, 226, 0.5);"></i>
            <p>This user has not made any purchases yet.</p>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.8rem;
        padding: 5px 10px;
        border-radius: 15px;
    }
    
    .text-muted {
        color: rgba(255, 255, 255, 0.5) !important;
    }

    .user-info-card {
        margin-top: 1rem;
        padding: 1rem;
    }

    .user-info-item {
        padding: 0.5rem;
        display: flex;
    }
    
    .user-info-item .info-value {
        color: white;
        margin-left: 1rem;
    }

    .user-stats-card{
        margin-top: 1rem;
        padding: 1rem;
    }
    
    .user-stats-card strong {
        color: white;
    }
    
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        gap: 5px;
    }
    
    .pagination li {
        display: inline-block;
    }
    
    .pagination li a, .pagination li span {
        padding: 8px 12px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        background: rgba(30, 30, 60, 0.5);
        color: white;
        border-radius: 4px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .pagination li.active span {
        background: rgba(138, 43, 226, 0.5);
        border-color: rgba(138, 43, 226, 0.7);
    }
    
    .pagination li a:hover {
        background: rgba(138, 43, 226, 0.3);
    }
    
    .admin-form-actions {
        margin-top: 20px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle items details
        const viewItemsButtons = document.querySelectorAll('.view-items');
        viewItemsButtons.forEach(button => {
            button.addEventListener('click', function() {
                const purchaseId = this.getAttribute('data-purchase-id');
                const itemsDetails = document.getElementById('items-' + purchaseId);
                
                if (itemsDetails.style.display === 'none') {
                    // Hide all other open items first
                    document.querySelectorAll('.items-details').forEach(el => {
                        el.style.display = 'none';
                    });
                    
                    // Show this one
                    itemsDetails.style.display = 'block';
                    
                    // Change button text
                    this.textContent = 'Hide Items';
                } else {
                    // Hide this one
                    itemsDetails.style.display = 'none';
                    
                    // Restore button text
                    const itemCount = this.getAttribute('data-purchase-id').split('-')[1];
                    this.textContent = `View Items (${itemCount})`;
                }
            });
        });
    });
</script>
@endpush