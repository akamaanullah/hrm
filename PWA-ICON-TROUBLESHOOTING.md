# PWA Icon Not Showing - Troubleshooting Guide

## Problem
PWA install prompt ya app icon mein logo show nahi ho raha.

## Quick Fixes

### Step 1: Clear Browser Cache
1. **Chrome DevTools** kholo (F12)
2. **Application** tab → **Storage**
3. **Clear site data** button click karo
4. **Hard refresh** karo (Ctrl+Shift+R)

### Step 2: Unregister Service Worker
1. **Application** tab → **Service Workers**
2. **Unregister** button click karo
3. Page refresh karo

### Step 3: Check Icon Path
1. Browser console (F12) mein check karo:
   ```
   http://172.16.32.59:8800/assets/images/LOGO.png
   ```
2. Agar 404 error aaye, to path galat hai
3. Agar image load ho, to path sahi hai

### Step 4: Verify Manifest
1. **Application** tab → **Manifest**
2. **Icons** section mein check karo
3. Icon preview dikhna chahiye
4. Agar error dikhe, to path check karo

### Step 5: Reinstall PWA
1. Agar app already installed hai, to **uninstall** karo
2. Browser cache clear karo
3. Page refresh karo
4. Phir se **install** karo

## Common Issues

### Issue 1: Icon Path Wrong
**Solution:** Manifest mein path verify karo:
```json
"src": "assets/images/LOGO.png"  // Relative path
```

### Issue 2: Icon Size Mismatch
**Solution:** Browser automatically resize karega, lekin warnings rahengi.

### Issue 3: Service Worker Cache
**Solution:** Service worker unregister karo aur cache clear karo.

### Issue 4: Browser Cache
**Solution:** Hard refresh (Ctrl+Shift+R) ya incognito mode mein test karo.

## Testing
1. Icon file directly browser mein open karo:
   ```
   http://172.16.32.59:8800/assets/images/LOGO.png
   ```
2. Agar image dikhe, to file sahi hai
3. Agar 404 aaye, to path check karo



