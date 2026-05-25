// Ali-Safi Service Worker
const CACHE_NAME = 'ali-safi-v1';
const STATIC_CACHE_NAME = 'ali-safi-static-v1';
const DYNAMIC_CACHE_NAME = 'ali-safi-dynamic-v1';

// Assets to cache on install
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/offline',
  '/css/app.css',
  '/js/app.js',
  '/favicon.ico',
  '/sounds/alarm_sound.mp3'
];

// API endpoints to cache (GET only)
const API_ENDPOINTS = [
  '/api/locations/counties'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing Service Worker...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating Service Worker...');
  
  const cacheWhitelist = [STATIC_CACHE_NAME, DYNAMIC_CACHE_NAME];
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (!cacheWhitelist.includes(cacheName)) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch event - network first with cache fallback
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);
  
  if (request.method !== 'GET' || url.protocol === 'chrome-extension:') {
    return;
  }
  
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(handleApiRequest(request));
    return;
  }
  
  if (request.mode === 'navigate') {
    event.respondWith(handleNavigationRequest(request));
    return;
  }
  
  if (isStaticAsset(url.pathname)) {
    event.respondWith(handleStaticAsset(request));
    return;
  }
  
  event.respondWith(networkFirst(request));
});

async function handleApiRequest(request) {
  try {
    const response = await fetch(request.clone());
    if (response && response.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) return cachedResponse;
    return new Response(JSON.stringify({
      error: 'You are offline. Please check your internet connection.',
      offline: true
    }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}

async function handleNavigationRequest(request) {
  try {
    const response = await fetch(request.clone());
    if (response && response.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) return cachedResponse;
    return caches.match('/offline');
  }
}

async function handleStaticAsset(request) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) return cachedResponse;
  
  try {
    const response = await fetch(request.clone());
    if (response && response.status === 200) {
      const cache = await caches.open(STATIC_CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    if (request.destination === 'image') {
      return caches.match('/images/placeholder.png');
    }
    return new Response('Resource not available offline', {
      status: 404,
      statusText: 'Not Found'
    });
  }
}

async function networkFirst(request) {
  try {
    const response = await fetch(request.clone());
    if (response && response.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) return cachedResponse;
    return new Response('Network error', {
      status: 408,
      statusText: 'Request Timeout'
    });
  }
}

function isStaticAsset(pathname) {
  const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.ico', '.webp', '.mp3'];
  return staticExtensions.some(ext => pathname.endsWith(ext));
}

// Background Sync for offline orders
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-orders') {
    event.waitUntil(syncOrders());
  }
});

async function syncOrders() {
  const cache = await caches.open('pending-orders');
  const requests = await cache.keys();
  
  for (const request of requests) {
    try {
      const response = await fetch(request);
      if (response && response.status === 201) {
        await cache.delete(request);
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
          client.postMessage({
            type: 'ORDER_SYNCED',
            data: await response.json()
          });
        });
      }
    } catch (error) {
      console.error('Failed to sync order:', error);
    }
  }
}

// ANNOYING PUSH NOTIFICATION HANDLER
self.addEventListener('push', (event) => {
  let data = {};
  
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      data = {
        title: '🔴 ALI-SAFI URGENT ALERT 🔴',
        body: event.data.text(),
        icon: '/icons/icon-192x192.png'
      };
    }
  }
  
  // Aggressive vibration pattern for mobile
  const vibratePattern = data.vibrate || [500, 200, 500, 200, 1000, 200, 500, 200, 500, 200, 2000];
  
  const options = {
    body: data.body || '🔴 URGENT: Action required immediately! 🔴',
    icon: data.icon || '/icons/icon-512x512.png',
    badge: '/icons/badge-72x72.png',
    vibrate: vibratePattern,
    sound: '/sounds/alarm_sound.mp3',
    silent: false,
    requireInteraction: true, // User must interact with notification
    renotify: true, // Will notify even if already shown
    tag: data.tag || 'urgent-ali-safi',
    data: {
      url: data.url || '/',
      orderId: data.orderId,
      type: data.type,
      timestamp: Date.now()
    },
    actions: [
      {
        action: 'view',
        title: '🔥 VIEW NOW 🔥'
      },
      {
        action: 'snooze',
        title: '⏰ Remind me in 1 min'
      },
      {
        action: 'dismiss',
        title: '❌ Dismiss'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title || '🔴 ALI-SAFI URGENT ALERT 🔴', options)
  );
});

// Notification click handler with aggressive reminders
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  
  if (event.action === 'snooze') {
    // Send reminder after 1 minute
    setTimeout(() => {
      self.registration.showNotification(event.notification.title, {
        body: '⏰ REMINDER: Action still required! Please respond immediately!',
        icon: '/icons/icon-512x512.png',
        vibrate: [500, 500, 500],
        sound: '/sounds/alarm_sound.mp3',
        requireInteraction: true,
        data: event.notification.data
      });
    }, 60000);
  } else if (event.action === 'dismiss') {
    // Log dismissal but do nothing else
    console.log('Notification dismissed');
  } else {
    // Open the URL
    const urlToOpen = event.notification.data?.url || '/';
    
    event.waitUntil(
      self.clients.matchAll({
        type: 'window',
        includeUncontrolled: true
      }).then((clientList) => {
        for (const client of clientList) {
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }
        if (self.clients.openWindow) {
          return self.clients.openWindow(urlToOpen);
        }
      })
    );
  }
});

// Handle background sync for pending notifications
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'check-notifications') {
    event.waitUntil(checkPendingNotifications());
  }
});

async function checkPendingNotifications() {
  const cache = await caches.open('pending-notifications');
  const pending = await cache.keys();
  
  for (const request of pending) {
    try {
      const response = await fetch(request);
      if (response.ok) {
        await cache.delete(request);
      }
    } catch (error) {
      console.error('Failed to sync notification:', error);
    }
  }
}