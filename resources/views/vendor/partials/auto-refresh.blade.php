@push('scripts')
<script>
// Auto-refresh configuration
const AUTO_REFRESH_CONFIG = {
    enabled: true,
    interval: 30, // seconds
    timer: null,
    countdown: 30,
    isPaused: false,
    isPageVisible: true
};

// Function to reload the page
function reloadPage() {
    if (AUTO_REFRESH_CONFIG.enabled && !AUTO_REFRESH_CONFIG.isPaused && AUTO_REFRESH_CONFIG.isPageVisible) {
        window.location.reload();
    }
}

// Start countdown timer
function startCountdown() {
    if (AUTO_REFRESH_CONFIG.timer) {
        clearInterval(AUTO_REFRESH_CONFIG.timer);
    }
    
    AUTO_REFRESH_CONFIG.countdown = AUTO_REFRESH_CONFIG.interval;
    updateCountdownDisplay();
    
    AUTO_REFRESH_CONFIG.timer = setInterval(() => {
        if (AUTO_REFRESH_CONFIG.isPaused || !AUTO_REFRESH_CONFIG.isPageVisible) {
            return;
        }
        
        AUTO_REFRESH_CONFIG.countdown--;
        updateCountdownDisplay();
        
        if (AUTO_REFRESH_CONFIG.countdown <= 0) {
            clearInterval(AUTO_REFRESH_CONFIG.timer);
            reloadPage();
        }
    }, 1000);
}

// Update countdown display
function updateCountdownDisplay() {
    const countdownElements = document.querySelectorAll('.auto-refresh-countdown');
    const statusElements = document.querySelectorAll('.auto-refresh-status');
    
    countdownElements.forEach(el => {
        if (AUTO_REFRESH_CONFIG.isPaused) {
            el.textContent = 'Paused';
        } else {
            el.textContent = `${AUTO_REFRESH_CONFIG.countdown}s`;
        }
    });
    
    statusElements.forEach(el => {
        if (AUTO_REFRESH_CONFIG.isPaused) {
            el.textContent = 'Auto-refresh paused';
            el.className = 'badge bg-secondary auto-refresh-status';
        } else {
            el.textContent = 'Auto-refreshing';
            el.className = 'badge bg-info auto-refresh-status';
        }
    });
}

// Toggle auto-refresh
function toggleAutoRefresh() {
    const toggleBtn = document.getElementById('toggleAutoRefreshBtn');
    AUTO_REFRESH_CONFIG.isPaused = !AUTO_REFRESH_CONFIG.isPaused;
    
    if (AUTO_REFRESH_CONFIG.isPaused) {
        if (toggleBtn) {
            toggleBtn.innerHTML = '<i class="bi bi-play-circle"></i> Resume';
            toggleBtn.classList.remove('btn-danger');
            toggleBtn.classList.add('btn-success');
        }
        if (AUTO_REFRESH_CONFIG.timer) {
            clearInterval(AUTO_REFRESH_CONFIG.timer);
            AUTO_REFRESH_CONFIG.timer = null;
        }
    } else {
        if (toggleBtn) {
            toggleBtn.innerHTML = '<i class="bi bi-pause-circle"></i> Pause';
            toggleBtn.classList.remove('btn-success');
            toggleBtn.classList.add('btn-danger');
        }
        startCountdown();
    }
    
    updateCountdownDisplay();
}

// Manual refresh
function manualRefresh() {
    if (AUTO_REFRESH_CONFIG.timer) {
        clearInterval(AUTO_REFRESH_CONFIG.timer);
        AUTO_REFRESH_CONFIG.timer = null;
    }
    window.location.reload();
}

// Handle page visibility
function handleVisibilityChange() {
    AUTO_REFRESH_CONFIG.isPageVisible = !document.hidden;
    
    if (AUTO_REFRESH_CONFIG.isPageVisible && !AUTO_REFRESH_CONFIG.isPaused && !AUTO_REFRESH_CONFIG.timer) {
        startCountdown();
    } else if (!AUTO_REFRESH_CONFIG.isPageVisible && AUTO_REFRESH_CONFIG.timer) {
        clearInterval(AUTO_REFRESH_CONFIG.timer);
        AUTO_REFRESH_CONFIG.timer = null;
    }
}

// Check for updates (optional - for orders page)
let lastItemCount = {{ $itemsCount ?? 0 }};

function checkForUpdates() {
    const currentUrl = window.location.href;
    
    // For orders index page - check for new orders count
    if (currentUrl.includes('/vendor/orders') && !currentUrl.includes('/orders/')) {
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newOrderCount = doc.querySelector('.orders-count')?.innerText || '0';
            const currentOrderCount = document.querySelector('.orders-count')?.innerText || '0';
            
            if (newOrderCount !== currentOrderCount) {
                showUpdateNotification('New orders have arrived!');
                setTimeout(() => manualRefresh(), 2000);
            }
        })
        .catch(error => console.error('Error checking updates:', error));
    }
}

// Show notification
function showUpdateNotification(message) {
    const toast = document.createElement('div');
    toast.className = 'alert alert-warning alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    toast.style.zIndex = '100000';
    toast.style.minWidth = '350px';
    toast.style.zIndex = '10000';
    toast.innerHTML = `
        <i class="bi bi-bell-fill me-2"></i>
        <strong>🔔 Update Available!</strong>
        <p class="mb-0 small">${message} Refreshing page...</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast) toast.remove();
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if auto-refresh is enabled for this page
    @if(!isset($disableAutoRefresh) || !$disableAutoRefresh)
        startCountdown();
        
        // Set up event listeners
        const toggleBtn = document.getElementById('toggleAutoRefreshBtn');
        if (toggleBtn) toggleBtn.addEventListener('click', toggleAutoRefresh);
        
        const refreshNowBtn = document.getElementById('refreshNowBtn');
        if (refreshNowBtn) refreshNowBtn.addEventListener('click', manualRefresh);
        
        // Listen for page visibility changes
        document.addEventListener('visibilitychange', handleVisibilityChange);
        
        // Check for updates every 15 seconds on orders page
        @if(Request::routeIs('vendor.orders.*') && !Request::routeIs('vendor.orders.show'))
            setInterval(checkForUpdates, 15000);
        @endif
    @endif
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (AUTO_REFRESH_CONFIG.timer) {
        clearInterval(AUTO_REFRESH_CONFIG.timer);
    }
});
</script>
@endpush