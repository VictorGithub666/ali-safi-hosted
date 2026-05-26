<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Ali-Safi') }} - {{ $pageTitle ?? 'Marketplace' }}</title>

        <!-- Favicon - Using PWA icons -->
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icons/icon-512x512.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

        <!-- Fallback for older browsers -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#05bb14">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Ali-Safi">
        <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

        <style>
            :root {
                --primary-green: #05bb14;
                --primary-blue: #237bdd;
                --light-gray: #f8f9fa;
            }

            body {
                font-family: 'Poppins', sans-serif;
                background-color: #fafafa;
            }

            .navbar {
                background-color: #fff;
                border-bottom: 1px solid #e9ecef;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .navbar-brand {
                font-weight: 700;
                color: var(--primary-green) !important;
                font-size: 1.5rem;
            }

            .nav-link {
                color: #495057 !important;
                transition: all 0.3s ease;
            }

            .nav-link:hover {
                color: var(--primary-green) !important;
            }

            .nav-link.active {
                color: var(--primary-green) !important;
                font-weight: 600;
            }

            .btn-primary {
                background-color: var(--primary-green);
                border-color: var(--primary-green);
            }

            .btn-primary:hover {
                background-color: #048a0f;
                border-color: #048a0f;
            }

            .btn-secondary {
                background-color: var(--primary-blue);
                border-color: var(--primary-blue);
            }

            .btn-secondary:hover {
                background-color: #1a59a8;
                border-color: #1a59a8;
            }

            .footer {
                background-color: #2c3e50;
                color: #ecf0f1;
                padding: 2rem 0;
                margin-top: 3rem;
            }

            .footer a {
                color: #ecf0f1;
                text-decoration: none;
            }

            .footer a:hover {
                color: var(--primary-green);
            }

            .card {
                border: none;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                transition: transform 0.3s, box-shadow 0.3s;
            }

            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.12);
            }

            .alert {
                border: none;
            }

            .alert-success {
                background-color: rgba(5, 187, 20, 0.1);
                color: #048a0f;
            }

            .alert-danger {
                background-color: rgba(220, 53, 69, 0.1);
                color: #c82333;
            }

            .form-control:focus {
                border-color: var(--primary-green);
                box-shadow: 0 0 0 0.2rem rgba(5, 187, 20, 0.25);
            }

            .badge-success {
                background-color: var(--primary-green);
            }

            .badge-info {
                background-color: var(--primary-blue);
            }

            /* Annoying Modal Styles */
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10% { transform: translateX(-10px); }
                20% { transform: translateX(10px); }
                30% { transform: translateX(-10px); }
                40% { transform: translateX(10px); }
                50% { transform: translateX(-5px); }
                60% { transform: translateX(5px); }
                70% { transform: translateX(-3px); }
                80% { transform: translateX(3px); }
                90% { transform: translateX(-1px); }
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); background: #ff4444; }
                50% { transform: scale(1.02); background: #ff6666; }
            }

            @keyframes blinkRed {
                0%, 100% { border-color: #ff0000; background: #fff; }
                50% { border-color: #ffff00; background: #ffff00; }
            }

            @keyframes shakeY {
                0%, 100% { transform: translateY(0); }
                25% { transform: translateY(-5px); }
                75% { transform: translateY(5px); }
            }

            .annoying-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.98);
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(5px);
                animation: shakeY 0.3s ease-in-out infinite;
            }

            .annoying-modal {
                background: linear-gradient(135deg, #ff0000, #cc0000);
                padding: 40px;
                border-radius: 0;
                max-width: 550px;
                text-align: center;
                border: 5px solid yellow;
                animation: shake 0.5s infinite, pulse 1s infinite;
                box-shadow: 0 0 50px rgba(255,0,0,0.8);
            }

            .annoying-modal h2 {
                color: white;
                font-size: 32px;
                margin-bottom: 20px;
                text-transform: uppercase;
                font-weight: bold;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            }

            .annoying-modal p {
                color: #ffcccc;
                font-size: 18px;
                margin-bottom: 30px;
            }

            .annoying-modal .acknowledge-btn {
                background: #ff4444;
                color: white;
                border: 3px solid white;
                padding: 15px 30px;
                font-size: 18px;
                font-weight: bold;
                cursor: pointer;
                animation: pulse 1s infinite;
                border-radius: 10px;
                margin: 10px;
                transition: all 0.3s;
            }

            .annoying-modal .acknowledge-btn:hover {
                background: #ff6666;
                transform: scale(1.05);
            }

            .annoying-modal .dismiss-btn {
                background: #333;
                color: white;
                border: 2px solid red;
                padding: 15px 30px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 10px;
                margin: 10px;
                transition: all 0.3s;
            }

            .annoying-modal .dismiss-btn:hover {
                background: #555;
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light sticky-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <img style="display: inline; height:75px;" src="/storage/logo-100.png" alt=""> Ali-Safi
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        @auth
                            @if(Auth::user()->user_type === 'customer')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('customer.products.index') }}"><i class="bi bi-shop"></i> Products</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link position-relative" href="{{ route('customer.cart') }}">
                                        <i class="bi bi-cart"></i> Cart
                                        @php
                                            $cartCount = auth()->user()->cart()->count();
                                        @endphp
                                        @if($cartCount > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background-color: var(--primary-green); font-size: 0.7rem;">
                                                {{ $cartCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('customer.orders') }}"><i class="bi bi-box"></i> Orders</a>
                                </li>
                            @elseif(Auth::user()->user_type === 'vendor')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('vendor.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('vendor.products.index') }}"><i class="bi bi-box"></i> Products</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('vendor.orders.index') }}"><i class="bi bi-box"></i> Orders</a>
                                </li>
                            @elseif(Auth::user()->user_type === 'rider')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('rider.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('rider.deliveries') }}"><i class="bi bi-truck"></i> Deliveries</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('rider.earnings') }}"><i class="bi bi-graph-up"></i> Earnings</a>
                                </li>
                            @elseif(Auth::user()->user_type === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.orders') }}">
                                        <i class="bi bi-box"></i> Orders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.vendors.index') }}">
                                        <i class="bi bi-shop"></i> Vendors
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.riders.index') }}">
                                        <i class="bi bi-bicycle"></i> Riders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.customers.index') }}">
                                        <i class="bi bi-people"></i> Customers
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.prices.index') }}">
                                        <i class="bi bi-tag"></i> Pricing
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.finances.dashboard') }}">
                                        <i class="bi bi-graph-up"></i> Finances
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.orders.assignment') }}">
                                        <i class="bi bi-truck"></i> Assign Riders
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.settings') }}">
                                        <i class="bi bi-gear"></i> Settings
                                    </a>
                                </li>
                            @endif
                            
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">Register</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Alerts -->
        <div class="container-fluid" style="margin-top: 1rem;">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="bi bi-exclamation-circle"></i> Errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Main Content -->
        <main class="min-vh-100">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <h6><i class="bi bi-droplet-fill"></i> Ali-Safi</h6>
                        <p class="small mb-0">Your trusted gas and water delivery platform</p>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <h6>Quick Links</h6>
                        <ul class="list-unstyled small">
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">Terms & Conditions</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Contact Us</h6>
                        <p class="small mb-1">
                            <i class="bi bi-envelope-fill me-2"></i>
                            <a href="mailto:alisafisolutionsltd@gmail.com" class="text-white text-decoration-none">alisafisolutionsltd@gmail.com</a>
                        </p>
                        <p class="small mb-0">
                            <i class="bi bi-telephone-fill me-2"></i>
                            <a href="tel:+254110007835" class="text-white text-decoration-none">+254 110 007 835</a>
                        </p>
                    </div>
                </div>
                <hr style="border-color: rgba(255,255,255,0.1); margin: 1rem 0;">
                <div class="text-center small">
                    <p class="mb-0">&copy; {{ date('Y') }} Ali-Safi Solutions Ltd. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        @auth
@if(Auth::user()->user_type === 'vendor')
<script>
// Global auto-refresh for vendor pages (30 seconds)
(function() {
    // Don't auto-refresh on edit/create pages or when form has unsaved changes
    const noRefreshPaths = ['/vendor/products/create', '/vendor/products/*/edit'];
    const currentPath = window.location.pathname;
    
    let shouldAutoRefresh = true;
    
    // Check if current page should not auto-refresh
    for (const path of noRefreshPaths) {
        const pattern = path.replace(/\*/g, '[^/]+');
        const regex = new RegExp('^' + pattern + '$');
        if (regex.test(currentPath)) {
            shouldAutoRefresh = false;
            break;
        }
    }
    
    if (shouldAutoRefresh) {
        let countdown = 30;
        
        // Create floating indicator
        const indicator = document.createElement('div');
        indicator.id = 'autoRefreshIndicator';
        indicator.style.cssText = 'position:fixed; bottom:20px; right:20px; background:#05bb14; color:white; padding:8px 15px; border-radius:20px; font-size:12px; z-index:9999; cursor:pointer; box-shadow:0 2px 10px rgba(0,0,0,0.2);';
        indicator.innerHTML = `🔄 Refresh in ${countdown}s <span style="margin-left:5px;">⏸️</span>`;
        document.body.appendChild(indicator);
        
        let timer = setInterval(() => {
            countdown--;
            indicator.innerHTML = `🔄 Refresh in ${countdown}s <span style="margin-left:5px;">⏸️</span>`;
            
            if (countdown <= 0) {
                clearInterval(timer);
                window.location.reload();
            }
        }, 1000);
        
        // Pause on click
        indicator.addEventListener('click', function() {
            if (timer) {
                clearInterval(timer);
                timer = null;
                indicator.style.background = '#dc3545';
                indicator.innerHTML = `⏸️ Auto-refresh paused <span style="margin-left:5px;">▶️</span>`;
                
                // Resume after 5 seconds if clicked again
                indicator.addEventListener('click', function resume() {
                    if (!timer) {
                        countdown = 30;
                        timer = setInterval(() => {
                            countdown--;
                            indicator.innerHTML = `🔄 Refresh in ${countdown}s <span style="margin-left:5px;">⏸️</span>`;
                            if (countdown <= 0) {
                                clearInterval(timer);
                                window.location.reload();
                            }
                        }, 1000);
                        indicator.style.background = '#05bb14';
                        indicator.innerHTML = `🔄 Refresh in ${countdown}s <span style="margin-left:5px;">⏸️</span>`;
                        indicator.removeEventListener('click', resume);
                    }
                });
            }
        });
    }
})();
</script>
@endif
@endauth


        <!-- Password Toggle Script -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle password visibility toggle
                const toggleButtons = document.querySelectorAll('.toggle-password');
                
                toggleButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const targetId = this.getAttribute('data-target');
                        const passwordInput = document.getElementById(targetId);
                        const icon = this.querySelector('i');
                        
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            icon.classList.remove('bi-eye');
                            icon.classList.add('bi-eye-slash');
                        } else {
                            passwordInput.type = 'password';
                            icon.classList.remove('bi-eye-slash');
                            icon.classList.add('bi-eye');
                        }
                    });
                });
            });
        </script>

        <!-- Annoying Notifications JS -->
        <script>
            // Global audio element for annoying sounds
            let annoyingAudio = null;
            
            function playAnnoyingSound() {
                if (annoyingAudio) {
                    annoyingAudio.pause();
                    annoyingAudio.currentTime = 0;
                }
                annoyingAudio = new Audio('{{ asset("sounds/alarm_sound.mp3") }}');
                annoyingAudio.loop = true;
                annoyingAudio.volume = 1.0;
                annoyingAudio.play().catch(e => console.log('Audio play failed', e));
            }
            
            function stopAnnoyingSound() {
                if (annoyingAudio) {
                    annoyingAudio.pause();
                    annoyingAudio = null;
                }
            }
            
            function vibrateDevice() {
                if (navigator.vibrate) {
                    navigator.vibrate([500, 200, 500, 200, 1000, 200, 500, 200, 500, 200, 2000]);
                }
            }
            
            function acknowledgeUrgentNotification(notificationId, orderId, type) {
                stopAnnoyingSound();
                
                fetch('{{ route("notifications.acknowledge") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        notification_id: notificationId, 
                        order_id: orderId,
                        type: type
                    })
                }).then(response => response.json())
                  .then(data => {
                      document.getElementById(`annoyingModal${notificationId}`).remove();
                      if (orderId && type === 'admin') {
                          window.location.href = '{{ url("/admin/orders") }}/' + orderId;
                      } else if (orderId && type === 'vendor') {
                          window.location.href = '{{ url("/vendor/orders") }}/' + orderId;
                      } else if (orderId && type === 'rider') {
                          window.location.href = '{{ url("/rider/deliveries") }}/' + orderId;
                      } else if (orderId && type === 'customer') {
                          window.location.href = '{{ url("/customer/orders/track") }}/' + orderId;
                      }
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      document.getElementById(`annoyingModal${notificationId}`).remove();
                  });
            }
            
            function dismissAnnoyingModal(notificationId) {
                stopAnnoyingSound();
                document.getElementById(`annoyingModal${notificationId}`).remove();
            }
        </script>

        <!-- Sticky Notifications Modals -->
        @if(session()->has('sticky_notifications') && count(session('sticky_notifications', [])) > 0)
            @foreach(session('sticky_notifications') as $notification)
            <div class="annoying-modal-overlay" id="annoyingModal{{ $notification['id'] }}">
                <div class="annoying-modal">
                    <div style="font-size: 48px; margin-bottom: 20px;">🔴🔔🔴</div>
                    <h2>{{ $notification['title'] }}</h2>
                    <p>{!! nl2br(e($notification['message'])) !!}</p>
                    
                    <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                        <button onclick="acknowledgeUrgentNotification('{{ $notification['id'] }}', {{ $notification['order_id'] ?? 'null' }}, '{{ $notification['type'] }}')" 
                                class="acknowledge-btn">
                            ✅ ACKNOWLEDGE NOW
                        </button>
                        <button onclick="dismissAnnoyingModal('{{ $notification['id'] }}')" 
                                class="dismiss-btn">
                            ❌ Dismiss (Remind in 1 min)
                        </button>
                    </div>
                    
                    <div style="margin-top: 20px; font-size: 12px; color: #ff9999;">
                        <span id="countdown{{ $notification['id'] }}">This alert will auto-dismiss in 30 seconds</span>
                    </div>
                </div>
            </div>
            
            <script>
                (function() {
                    let countdown = 30;
                    const countdownEl = document.getElementById('countdown{{ $notification['id'] }}');
                    
                    // Play sound and vibrate
                    playAnnoyingSound();
                    vibrateDevice();
                    
                    // Countdown timer
                    const timer = setInterval(() => {
                        countdown--;
                        if (countdownEl) {
                            countdownEl.textContent = `This alert will auto-dismiss in ${countdown} seconds`;
                        }
                        if (countdown <= 0) {
                            clearInterval(timer);
                            stopAnnoyingSound();
                            const modal = document.getElementById('annoyingModal{{ $notification['id'] }}');
                            if (modal) modal.remove();
                            
                            // Auto-acknowledge after timeout
                            fetch('{{ route("notifications.acknowledge") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ 
                                    notification_id: '{{ $notification['id'] }}', 
                                    order_id: {{ $notification['order_id'] ?? 'null' }},
                                    auto_acknowledged: true
                                })
                            });
                        }
                    }, 1000);
                })();
            </script>
            @endforeach
        @endif

        <!-- Notification Checker for New Alerts -->
        <script>
            // Replace the notification checker with this version that only checks for notifications
            // that belong to the current user type (this should be handled by your backend)

            let lastNotificationCount = 0;
            let lastCheckTime = Date.now();

            function checkForNewNotifications() {
                // Don't check if this is a customer page (optional)
                // You can add logic to skip checking for customers if needed
                
                fetch('{{ route("notifications.check") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.has_notifications && data.count !== lastNotificationCount) {
                            lastNotificationCount = data.count;
                            
                            // Show a toast notification about new alert
                            const toast = document.createElement('div');
                            toast.className = 'alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
                            toast.style.zIndex = '100000';
                            toast.style.minWidth = '350px';
                            toast.style.background = '#ff0000';
                            toast.style.color = 'white';
                            toast.style.fontWeight = 'bold';
                            toast.innerHTML = `
                                <i class="bi bi-bell-fill me-2"></i>
                                <strong>🔴 NEW URGENT ALERT! 🔴</strong>
                                <p class="mb-0 small">You have a new notification that requires immediate attention!</p>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                            `;
                            document.body.appendChild(toast);
                            
                            // Play sound for new alert
                            playAnnoyingSound();
                            setTimeout(() => stopAnnoyingSound(), 3000);
                            
                            setTimeout(() => {
                                if (toast) toast.remove();
                            }, 5000);
                            
                            // Refresh page to show modal after 2 seconds
                            setTimeout(() => location.reload(), 2000);
                        }
                    })
                    .catch(error => console.error('Error checking notifications:', error));
            }

            // Check every 10 seconds instead of 5 to reduce load
            setInterval(checkForNewNotifications, 10000);
        </script>

        <script>
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', function() {
                        navigator.serviceWorker.register('/sw.js').then(function(registration) {
                            console.log('Service Worker registered with scope:', registration.scope);
                            
                            // Check for updates
                            registration.addEventListener('updatefound', () => {
                                const newWorker = registration.installing;
                                console.log('New service worker found:', newWorker);
                                
                                newWorker.addEventListener('statechange', () => {
                                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        // New update available
                                        showUpdateNotification();
                                    }
                                });
                            });
                        }).catch(function(error) {
                            console.log('Service Worker registration failed:', error);
                        });
                    });
                }

                function showUpdateNotification() {
                    const toast = document.createElement('div');
                    toast.className = 'alert alert-info alert-dismissible fade show position-fixed bottom-0 end-0 m-3';
                    toast.style.zIndex = '9999';
                    toast.style.minWidth = '280px';
                    toast.innerHTML = `
                        <i class="bi bi-arrow-repeat me-2"></i>
                        <strong>Update Available!</strong>
                        <p class="mb-2 small">A new version is available. Refresh to update.</p>
                        <button type="button" class="btn btn-sm btn-primary" onclick="location.reload()">Refresh</button>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.body.appendChild(toast);
                }

                // Push notification subscription
                async function subscribeToPushNotifications() {
                    if (!('PushManager' in window)) {
                        console.log('Push notifications not supported');
                        return;
                    }
                    
                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array('{{ env('VAPID_PUBLIC_KEY', '') }}')
                        });
                        
                        // Send subscription to server
                        await fetch('/api/push-subscribe', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            },
                            body: JSON.stringify(subscription)
                        });
                        
                        console.log('Push notification subscription successful');
                    } catch (error) {
                        console.error('Push subscription error:', error);
                    }
                }

                function urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding)
                        .replace(/-/g, '+')
                        .replace(/_/g, '/');
                    
                    const rawData = window.atob(base64);
                    const outputArray = new Uint8Array(rawData.length);
                    
                    for (let i = 0; i < rawData.length; ++i) {
                        outputArray[i] = rawData.charCodeAt(i);
                    }
                    return outputArray;
                }

                // Cache API responses for offline access
                async function cacheApiResponse(url, data) {
                    const cache = await caches.open('ali-safi-dynamic-v1');
                    const response = new Response(JSON.stringify(data), {
                        headers: { 'Content-Type': 'application/json' }
                    });
                    await cache.put(url, response);
                }

                // Export for use in other scripts
                window.cacheApiResponse = cacheApiResponse;
                window.subscribeToPushNotifications = subscribeToPushNotifications;
            </script>
    </body>
</html>