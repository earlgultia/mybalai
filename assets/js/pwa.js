(function () {
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
      const currentSession = sessionStorage.getItem('mybalai-pwa-prompt-session');
      if (currentSession === '1') {
        return false;
      }
    } catch (error) {
      console.warn('Install prompt session storage unavailable', error);
    }

    return true;
  }

  function markInstallPromptSeen() {
    try {
      sessionStorage.setItem('mybalai-pwa-prompt-session', '1');
    } catch (error) {
      console.warn('Could not save install prompt session state', error);
    }
  }

  function markInstallPromptDismissed() {
    try {
      sessionStorage.removeItem('mybalai-pwa-prompt-session');
    } catch (error) {
      console.warn('Could not clear install prompt session state', error);
    }
  }

  function injectSweetAlertStyles() {
    if (document.getElementById('pwa-swal-styles')) return;

    const style = document.createElement('style');
    style.id = 'pwa-swal-styles';
    style.textContent = `
      .pwa-swal-popup {
        border-radius: 20px;
      }
      .pwa-swal-confirm,
      .pwa-swal-cancel {
        border-radius: 999px;
        padding: 0.7rem 1.2rem;
        font-weight: 600;
      }
    `;
    document.head.appendChild(style);
  }

  function loadSweetAlert() {
    if (window.Swal) {
      return Promise.resolve();
    }

    return new Promise(function (resolve) {
      const existingScript = document.querySelector('script[src*="sweetalert2"]');
      if (existingScript) {
        existingScript.addEventListener('load', function () {
          resolve();
        }, { once: true });
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
      script.async = true;
      script.onload = function () {
        resolve();
      };
      script.onerror = function () {
        resolve();
      };
      document.head.appendChild(script);
    });
  }

  let deferredPrompt = null;
  let hasShownPrompt = false;

  async function installApp() {
    if (deferredPrompt) {
      try {
        deferredPrompt.prompt();
        const choiceResult = await deferredPrompt.userChoice;

        if (choiceResult.outcome === 'accepted') {
          console.log('✓ User accepted install');
          markInstallPromptDismissed();
        } else {
          console.log('✗ User dismissed install');
        }

        deferredPrompt = null;
      } catch (error) {
        console.error('Install error:', error);
        Swal.fire({
          title: 'Unable to install',
          text: 'Please try again from your browser menu.',
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
      return;
    }

    Swal.fire({
      title: 'Install is not ready yet',
      text: 'Your browser may be blocking the install prompt. Please try again from the browser menu or refresh the page later.',
      icon: 'info',
      confirmButtonText: 'Okay',
      confirmButtonColor: '#2563eb'
    });
  }

  async function showInstallPrompt() {
    if (hasShownPrompt || !shouldShowInstallPrompt()) return;
    hasShownPrompt = true;
    markInstallPromptSeen();

    injectSweetAlertStyles();
    await loadSweetAlert();

    if (!window.Swal) {
      console.warn('SweetAlert2 unavailable; falling back to browser alert');
      window.alert('Install MyBalai for quicker access and offline support.');
      return;
    }

    Swal.fire({
      title: 'Install MyBalai?',
      text: 'Add it to your home screen for faster access and offline support.',
      icon: 'info',
      showCancelButton: true,
      confirmButtonText: 'Install',
      cancelButtonText: 'Maybe later',
      confirmButtonColor: '#2563eb',
      cancelButtonColor: '#64748b',
      allowOutsideClick: false,
      allowEscapeKey: false,
      customClass: {
        popup: 'pwa-swal-popup',
        confirmButton: 'pwa-swal-confirm',
        cancelButton: 'pwa-swal-cancel'
      }
    }).then(function (result) {
      if (!result.isConfirmed) {
        markInstallPromptDismissed();
        return;
      }

      installApp();
    });
  }

  ensureMeta('theme-color', themeColor);
  ensureMeta('apple-mobile-web-app-capable', 'yes');
  ensureMeta('apple-mobile-web-app-status-bar-style', 'black-translucent');
  ensureMeta('apple-mobile-web-app-title', 'MyBalai');
  ensureMeta('mobile-web-app-capable', 'yes');
  ensureLink('manifest', resolveAppUrl('manifest.json'));
  ensureLink('apple-touch-icon', resolveAppUrl('assets/icons/appicon.png'));

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      const serviceWorkerUrl = resolveAppUrl('service-worker.js');
      navigator.serviceWorker
        .register(serviceWorkerUrl, { scope: getAppRootUrl().pathname })
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
    setTimeout(function () {
      showInstallPrompt();
    }, 1200);
  });

  window.addEventListener('appinstalled', function () {
    console.log('✓ App installed');
    deferredPrompt = null;
    markInstallPromptDismissed();
  });

  window.addEventListener('load', function () {
    if (!isInstalled()) {
      setTimeout(function () {
        showInstallPrompt();
      }, 1500);
    }
  });
})();
