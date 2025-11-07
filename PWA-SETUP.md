# PWA Setup Guide - HRM Portal

## Overview
The HRM Portal has been configured as a Progressive Web App (PWA), allowing users to install it on their devices and use it offline.

## Features Implemented

### ✅ 1. Web App Manifest
- **File**: `manifest.json`
- Defines app metadata, icons, theme colors, and display mode
- Enables "Add to Home Screen" functionality

### ✅ 2. Service Worker
- **File**: `service-worker.js`
- Provides offline functionality
- Caches static assets and API responses
- Implements Cache First and Network First strategies

### ✅ 3. Offline Page
- **File**: `offline.html`
- Shows a friendly message when user is offline
- Auto-redirects when connection is restored

### ✅ 4. Install Prompt
- Custom install banner appears when app is installable
- Users can install the app directly from the browser

## Installation Instructions

### For Users:

#### Desktop (Chrome/Edge):
1. Visit the HRM Portal website
2. Look for the install icon in the address bar (or install banner)
3. Click "Install" to add to desktop
4. The app will open in a standalone window

#### Mobile (Android/Chrome):
1. Visit the HRM Portal website
2. Tap the menu (3 dots) in Chrome
3. Select "Add to Home screen" or "Install app"
4. Confirm installation
5. App icon will appear on home screen

#### Mobile (iOS/Safari):
1. Visit the HRM Portal website
2. Tap the Share button
3. Select "Add to Home Screen"
4. Customize the name (optional)
5. Tap "Add"
6. App icon will appear on home screen

## Testing PWA Features

### 1. Test Service Worker:
```javascript
// Open browser console and run:
navigator.serviceWorker.getRegistrations().then(registrations => {
  console.log('Service Workers:', registrations);
});
```

### 2. Test Offline Mode:
1. Open DevTools (F12)
2. Go to Network tab
3. Check "Offline" checkbox
4. Refresh page - should show offline.html

### 3. Test Install Prompt:
- Visit the site on a supported browser
- The install banner should appear automatically
- Or check browser's install button in address bar

## Configuration

### Update App Name/Icon:
Edit `manifest.json`:
```json
{
  "name": "Your App Name",
  "short_name": "Short Name",
  "icons": [
    {
      "src": "path/to/icon-192x192.png",
      "sizes": "192x192"
    }
  ]
}
```

### Update Service Worker Cache:
Edit `service-worker.js`:
- Change `CACHE_NAME` version to force cache update
- Add/remove URLs in `STATIC_ASSETS` array

### Update Theme Color:
Edit `manifest.json` and meta tags in headers:
- `theme-color`: `#00bfa5` (current)
- Change to match your brand colors

## Browser Support

### Full Support:
- ✅ Chrome/Edge (Desktop & Mobile)
- ✅ Firefox (Desktop & Mobile)
- ✅ Safari (iOS 11.3+)
- ✅ Samsung Internet

### Partial Support:
- ⚠️ Safari (Desktop) - Limited PWA features

## Troubleshooting

### Service Worker Not Registering:
1. Check browser console for errors
2. Ensure site is served over HTTPS (or localhost)
3. Clear browser cache and reload

### Install Prompt Not Showing:
1. Ensure all PWA requirements are met:
   - HTTPS (or localhost)
   - Valid manifest.json
   - Service worker registered
   - At least one icon (192x192 or larger)
2. Check if app is already installed
3. Some browsers require user interaction before showing prompt

### Offline Mode Not Working:
1. Check service worker is active in DevTools > Application > Service Workers
2. Verify offline.html exists and is cached
3. Check Network tab to see what's being cached

### Cache Not Updating:
1. Update `CACHE_NAME` version in service-worker.js
2. Unregister old service worker in DevTools
3. Hard refresh (Ctrl+Shift+R / Cmd+Shift+R)

## Files Modified/Created

### Created:
- `manifest.json` - App manifest
- `service-worker.js` - Service worker
- `offline.html` - Offline fallback page
- `assets/js/pwa-register.js` - Service worker registration

### Modified:
- `admin/header.php` - Added PWA meta tags
- `admin/footer.php` - Added PWA script
- `user/header.php` - Added PWA meta tags
- `user/footer.php` - Added PWA script
- `login.php` - Added PWA meta tags and script

## Next Steps (Optional Enhancements)

1. **Add App Icons**: Create proper 192x192 and 512x512 PNG icons
2. **Background Sync**: Implement sync for offline actions (attendance, leave requests)
3. **Push Notifications**: Add push notification support
4. **Update Notifications**: Notify users when new version is available
5. **Offline Forms**: Cache form data when offline, submit when online

## Notes

- Service worker only works over HTTPS (or localhost for development)
- Cache strategy: Network First for API calls, Cache First for static assets
- App will work offline for cached pages, but API calls require internet
- Users need to visit site at least once for service worker to register



