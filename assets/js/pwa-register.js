// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    // Get relative path to service worker based on current script location
    const getServiceWorkerPath = () => {
      // Find current script location
      const scripts = document.getElementsByTagName('script');
      let scriptPath = '';
      
      for (let script of scripts) {
        if (script.src && script.src.includes('pwa-register.js')) {
          scriptPath = script.src;
          break;
        }
      }
      
      // Calculate relative path to root directory
      // pwa-register.js is at: assets/js/pwa-register.js
      // service-worker.js is at: root/service-worker.js
      // So from assets/js/ we need to go up 2 levels: ../../service-worker.js
      
      if (scriptPath) {
        // Extract directory path
        const url = new URL(scriptPath);
        const pathname = url.pathname;
        
        // If script is loaded from assets/js/pwa-register.js
        if (pathname.includes('/assets/js/pwa-register.js')) {
          // From assets/js/ go up 2 levels to root
          return '../../service-worker.js';
        }
        // If script is loaded from admin/ or user/ directory
        else if (pathname.includes('/admin/') || pathname.includes('/user/')) {
          // From admin/ or user/ go up 1 level to root
          return '../service-worker.js';
        }
      }
      
      // Fallback: check current page location
      const currentPath = window.location.pathname;
      if (currentPath.includes('/admin/') || currentPath.includes('/user/')) {
        return '../service-worker.js';
      }
      
      // Default: same directory (root)
      return 'service-worker.js';
    };
    
    const swPath = getServiceWorkerPath();
    
    console.log('[PWA] Registering service worker:', swPath);
    
    navigator.serviceWorker.register(swPath)
      .then((registration) => {
        console.log('[PWA] Service Worker registered successfully:', registration.scope);
        
        // Check registration errors
        if (registration.installing) {
          registration.installing.addEventListener('error', (error) => {
            console.error('[PWA] Service Worker installation error:', error);
          });
        }
        
        // Check for updates
        registration.addEventListener('updatefound', () => {
          const newWorker = registration.installing;
          console.log('[PWA] New service worker found, installing...');
          
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // New service worker available, show update notification
              if (confirm('A new version of HRM Portal is available. Would you like to update now?')) {
                newWorker.postMessage({ action: 'skipWaiting' });
                window.location.reload();
              }
            }
          });
        });
      })
      .catch((error) => {
        console.error('[PWA] Service Worker registration failed:', error);
      });
    
    // Listen for service worker updates
    let refreshing = false;
    navigator.serviceWorker.addEventListener('controllerchange', () => {
      if (!refreshing) {
        refreshing = true;
        window.location.reload();
      }
    });
  });
  
  // Handle beforeinstallprompt event
  let deferredPrompt;
  window.addEventListener('beforeinstallprompt', (e) => {
    console.log('[PWA] Install prompt available');
    // Prevent the mini-infobar from appearing on mobile
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;
    
    // Show custom install button/notification
    showInstallPrompt();
  });
  
  // Show install prompt
  function showInstallPrompt() {
    // Check if already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
      return; // Already installed
    }
    
    // Create install banner
    const installBanner = document.createElement('div');
    installBanner.id = 'pwa-install-banner';
    installBanner.style.cssText = `
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: white;
      padding: 1rem 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      z-index: 10000;
      display: flex;
      align-items: center;
      gap: 1rem;
      max-width: 90%;
      animation: slideUp 0.3s ease;
    `;
    installBanner.innerHTML = `
      <div style="display: flex; align-items: center; gap: 0.75rem;">
        <i class="fas fa-mobile-alt" style="color: #00bfa5; font-size: 1.5rem;"></i>
        <div>
          <div style="font-weight: 600; color: #2d3436;">Install HRM Portal</div>
          <div style="font-size: 0.85rem; color: #636e72;">Add to home screen for quick access</div>
        </div>
      </div>
      <button id="pwa-install-btn" style="background: linear-gradient(135deg, #00bfa5, #02d6ba); color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer;">
        Install
      </button>
      <button id="pwa-dismiss-btn" style="background: transparent; border: none; color: #636e72; cursor: pointer; padding: 0.5rem;">
        <i class="fas fa-times"></i>
      </button>
    `;
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideUp {
        from {
          transform: translateX(-50%) translateY(100px);
          opacity: 0;
        }
        to {
          transform: translateX(-50%) translateY(0);
          opacity: 1;
        }
      }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(installBanner);
    
    // Install button click
    document.getElementById('pwa-install-btn').addEventListener('click', async () => {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log('[PWA] User choice:', outcome);
        deferredPrompt = null;
        installBanner.remove();
      }
    });
    
    // Dismiss button click
    document.getElementById('pwa-dismiss-btn').addEventListener('click', () => {
      installBanner.remove();
      localStorage.setItem('pwa-install-dismissed', Date.now());
    });
    
    // Auto-dismiss after 10 seconds
    setTimeout(() => {
      if (installBanner.parentNode) {
        installBanner.remove();
      }
    }, 10000);
  }
  
  // Check if user previously dismissed
  const dismissed = localStorage.getItem('pwa-install-dismissed');
  if (dismissed && (Date.now() - parseInt(dismissed)) < 7 * 24 * 60 * 60 * 1000) {
    // Don't show if dismissed within last 7 days
  } else {
    // Will show when beforeinstallprompt fires
  }
  
  // Track app installed event
  window.addEventListener('appinstalled', () => {
    console.log('[PWA] App installed successfully');
    deferredPrompt = null;
    // Remove install banner if exists
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
      banner.remove();
    }
  });
}

// Check if app is running as PWA
if (window.matchMedia('(display-mode: standalone)').matches) {
  console.log('[PWA] Running as installed app');
  document.documentElement.setAttribute('data-pwa', 'true');
}

