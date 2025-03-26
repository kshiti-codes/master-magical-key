@extends('layouts.admin')

@section('title', 'User Purchase Analysis')

@push('styles')
<style>
    .period-buttons {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    .period-btn {
        background: rgba(30, 30, 60, 0.5);
        color: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(138, 43, 226, 0.3);
        padding: 8px 15px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .period-btn:first-child {
        border-radius: 5px 0 0 5px;
    }
    
    .period-btn:last-child {
        border-radius: 0 5px 5px 0;
    }
    
    .period-btn:hover {
        background: rgba(30, 30, 60, 0.8);
    }
    
    .period-btn.active {
        background: rgba(138, 43, 226, 0.5);
        color: white;
    }
    
    .metrics-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .metric-card {
        background: rgba(30, 30, 60, 0.7);
        border-radius: 10px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .metric-value {
        font-size: 1.8rem;
        font-weight: bold;
        color: #d8b5ff;
        margin-bottom: 5px;
    }
    
    .metric-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        text-align: center;
    }
    
    .chart-container {
        height: 400px;
        position: relative;
        margin-bottom: 40px;
    }
    
    .table-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.2rem;
        margin-bottom: 15px;
        margin-top: 30px;
    }
    
    .customer-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }
    
    .customer-table th {
        background: rgba(30, 30, 70, 0.6);
        padding: 12px 15px;
        text-align: left;
        color: #d8b5ff;
        font-weight: normal;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .customer-table td {
        padding: 10px 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .customer-table tr:last-child td {
        border-bottom: none;
    }
    
    .customer-table .text-right {
        text-align: right;
    }
    
    .segments-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .segment-card {
        background: rgba(15, 15, 35, 0.6);
        border-radius: 8px;
        padding: 20px;
        border: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .segment-card h3 {
        color: #d8b5ff;
        font-size: 1.1rem;
        margin-bottom: 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
        padding-bottom: 10px;
    }
    
    .segment-stat {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .segment-label {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .segment-value {
        color: #d8b5ff;
        font-weight: 500;
    }
    
    .progress-container {
        background: rgba(138, 43, 226, 0.1);
        height: 10px;
        border-radius: 5px;
        margin-top: 5px;
        overflow: hidden;
    }
    
    .progress-bar {
        height: 100%;
        background: linear-gradient(to right, #4b0082, #9400d3);
        border-radius: 5px;
    }
</style>
@endpush

@section('content')
    <h1 class="admin-page-title">User Purchase Analysis</h1>
    
    <div class="admin-card">
        <div class="admin-card-title">Customer Behavior Insights</div>
        
        <!-- Period Selection -->
        <form method="GET" action="{{ route('admin.reports.user_analysis') }}" class="period-buttons">
            <button type="submit" name="period" value="30days" class="period-btn {{ $period === '30days' ? 'active' : '' }}">Last 30 Days</button>
            <button type="submit" name="period" value="90days" class="period-btn {{ $period === '90days' ? 'active' : '' }}">Last 90 Days</button>
            <button type="submit" name="period" value="6months" class="period-btn {{ $period === '6months' ? 'active' : '' }}">Last 6 Months</button>
            <button type="submit" name="period" value="12months" class="period-btn {{ $period === '12months' ? 'active' : '' }}">Last 12 Months</button>
        </form>
        
        <!-- Key Metrics -->
        <div class="metrics-container">
            <div class="metric-card">
                <div class="metric-value">${{ number_format($avgOrderValue, 2) }}</div>
                <div class="metric-label">Average Order Value</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">{{ number_format($purchaseFrequency, 1) }}</div>
                <div class="metric-label">Average Purchases per Customer</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">{{ $repeatPurchaseDelay ? number_format($repeatPurchaseDelay, 0) : 'N/A' }}</div>
                <div class="metric-label">Average Days Between Purchases</div>
            </div>
        </div>
        
        <!-- New User Registration Chart -->
        <div class="chart-container">
            <canvas id="newUsersChart"></canvas>
        </div>
        
        <!-- Customer Segments -->
        <div class="segments-container">
            <!-- Purchase Frequency Segments -->
            <div class="segment-card">
                <h3>Purchase Frequency</h3>
                
                @php
                    // Calculate frequency distribution
                    $onetime = $userPurchases->where('purchase_count', 1)->count();
                    $occasional = $userPurchases->where('purchase_count', '>=', 2)->where('purchase_count', '<=', 3)->count();
                    $frequent = $userPurchases->where('purchase_count', '>=', 4)->where('purchase_count', '<=', 6)->count();
                    $loyal = $userPurchases->where('purchase_count', '>', 6)->count();
                    
                    $total = $onetime + $occasional + $frequent + $loyal;
                    $onetimePercent = $total > 0 ? ($onetime / $total) * 100 : 0;
                    $occasionalPercent = $total > 0 ? ($occasional / $total) * 100 : 0;
                    $frequentPercent = $total > 0 ? ($frequent / $total) * 100 : 0;
                    $loyalPercent = $total > 0 ? ($loyal / $total) * 100 : 0;
                @endphp
                
                <div class="segment-stat">
                    <div class="segment-label">One-time Buyers</div>
                    <div class="segment-value">{{ $onetime }} ({{ number_format($onetimePercent, 1) }}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $onetimePercent }}%"></div>
                </div>
                
                <div class="segment-stat">
                    <div class="segment-label">Occasional (2-3 purchases)</div>
                    <div class="segment-value">{{ $occasional }} ({{ number_format($occasionalPercent, 1) }}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $occasionalPercent }}%"></div>
                </div>
                
                <div class="segment-stat">
                    <div class="segment-label">Frequent (4-6 purchases)</div>
                    <div class="segment-value">{{ $frequent }} ({{ number_format($frequentPercent, 1) }}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $frequentPercent }}%"></div>
                </div>
                
                <div class="segment-stat">
                    <div class="segment-label">Loyal (7+ purchases)</div>
                    <div class="segment-value">{{ $loyal }} ({{ number_format($loyalPercent, 1) }}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $loyalPercent }}%"></div>
                </div>
            </div>
            
            <!-- Spending Segments -->
            <div class="segment-card">
                <h3>Customer Spending</h3>
                
                @php
                    // Calculate spending distribution
                    $low = $userPurchases->where('total_spent', '<', 50)->count();
                    $medium = $userPurchases->where('total_spent', '>=', 50)->where('total_spent', '<', 100)->count();
                    $high = $userPurchases->where('total_spent', '>=', 100)->where('total_spent', '<', 200)->count();
                    $vip = $userPurchases->where('total_spent', '>=', 200)->count();
                    
                    $totalSpenders = $low + $medium + $high + $vip;
                    $lowPercent = $totalSpenders > 0 ? ($low / $totalSpenders) * 100 : 0;
                    $mediumPercent = $totalSpenders > 0 ? ($medium / $totalSpenders) * 100 : 0;
                    $highPercent = $totalSpenders > 0 ? ($high / $totalSpenders) * 100 : 0;
                    $vipPercent = $totalSpenders > 0 ? ($vip / $totalSpenders) * 100 : 0;
                @endphp
                
                <div class="segment-stat">
                    <div class="segment-label">Low Spenders (<$50)</div>
                    <div class="segment-value">{{ $low }} ({{ number_format($lowPercent, 1) }}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $lowPercent }}%"></div>
                </div>
                
                <div class="segment-stat">
                    <div class="segment-label">Medium Spenders ($50-$99)</div>
                    <div class="segment-value">{{ $medium }} ({{ number_format($mediumPercent, 1) }}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $mediumPercent }}%"></div>
                </div>
                
                <div class="segment-stat">
                    <div class="segment-label">High Spenders ($100-$199)</div>
                    <div class="segment-value">{{ $high }} ({{ number_format($highPercent, 1) }}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $highPercent }}%"></div>
                </div>
                
                <div class="segment-stat">
                    <div class="segment-label">VIP Customers ($200+)</div>
                    <div class="segment-value">{{ $vip }} ({{ number_format($vipPercent, 1) }}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $vipPercent }}%"></div>
                </div>
            </div>
        </div>
        
        <!-- Top Customers Table -->
        <h3 class="table-title">Top Customers by Spending</h3>
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Joined</th>
                    <th class="text-right">Purchases</th>
                    <th class="text-right">Total Spent</th>
                    <th class="text-right">Average Order</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userPurchases->take(10) as $purchase)
                    @php
                        $user = App\Models\User::find($purchase->user_id);
                        if (!$user) continue;
                        $avgOrder = $purchase->purchase_count > 0 ? ($purchase->total_spent / $purchase->purchase_count) : 0;
                    @endphp
                    <tr>
                        <td>
                            {{ $user->name }}<br>
                            <small>{{ $user->email }}</small>
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-right">{{ $purchase->purchase_count }}</td>
                        <td class="text-right">${{ number_format($purchase->total_spent, 2) }}</td>
                        <td class="text-right">${{ number_format($avgOrder, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // New Users Registration Chart
        const ctx = document.getElementById('newUsersChart').getContext('2d');
        
        // Format the data for the chart
        const userData = @json($newUsers);
        const dates = userData.map(item => item.date);
        const counts = userData.map(item => item.count);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'New User Registrations',
                    data: counts,
                    backgroundColor: 'rgba(138, 43, 226, 0.2)',
                    borderColor: 'rgba(138, 43, 226, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(138, 43, 226, 1)',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                            color: 'rgba(255, 255, 255, 0.7)',
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    });
</script>
@endpush
