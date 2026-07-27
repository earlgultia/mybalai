# MyBalai Progressive Web App (PWA) Setup Guide

## Overview

MyBalai is now a fully-featured Progressive Web App (PWA) that can be **installed on phones and tablets** like a native app and **works offline** with cached content.

---

## ✨ PWA Features Enabled

### 1. **Installable App**
- Install prompt appears in mobile browsers (Chrome, Edge, Samsung, etc.)
- Add to home screen with custom icon and branding
- App runs in standalone mode (no browser UI)
- Custom splash screen and theme color

### 2. **Offline Support**
- Core app shell cached on first load
- Offline fallback page when network is unavailable
- Graceful degradation for authenticated content

### 3. **Mobile Optimizations**
- Responsive viewport settings for all devices
- iOS Web App metadata for full-screen support
- Safe area support for notched devices
- Theme color aligned with app branding (#2563eb)

### 4. **Smart Caching**
- App shell strategy for fast loads
- Network-first for dynamic content (API calls)
- Cache fallback when offline
- Automatic cache versioning (v1 in cache name)

---

## 📁 New Files Added

```
mybalai/
├── manifest.json                 ← PWA metadata & app configuration
├── service-worker.js             ← Offline & caching logic
├── offline.html                  ← Offline fallback page
├── assets/
│   ├── js/
│   │   └── pwa.js               ← Service worker registration & install button
│   └── icons/
│       └── appicon.png          ← App icon (PNG - used for all sizes)
```

---

## 🔧 Configuration in PHP Pages

All main entry points have been updated with PWA metadata:
- `index.php` (public homepage)
- `login.php`
- `register.php`
- `admin/_admin_common.php` (admin dashboard header)
- `resident/_resident_common.php` (resident portal header)

**Changes made:**
```html
<!-- Added PWA metadata tags -->
<meta name="theme-color" content="#2563eb">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="MyBalai">
<meta name="mobile-web-app-capable" content="yes">

<!-- Added PWA links -->
<link rel="manifest" href="manifest.json">
<link rel="apple-touch-icon" href="assets/icons/appicon.png">

<!-- Added PWA script -->
<script src="assets/js/pwa.js"></script>
```

---

## 📱 How Users Install the App

### **Android (Chrome/Edge)**
1. Open MyBalai in Chrome or Edge
2. Tap the menu (⋮)
3. Tap "Install app" or "Add to home screen"
4. Tap "Install"
5. App appears on home screen as standalone app

### **iOS (Safari)**
1. Open MyBalai in Safari
2. Tap Share button
3. Select "Add to Home Screen"
4. Name it "MyBalai"
5. Tap "Add"
6. App appears on home screen

### **Desktop (PWA capable browsers)**
- Chrome, Edge, and Opera show an install button (🔵➕ icon in address bar)
- Click to install, runs in its own window

---

## 🚀 Deployment Requirements

### **HTTPS Required**
Service workers only work over HTTPS (or localhost for testing). Ensure:
- Your hosting uses SSL/TLS certificate
- All assets are served over `https://`
- No mixed HTTP/HTTPS content

### **Server Configuration**
No special server setup needed if file permissions are correct:
```bash
# Ensure files are readable
chmod 644 manifest.json service-worker.js offline.html
chmod 755 assets/js/ assets/icons/
```

### **Path Configuration**
The manifest and service worker use **root-relative paths** (`/manifest.json`, `/service-worker.js`).

**If deployed in a subdirectory** (e.g., `example.com/mybalai/`), update:

**manifest.json:**
```json
"start_url": "/mybalai/index.php",
"scope": "/mybalai/"
```

**service-worker.js:**
```javascript
const APP_SHELL = [
  '/mybalai/',
  '/mybalai/index.php',
  '/mybalai/login.php',
  '/mybalai/offline.html',
  '/mybalai/assets/css/app.css',
  '/mybalai/assets/js/pwa.js'
];
```

**assets/js/pwa.js:**
```javascript
const manifestUrl = '/mybalai/manifest.json';
ensureLink('manifest', manifestUrl);
navigator.serviceWorker.register('/mybalai/service-worker.js');
```

---

## 🧪 Testing the PWA

### **Test Service Worker Registration**
1. Open browser DevTools (F12)
2. Go to **Application** tab
3. Click **Service Workers** on the left
4. You should see `/service-worker.js` listed as "activated and running"

### **Test Offline Mode**
1. In DevTools, go to **Network** tab
2. Check **Offline** checkbox
3. Reload the page
4. App shell should load; dynamic content shows offline page

### **Test Install Prompt**
1. On mobile, the install button appears after a few seconds
2. Or manually add to home screen (iOS) or use browser menu (Android)

### **Test Caching**
1. DevTools → **Application** → **Cache Storage**
2. Expand `mybalai-pwa-v1`
3. See cached files and resources

---

## 📊 Manifest.json Breakdown

```json
{
  "name": "MyBalai",                          // Full app name
  "short_name": "MyBalai",                   // Shown under icon
  "description": "Smart barangay...",        // App description
  "start_url": "/index.php",                 // Page to load when app opens
  "scope": "/",                              // Pages included in PWA scope
  "display": "standalone",                   // Hide browser UI
  "background_color": "#f4f7fb",             // Splash screen background
  "theme_color": "#2563eb",                  // Status bar & address bar color
  "orientation": "portrait",                 // Lock to portrait on mobile
  "icons": [
    {
      "src": "/assets/icons/appicon.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/assets/icons/appicon.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any"
    }
  ]
}
```

---

## 🔄 Service Worker Lifecycle

The service worker (`service-worker.js`) handles:

### **Installation**
- Caches app shell on first load
- Pre-caches critical pages and assets

### **Activation**
- Cleans up old cache versions
- Claims all clients

### **Fetch Events**
- **GET requests**: Network first, fallback to cache
- **Other methods**: Pass through (no caching)
- **Cross-origin requests**: Ignored
- **Offline**: Returns cached response or offline.html

---

## 🛠️ Customization

### **Change App Colors**
1. Edit `manifest.json`:
   ```json
   "theme_color": "#1e40af",
   "background_color": "#ffffff"
   ```

2. Update `assets/js/pwa.js`:
   ```javascript
   const themeColor = '#1e40af';
   ```

### **Add More Cached Files**
Edit `service-worker.js` `APP_SHELL` array:
```javascript
const APP_SHELL = [
  '/',
  '/index.php',
  '/assets/css/custom.css',
  '/assets/images/logo.svg'
];
```

### **Change Icon**
1. Replace `appicon.png` in `assets/icons/`
2. Use a PNG image (minimum 512x512 recommended)
3. The same image is used for all icon sizes (192x192 and 512x512)

---

## 🐛 Troubleshooting

### **Install button doesn't appear**
- ✅ App must be served over HTTPS
- ✅ Service worker must register successfully
- ✅ Check DevTools for service worker errors
- ✅ Wait ~10 seconds after page load

### **Offline page shows blank**
- ✅ Check browser DevTools → Network → Offline
- ✅ Verify `offline.html` exists and is in cache
- ✅ Check service worker for errors

### **Service worker won't register**
- ✅ Verify HTTPS is enabled
- ✅ Check DevTools → Application → Service Workers for errors
- ✅ Ensure `service-worker.js` is at root level
- ✅ No JavaScript errors in console

### **Cached content is stale**
- ✅ Update cache version in `service-worker.js`:
  ```javascript
  const CACHE_NAME = 'mybalai-pwa-v2';
  ```
- ✅ Deploy and reload app

---

## 📦 Build & Release Checklist

- [ ] HTTPS certificate installed and active
- [ ] `manifest.json` validated (test with `node -m json.tool manifest.json`)
- [ ] Service worker syntax validated (`node --check service-worker.js`)
- [ ] Icons placed in `assets/icons/appicon.png` (minimum 512x512)
- [ ] All PHP pages include PWA metadata tags
- [ ] All PHP pages include `pwa.js` script
- [ ] Cache paths updated if deployed in subdirectory
- [ ] Tested on multiple browsers (Chrome, Safari, Edge)
- [ ] Tested offline functionality
- [ ] Tested install/add to home screen flow

---

## 📚 Resources

- [Web App Manifest Spec](https://www.w3.org/TR/appmanifest/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [PWA Checklist](https://web.dev/pwa-checklist/)
- [MDN: Progressive Web Apps](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)

---

## 🎉 Next Steps

1. **Deploy to HTTPS hosting** – PWA only works over secure connections
2. **Test on mobile devices** – Install prompt appears after app is cached
3. **Monitor analytics** – Track installations in Google Analytics
4. **Update cache version** – When deploying new features, increment `CACHE_NAME`
5. **Collect user feedback** – Improve based on real user experiences

---

**MyBalai PWA is now production-ready!** 🚀
