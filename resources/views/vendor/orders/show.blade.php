@extends('layouts.app')
@include('vendor.partials.auto-refresh')

@section('content')
<div class="container-fluid py-4">
     <!-- Auto-refresh Control Bar (only if order is not completed) -->
    @if(!in_array($order->status, ['delivered', 'cancelled']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible fade show mb-0" style="background: #f0f7ff; border-left: 4px solid #05bb14;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="bi bi-arrow-repeat me-2"></i>
                        <span class="auto-refresh-status fw-bold">Auto-refreshing</span>
                        <span class="badge bg-light text-dark ms-2">
                            Next refresh: <span class="auto-refresh-countdown">30s</span>
                        </span>
                        @if($order->status === 'pending')
                            <span class="badge bg-warning ms-2">
                                <i class="bi bi-clock"></i> Awaiting your action
                            </span>
                        @endif
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" id="refreshNowBtn" title="Refresh Now">
                            <i class="bi bi-arrow-repeat"></i> Refresh Now
                        </button>
                        <button type="button" class="btn btn-outline-danger" id="toggleAutoRefreshBtn" title="Pause Auto-Refresh">
                            <i class="bi bi-pause-circle"></i> Pause
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-box"></i> Order #{{ $order->order_number }}
            </h2>
            <p class="text-muted mb-0">Order Details & Management</p>
        </div>
        <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8">
            <!-- Order Status & Info -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Order Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Order Number</p>
                            <p class="mb-0"><strong>#{{ $order->order_number }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Status</p>
                            <p class="mb-0">
                                <span class="badge badge-{{ 
                                    $order->status == 'delivered' ? 'success' : 
                                    ($order->status == 'cancelled' ? 'danger' : 'warning')
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Ordered On</p>
                            <p class="mb-0">{{ $order->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Payment Status</p>
                            <p class="mb-0">
                                <span class="badge bg-success">Paid</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-list-ul"></i> Order Items
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                    </td>
                                    <td>KES {{ number_format($item->product->base_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td><strong>KES {{ number_format($item->product->base_price * $item->quantity, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            @php
                                $baseSubtotal = $order->items->sum(function($item) {
                                    return $item->product->base_price * $item->quantity;
                                });
                            @endphp
                            <div class="mb-2 d-flex justify-content-between">
                                <span>Subtotal (Base Price):</span>
                                <strong>KES {{ number_format($baseSubtotal, 2) }}</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total:</span>
                                <strong class="fs-5 text-success">KES {{ number_format($baseSubtotal, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Status -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-arrow-repeat"></i> Update Order Status
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="status" class="form-label">New Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Select new status</option>
                                    <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>Preparing</option>
                                    <option value="ready_for_pickup" {{ $order->status == 'ready_for_pickup' ? 'selected' : '' }}>Ready for Pickup</option>
                                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle"></i> Update Status
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Add any notes about this order...">{{ old('notes', $order->notes ?? '') }}</textarea>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Customer & Delivery Info -->
        <div class="col-lg-4">
            <!-- Customer Info -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-person"></i> Customer Information
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>{{ $order->customer->name }}</strong>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-telephone"></i> {{ $order->customer->phone }}
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-envelope"></i> {{ $order->customer->email }}
                    </p>
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-map"></i> Delivery Information
                    </h6>
                </div>
                <div class="card-body small">
                    @if($order->rider)
                        <p class="mb-2">
                            <strong>Rider:</strong><br>
                            {{ $order->rider->user->name }}
                        </p>
                        <p class="mb-2">
                            <strong>Contact:</strong><br>
                            {{ $order->rider->user->phone }}
                        </p>
                    @else
                        <p class="text-muted mb-0">Rider not yet assigned</p>
                    @endif
                </div>
            </div>

            <!-- Order Timeline -->
            @if($order->tracking && $order->tracking->count() > 0)
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-clock-history"></i> Order Timeline
                        </h6>
                    </div>
                    <div class="card-body small">
                        @foreach($order->tracking as $track)
                            <div class="mb-2 pb-2" style="border-bottom: 1px solid #eee;">
                                <p class="mb-1">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $track->status)) }}</strong>
                                </p>
                                <small class="text-muted">
                                    {{ $track->created_at->format('M d, Y H:i') }}
                                </small>
                                @if($track->notes)
                                    <p class="mb-0 mt-1 text-muted">{{ $track->notes }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning"></i> Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('vendor.orders.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-list"></i> View All Orders
                        </a>
                        <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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
@endpush

@endsection
