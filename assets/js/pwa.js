(function () {
  const themeColor = '#2563eb';
  const pwaVersion = '20260731';
  const sessionKey = 'mybalai-pwa-prompt-session';
  let deferredPrompt = null;
  let modalShown = false;
  let installModal = null;

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

  function getScriptUrl() {
    if (document.currentScript && document.currentScript.src) {
      return document.currentScript.src;
    }

    const script = Array.from(document.scripts).find(function (item) {
      return /pwa\.js(?:\?|$)/.test(item.src);
    });

    return script ? script.src : window.location.href;
  }

  function getAppRootUrl() {
    return new URL('../../', getScriptUrl());
  }

  function resolveAppUrl(path) {
    return new URL(path, getAppRootUrl()).toString();
  }

  function isInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches ||
      window.navigator.standalone === true ||
      window.location.search.includes('pwa=installed');
  }

  function shouldShowInstallPrompt() {
    if (isInstalled()) return false;

    try {
      if (sessionStorage.getItem(sessionKey) === '1') {
        return false;
      }
    } catch (error) {
      console.warn('Install prompt storage unavailable', error);
    }

    return true;
  }

  function markInstallPromptSeen() {
    try {
      sessionStorage.setItem(sessionKey, '1');
    } catch (error) {
      console.warn('Could not save install prompt session state', error);
    }
  }

  function markInstallPromptDismissed() {
    try {
      sessionStorage.removeItem(sessionKey);
    } catch (error) {
      console.warn('Could not clear install prompt session state', error);
    }
  }

  function injectModalStyles() {
    if (document.getElementById('mybalai-pwa-styles')) return;

    const style = document.createElement('style');
    style.id = 'mybalai-pwa-styles';
    style.textContent = `
      #mybalai-pwa-modal {
        position: fixed;
        inset: 0;
        z-index: 2147483647;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.55);
      }
      #mybalai-pwa-modal.show {
        display: flex;
      }
      #mybalai-pwa-modal .pwa-card {
        width: min(100%, 480px);
        background: white;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 20px 55px rgba(0, 0, 0, 0.25);
        text-align: center;
      }
      #mybalai-pwa-modal .pwa-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 14px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #2563eb, #4f46e5);
        color: white;
        font-size: 32px;
      }
      #mybalai-pwa-modal h3 {
        margin: 0 0 10px;
        font-size: 22px;
        color: #0f172a;
      }
      #mybalai-pwa-modal p {
        margin: 0 0 18px;
        line-height: 1.6;
        color: #475569;
      }
      #mybalai-pwa-modal .pwa-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
      }
      #mybalai-pwa-modal .pwa-btn {
        border: 0;
        border-radius: 999px;
        padding: 10px 18px;
        font-weight: 700;
        cursor: pointer;
      }
      #mybalai-pwa-modal .pwa-btn-later {
        background: #fef3c7;
        color: #92400e;
      }
      #mybalai-pwa-modal .pwa-btn-install {
        background: #2563eb;
        color: white;
      }
      @media (max-width: 480px) {
        #mybalai-pwa-modal {
          padding: 12px;
        }
        #mybalai-pwa-modal .pwa-actions {
          flex-direction: column;
        }
      }
    `;
    document.head.appendChild(style);
  }

  function createInstallModal() {
    if (installModal) return installModal;

    injectModalStyles();

    installModal = document.createElement('div');
    installModal.id = 'mybalai-pwa-modal';
    installModal.innerHTML = `
      <div class="pwa-card">
        <div class="pwa-icon">⬇</div>
        <h3>Install MyBalai</h3>
        <p>Add it to your home screen for faster access and offline support.</p>
        <div class="pwa-actions">
          <button class="pwa-btn pwa-btn-later" id="mybalai-pwa-later">Maybe later</button>
          <button class="pwa-btn pwa-btn-install" id="mybalai-pwa-install">Install</button>
        </div>
      </div>
    `;

    document.body.appendChild(installModal);

    installModal.querySelector('#mybalai-pwa-later').addEventListener('click', function () {
      markInstallPromptDismissed();
      hideInstallModal();
    });

    installModal.querySelector('#mybalai-pwa-install').addEventListener('click', function () {
      installApp();
    });

    return installModal;
  }

  function showInstallModal() {
    if (!installModal) {
      createInstallModal();
    }

    installModal.classList.add('show');
  }

  function getInstallInstructions() {
    const userAgent = navigator.userAgent || '';

    if (/iPhone|iPad|iPod/i.test(userAgent)) {
      return 'On Safari, tap the Share button and choose Add to Home Screen.';
    }

    if (/Android/i.test(userAgent)) {
      return 'On Chrome or Edge, tap the menu (⋮) and choose Install app or Add to Home Screen.';
    }

    if (/Macintosh|Mac OS/i.test(userAgent)) {
      return 'On Safari, open the Share menu and choose Add to Dock or Add to Home Screen.';
    }

    return 'Use your browser menu and choose Install app or Add to Home Screen.';
  }

  function hideInstallModal() {
    if (installModal) {
      installModal.classList.remove('show');
    }
  }

  async function installApp() {
    if (deferredPrompt) {
      try {
        deferredPrompt.prompt();
        const choiceResult = await deferredPrompt.userChoice;

        if (choiceResult.outcome === 'accepted') {
          console.log('✓ User accepted install');
        } else {
          console.log('✗ User dismissed install');
        }

        deferredPrompt = null;
        hideInstallModal();
      } catch (error) {
        console.error('Install error:', error);
        hideInstallModal();
        alert('Unable to install right now. Please use your browser menu and choose Install app or Add to Home Screen.');
      }
      return;
    }

    hideInstallModal();
    alert('Install is not available in this browser yet. ' + getInstallInstructions());
  }

  function showInstallPrompt() {
    if (modalShown || !shouldShowInstallPrompt()) return;

    modalShown = true;
    markInstallPromptSeen();
    showInstallModal();
  }

  ensureMeta('theme-color', themeColor);
  ensureMeta('apple-mobile-web-app-capable', 'yes');
  ensureMeta('apple-mobile-web-app-status-bar-style', 'black-translucent');
  ensureMeta('apple-mobile-web-app-title', 'MyBalai');
  ensureMeta('mobile-web-app-capable', 'yes');
  ensureLink('manifest', resolveAppUrl('manifest.json?v=' + pwaVersion));
  ensureLink('apple-touch-icon', resolveAppUrl('assets/icons/appicon.png?v=' + pwaVersion));

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker
        .register(resolveAppUrl('service-worker.js?v=' + pwaVersion), { scope: getAppRootUrl().pathname })
        .then(function () {
          console.log('✓ Service Worker registered successfully');
        })
        .catch(function (error) {
          console.warn('✗ Service Worker registration failed:', error);
        });
    });
  }

  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPrompt = event;
    console.log('✓ Install prompt available');
    showInstallPrompt();
  });

  window.addEventListener('appinstalled', function () {
    console.log('✓ App installed');
    deferredPrompt = null;
    hideInstallModal();
    markInstallPromptDismissed();
  });

  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(showInstallPrompt, 800);
  });

  window.addEventListener('load', function () {
    setTimeout(showInstallPrompt, 800);
  });
})();
