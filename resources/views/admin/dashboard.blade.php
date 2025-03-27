@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .stats-card {
        background: rgba(30, 30, 60, 0.7);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        border-color: rgba(138, 43, 226, 0.7);
    }
    
    .stats-card .stat-title {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 10px;
    }
    
    .stats-card .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #d8b5ff;
        margin-bottom: 5px;
    }
    
    .stats-card .stat-subtitle {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.5);
    }
    
    .stats-card .stat-icon {
        font-size: 2.5rem;
        opacity: 0.2;
        top: 20px;
        right: 20px;
        float: right;
        color: #d8b5ff;
    }
    
    .recent-table {
        width: 100%;
        background: rgba(30, 30, 60, 0.7);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .recent-table th {
        background: rgba(138, 43, 226, 0.2);
        color: white;
        padding: 15px;
        text-align: left;
    }
    
    .recent-table td {
        padding: 12px 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.1);
        color: rgba(255, 255, 255, 0.8);
    }
    
    .chart-container {
        background: rgba(30, 30, 60, 0.7);
        border-radius: 10px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        margin-bottom: 20px;
    }
    
    .chart-title {
        font-size: 1.2rem;
        color: white;
        margin-bottom: 15px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="admin-page-title mb-4">Admin Dashboard</h1>
    
    <!-- Stats Row -->
    <div class="row">
        <div class="col-lg-8" style="display: flex; gap:1rem;">
            <!-- Sales Stats -->
            <div class="col-lg-2" style="width: 25%;">
                <div class="stats-card">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-title">Total Sales</div>
                    <div class="stat-value">${{ number_format($totalSales, 2) }}</div>
                    <div class="stat-subtitle">Lifetime</div>
                </div>
            </div>
            
            <div class="col-lg-2" style="width: 25%;">
                <div class="stats-card">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-title">Monthly Sales</div>
                    <div class="stat-value">${{ number_format($monthSales, 2) }}</div>
                    <div class="stat-subtitle">This Month</div>
                </div>
            </div>
            
            <!-- Content Stats -->
            <div class="col-lg-2" style="width: 25%;">
                <div class="stats-card">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-title">Content Stats</div>
                    <div class="stat-value">{{ $publishedChaptersCount }}/{{ $chaptersCount }}</div>
                    <div class="stat-subtitle">Published Chapters</div>
                </div>
            </div>
            
            <div class="col-lg-2" style="width: 25%;">
                <div class="stats-card">
                    <div class="stat-icon"><i class="fas fa-magic"></i></div>
                    <div class="stat-title">Spell Stats</div>
                    <div class="stat-value">{{ $publishedSpellsCount }}/{{ $spellsCount }}</div>
                    <div class="stat-subtitle">Published Spells</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Stats Row -->
    <div class="row">
        <div class="col-lg-8" style="display: flex; gap:1rem;">
            <div class="col-lg-3" style="width: 50%;">
                <div class="stats-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-title">Total Users</div>
                    <div class="stat-value">{{ $usersCount }}</div>
                    <div class="stat-subtitle">Registered Users</div>
                </div>
            </div>
            <div class="col-lg-3" style="width: 50%;">
                <div class="stats-card">
                    <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-title">New Users</div>
                    <div class="stat-value">{{ $newUsers }}</div>
                    <div class="stat-subtitle">This Month</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row">
        <div class="col-lg-8" style="display: flex; gap:1rem;">
            <div class="col-lg-4" style="width: 50%;">
                <div class="chart-container">
                    <h2 class="chart-title">Monthly Sales ({{ date('Y') }})</h2>
                    <canvas id="monthlySalesChart" height="300"></canvas>
                </div>
            </div>
            
            <div class="col-lg-4" style="width: 50%;">
                <div class="chart-container">
                    <h2 class="chart-title">Content Distribution</h2>
                    <canvas id="contentDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Purchases Table -->
        <div class="row">
            <div class="col-lg-12">
                <h2 class="mb-3">Recent Purchases</h2>
                <div class="table-responsive">
                    <table class="recent-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Items</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPurchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->invoice_number }}</td>
                                    <td>{{ $purchase->user->name }}</td>
                                    <td>${{ number_format($purchase->amount, 2) }}</td>
                                    <td>{{ $purchase->items->count() }} items</td>
                                    <td>{{ $purchase->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No recent purchases</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Sales Chart
        const salesCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const salesData = @json($chartData);
        
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: salesData.map(data => data.month),
                datasets: [{
                    label: 'Monthly Sales ($)',
                    data: salesData.map(data => data.total),
                    backgroundColor: 'rgba(138, 43, 226, 0.6)',
                    borderColor: 'rgba(138, 43, 226, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
        
        // Content Distribution Chart
        const contentCtx = document.getElementById('contentDistributionChart').getContext('2d');
        
        new Chart(contentCtx, {
            type: 'pie',
            data: {
                labels: ['Published Chapters', 'Unpublished Chapters', 'Published Spells', 'Unpublished Spells'],
                datasets: [{
                    data: [
                        {{ $publishedChaptersCount }}, 
                        {{ $chaptersCount - $publishedChaptersCount }},
                        {{ $publishedSpellsCount }},
                        {{ $spellsCount - $publishedSpellsCount }}
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(75, 192, 192, 0.3)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(153, 102, 255, 0.3)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush