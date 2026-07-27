const CACHE_NAME = 'mybalai-pwa-v1';
const APP_SHELL = [
  '/',
  '/index.php',
  '/login.php',
  '/register.php',
  '/manifest.json',
  '/offline.html',
  '/assets/css/app.css',
  '/assets/js/pwa.js',
  '/assets/icons/appicon.png'
];

self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching app shell');
        return cache.addAll(APP_SHELL).catch((error) => {
          console.warn('Service Worker: Some cache items failed to add', error);
          // Continue even if some items fail
          return Promise.resolve();
        });
      })
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activating...');
  event.waitUntil(
    caches.keys()
      .then((keys) => {
        return Promise.all(
          keys
            .filter((key) => key !== CACHE_NAME)
            .map((key) => {
              console.log('Service Worker: Deleting old cache', key);
              return caches.delete(key);
            })
        );
      })
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const { method, url } = request;

  // Skip non-GET requests
  if (method !== 'GET') {
    return;
  }

  // Skip protocol check for local development
  if (!url.startsWith('http:') && !url.startsWith('https:')) {
    return;
  }

  const urlObj = new URL(url);

  // Don't cache cross-origin requests
  if (urlObj.origin !== self.location.origin) {
    return;
  }

  // Don't cache offline.html fetch
  if (urlObj.pathname === '/offline.html') {
    return;
  }

  // Network first strategy with cache fallback
  event.respondWith(
    fetch(request)
      .then((response) => {
        // Don't cache error responses
        if (!response || response.status !== 200 || response.type === 'error') {
          return response;
        }

        // Clone the response before caching
        const responseToCache = response.clone();
        caches.open(CACHE_NAME)
          .then((cache) => {
            cache.put(request, responseToCache).catch((error) => {
              console.warn('Service Worker: Failed to cache', url, error);
            });
          });

        return response;
      })
      .catch(() => {
        // Network request failed, try cache
        return caches.match(request)
          .then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }

            // No cache, show offline page
            return caches.match('/offline.html');
          })
          .catch(() => {
            // Fallback if offline.html is not cached
            return new Response('You are offline', {
              status: 503,
              statusText: 'Service Unavailable',
              headers: new Headers({
                'Content-Type': 'text/plain'
              })
            });
          });
      })
  );
});
