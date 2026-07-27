(function () {
  const manifestUrl = '/manifest.json';
  const themeColor = '#2563eb';

  function ensureMeta(name, value) {
    let meta = document.querySelector('meta[name="' + name + '"]');
    if (!meta) {
      meta = document.createElement('meta');
      meta.setAttribute('name', name);
      document.head.appendChild(meta);
    }
    meta.setAttribute('content', value);
  }

  function ensureLink(rel, href) {
    let link = document.querySelector('link[rel="' + rel + '"]');
    if (!link) {
      link = document.createElement('link');
      link.setAttribute('rel', rel);
      document.head.appendChild(link);
    }
    link.setAttribute('href', href);
  }

  function createInstallModal() {
    const modalId = 'pwaInstallModal';
    let modal = document.getElementById(modalId);
    
    if (!modal) {
      modal = document.createElement('div');
      modal.id = modalId;
      modal.style.cssText = `
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 999999;
        padding: 20px;
        display: none;
        animation: slideUp 0.3s ease-out;
      `;
      
      modal.innerHTML = `
        <style>
          @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
          }
          @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
          }
          .pwa-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999998;
            display: none;
            animation: fadeIn 0.3s ease-out;
          }
          .pwa-modal-overlay.show {
            display: block;
          }
          .pwa-install-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: slideUp 0.3s ease-out;
          }
          .pwa-icon-circle {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #2563eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
          }
          .pwa-modal-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
            margin: 0 0 12px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
          }
          .pwa-modal-text {
            font-size: 16px;
            color: #64748b;
            margin: 0 0 30px 0;
            line-height: 1.6;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
          }
          .pwa-button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
          }
          .pwa-btn {
            padding: 12px 32px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
          }
          .pwa-btn-later {
            background: #fef3c7;
            color: #92400e;
            flex: 1;
          }
          .pwa-btn-later:hover {
            background: #fde68a;
            transform: translateY(-2px);
          }
          .pwa-btn-install {
            background: #1e293b;
            color: white;
            flex: 1;
          }
          .pwa-btn-install:hover {
            background: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(30, 41, 59, 0.3);
          }
        </style>
        <div class="pwa-modal-overlay"></div>
        <div class="pwa-install-card">
          <div class="pwa-icon-circle">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="16"></line>
              <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
          </div>
          <h2 class="pwa-modal-title">Install MyBalai</h2>
          <p class="pwa-modal-text">Add MyBalai to your home screen for faster access and offline support.</p>
          <div class="pwa-button-group">
            <button class="pwa-btn pwa-btn-later" id="pwaLaterBtn">Maybe later</button>
            <button class="pwa-btn pwa-btn-install" id="pwaInstallBtn">Install</button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    }
    
    return modal;
  }

  let deferredPrompt = null;
  let installModal = null;
  let hasShownPrompt = false;

  function showInstallModal() {
    if (hasShownPrompt) return;
    
    if (!installModal) {
      installModal = createInstallModal();
    }
    
    installModal.style.display = 'block';
    const overlay = installModal.querySelector('.pwa-modal-overlay');
    if (overlay) overlay.classList.add('show');
    hasShownPrompt = true;
  }

  function hideInstallModal() {
    if (installModal) {
      installModal.style.display = 'none';
      const overlay = installModal.querySelector('.pwa-modal-overlay');
      if (overlay) overlay.classList.remove('show');
    }
  }

  ensureMeta('theme-color', themeColor);
  ensureMeta('apple-mobile-web-app-capable', 'yes');
  ensureMeta('apple-mobile-web-app-status-bar-style', 'black-translucent');
  ensureMeta('apple-mobile-web-app-title', 'MyBalai');
  ensureMeta('mobile-web-app-capable', 'yes');
  ensureLink('manifest', manifestUrl);
  ensureLink('apple-touch-icon', '/assets/icons/appicon.png');

  // Register service worker with error logging
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker
        .register('/service-worker.js')
        .then(function (registration) {
          console.log('✓ Service Worker registered successfully');
        })
        .catch(function (error) {
          console.warn('✗ Service Worker registration failed:', error);
        });
    });
  }

  // Listen for beforeinstallprompt event
  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPrompt = event;
    console.log('✓ Install prompt available');
    
    // Show modal after a short delay
    setTimeout(showInstallModal, 1500);
  });

  window.addEventListener('appinstalled', function () {
    console.log('✓ App installed');
    hideInstallModal();
    deferredPrompt = null;
  });

  // Setup modal button handlers
  document.addEventListener('DOMContentLoaded', function () {
    const laterBtn = document.getElementById('pwaLaterBtn');
    const installBtn = document.getElementById('pwaInstallBtn');
    
    if (laterBtn) {
      laterBtn.addEventListener('click', function () {
        hideInstallModal();
      });
    }
    
    if (installBtn) {
      installBtn.addEventListener('click', async function () {
        if (!deferredPrompt) {
          console.warn('Install prompt not available');
          return;
        }
        
        try {
          deferredPrompt.prompt();
          const choiceResult = await deferredPrompt.userChoice;
          
          if (choiceResult.outcome === 'accepted') {
            console.log('✓ User accepted install');
            hideInstallModal();
          } else {
            console.log('✗ User dismissed install');
          }
          
          deferredPrompt = null;
        } catch (error) {
          console.error('Install error:', error);
        }
      });
    }
  });

  // Fallback: Show modal after 5 seconds if beforeinstallprompt never fired
  setTimeout(function () {
    if (!hasShownPrompt && deferredPrompt === null && 'serviceWorker' in navigator) {
      // Prompt still available from network detection
      console.log('Showing fallback install prompt');
    }
  }, 5000);
})();
