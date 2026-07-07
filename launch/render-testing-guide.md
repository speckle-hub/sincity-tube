# SinCity — Render.com Testing & Verification Guide

---

## 1. Local Testing (Before Render Deploy)

Since Render has no shell access, test everything locally first:

### 1.1 Docker Local Test

```bash
# From project root
docker build -t sincity-test -f render/Dockerfile .

# Run locally (replace DB env vars with your local MariaDB/MySQL)
docker run -p 8080:80 \
  -e DB_HOST=host.docker.internal \
  -e DB_NAME=sincity_test \
  -e DB_USER=root \
  -e DB_PASSWORD=yourpassword \
  -e WP_HOME=http://localhost:8080 \
  -e WP_SITEURL=http://localhost:8080 \
  -e DISABLE_WP_CRON=true \
  -e WP_DEBUG=true \
  sincity-test

# Visit http://localhost:8080 → should show WP install page
```

### 1.2 LocalWP Testing (Alternative)

For quick theme/CSS testing, use LocalWP instead of Docker:

```
1. Install LocalWP
2. Create site: sincity-local
3. Set PHP 8.2, MySQL 8.0
4. Activate Kadence theme
5. Copy sincity-child/ to wp-content/themes/
6. Activate child theme
7. Create test videos manually
8. Test embed handler, age gate, all features
```

---

## 2. Render Deploy Testing Checklist

### 2.1 Deployment Health

```
□ 2.1.1 Build succeeds: Check Render > Events > "Build successful"
□ 2.1.2 Deploy succeeds: Check Render > Events > "Deploy successful"
□ 2.1.3 Health check passes: https://sincity.onrender.com/healthz.php → "OK"
□ 2.1.4 Homepage loads: https://sincity.onrender.com → not 502/504
□ 2.1.5 WP Admin loads: https://sincity.onrender.com/wp-admin/
```

### 2.2 Database Connection

```
□ 2.2.1 WP Admin > Settings > SinCity admin bar visible
□ 2.2.2 Create a test post → saves without DB error
□ 2.2.3 Check Render DB logs for connection errors
```

### 2.3 Embed Handler Tests

Test from Chrome Incognito (to simulate first visit):

```
□ 2.3.1 Pornhub embed: Visit /video/test-pornhub-embed
   Expected: iframe loads, video plays, no ads

□ 2.3.2 XVideos embed: Create similar test
   Expected: iframe loads with xvideos.com/embedframe/ URL

□ 2.3.3 xHamster embed: Create similar test
   Expected: iframe loads with xhamster.com/embed/ URL

□ 2.3.4 Verify watermark SVG renders in player bottom-left

□ 2.3.5 Check mobile: Chrome DevTools > 375px viewport
   Player should be 100% width, 16:9 ratio
```

### 2.4 Lazy Load Verification

```
1. Open DevTools > Network tab
2. Reload homepage
3. Look for iframe requests — should be NONE on initial load
4. Scroll down → iframes should load as they enter viewport
5. This confirms IntersectionObserver lazy loading works
```

### 2.5 Age Gate Testing on Render

```
□ 2.5.1 Incognito window → https://sincity.porn
   Expected: Age gate shows (full-screen dark overlay)

□ 2.5.2 Select valid DOB → "I Am 18+"
   Expected: Redirects to homepage, no gate

□ 2.5.3 Cookie check: DevTools > Application > Cookies
   Find: sincity_age_verified = 1; HttpOnly; Secure; SameSite=Lax

□ 2.5.4 Underage test: Enter invalid/underage DOB
   Expected: 5-second delay → Google redirect

□ 2.5.5 Mobile: 375px viewport, gate should be full-width, tappable dropdowns
```

### 2.6 Cold Start Performance

```
□ 2.6.1 Wait 20 minutes (site spins down)
□ 2.6.2 Visit homepage — measure time until page renders
   Expected: < 8 sec for full page load

□ 2.6.3 Refresh immediately — should load in < 2 sec (warm)
```

### 2.7 Cron Job Verification

```
□ 2.7.1 Set up cron-job.org (pings /wp-cron.php every 15 min)
□ 2.7.2 Wait for first ping → check Render logs for:
   "GET /wp-cron.php" → 200 OK
□ 2.7.3 If scheduled imports exist: check posts appear after scheduled time
```

### 2.8 Mobile Responsiveness

```
Test in Chrome DevTools or real device:

□ 2.8.1 iPhone 14 (390x844):
   - Homepage: 2-column grid, stacked categories
   - Video page: full-width player, wrapped meta
   - Age gate: readable, tappable
   - Navigation: hamburger menu works

□ 2.8.2 Galaxy S21 (360x800):
   - Same as above, no horizontal scroll
   - Text not cut off
```

### 2.9 Performance (Lighthouse)

```
Run Lighthouse in Chrome DevTools:

□ 2.9.1 Mobile:
   Performance: > 45 (acceptable for first paint — embeds excluded)
   Accessibility: > 85
   SEO: > 90

□ 2.9.2 Desktop:
   Performance: > 65
   Accessibility: > 85
   SEO: > 90

Note: Low Performance scores are normal on free tier due to cold start,
no cache, and no CDN. Score improves on warm start.
```

### 2.10 Legal Pages

```
□ 2.10.1 https://sincity.porn/dmca/ → has takedown procedure
□ 2.10.2 https://sincity.porn/privacy-policy/ → GDPR + CCPA
□ 2.10.3 https://sincity.porn/2257/ → custodian of records statement
□ 2.10.4 https://sincity.porn/terms/ → age clause, liability
□ 2.10.5 Footer links to all legal pages
```

---

## 3. Expected vs. Actual Results Table

| Test | Expected | Actual | Pass/Fail |
|------|----------|--------|-----------|
| Build succeeds | Docker image builds | | |
| Homepage loads | 200 OK, dark theme | | |
| Embed (PH) | Video plays, no ads | | |
| Embed (XV) | Video plays, no ads | | |
| Embed (XH) | Video plays, no ads | | |
| Age gate (adult) | Enters site | | |
| Age gate (minor) | Redirects to Google | | |
| Cookie persists | 30-day HttpOnly | | |
| Lazy load | No iframes on load | | |
| Cold start | < 8 sec | | |
| Cron job | 200 on /wp-cron.php | | |
| Mobile | No broken layout | | |
| Lighthouse SEO | > 90 | | |
| Legal pages | All load correctly | | |

---

## 4. Common Render-Specific Issues

### Issue: "502 Bad Gateway" after deploy

```
Cause: PHP-FPM not running or Nginx can't find the socket.
Fix: 
1. Check deploy logs for FPM errors
2. Verify start.sh has correct PHP-FPM socket path
3. Default socket: /var/run/php/php8.2-fpm.sock
4. If different, update both start.sh and nginx/default.conf
```

### Issue: "Connection refused" for database

```
Cause: Wrong DB_HOST env var.
Fix:
1. Use Render INTERNAL host (not external):
   Correct:  10.0.0.123  (or render internal hostname)
   Wrong:    localhost
2. Verify port (default: 5432 for PostgreSQL)
```

### Issue: "White screen of death"

```
Cause: PHP fatal error (syntax, missing function, etc.)
Fix:
1. Set WP_DEBUG=true in env vars
2. Redeploy
3. Visit site → you'll see the actual PHP error
4. Fix in code → git push
5. Set WP_DEBUG back to false
```

### Issue: Age gate not appearing after deploy

```
Cause: Cookie not set due to FORCE_SSL mismatch.
Fix:
1. If FORCE_SSL is true, ensure you're visiting https:// (not http://)
2. Check age-gate.php's setcookie() secure parameter
3. On Render's onrender.com domain, SSL is automatic
```

### Issue: "The link you followed has expired" when importing

```
Cause: WP All Import nonce expired (common on slow deployments)
Fix:
1. Split CSV into smaller batches (25 rows each)
2. Import each batch separately
3. Or increase WP_MEMORY_LIMIT in env vars to 256M
```
