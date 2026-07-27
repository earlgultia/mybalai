(function () {
  const manifestUrl = '/manifest.json';
  const themeColor = '#2563eb';
  const installButtonId = 'pwaInstallButton';

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

  function createInstallButton() {
    let button = document.getElementById(installButtonId);
    if (!button) {
      button = document.createElement('button');
      button.id = installButtonId;
      button.type = 'button';
      button.textContent = 'Install app';
      button.style.cssText = 'position:fixed;right:1rem;bottom:1rem;z-index:9999;padding:0.8rem 1rem;border:none;border-radius:999px;background:#2563eb;color:#fff;font-weight:700;box-shadow:0 16px 40px rgba(37,99,235,0.24);display:none;';
      document.body.appendChild(button);
    }
    return button;
  }

  let deferredPrompt = null;
  let installButton = null;

  function showInstallButton() {
    if (!installButton) {
      installButton = createInstallButton();
    }
    installButton.style.display = 'inline-flex';
  }

  function hideInstallButton() {
    if (installButton) {
      installButton.style.display = 'none';
    }
  }

  ensureMeta('theme-color', themeColor);
  ensureMeta('apple-mobile-web-app-capable', 'yes');
  ensureMeta('apple-mobile-web-app-status-bar-style', 'black-translucent');
  ensureMeta('apple-mobile-web-app-title', 'MyBalai');
  ensureMeta('mobile-web-app-capable', 'yes');
  ensureLink('manifest', manifestUrl);
  ensureLink('apple-touch-icon', '/assets/icons/appicon.png');

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/service-worker.js').catch(function (error) {
        console.warn('Service worker registration failed', error);
      });
    });
  }

  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPrompt = event;
    showInstallButton();
  });

  window.addEventListener('appinstalled', function () {
    hideInstallButton();
  });

  document.addEventListener('DOMContentLoaded', function () {
    installButton = createInstallButton();
    installButton.addEventListener('click', async function () {
      if (!deferredPrompt) {
        return;
      }
      deferredPrompt.prompt();
      const choiceResult = await deferredPrompt.userChoice;
      if (choiceResult.outcome === 'accepted') {
        hideInstallButton();
      }
      deferredPrompt = null;
    });
  });
})();
