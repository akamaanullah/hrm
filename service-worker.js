    // Service Worker for HRM Portal PWA
    const CACHE_NAME = 'hrm-portal-v1.0.0';
    const RUNTIME_CACHE = 'hrm-runtime-v1.0.0';

    // Get base path from service worker location (service worker is in root directory)
    const getBasePath = () => {
        // Get service worker script location
        const swLocation = self.location.pathname;
        // Remove 'service-worker.js' from path to get base path
        const basePath = swLocation.substring(0, swLocation.lastIndexOf('/') + 1);
        return basePath;
    };

    const BASE_PATH = getBasePath();

    // Assets to cache on install (relative paths from root directory)
    const STATIC_ASSETS = [
        BASE_PATH,                                    // Root index
        BASE_PATH + 'login.php',                     // Login page
        BASE_PATH + 'assets/css/style.css',          // CSS file
        BASE_PATH + 'assets/images/LOGO.png',        // Logo image
        BASE_PATH + 'manifest.json',                 // Manifest file
        BASE_PATH + 'offline.html'                   // Offline page
    ];

    // Helper function to check if URL is cacheable
    function isCacheable(url) {
    try {
        const urlObj = new URL(url, self.location.origin);
        // Don't cache chrome-extension, moz-extension, or other extension schemes
        if (urlObj.protocol === 'chrome-extension:' || 
            urlObj.protocol === 'moz-extension:' ||
            urlObj.protocol === 'safari-extension:') {
        return false;
        }
        // Only cache http/https
        return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
    } catch (e) {
        return false;
    }
    }

    // Install event - cache static assets with error handling
    self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
        .then((cache) => {
            console.log('[Service Worker] Caching static assets');
            // Cache assets individually to handle missing files gracefully
            const cachePromises = STATIC_ASSETS
            .filter(url => {
                // Only cache local assets and check if cacheable
                return url.startsWith(BASE_PATH) && isCacheable(url);
            })
            .map(url => {
                return cache.add(url).catch(error => {
                console.warn(`[Service Worker] Failed to cache ${url}:`, error.message);
                // Continue even if some assets fail to cache
                return null;
                });
            });
            
            return Promise.all(cachePromises);
        })
        .then(() => {
            console.log('[Service Worker] Installed successfully');
            return self.skipWaiting(); // Activate immediately
        })
        .catch((error) => {
            console.error('[Service Worker] Install failed:', error);
            // Still activate even if caching fails
            return self.skipWaiting();
        })
    );
    });

    // Activate event - clean up old caches
    self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
        return Promise.all(
            cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
                console.log('[Service Worker] Deleting old cache:', cacheName);
                return caches.delete(cacheName);
            }
            })
        );
        })
        .then(() => {
        console.log('[Service Worker] Activated');
        return self.clients.claim(); // Take control of all pages
        })
    );
    });

    // Fetch event - serve from cache, fallback to network
    self.addEventListener('fetch', (event) => {
    const { request } = event;
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Check if URL is cacheable
    if (!isCacheable(request.url)) {
        return; // Skip chrome-extension and other unsupported schemes
    }
    
    const url = new URL(request.url);

    // Strategy: Cache First for static assets, Network First for API calls
    if (url.pathname.includes('/include/api/') || url.pathname.includes('.php')) {
        // Network First for API and PHP files
        event.respondWith(
        fetch(request)
            .then((response) => {
            // Clone the response
            const responseToCache = response.clone();
            
            // Cache successful responses (only if cacheable)
            if (response.status === 200 && isCacheable(request.url)) {
                caches.open(RUNTIME_CACHE).then((cache) => {
                cache.put(request, responseToCache).catch(err => {
                    console.warn('[Service Worker] Failed to cache API response:', err);
                });
                });
            }
            
            return response;
            })
            .catch(() => {
            // Network failed, try cache
            return caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                return cachedResponse;
                }
                
                // If no cache, return offline page for navigation requests
                if (request.mode === 'navigate') {
                return caches.match(BASE_PATH + 'offline.html').then(cached => {
                    if (cached) return cached;
                    // Fallback if offline.html not in cache
                    return new Response('You are offline. Please check your internet connection.', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({
                        'Content-Type': 'text/html'
                    })
                    });
                }).catch(() => {
                    return new Response('You are offline. Please check your internet connection.', {
                    status: 503,
                    statusText: 'Service Unavailable',
                    headers: new Headers({
                        'Content-Type': 'text/html'
                    })
                    });
                });
                }
                
                return new Response('Offline', { status: 503 });
            });
            })
        );
    } else {
        // Cache First for static assets
        event.respondWith(
        caches.match(request)
            .then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }
            
            return fetch(request)
                .then((response) => {
                // Don't cache if not a valid response
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                
                // Only cache if URL is cacheable
                if (isCacheable(request.url)) {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                    cache.put(request, responseToCache).catch(err => {
                        console.warn('[Service Worker] Failed to cache response:', err);
                    });
                    });
                }
                
                return response;
                })
                .catch(() => {
                // If fetch fails and it's a navigation request, show offline page
                if (request.mode === 'navigate') {
                    return caches.match(BASE_PATH + 'offline.html').then(cached => {
                        if (cached) return cached;
                        // Fallback if offline.html not in cache
                        return new Response('You are offline. Please check your internet connection.', {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: new Headers({
                            'Content-Type': 'text/html'
                        })
                        });
                    }).catch(() => {
                    return new Response('You are offline. Please check your internet connection.', {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: new Headers({
                        'Content-Type': 'text/html'
                        })
                    });
                    });
                }
                
                return new Response('Offline', { status: 503 });
                });
            })
        );
    }
    });

    // Background sync for offline actions (if needed in future)
    self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);
    // Can be used to sync attendance, leave requests when back online
    });

    // Push notifications (if needed in future)
    self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push notification received');
    const options = {
        body: event.data ? event.data.text() : 'New notification from HRM Portal',
        icon: BASE_PATH + 'assets/images/LOGO.png',
        badge: BASE_PATH + 'assets/images/LOGO.png',
        vibrate: [200, 100, 200],
        tag: 'hrm-notification',
        requireInteraction: false
    };
    
    event.waitUntil(
        self.registration.showNotification('HRM Portal', options)
    );
    });

    // Notification click handler
    self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow(BASE_PATH)
    );
    });

