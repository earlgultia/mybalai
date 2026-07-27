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
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);
  if (requestUrl.protocol !== 'http:' && requestUrl.protocol !== 'https:') {
    return;
  }
  if (requestUrl.origin !== self.location.origin) {
    return;
  }

  if (requestUrl.pathname === '/offline.html' || requestUrl.pathname === '/favicon.ico') {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
        return response;
      })
      .catch(() => caches.match(event.request).then((cached) => cached || caches.match('/offline.html')))
  );
});
