/**
 * EPIC Hub Service Worker - Performance Optimized
 * Advanced caching strategies for maximum performance
 */

const CACHE_NAME = 'epic-hub-v1.0.0';
const STATIC_CACHE = 'epic-static-v1.0.0';
const DYNAMIC_CACHE = 'epic-dynamic-v1.0.0';
const IMAGE_CACHE = 'epic-images-v1.0.0';

// Cache strategies
const CACHE_STRATEGIES = {
    CACHE_FIRST: 'cache-first',
    NETWORK_FIRST: 'network-first',
    STALE_WHILE_REVALIDATE: 'stale-while-revalidate',
    NETWORK_ONLY: 'network-only',
    CACHE_ONLY: 'cache-only'
};

// Static assets to cache immediately
const STATIC_ASSETS = [
    '/themes/modern/admin/admin.css',
    '/themes/modern/admin/admin.js',
    '/themes/modern/admin/components.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
    'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js',
    'https://unpkg.com/feather-icons'
];

// API endpoints to cache with different strategies
const API_CACHE_PATTERNS = {
    // Cache first (for relatively static data)
    cacheFirst: [
        /\/api\/admin\/user\/current/,
        /\/api\/admin\/settings/,
        /\/api\/admin\/menu/
    ],
    // Network first (for dynamic data)
    networkFirst: [
        /\/api\/admin\/notifications/,
        /\/api\/admin\/stats/,
        /\/api\/admin\/dashboard/
    ],
    // Stale while revalidate (for frequently accessed data)
    staleWhileRevalidate: [
        /\/api\/admin\/data/,
        /\/api\/admin\/table/
    ]
};

// Cache configuration
const CACHE_CONFIG = {
    maxEntries: {
        static: 50,
        dynamic: 100,
        api: 200
    },
    maxAge: {
        static: 30 * 24 * 60 * 60 * 1000, // 30 days
        dynamic: 7 * 24 * 60 * 60 * 1000,  // 7 days
        api: 5 * 60 * 1000                 // 5 minutes
    }
};

/**
 * Service Worker Installation
 */
self.addEventListener('install', event => {
    console.log('Service Worker installing...');
    
    event.waitUntil(
        Promise.all([
            // Cache static assets
            caches.open(STATIC_CACHE_NAME).then(cache => {
                console.log('Caching static assets...');
                return cache.addAll(STATIC_ASSETS.filter(url => {
                    try {
                        new URL(url, self.location.origin);
                        return true;
                    } catch {
                        console.warn(`Invalid URL skipped: ${url}`);
                        return false;
                    }
                }));
            }),
            
            // Skip waiting to activate immediately
            self.skipWaiting()
        ])
    );
});

/**
 * Service Worker Activation
 */
self.addEventListener('activate', event => {
    console.log('Service Worker activating...');
    
    event.waitUntil(
        Promise.all([
            // Clean up old caches
            cleanupOldCaches(),
            
            // Claim all clients
            self.clients.claim()
        ])
    );
});

/**
 * Fetch Event Handler
 */
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome-extension and other non-http requests
    if (!url.protocol.startsWith('http')) {
        return;
    }
    
    // Handle different types of requests
    if (isStaticAsset(request)) {
        event.respondWith(handleStaticAsset(request));
    } else if (isAPIRequest(request)) {
        event.respondWith(handleAPIRequest(request));
    } else if (isNavigationRequest(request)) {
        event.respondWith(handleNavigationRequest(request));
    } else {
        event.respondWith(handleDynamicRequest(request));
    }
});

/**
 * Message Handler for cache management
 */
self.addEventListener('message', event => {
    const { type, payload } = event.data;
    
    switch (type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
            
        case 'CLEAR_CACHE':
            clearAllCaches().then(() => {
                event.ports[0].postMessage({ success: true });
            });
            break;
            
        case 'CACHE_URLS':
            cacheUrls(payload.urls).then(() => {
                event.ports[0].postMessage({ success: true });
            });
            break;
            
        case 'GET_CACHE_INFO':
            getCacheInfo().then(info => {
                event.ports[0].postMessage(info);
            });
            break;
    }
});

/**
 * Check if request is for static asset
 */
function isStaticAsset(request) {
    const url = new URL(request.url);
    const pathname = url.pathname;
    
    return (
        pathname.endsWith('.css') ||
        pathname.endsWith('.js') ||
        pathname.endsWith('.woff2') ||
        pathname.endsWith('.woff') ||
        pathname.includes('/themes/modern/admin/') ||
        url.hostname === 'fonts.googleapis.com' ||
        url.hostname === 'unpkg.com'
    );
}

/**
 * Check if request is for API
 */
function isAPIRequest(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith('/api/');
}

/**
 * Check if request is navigation request
 */
function isNavigationRequest(request) {
    return request.mode === 'navigate';
}

/**
 * Handle static asset requests
 */
async function handleStaticAsset(request) {
    try {
        // Try cache first
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Fetch from network and cache
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE_NAME);
            cache.put(request, networkResponse.clone());
            
            // Clean up cache if needed
            await cleanupCache(STATIC_CACHE_NAME, CACHE_CONFIG.maxEntries.static);
        }
        
        return networkResponse;
    } catch (error) {
        console.error('Static asset fetch failed:', error);
        
        // Return cached version if available
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline fallback
        return new Response('Asset not available offline', {
            status: 503,
            statusText: 'Service Unavailable'
        });
    }
}

/**
 * Handle API requests with different strategies
 */
async function handleAPIRequest(request) {
    const url = new URL(request.url);
    const pathname = url.pathname;
    
    // Determine cache strategy
    let strategy = 'networkFirst'; // default
    
    for (const [strategyName, patterns] of Object.entries(API_CACHE_PATTERNS)) {
        if (patterns.some(pattern => pattern.test(pathname))) {
            strategy = strategyName;
            break;
        }
    }
    
    switch (strategy) {
        case 'cacheFirst':
            return handleCacheFirst(request);
        case 'networkFirst':
            return handleNetworkFirst(request);
        case 'staleWhileRevalidate':
            return handleStaleWhileRevalidate(request);
        default:
            return handleNetworkFirst(request);
    }
}

/**
 * Cache First Strategy
 */
async function handleCacheFirst(request) {
    try {
        const cachedResponse = await caches.match(request);
        if (cachedResponse && !isExpired(cachedResponse)) {
            return cachedResponse;
        }
        
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(API_CACHE_NAME);
            const responseToCache = networkResponse.clone();
            
            // Add timestamp header
            const headers = new Headers(responseToCache.headers);
            headers.set('sw-cached-at', Date.now().toString());
            
            const modifiedResponse = new Response(responseToCache.body, {
                status: responseToCache.status,
                statusText: responseToCache.statusText,
                headers: headers
            });
            
            cache.put(request, modifiedResponse);
            await cleanupCache(API_CACHE_NAME, CACHE_CONFIG.maxEntries.api);
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        throw error;
    }
}

/**
 * Network First Strategy
 */
async function handleNetworkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(API_CACHE_NAME);
            const responseToCache = networkResponse.clone();
            
            // Add timestamp header
            const headers = new Headers(responseToCache.headers);
            headers.set('sw-cached-at', Date.now().toString());
            
            const modifiedResponse = new Response(responseToCache.body, {
                status: responseToCache.status,
                statusText: responseToCache.statusText,
                headers: headers
            });
            
            cache.put(request, modifiedResponse);
            await cleanupCache(API_CACHE_NAME, CACHE_CONFIG.maxEntries.api);
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline response
        return new Response(JSON.stringify({
            error: 'Network unavailable',
            message: 'This data is not available offline'
        }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

/**
 * Stale While Revalidate Strategy
 */
async function handleStaleWhileRevalidate(request) {
    const cache = await caches.open(API_CACHE_NAME);
    const cachedResponse = await cache.match(request);
    
    // Start network request (don't await)
    const networkPromise = fetch(request).then(networkResponse => {
        if (networkResponse.ok) {
            const responseToCache = networkResponse.clone();
            
            // Add timestamp header
            const headers = new Headers(responseToCache.headers);
            headers.set('sw-cached-at', Date.now().toString());
            
            const modifiedResponse = new Response(responseToCache.body, {
                status: responseToCache.status,
                statusText: responseToCache.statusText,
                headers: headers
            });
            
            cache.put(request, modifiedResponse);
            cleanupCache(API_CACHE_NAME, CACHE_CONFIG.maxEntries.api);
        }
        return networkResponse;
    }).catch(error => {
        console.warn('Background fetch failed:', error);
    });
    
    // Return cached response immediately if available
    if (cachedResponse) {
        return cachedResponse;
    }
    
    // Wait for network response if no cache
    try {
        return await networkPromise;
    } catch (error) {
        return new Response(JSON.stringify({
            error: 'Network unavailable',
            message: 'This data is not available offline'
        }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

/**
 * Handle navigation requests
 */
async function handleNavigationRequest(request) {
    try {
        return await fetch(request);
    } catch (error) {
        // Return cached admin page or offline page
        const cachedResponse = await caches.match('/admin');
        if (cachedResponse) {
            return cachedResponse;
        }
        
        return new Response(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Offline - EPIC Hub Admin</title>
                <style>
                    body { font-family: Inter, sans-serif; text-align: center; padding: 50px; }
                    .offline-message { max-width: 400px; margin: 0 auto; }
                </style>
            </head>
            <body>
                <div class="offline-message">
                    <h1>You're Offline</h1>
                    <p>Please check your internet connection and try again.</p>
                    <button onclick="window.location.reload()">Retry</button>
                </div>
            </body>
            </html>
        `, {
            headers: { 'Content-Type': 'text/html' }
        });
    }
}

/**
 * Handle dynamic requests
 */
async function handleDynamicRequest(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE_NAME);
            cache.put(request, networkResponse.clone());
            await cleanupCache(DYNAMIC_CACHE_NAME, CACHE_CONFIG.maxEntries.dynamic);
        }
        
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        throw error;
    }
}

/**
 * Check if cached response is expired
 */
function isExpired(response) {
    const cachedAt = response.headers.get('sw-cached-at');
    if (!cachedAt) return false;
    
    const age = Date.now() - parseInt(cachedAt);
    return age > CACHE_CONFIG.maxAge.api;
}

/**
 * Clean up old caches
 */
async function cleanupOldCaches() {
    const cacheNames = await caches.keys();
    const currentCaches = [CACHE_NAME, STATIC_CACHE_NAME, DYNAMIC_CACHE_NAME, API_CACHE_NAME];
    
    const deletePromises = cacheNames
        .filter(cacheName => !currentCaches.includes(cacheName))
        .map(cacheName => {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
        });
    
    await Promise.all(deletePromises);
}

/**
 * Clean up cache entries to maintain size limits
 */
async function cleanupCache(cacheName, maxEntries) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    
    if (keys.length > maxEntries) {
        const entriesToDelete = keys.length - maxEntries;
        const keysToDelete = keys.slice(0, entriesToDelete);
        
        await Promise.all(
            keysToDelete.map(key => cache.delete(key))
        );
        
        console.log(`Cleaned up ${entriesToDelete} entries from ${cacheName}`);
    }
}

/**
 * Clear all caches
 */
async function clearAllCaches() {
    const cacheNames = await caches.keys();
    await Promise.all(
        cacheNames.map(cacheName => caches.delete(cacheName))
    );
    console.log('All caches cleared');
}

/**
 * Cache specific URLs
 */
async function cacheUrls(urls) {
    const cache = await caches.open(DYNAMIC_CACHE_NAME);
    await cache.addAll(urls);
    console.log(`Cached ${urls.length} URLs`);
}

/**
 * Get cache information
 */
async function getCacheInfo() {
    const cacheNames = await caches.keys();
    const info = {};
    
    for (const cacheName of cacheNames) {
        const cache = await caches.open(cacheName);
        const keys = await cache.keys();
        info[cacheName] = {
            entries: keys.length,
            urls: keys.map(key => key.url)
        };
    }
    
    return info;
}

/**
 * Background sync for failed requests
 */
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

/**
 * Perform background sync
 */
async function doBackgroundSync() {
    // Implementation for retrying failed requests
    console.log('Performing background sync...');
    
    // This would typically involve:
    // 1. Retrieving failed requests from IndexedDB
    // 2. Retrying them
    // 3. Cleaning up successful requests
}

/**
 * Push notification handler
 */
self.addEventListener('push', event => {
    if (!event.data) return;
    
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/themes/modern/admin/icons/notification.png',
        badge: '/themes/modern/admin/icons/badge.png',
        data: data.data,
        actions: data.actions || []
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

/**
 * Notification click handler
 */
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action) {
        // Handle action clicks
        console.log('Notification action clicked:', event.action);
    } else {
        // Handle notification click
        event.waitUntil(
            clients.openWindow(event.notification.data?.url || '/admin')
        );
    }
});

console.log('EPIC Hub Admin Service Worker loaded');