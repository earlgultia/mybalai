const CACHE_NAME = 'mybalai-pwa-v5';
const APP_SHELL = [
  'manifest.json',
  'offline.html',
  'assets/css/app.css',
  'assets/js/pwa.js',
  'assets/icons/appicon.png'
];

function getScopeUrl() {
  return new URL('./', self.registration.scope || self.location.href);
}

function getAppShellUrls() {
  const scopeUrl = getScopeUrl();
  return APP_SHELL.map((path) => new URL(path, scopeUrl).toString());
}

self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching app shell');
        return cache.addAll(getAppShellUrls()).catch((error) => {
          console.warn('Service Worker: Some cache items failed to add', error);
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

  if (method !== 'GET') {
    return;
  }

  if (!url.startsWith('http:') && !url.startsWith('https:')) {
    return;
  }

  const urlObj = new URL(url);
  const scopeUrl = getScopeUrl();

  if (urlObj.origin !== self.location.origin) {
    return;
  }

  // PHP pages and API responses must always reflect the current server state.
  // Caching them can preserve an old blank/error response after a deployment.
  if (request.mode === 'navigate' || urlObj.pathname.endsWith('.php')) {
    return;
  }

  if (urlObj.pathname === new URL('./offline.html', scopeUrl).pathname) {
    return;
  }

  event.respondWith(
    fetch(request)
      .then((response) => {
        if (!response || response.status !== 200 || response.type === 'error') {
          return response;
        }

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
        return caches.match(request)
          .then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }

            return caches.match(new URL('./offline.html', scopeUrl).toString());
          })
          .catch(() => {
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
