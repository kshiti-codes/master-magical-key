@extends('layouts.admin')

@section('title', 'Purchase History')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        display: inline-block;
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
    
    .filter-section {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: rgba(30, 30, 60, 0.7);
        border-radius: 10px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .stat-value {
        font-size: 1.8rem;
        font-weight: bold;
        color: #d8b5ff;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
    }
    
    .daterangepicker {
        background-color: rgba(30, 30, 60, 0.95);
        border: 1px solid rgba(138, 43, 226, 0.5);
        color: white;
    }
    
    .daterangepicker:before, .daterangepicker:after {
        border-bottom-color: rgba(138, 43, 226, 0.5);
    }
    
    .daterangepicker .calendar-table {
        background-color: rgba(20, 20, 40, 0.95);
        border: none;
    }
    
    .daterangepicker .calendar-table th, 
    .daterangepicker .calendar-table td {
        color: white;
    }
    
    .daterangepicker td.available:hover, 
    .daterangepicker th.available:hover {
        background-color: rgba(138, 43, 226, 0.3);
    }
    
    .daterangepicker td.in-range {
        background-color: rgba(138, 43, 226, 0.2);
        color: white;
    }
    
    .daterangepicker td.active, 
    .daterangepicker td.active:hover {
        background-color: rgba(138, 43, 226, 0.8);
        color: white;
    }
    
    .daterangepicker .drp-buttons {
        border-top: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .daterangepicker .btn {
        background-color: rgba(138, 43, 226, 0.6);
        color: white;
        border: none;
    }
    
    .daterangepicker .btn:hover {
        background-color: rgba(138, 43, 226, 0.8);
    }
    
    .export-btn {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .export-btn i {
        font-size: 1rem;
    }
</style>
@endpush

@section('content')
    <h1 class="admin-page-title">Purchase History</h1>
    
    <!-- Summary Statistics -->
    <div class="summary-stats">
        <div class="stat-card">
            <div class="stat-value" id="totalRevenue">${{ number_format($totalRevenue, 2) }}</div>
            <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="monthlyRevenue">${{ number_format($monthlyRevenue, 2) }}</div>
            <div class="stat-label">This Month's Revenue</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="totalPurchases">{{ $totalPurchases }}</div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="avgOrderValue">${{ $totalPurchases ? number_format($totalRevenue / $totalPurchases, 2) : '0.00' }}</div>
            <div class="stat-label">Average Order Value</div>
        </div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-title">Manage Purchases</div>
        
        <!-- Filters -->
        <form id="filterForm" method="GET" action="{{ route('admin.purchases.index') }}" class="filter-section">
            <div class="filter-group">
                <label>Status</label>
                <select name="status" class="admin-form form-control">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Date Range</label>
                <input type="text" name="date_range" class="admin-form form-control" id="dateRangePicker" 
                       value="{{ request('date_range') }}" placeholder="Select date range">
            </div>
            
            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" class="admin-form form-control" 
                       value="{{ request('search') }}" placeholder="Invoice # or Customer">
            </div>
            
            <div>
                <button type="button" id="applyFilters" class="btn-admin-primary">Apply Filters</button>
                <button type="button" id="resetFilters" class="btn-admin-secondary ml-2">Reset</button>
            </div>
            
            <div class="ml-auto">
                <a href="{{ route('admin.purchases.export', request()->all()) }}" class="btn-admin-secondary export-btn">
                    <i class="fas fa-file-export"></i> Export CSV
                </a>
            </div>
        </form>
        
        <!-- Purchases Table -->
        <div id="purchasesTableContainer">
            <!-- Purchases Table content -->
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
                                    @if($itemCount > 2)
                                        <div>... and {{ $itemCount - 2 }} more</div>
                                    @else
                                        <div>{{ $itemCount }} </div>
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
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $purchases->appends(request()->except('page'))->links() }}
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize daterangepicker (requires jQuery anyway)
        // If you're using daterangepicker, you need jQuery, so solution 1 is better
        
        // AJAX filter handling
        document.getElementById('applyFilters').addEventListener('click', function() {
            fetchFilteredData();
        });
        
        document.getElementById('resetFilters').addEventListener('click', function() {
            // Clear all form fields
            document.getElementById('filterForm').reset();
            fetchFilteredData();
        });
        
        function fetchFilteredData() {
            // Show a loading indicator
            document.getElementById('purchasesTableContainer').innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>';
            
            // Get form data
            const formData = new FormData(document.getElementById('filterForm'));
            const queryString = new URLSearchParams(formData).toString();
            
            // Make AJAX request
            fetch('{{ route("admin.purchases.data") }}?' + queryString, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update the table container with new data
                document.getElementById('purchasesTableContainer').innerHTML = data.html;
                
                // Update summary statistics
                updateSummaryStats(data.stats);
                
                // Update URL with filter parameters without reloading
                const url = new URL(window.location);
                
                // Clear existing parameters
                [...url.searchParams.keys()].forEach(key => {
                    url.searchParams.delete(key);
                });
                
                // Add new parameters from form
                for (let pair of formData.entries()) {
                    if (pair[1]) {
                        url.searchParams.set(pair[0], pair[1]);
                    }
                }
                
                // Update URL without reloading
                window.history.pushState({}, '', url);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                document.getElementById('purchasesTableContainer').innerHTML = '<div class="alert admin-alert-danger">Error loading data. Please try again.</div>';
            });
        }
        
        function updateSummaryStats(stats) {
            // Add safety checks to prevent undefined errors
            if (!stats) {
                console.error('Stats data is undefined');
                return;
            }
            
            // Use optional chaining to prevent errors
            document.getElementById('totalRevenue').textContent = stats.totalRevenue ? ('$' + stats.totalRevenue.toFixed(2)) : '$0.00';
            document.getElementById('monthlyRevenue').textContent = stats.monthlyRevenue ? ('$' + stats.monthlyRevenue.toFixed(2)) : '$0.00';
            document.getElementById('totalPurchases').textContent = stats.totalPurchases || 0;
            document.getElementById('avgOrderValue').textContent = stats.avgOrderValue ? ('$' + stats.avgOrderValue.toFixed(2)) : '$0.00';
        }
    });
</script>
@endpush