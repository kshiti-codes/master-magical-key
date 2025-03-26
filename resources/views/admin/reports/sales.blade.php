@extends('layouts.admin')

@section('title', 'Sales Report')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .report-controls {
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
    
    .top-items-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .period-toggle {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .period-toggle .btn-group {
        display: inline-flex;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .period-toggle .btn {
        background: rgba(30, 30, 60, 0.5);
        color: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(138, 43, 226, 0.3);
        padding: 8px 15px;
        transition: all 0.3s ease;
    }
    
    .period-toggle .btn:hover {
        background: rgba(30, 30, 60, 0.8);
    }
    
    .period-toggle .btn.active {
        background: rgba(138, 43, 226, 0.5);
        color: white;
    }
    
    /* Table styles for top selling items */
    .top-selling-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .top-selling-table th {
        background: rgba(30, 30, 70, 0.6);
        padding: 12px 15px;
        text-align: left;
        color: #d8b5ff;
        font-weight: normal;
    }
    
    .top-selling-table td {
        padding: 10px 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .top-selling-table tr:last-child td {
        border-bottom: none;
    }
    
    .top-selling-table .text-right {
        text-align: right;
    }
</style>
@endpush

@section('content')
    <h1 class="admin-page-title">Sales Report</h1>
    
    <div class="admin-card">
        <div class="admin-card-title">Sales Analysis</div>
        
        <!-- Report Filters -->
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="report-controls">
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
                <button type="submit" class="btn-admin-primary">Generate Report</button>
            </div>
        </form>
        
        <!-- Summary Statistics -->
        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-value">${{ number_format($salesData['totalAmount'], 2) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $salesData['totalCount'] }}</div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $newCustomers }}</div>
                <div class="stat-label">New Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $returningCustomers }}</div>
                <div class="stat-label">Returning Customers</div>
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
                <table class="top-selling-table">
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
    console.log('DOM loaded, checking chart element');
    const chartCanvas = document.getElementById('salesChart');
    
    if (!chartCanvas) {
        console.error('Chart canvas element not found!');
        return;
    }
    
    console.log('Canvas found, initializing chart');
    const ctx = chartCanvas.getContext('2d');
    
    // Check if data exists and is properly formatted
    const labels = @json($salesData['labels'] ?? []);
    const salesAmounts = @json($salesData['salesAmount'] ?? []);
    const salesCounts = @json($salesData['salesCount'] ?? []);
    
    console.log('Chart data:', { labels, salesAmounts, salesCounts });
    
    if (!labels.length) {
        console.error('No data available for chart');
        // Display a message in the chart area
        const chartContainer = chartCanvas.parentNode;
        const noDataMessage = document.createElement('div');
        noDataMessage.textContent = 'No data available for the selected period';
        noDataMessage.style.textAlign = 'center';
        noDataMessage.style.padding = '40px';
        noDataMessage.style.color = 'rgba(255, 255, 255, 0.7)';
        chartContainer.appendChild(noDataMessage);
        return;
    }
    
    try {
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue ($)',
                        data: salesAmounts,
                        backgroundColor: 'rgba(138, 43, 226, 0.5)',
                        borderColor: 'rgba(138, 43, 226, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                        order: 1
                    },
                    {
                        label: 'Orders',
                        data: salesCounts,
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
                                    label += '$' + context.raw.toFixed(2);
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
        console.log('Chart successfully initialized');
    } catch (error) {
        console.error('Error creating chart:', error);
    }
});
</script>
@endpush