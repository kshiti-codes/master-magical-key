@extends('layouts.admin')

@section('title', 'Sales Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link href="{{ asset('css/components/date-range-picker.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin/admin-purchase-report.css') }}" rel="stylesheet">
@endpush

@section('content')
    <h1 class="admin-page-title">Sales Report</h1>
    
    <div class="admin-card">
        <div class="admin-card-title">Sales Analysis</div>
        
        <!-- Report Filters -->
        <form id="reportForm" method="GET" action="{{ route('admin.reports.sales') }}" class="report-controls">
            <div class="filter-group">
                <label>Time Period</label>
                <select name="period" class="admin-form form-control" id="period">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            
            <div class="filter-group" id="yearFilter" style="{{ $period === 'monthly' ? '' : 'display: none;' }}">
                <label>Year</label>
                <select name="year" class="admin-form form-control">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            
            <div class="filter-group" id="dateRangeFilter" style="{{ $period !== 'monthly' ? '' : 'display: none;' }}">
                <label>Date Range</label>
                <input type="text" name="date_range" class="admin-form form-control" id="dateRangePicker" 
                       value="{{ $startDate && $endDate ? "$startDate - $endDate" : '' }}" placeholder="Select date range">
                <input type="hidden" name="start_date" id="startDate" value="{{ $startDate }}">
                <input type="hidden" name="end_date" id="endDate" value="{{ $endDate }}">
            </div>
            
            <div>
                <button type="button" id="generateReport" class="btn-admin-primary">Generate Report</button>
            </div>
        </form>
        
        <!-- Summary Statistics -->
        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-value" id="totalRevenue">${{ number_format($salesData['totalAmount'], 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="totalOrders">{{ $salesData['totalCount'] }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="newCustomers">{{ $newCustomers }}</div>
                <div class="stat-label">New Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="returningCustomers">{{ $returningCustomers }}</div>
                <div class="stat-label">Returning Customers</div>
            </div>
        </div>

        <!-- Add a loading overlay -->
        <div id="reportLoadingOverlay" style="display: none !important; position: absolute; width: 75%; height: 100%; background: rgba(0,0,0,0.7); z-index: 100; display: flex; justify-content: center; align-items: center;">
            <div style="text-align: center; color: white;">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p class="mt-3">Generating report...</p>
            </div>
        </div>
        
        <!-- Sales Chart -->
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
        
        <!-- Top Selling Items -->
        <div class="top-items-container">
            <!-- Top Chapters -->
            <div>
                <h3 class="table-title">Top Selling Chapters</h3>
                <table class="top-selling-table" id="topChaptersTable">
                    <thead>
                        <tr>
                            <th>Chapter</th>
                            <th class="text-right">Sales Count</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topChapters as $chapter)
                            <tr>
                                <td>{{ $chapter->title }}</td>
                                <td class="text-right">{{ $chapter->sales_count }}</td>
                                <td class="text-right">${{ number_format($chapter->sales_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No chapter sales data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Top Spells -->
            <div>
                <h3 class="table-title">Top Selling Spells</h3>
                <table class="top-selling-table">
                    <thead>
                        <tr>
                            <th>Spell</th>
                            <th class="text-right">Sales Count</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSpells as $spell)
                            <tr>
                                <td>{{ $spell->title }}</td>
                                <td class="text-right">{{ $spell->sales_count }}</td>
                                <td class="text-right">${{ number_format($spell->sales_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No spell sales data available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize daterangepicker
        $('#dateRangePicker').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'Apply',
                cancelLabel: 'Clear'
            }
        });
        
        $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            $('#startDate').val(picker.startDate.format('YYYY-MM-DD'));
            $('#endDate').val(picker.endDate.format('YYYY-MM-DD'));
        });
        
        $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#startDate').val('');
            $('#endDate').val('');
        });
        
        // Toggle between year and date range based on period selection
        $('#period').on('change', function() {
            if ($(this).val() === 'monthly') {
                $('#yearFilter').show();
                $('#dateRangeFilter').hide();
            } else {
                $('#yearFilter').hide();
                $('#dateRangeFilter').show();
            }
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('generateReport').addEventListener('click', function() {
        generateReportAjax();
    });
    
    function generateReportAjax() {
        // Show loading overlay
        const loadingOverlay = document.getElementById('reportLoadingOverlay');
        loadingOverlay.style.display = 'flex';
        
        // Get form data
        const formData = new FormData(document.getElementById('reportForm'));
        const queryString = new URLSearchParams(formData).toString();
        
        // Make AJAX request
        fetch('{{ route("admin.reports.sales.data") }}?' + queryString, {
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
                // Hide loading overlay
                loadingOverlay.style.display = 'none';
                
                // Update summary statistics
                if (data.stats) {
                    updateSummaryStats(data.stats);
                }
                
                // Update chart
                if (data.chartData) {
                    updateSalesChart(data.chartData);
                }
                
                // Update URL with filter parameters without reloading
                updateUrlWithFilters(formData);
            })
            .catch(error => {
                console.error('Error fetching report data:', error);
                loadingOverlay.style.display = 'none';
                alert('Error generating report. Please try again.');
        });
    }
    
    // Update summary statistics
    function updateSummaryStats(stats) {
        document.getElementById('totalRevenue').textContent = '$' + parseFloat(stats.totalAmount).toFixed(2);
        document.getElementById('totalOrders').textContent = stats.totalCount;
        document.getElementById('newCustomers').textContent = stats.newCustomers;
        document.getElementById('returningCustomers').textContent = stats.returningCustomers;
    }
    
    // Update sales chart
    function updateSalesChart(chartData) {
        // Destroy existing chart if it exists
        if (window.salesChart instanceof Chart) {
            window.salesChart.destroy();
        }
        
        const ctx = document.getElementById('salesChart').getContext('2d');
        window.salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
            {
                label: 'Revenue ($)',
                data: chartData.salesAmount,
                backgroundColor: 'rgba(138, 43, 226, 0.5)',
                borderColor: 'rgba(138, 43, 226, 1)',
                borderWidth: 1,
                yAxisID: 'y',
                order: 1
            },
            {
                label: 'Orders',
                data: chartData.salesCount,
                type: 'line',
                fill: false,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                pointRadius: 4,
                tension: 0.1,
                yAxisID: 'y1',
                order: 0
            }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                display: true,
                text: 'Revenue ($)',
                color: 'rgba(138, 43, 226, 1)'
                },
                grid: {
                color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                color: 'rgba(255, 255, 255, 0.7)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                display: true,
                text: 'Orders',
                color: 'rgba(75, 192, 192, 1)'
                },
                grid: {
                drawOnChartArea: false
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
                intersect: false,
                callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                    label += ': ';
                    }
                    if (context.dataset.yAxisID === 'y') {
                    label += '$' + Number(context.raw).toFixed(2);
                    } else {
                    label += context.raw;
                    }
                    return label;
                }
                }
            }
            }
        }
        });
    }
    
    // Update URL with filters
    function updateUrlWithFilters(formData) {
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
    }
    
    // Initialize chart on page load
    const initialChartData = {
        labels: @json($salesData['labels']),
        salesAmount: @json($salesData['salesAmount']),
        salesCount: @json($salesData['salesCount'])
    };
    
    updateSalesChart(initialChartData);
});
</script>
@endpush