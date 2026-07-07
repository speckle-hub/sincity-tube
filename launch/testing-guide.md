# SinCity — Testing & Verification Guide

---

## 1. Local Environment Setup

### Option A: LocalWP (Recommended — easiest)

```
1. Download LocalWP from https://localwp.com
2. Install and launch
3. Click "Create New Site"
4. Site name: "sincity-dev"
5. Environment: Preferred (nginx, PHP 8.2, MySQL 8.0)
6. WordPress: Latest + admin user setup
7. Wait for site creation (~2 min)
8. Click "Open Site" → verify WordPress default page loads
```

### Option B: Laragon

```
1. Download Laragon from https://laragon.org
2. Install (full, includes Nginx/ Apache, PHP, MariaDB)
3. Launch Laragon → Start All
4. Menu → Quick Create → WordPress
5. Site name: sincity-dev
6. Complete WP install via browser at http://sincity-dev.test
```

### Option C: Manual (XAMPP / WAMP)

```
1. Start Apache + MySQL in control panel
2. Create DB: sincity_test
3. Download WP → extract to htdocs/sincity-dev
4. Configure wp-config.php with DB credentials
5. Run WP install at http://localhost/sincity-dev
```

---

## 2. Child Theme Installation

```
1. Copy sincity-child/ → /wp-content/themes/sincity-child/
2. Go to WP Admin > Appearance > Themes
3. Find "SinCity Child" → Activate
4. Verify parent theme "Kadence" is installed
   If missing: Appearance > Themes > Add New > Search "Kadence" > Install & Activate
5. Verify child theme is active (not Kadence parent)
```

**Expected result**: Site should look mostly unchanged (Kadene base styles), with dark theme applied after CSS loads.

### Verify functions.php loaded

```
WP Admin > Plugins > Add New > search "Code Snippets" or use this snippet:
Add <?php echo '<!-- SinCity functions loaded -->'; ?> to functions.php
Or check browser console for any JS errors from the child theme.
```

---

## 3. Required Plugins — Local Install

Install and activate these EXACT plugins for testing:

| # | Plugin | Test Needed | Why |
|---|--------|-------------|-----|
| 1 | **Advanced Custom Fields (free)** | [ ] | Required for `get_field()` calls |
| 2 | **WP All Import (free)** | [ ] | For CSV import testing |
| 3 | **Yoast SEO (free)** | [ ] | Schema + sitemaps |
| 4 | **wpDiscuz (free)** | [ ] | Comment system |
| 5 | **User Profile Builder (free)** | [ ] | Registration/login |
| 6 | **Wordfence (free)** | [ ] | Security testing |
| 7 | **CookieYes (free)** | [ ] | GDPR compliance |
| 8 | **ElasticPress (free)** | [ ] | Search (skip if no ES) |

**Install via**: WP Admin > Plugins > Add New > Search each > Install > Activate

### Plugins that can be skipped during local testing:
- WP Rocket (cache can break local edits — install on staging)
- MemberPress (don't need paywall locally)
- WP-Script Mass Embedder (skip — test embed handler manually)
- ShortPixel (no need for image optimization locally)

---

## 4. Embed Handler Testing

### 4.1 Create a Test Video Manually

```
1. Go to WP Admin > SinCity Videos > Add New
2. Title: "Test Video — Pornhub Embed"
3. Scroll to "Video Details" meta box
4. Fill in:
   - Embed URL: (use one from section 4.2)
   - Source Site: select matching source
   - Duration: "15:00"
5. Set a category: Normal Porn > Amateur (create if not exists)
6. Add a tag: "test-embed"
7. Click "Publish" (as Draft is fine)
8. View the post at /video/{slug}/
```

### 4.2 Real Example Embed URLs for Testing

```
Pornhub:
  https://www.pornhub.com/view_video.php?viewkey=ph5e2b8e1b2c3d4
  Expected iframe src: https://www.pornhub.com/embed/ph5e2b8e1b2c3d4

XVideos:
  https://www.xvideos.com/video123456/example_title
  Expected iframe src: https://www.xvideos.com/embedframe/123456

xHamster:
  https://xhamster.com/videos/example-title-1234567
  Expected iframe src: https://xhamster.com/embed/example-title-1234567

RedTube:
  https://www.redtube.com/123456
  Expected iframe src: https://www.redtube.com/embed/123456
```

### 4.3 Verification Steps

```html
<!-- Add this shortcode test to a test page or post -->
[sincity_trending count="4"]
```

```
1. Visit the single video page
2. Expected: embed player shows, no ads from source site
3. Right-click the iframe → Inspect → verify src matches expected format
4. Check browser Network tab → "media" filter → should show video segments loading
5. Verify watermark logo shows in bottom-left of player
6. Scroll below the player → metadata, tags, related videos should render

Mobile test:
7. Resize browser to 375px width
8. Player should resize to full width (16:9 maintained)
9. Action buttons should stack or wrap
```

### 4.4 Common Embed Troubleshooting

| Symptom | Likely Cause | Fix |
|---------|-------------|-----|
| Blank player area | External ID regex didn't match | Check URL format; add `error_log()` in `sc_filter_embed()` to debug |
| "Video unavailable" | No embed_url set | Edit video and fill in the Embed URL field |
| iframe shows but no video loads | Source site blocking embed via referrer | Add `<meta name="referrer" content="no-referrer">` to `<head>` |
| Video plays but has source ads | Regex matched wrong source or fallback used | Check `source_site` dropdown value; try different source |
| YouTube/vimeo-like source | Source not in supported list | Use 'Other' — falls back to raw embed code extraction |
| 403 error in iframe | Source site geo-blocks your region | Use a VPN or try a different source URL |
| Lazy load not working | JS error in player.js | Check browser console; ensure `player.js` is enqueued |

### 4.5 Expected Output for Each Source

```
Pornhub:   <iframe src="https://www.pornhub.com/embed/phXXX" ...>
XVideos:   <iframe src="https://www.xvideos.com/embedframe/123456" ...>
xHamster:  <iframe src="https://xhamster.com/embed/abc-def-123" ...>
RedTube:   <iframe src="https://www.redtube.com/embed/123456" ...>
Tube8:     <iframe src="https://www.tube8.com/embed/abc123" ...>
YouPorn:   <iframe src="https://www.youporn.com/embed/abc123" ...>
Eporner:   <iframe src="https://www.eporner.com/embed/abc123" ...>
```

---

## 5. Age Gate Testing

### Desktop Test
```
1. Open Incognito/Private window
2. Navigate to homepage (or any page)
3. Expected: Age gate overlay shows (full-screen, dark, centered)
4. Test 1 — Valid age:
   - Select Day: 15, Month: June, Year: 1990
   - Click "I Am 18+"
   - Expected: Redirects to homepage, shows site content
5. Test 2 — Cookie persistence:
   - Refresh the page
   - Expected: No age gate, goes straight to content
6. Test 3 — Underage:
   - Clear cookies (DevTools > Application > Cookies > Clear All)
   - Refresh → gate shows
   - Select Day: 1, Month: Jan, Year: 2010
   - Click "I Am 18+"
   - Expected: "ACCESS DENIED" page → redirects to Google after 5s
7. Test 4 — Invalid date (Feb 30):
   - Expected: "Please enter a valid date of birth." error
```

### Mobile Test
```
1. Chrome DevTools > Device Toolbar (Ctrl+Shift+M)
2. Select iPhone 12/13/14 or Galaxy S21
3. Refresh
4. Expected: Gate is mobile-responsive (full-width, touch-friendly selects)
5. Verify DOB dropdowns are easily tappable on touch screen
6. Verify button is large enough to tap (min 44px height)
```

### Cookie Verification
```
DevTools > Application > Cookies > sincity.porn (or localhost):
  Check: sincity_age_verified = 1
  Check: HttpOnly = true
  Check: Secure = true (if localhost has SSL, else false — OK for dev)
  Check: SameSite = Lax
  Check: Expires = +30 days
```

---

## 6. CSV Import Test (Small Batch)

### 6.1 Prepare the CSV
```
1. Download sample CSV from imports/sample-import.csv (or generate below)
2. Open in Excel/Google Sheets
3. Verify columns match WP All Import template:
   post_title, post_content, sc_category, sc_tag, embed_url, duration, source_site, thumbnail_url, external_id
```

### 6.2 WP All Import Import Steps
```
1. WP Admin > All Import > New Import
2. Upload CSV file
3. Step 1: Choose "sc_video" as post type
4. Step 2: Map columns:
   - post_title → post_title
   - post_content → post_content
   - sc_category → Taxonomy sc_category (create new terms)
   - sc_tag → Taxonomy sc_tag (create new terms)
   - embed_url → Custom Field embed_url
   - duration → Custom Field duration
   - source_site → Custom Field source_site
   - thumbnail_url → Download as Featured Image
   - external_id → Custom Field external_id
5. Step 3: Configure options:
   - Post Status: Draft
   - Duplicate Detection: By external_id meta field
   - Schedule Import: Manual (just for test)
6. Run Import
7. Check results: "X posts created, Y duplicates skipped"
```

### 6.3 Post-Import Verification
```
1. Go to SinCity Videos > All Videos
2. Confirm: 30 new posts exist (10 per category)
3. Check: Each has a thumbnail (featured image)
4. Open 3 test videos (1 per category) → verify embed loads
5. Check: Categories are correctly assigned
6. Check: Tags are correctly assigned
7. Visit category archive pages → videos should appear
```

### 6.4 Expected Import Results

| Check | Expected | Actual |
|-------|----------|--------|
| Total posts imported | 30 | __ |
| Normal Porn category | 10 videos | __ |
| Hentai category | 10 videos | __ |
| JAV category | 10 videos | __ |
| Thumbnails downloaded | 25+ of 30 | __ |
| Embeds playable | 27+ of 30 | __ |
| Duplicates rejected | 0 | __ |

---

## 7. Performance Checklist

### 7.1 GTmetrix / PageSpeed Targets (Local)

```
GTmetrix (https://gtmetrix.com):
  Load Time:          < 3.0s  local  / < 4.0s  production
  Page Size:          < 3MB   (embeds excluded — they load async)
  Requests:           < 40    (aim for < 25 on first load)
  Performance Score:  > 85    local  / > 75  production

PageSpeed Insights:
  Mobile:             > 65    (embeds penalty is normal)
  Desktop:            > 80
  LCP:                < 2.5s
  FID:                < 100ms
  CLS:                < 0.1
```

### 7.2 Optimization Checks for Local

```
[ ] Lazy loading: verify images + embeds don't load until scrolled to
    DevTools > Network tab > scroll down → new requests appear

[ ] No render-blocking resources
    DevTools > Lighthouse > Performance > "Eliminate render-blocking resources"
    Verdict: should have 0 (player.js is deferred)

[ ] Images served as WebP (or locally use JPEG — fine for testing)
    WP All Import thumbnail downloads: PNG is OK locally

[ ] Fonts: preconnect to Google Fonts
    Expected: <link rel="preconnect" href="https://fonts.googleapis.com">

[ ] CSS: minified (use WP Rocket or Autoptimize live, not needed locally)

[ ] JS: deferred
    Expected: <script src="...player.js" defer></script>
```

### 7.3 Browser DevTools Audit
```
Lighthouse Tab (Chrome DevTools):
  Run on mobile preset
  Verify: Performance > 65, Accessibility > 85, SEO > 90
  Note any warnings (ignore "third-party cookies" warnings)

Coverage Tab:
  Record reload
  Verify unused CSS < 40%
  (Large unused CSS = normal for Kadence + SinCity combined at start)
```

---

## 8. Common WordPress Testing Issues

| Issue | Fix |
|-------|-----|
| White screen after activating child theme | Check PHP error log; comment out sections of functions.php to isolate |
| ACF fields not showing | Install & activate ACF plugin; verify `acf_add_local_field_group` ran |
| get_field() returning nothing | Check field names match exactly; ensure ACF plugin is active |
| 404 on /video/ post type | Go to Settings > Permalinks > Save (flush rewrite rules) |
| Categories not showing | Run `sc_seed_categories()` manually: add `?force_seed=1` to functions.php |
| Age gate loops redirect | Clear browser cookies and session; check `$_SESSION` is starting |
| Import fails as "draft" | Check post_status mapping in WP All Import; verify string matches |
| Thumbnail not downloading | Check URL accessibility; WP All Import requires `allow_url_fopen` |

### Quick Debug Functions

```php
// Add to functions.php temporarily for debugging:
add_action('init', function () {
    // Test embed handler output
    $test = sc_filter_embed('', 'https://www.pornhub.com/view_video.php?viewkey=ph5e2b8e1b2c3d4', 'ph');
    error_log('SinCity embed test: ' . $test);

    // Test age gate session
    error_log('SinCity session: ' . print_r($_SESSION ?? [], true));
    error_log('SinCity age cookie: ' . ($_COOKIE['sincity_age_verified'] ?? 'not set'));
});

// Test category creation:
add_action('init', function () {
    if (isset($_GET['force_seed'])) {
        sc_seed_categories();
        error_log('SinCity categories seeded');
    }
});
```
