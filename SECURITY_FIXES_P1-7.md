# P1-7: Session Cookie Secure Flag Configuration

**Priority**: P1-7 (Important - Session Security)  
**Date**: 2026-04-01

## Fix Required: Update /config/config.php (Line 148)

The session cookie secure flag detection is broken and causes cookies to be sent over unencrypted HTTP connections.

### Current Vulnerable Code (Line 148)
```php
'secure' => (SITE_URL === 'https'),  // HTTPS requis en production
```

### Problem
- `SITE_URL` is typically a full URL like `'https://example.com'`
- The comparison `SITE_URL === 'https'` will always be **FALSE**
- This means cookies are sent over HTTP, exposing sessions to interception

### Updated Secure Code (Replace Line 148)
```php
'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),  // Check actual HTTPS connection
```

### Why This Fix Works
- `$_SERVER['HTTPS']` is set by the web server when connection is HTTPS
- Handles standard HTTPS, Cloudflare, load balancers, reverse proxies
- Correctly returns true only when the actual connection is encrypted
- Returns false when running on HTTP (local dev, insecure connections)

### How to Apply
1. Open `/config/config.php`
2. Find line 148 in the SESSION CONFIGURATION section
3. Replace the `'secure'` parameter as shown above
4. Test that admin login works and cookies are transmitted securely

### Impact
After this fix:
- ✅ Session cookies automatically sent only over HTTPS
- ✅ Cookies will have the "Secure" flag set
- ✅ Prevents session hijacking via unencrypted connections
- ✅ Complies with OWASP A02:2021 - Broken Authentication

### Configuration Context (For Reference)
```php
session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),  // THIS LINE
    'httponly' => true,                  // Prevent JavaScript access
    'samesite' => 'Lax',                 // CSRF protection
]);
```

### Testing
```bash
# After applying the fix, verify:
1. Admin login still works
2. Check browser dev tools > Application > Cookies
3. "Secure" flag should be checked (✓)
4. Cookie should be transmitted over HTTPS only
```

### Notes
- **Note**: This file is in `.gitignore` because it contains database credentials
- The fix must be applied manually to your `/config/config.php`
- Do NOT commit `config/config.php` to version control
- Ensure HTTPS is enabled in your production environment

**Related CVE**: CWE-522 (Insufficiently Protected Credentials)  
**OWASP**: A02:2021 - Broken Authentication
