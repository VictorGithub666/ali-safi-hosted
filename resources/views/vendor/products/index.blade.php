@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-box-seam"></i> Products
            </h2>
            <p class="text-muted mb-0">Manage your products and inventory</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.products.export') }}" class="btn btn-outline-secondary">
                <i class="bi bi-download"></i> Export CSV
            </a>
            <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add Product
            </a>
        </div>
    </div>

    @if($products->count() > 0)
        <!-- Filter & Search -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input product-checkbox" 
                                           value="{{ $product->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" 
                                                 alt="{{ $product->name }}" 
                                                 class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded me-2 bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $product->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ Str::limit($product->description, 40) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $product->category->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong>KES {{ number_format($product->price, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge {{ $product->pivot->stock_quantity > 10 ? 'bg-success' : ($product->pivot->stock_quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $product->pivot->stock_quantity }}
                                    </span>
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('vendor.products.show', $product) }}" 
                                       class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('vendor.products.edit', $product) }}" 
                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('vendor.products.destroy', $product) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>No products yet!</strong> Start by adding your first product.
            <a href="{{ route('vendor.products.create') }}" class="alert-link">Create Product</a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Availability toggle with AJAX - using direct URL path
    const csrfToken = "{{ csrf_token() }}";
    
    document.querySelectorAll('.availability-toggle').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            const productId = this.dataset.productId;
            const isAvailable = this.checked;
            const originalState = this.checked;
            
            try {
                // Use direct URL path instead of route helper
                const response = await fetch('/vendor/products/toggle-availability', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        is_available: isAvailable
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    console.log('Availability updated for product:', productId);
                } else {
                    throw new Error(data.message || 'Update failed');
                }
            } catch (error) {
                console.error('Error:', error);
                // Revert the toggle if there's an error
                this.checked = !originalState;
                alert('Failed to update availability. Please try again.');
            }
        });
    });
});
</script>
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
