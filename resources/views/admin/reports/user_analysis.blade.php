@extends('layouts.admin')

@section('title', 'User Purchase Analysis')

@push('styles')
<link href="{{ asset('css/admin/admin-purchase-report.css') }}" rel="stylesheet">
@endpush

@section('content')
    <h1 class="admin-page-title">User Purchase Analysis</h1>
    
    <div class="admin-card">
        <div class="admin-card-title">Customer Behavior Insights</div>
        
        <!-- Period Selection -->
        <form method="GET" action="{{ route('admin.reports.user_analysis') }}" class="period-buttons">
            <button type="button" data-period="30days" name="period" value="30days" class="period-btn {{ $period === '30days' ? 'active' : '' }}">Last 30 Days</button>
            <button type="button" data-period="90days" name="period" value="90days" class="period-btn {{ $period === '90days' ? 'active' : '' }}">Last 90 Days</button>
            <button type="button" data-period="6months" name="period" value="6months" class="period-btn {{ $period === '6months' ? 'active' : '' }}">Last 6 Months</button>
            <button type="button" data-period="12months" name="period" value="12months" class="period-btn {{ $period === '12months' ? 'active' : '' }}">Last 12 Months</button>
        </form>
        
        <!-- Key Metrics -->
        <div class="metrics-container">
            <div class="metric-card">
                <div class="metric-value" id="avgOrderValue">${{ number_format($avgOrderValue, 2) }}</div>
                <div class="metric-label">Average Order Value</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" id="purchaseFrequency">{{ number_format($purchaseFrequency, 1) }}</div>
                <div class="metric-label">Average Purchases per Customer</div>
            </div>
            <div class="metric-card">
                <div class="metric-value" id="repeatPurchaseDelay">{{ $repeatPurchaseDelay ? number_format($repeatPurchaseDelay, 0) : 'N/A' }}</div>
                <div class="metric-label">Average Days Between Purchases</div>
            </div>
        </div>

        <!-- Add a loading overlay -->
        <div id="analysisLoadingOverlay" style="display: none !important; position: absolute; width: 75%; height: 100%; background: rgba(0,0,0,0.7); z-index: 100; display: flex; justify-content: center; align-items: center;">
            <div style="text-align: center; color: white;">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p class="mt-3">Loading data...</p>
            </div>
        </div>
        
        <!-- New User Registration Chart -->
        <div class="chart-container">
            <canvas id="newUsersChart"></canvas>
        </div>
        
        <!-- Customer Segments -->
        <div id="segmentsContainer" class="segments-container">
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
        // Initialize user chart with initial data
        initUserChart();
        
        // Add event listeners to period buttons
        document.querySelectorAll('.period-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Get the period value
                const period = this.getAttribute('data-period');
                
                // Make the AJAX request to load data for this period
                loadUserAnalysisData(period);
                
                // Update active button state
                document.querySelectorAll('.period-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
            });
        });
        
        function loadUserAnalysisData(period) {
            // Show loading overlay
            const loadingOverlay = document.getElementById('analysisLoadingOverlay');
            loadingOverlay.style.display = 'flex';
            
            // Make AJAX request
            fetch(`{{ route('admin.reports.user_analysis.data') }}?period=${period}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('User analysis data received:', data);
                
                // Hide loading overlay
                loadingOverlay.style.display = 'none';
                
                // Update metrics
                if (data.metrics) {
                    updateMetrics(data.metrics);
                }
                
                // Update chart
                if (data.newUsers) {
                    updateUserChart(data.newUsers);
                }
                
                // Update segments
                if (data.segments) {
                    updateSegments(data.segments);
                }
                
                // Update URL without page reload
                const url = new URL(window.location);
                url.searchParams.set('period', period);
                window.history.pushState({}, '', url);
            })
            .catch(error => {
                console.error('Error fetching user analysis data:', error);
                loadingOverlay.style.display = 'none';
                alert('Error loading data. Please try again.');
            });
        }
        
        function updateMetrics(metrics) {
            document.getElementById('avgOrderValue').textContent = '$' + parseFloat(metrics.avgOrderValue).toFixed(2);
            document.getElementById('purchaseFrequency').textContent = parseFloat(metrics.purchaseFrequency).toFixed(1);
            
            if (metrics.repeatPurchaseDelay) {
                document.getElementById('repeatPurchaseDelay').textContent = parseInt(metrics.repeatPurchaseDelay);
            } else {
                document.getElementById('repeatPurchaseDelay').textContent = 'N/A';
            }
        }
        
        function updateUserChart(userData) {
            // Get chart data
            const dates = userData.map(item => item.date);
            const counts = userData.map(item => item.count);
            
            // Destroy previous chart if it exists
            if (window.newUsersChart) {
                window.newUsersChart.destroy();
            }
            
            // Create new chart
            const ctx = document.getElementById('newUsersChart').getContext('2d');
            window.newUsersChart = new Chart(ctx, {
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
        }
        
        function updateSegments(segments) {
            const container = document.getElementById('segmentsContainer');
            container.innerHTML = '';
            
            // Purchase Frequency Segments
            if (segments.frequency) {
                const frequencyCard = document.createElement('div');
                frequencyCard.className = 'segment-card';
                
                let frequencyHtml = '<h3>Purchase Frequency</h3>';
                
                const frequencyData = segments.frequency;
                
                // One-time buyers
                frequencyHtml += createSegmentRow('One-time Buyers', frequencyData.onetime, frequencyData.onetimePercent);
                
                // Occasional buyers
                frequencyHtml += createSegmentRow('Occasional (2-3 purchases)', frequencyData.occasional, frequencyData.occasionalPercent);
                
                // Frequent buyers
                frequencyHtml += createSegmentRow('Frequent (4-6 purchases)', frequencyData.frequent, frequencyData.frequentPercent);
                
                // Loyal buyers
                frequencyHtml += createSegmentRow('Loyal (7+ purchases)', frequencyData.loyal, frequencyData.loyalPercent);
                
                frequencyCard.innerHTML = frequencyHtml;
                container.appendChild(frequencyCard);
            }
            
            // Spending Segments
            if (segments.spending) {
                const spendingCard = document.createElement('div');
                spendingCard.className = 'segment-card';
                
                let spendingHtml = '<h3>Customer Spending</h3>';
                
                const spendingData = segments.spending;
                
                // Low spenders
                spendingHtml += createSegmentRow('Low Spenders (<$50)', spendingData.low, spendingData.lowPercent);
                
                // Medium spenders
                spendingHtml += createSegmentRow('Medium Spenders ($50-$99)', spendingData.medium, spendingData.mediumPercent);
                
                // High spenders
                spendingHtml += createSegmentRow('High Spenders ($100-$199)', spendingData.high, spendingData.highPercent);
                
                // VIP customers
                spendingHtml += createSegmentRow('VIP Customers ($200+)', spendingData.vip, spendingData.vipPercent);
                
                spendingCard.innerHTML = spendingHtml;
                container.appendChild(spendingCard);
            }
        }
        
        function createSegmentRow(label, value, percent) {
            return `
                <div class="segment-stat">
                    <div class="segment-label">${label}</div>
                    <div class="segment-value">${value} (${percent.toFixed(1)}%)</div>
                </div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: ${percent}%"></div>
                </div>
            `;
        }
        
        // Initialize user chart on page load
        function initUserChart() {
            const userData = @json($newUsers);
            const dates = userData.map(item => item.date);
            const counts = userData.map(item => item.count);
            
            const ctx = document.getElementById('newUsersChart').getContext('2d');
            window.newUsersChart = new Chart(ctx, {
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
        }
    });
</script>
@endpush
