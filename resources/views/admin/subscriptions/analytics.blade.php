@extends('layouts.admin')

@section('title', 'Subscription Analytics')

@push('styles')
<style>
    .analytics-card {
        background-color: rgba(30, 30, 60, 0.7);
        border-radius: 10px;
        margin-bottom: 20px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .analytics-title {
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .stat-card {
        padding: 15px;
        border-radius: 8px;
        background-color: rgba(10, 10, 30, 0.7);
        text-align: center;
        height: 100%;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #d8b5ff;
    }
    
    .stat-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }
    
    .plan-revenue-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .plan-revenue-item:last-child {
        border-bottom: none;
    }
    
    .plan-name {
        font-weight: 500;
    }
    
    .plan-revenue {
        color: #d8b5ff;
    }
    
    .subscription-type-distribution {
        margin-top: 20px;
        text-align: center;
    }
    
    .subscription-share {
        position: relative;
        height: 30px;
        overflow: hidden;
        border-radius: 15px;
        margin-bottom: 15px;
    }
    
    .subscription-share .lifetime-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: linear-gradient(to right, #9400d3, #4b0082);
    }
    
    .subscription-share .recurring-bar {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(to right, #4169e1, #0047ab);
    }
    
    .subscription-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 3px;
    }
    
    .lifetime-color {
        background: linear-gradient(to right, #9400d3, #4b0082);
    }
    
    .recurring-color {
        background: linear-gradient(to right, #4169e1, #0047ab);
    }
</style>
@endpush

@section('content')
<h1 class="admin-page-title">Subscription Analytics</h1>

<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('admin.subscriptions.index') }}" class="btn-admin-secondary">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>
</div>

<div class="row" style="margin-top: 1rem;">
    <!-- Key Metrics -->
    <div class="col-md-6">
        <div class="analytics-card">
            <h2 class="analytics-title">Revenue Overview</h2>
            
            <div class="row" style="display: flex;gap: 1rem;">
                <div class="col-md-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">${{ number_format($totalRevenue, 2) }}</div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">{{ $lifetimeSubs + $recurringSubs }}</div>
                        <div class="stat-label">Total Subscriptions</div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">${{ number_format($lifetimeRevenue, 2) }}</div>
                        <div class="stat-label">Lifetime Revenue</div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="stat-card">
                        <div class="stat-number">${{ number_format($recurringRevenue, 2) }}</div>
                        <div class="stat-label">Recurring Revenue</div>
                    </div>
                </div>
            </div>
            
            <!-- Subscription Type Distribution -->
            <div class="subscription-type-distribution mt-4">
                <h3 class="fs-5 mb-3">Subscription Type Distribution</h3>
                
                @php
                    $totalSubs = $lifetimeSubs + $recurringSubs;
                    $lifetimePercent = $totalSubs > 0 ? ($lifetimeSubs / $totalSubs) * 100 : 0;
                    $recurringPercent = $totalSubs > 0 ? ($recurringSubs / $totalSubs) * 100 : 0;
                @endphp
                
                <div class="subscription-share">
                    <div class="lifetime-bar" style="width: {{ $lifetimePercent }}%"></div>
                    <div class="recurring-bar" style="width: {{ $recurringPercent }}%"></div>
                </div>
                
                <div class="subscription-legend">
                    <div class="legend-item">
                        <div class="legend-color lifetime-color"></div>
                        <div>Lifetime: {{ $lifetimeSubs }} ({{ number_format($lifetimePercent, 1) }}%)</div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color recurring-color"></div>
                        <div>Recurring: {{ $recurringSubs }} ({{ number_format($recurringPercent, 1) }}%)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue By Plan -->
    <div class="col-md-6">
        <div class="analytics-card">
            <h2 class="analytics-title">Revenue By Plan</h2>
            
            @if(count($planRevenue) > 0)
                <div>
                    @foreach($planRevenue as $plan)
                        <div class="plan-revenue-item">
                            <div class="plan-name">{{ $plan['name'] }}</div>
                            <div class="plan-revenue">${{ number_format($plan['revenue'], 2) }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <p>No revenue data available.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Monthly Growth Chart -->
<div class="analytics-card">
    <h2 class="analytics-title">Monthly Subscription Growth ({{ date('Y') }})</h2>
    
    <div class="chart-container">
        <canvas id="growthChart"></canvas>
    </div>
</div>

<!-- Plans Status -->
<div class="analytics-card">
    <h2 class="analytics-title">Subscription Plans Status</h2>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Active Subscribers</th>
                    <th>Availability</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                    <tr>
                        <td>{{ $plan->name }}</td>
                        <td>
                            @if($plan->is_lifetime)
                                <span class="badge bg-purple">Lifetime</span>
                            @else
                                <span class="badge bg-blue">{{ ucfirst($plan->billing_interval) }}ly</span>
                            @endif
                        </td>
                        <td>${{ number_format($plan->price, 2) }} {{ $plan->currency }}</td>
                        <td>
                            <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            {{ $plan->subscriptions()->where('status', 'active')->count() }}
                        </td>
                        <td>
                            @if($plan->available_until)
                                @if($plan->available_until->isPast())
                                    <span class="text-danger">Expired on {{ $plan->available_until->format('M d, Y') }}</span>
                                @else
                                    Until {{ $plan->available_until->format('M d, Y') }}
                                @endif
                            @else
                                Always
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        
        const monthlyData = @json($monthlyGrowth);
        
        const growthChart = new Chart(growthCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [
                    {
                        label: 'New Subscriptions',
                        data: monthlyData.map(item => item.new),
                        backgroundColor: 'rgba(138, 43, 226, 0.7)',
                        borderColor: 'rgba(138, 43, 226, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Canceled Subscriptions',
                        data: monthlyData.map(item => item.canceled),
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Net Growth',
                        data: monthlyData.map(item => item.net),
                        type: 'line',
                        backgroundColor: 'rgba(0, 123, 255, 0.5)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 2,
                        tension: 0.1,
                        pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                        pointRadius: 4
                    }
                ]
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
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgba(255, 255, 255, 0.8)'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush