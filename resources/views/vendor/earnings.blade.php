@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-graph-up"></i> Earnings
            </h2>
            <p class="text-muted mb-0">Track your revenue and sales performance</p>
        </div>
        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </div>

    {{-- Wallet Balance Card --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 text-white-50">Current Wallet Balance</h6>
                            <h2 class="mb-0 fw-bold">KES {{ number_format($vendor->wallet_balance ?? 0, 2) }}</h2>
                        </div>
                        <div>
                            <i class="bi bi-wallet2" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #05bb14;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Earnings</h6>
                    <h3 class="fw-bold mb-0">KES {{ number_format($totalEarnings ?? 0, 2) }}</h3>
                    <small class="text-muted">from delivered orders</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #237bdd;">
                        <i class="bi bi-box"></i>
                    </div>
                    <h6 class="text-muted mb-2">Completed Orders</h6>
                    <h3 class="fw-bold mb-0">{{ $totalOrders ?? 0 }}</h3>
                    <small class="text-muted">delivered successfully</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="mb-2" style="font-size: 2rem; color: #28a745;">
                        <i class="bi bi-calculator"></i>
                    </div>
                    <h6 class="text-muted mb-2">Average Order Value</h6>
                    <h3 class="fw-bold mb-0">
                        KES {{ $totalOrders > 0 ? number_format($totalEarnings / $totalOrders, 2) : '0.00' }}
                    </h3>
                    <small class="text-muted">per delivered order</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.earnings') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ $dateFrom ?? now()->subDays(30)->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ $dateTo ?? now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Apply Filter
                    </button>
                    <a href="{{ route('vendor.earnings') }}" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Date Filters --}}
    <div class="mb-4">
        <div class="btn-group" role="group">
            <a href="{{ route('vendor.earnings', ['date_from' => today()->subDays(7)->format('Y-m-d'), 'date_to' => today()->format('Y-m-d')]) }}" 
               class="btn btn-outline-secondary btn-sm {{ request('date_from') == today()->subDays(7)->format('Y-m-d') ? 'active' : '' }}">
                Last 7 Days
            </a>
            <a href="{{ route('vendor.earnings', ['date_from' => today()->subDays(30)->format('Y-m-d'), 'date_to' => today()->format('Y-m-d')]) }}" 
               class="btn btn-outline-secondary btn-sm {{ !request('date_from') || request('date_from') == today()->subDays(30)->format('Y-m-d') ? 'active' : '' }}">
                Last 30 Days
            </a>
            <a href="{{ route('vendor.earnings', ['date_from' => today()->startOfMonth()->format('Y-m-d'), 'date_to' => today()->format('Y-m-d')]) }}" 
               class="btn btn-outline-secondary btn-sm">
                This Month
            </a>
            <a href="{{ route('vendor.earnings', ['date_from' => today()->subMonths(1)->startOfMonth()->format('Y-m-d'), 'date_to' => today()->subMonths(1)->endOfMonth()->format('Y-m-d')]) }}" 
               class="btn btn-outline-secondary btn-sm">
                Last Month
            </a>
        </div>
    </div>

    {{-- Debug Information (Remove in production) --}}
    @if(app()->environment('local'))
        <div class="alert alert-info alert-dismissible fade show small" role="alert">
            <strong><i class="bi bi-bug"></i> Debug Info:</strong><br>
            Vendor ID: {{ $vendor->id }}<br>
            Business: {{ $vendor->business_name }}<br>
            Wallet Balance: KES {{ number_format($vendor->wallet_balance ?? 0, 2) }}<br>
            Total Earnings: KES {{ number_format($totalEarnings ?? 0, 2) }}<br>
            Completed Orders: {{ $totalOrders ?? 0 }}<br>
            Date Range: {{ $dateFrom ?? 'N/A' }} to {{ $dateTo ?? 'N/A' }}<br>
            Earnings Data Count: {{ $earnings->count() ?? 0 }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Earnings Chart -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-bar-chart"></i> Earnings Trend
            </h6>
        </div>
        <div class="card-body">
            @if($earnings && $earnings->count() > 0)
                <div style="height: 300px;">
                    <canvas id="earningsChart"></canvas>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-info-circle" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3 mb-0">
                        No earnings data available for the selected period.
                    </p>
                    <small class="text-muted">
                        Orders must be marked as "Delivered" to appear here.
                    </small>
                </div>
            @endif
        </div>
    </div>

    <!-- Earnings Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="bi bi-table"></i> Daily Earnings Breakdown
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th class="text-end">Orders</th>
                        <th class="text-end">Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($earnings ?? [] as $earning)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($earning->date)->format('M d, Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($earning->date)->format('l') }}</small>
                            </td>
                            <td class="text-end">
                                @php
                                    $orderCount = \App\Models\Order::where('vendor_id', $vendor->id)
                                        ->where('status', 'delivered')
                                        ->whereDate('created_at', $earning->date)
                                        ->count();
                                @endphp
                                <span class="badge bg-secondary">{{ $orderCount }}</span>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">KES {{ number_format($earning->total ?? 0, 2) }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                                <p class="text-muted mt-2 mb-0">
                                    No earnings data available for the selected period.
                                </p>
                                <small class="text-muted">
                                    Try adjusting the date range or mark orders as delivered.
                                </small>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($earnings && $earnings->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <th>Total</th>
                            <th class="text-end">{{ $totalOrders }}</th>
                            <th class="text-end text-success">KES {{ number_format($totalEarnings, 2) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Recent Delivered Orders --}}
    @php
        $recentDeliveredOrders = \App\Models\Order::where('vendor_id', $vendor->id)
            ->where('status', 'delivered')
            ->with(['customer'])
            ->latest()
            ->take(5)
            ->get();
    @endphp

    @if($recentDeliveredOrders->count() > 0)
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-check-circle"></i> Recent Delivered Orders
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentDeliveredOrders as $deliveredOrder)
                            @php
                                $baseOrderTotal = $deliveredOrder->items->sum(function($item) {
                                    return $item->product->base_price * $item->quantity;
                                });
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('vendor.orders.show', $deliveredOrder) }}">
                                        #{{ $deliveredOrder->order_number }}
                                    </a>
                                </td>
                                <td>{{ $deliveredOrder->customer->name ?? 'N/A' }}</td>
                                <td>{{ $deliveredOrder->delivered_at ? $deliveredOrder->delivered_at->format('M d, Y') : $deliveredOrder->created_at->format('M d, Y') }}</td>
                                <td class="text-end text-success">
                                    KES {{ number_format($baseOrderTotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@push('scripts')
@if($earnings && $earnings->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
        let refreshTimer = null;
        let countdown = 30;
        let isAutoRefreshEnabled = true;
        let isPageVisible = true;

        // Function to reload the page
        function reloadPage() {
            if (isAutoRefreshEnabled && isPageVisible) {
                window.location.reload();
            }
        }

        // Countdown timer for next refresh
        function startCountdown() {
            countdown = 30;
            const countdownElement = document.getElementById('countdownTimer');
            const refreshStatus = document.getElementById('refreshStatus');
            
            if (refreshTimer) {
                clearInterval(refreshTimer);
            }
            
            refreshTimer = setInterval(() => {
                if (!isAutoRefreshEnabled) return;
                
                countdown--;
                
                if (countdownElement) {
                    countdownElement.textContent = `Next refresh in ${countdown}s`;
                }
                
                if (countdown <= 0) {
                    clearInterval(refreshTimer);
                    if (refreshStatus) refreshStatus.textContent = 'Refreshing...';
                    reloadPage();
                }
            }, 1000);
        }

        // Toggle refresh function
        function toggleRefresh() {
            const toggleBtn = document.getElementById('toggleRefreshBtn');
            const refreshStatus = document.getElementById('refreshStatus');
            
            if (isAutoRefreshEnabled) {
                // Stop auto-refresh
                isAutoRefreshEnabled = false;
                if (refreshTimer) clearInterval(refreshTimer);
                
                if (toggleBtn) {
                    toggleBtn.innerHTML = '<i class="bi bi-play-circle"></i> Resume';
                    toggleBtn.classList.remove('btn-outline-danger');
                    toggleBtn.classList.add('btn-outline-success');
                }
                if (refreshStatus) refreshStatus.textContent = 'Auto-refresh paused';
                
                const countdownElement = document.getElementById('countdownTimer');
                if (countdownElement) countdownElement.textContent = 'Paused';
            } else {
                // Start auto-refresh
                isAutoRefreshEnabled = true;
                startCountdown();
                
                if (toggleBtn) {
                    toggleBtn.innerHTML = '<i class="bi bi-pause-circle"></i> Pause';
                    toggleBtn.classList.remove('btn-outline-success');
                    toggleBtn.classList.add('btn-outline-danger');
                }
                if (refreshStatus) refreshStatus.textContent = 'Auto-refreshing';
            }
        }

        // Manual refresh
        // function manualRefresh() {
        //     if (refreshTimer) clearInterval(refreshTimer);
        //     window.location.reload();
        // }

        // // Handle page visibility (don't refresh when tab is hidden)
        // function handleVisibilityChange() {
        //     isPageVisible = !document.hidden;
            
        //     if (isPageVisible && isAutoRefreshEnabled && !refreshTimer) {
        //         startCountdown();
        //     } else if (!isPageVisible && refreshTimer) {
        //         clearInterval(refreshTimer);
        //         refreshTimer = null;
        //     }
        // }

        // Auto-refresh on new orders only (optional - poll for new orders)
        let lastOrderCount = {{ $totalOrders ?? 0 }};
        let lastPendingCount = {{ $pendingOrders ?? 0 }};

        function checkForNewOrders() {
            fetch('{{ route("vendor.orders.check-new") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.has_new_orders) {
                        // Show notification
                        const toast = document.createElement('div');
                        toast.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                        toast.style.zIndex = '100000';
                        toast.style.minWidth = '350px';
                        toast.style.zIndex = '10000';
                        toast.innerHTML = `
                            <i class="bi bi-bell-fill me-2"></i>
                            <strong>🔔 New Order Received!</strong>
                            <p class="mb-0 small">You have ${data.new_count} new order(s). Refreshing page...</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        `;
                        document.body.appendChild(toast);
                        
                        // Refresh immediately
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                })
                .catch(error => console.error('Error checking orders:', error));
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            startCountdown();
            
            // Set up event listeners
            const toggleBtn = document.getElementById('toggleRefreshBtn');
            if (toggleBtn) toggleBtn.addEventListener('click', toggleRefresh);
            
            const refreshNowBtn = document.getElementById('refreshNowBtn');
            if (refreshNowBtn) refreshNowBtn.addEventListener('click', manualRefresh);
            
            // Listen for page visibility changes
            document.addEventListener('visibilitychange', handleVisibilityChange);
            
            // Check for new orders every 15 seconds (optional)
            setInterval(checkForNewOrders, 15000);
        });

        // Clean up intervals on page unload
        window.addEventListener('beforeunload', function() {
            if (refreshTimer) {
                clearInterval(refreshTimer);
            }
        });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('earningsChart').getContext('2d');
    
    const earningsData = @json($earnings);
    
    const labels = earningsData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    const data = earningsData.map(item => parseFloat(item.total || 0));
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Earnings (KES)',
                data: data,
                borderColor: '#05bb14',
                backgroundColor: 'rgba(5, 187, 20, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#05bb14',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'KES ' + context.parsed.y.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'KES ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endpush

@endsection