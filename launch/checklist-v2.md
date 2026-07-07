# SinCity — 30-Day Launch Checklist v2

**Priority levels**: 🔴 Critical | 🟡 Important | 🟢 Nice-to-have
**Status tags**: [  ] Pending | [x] Done | [!] Blocked

---

## Week 1: Foundation (Days 1-7)

### Day 1-2: Domain & Infrastructure
- [ ] 🔴 Register domain with Namecheap (adult-friendly registrar)
- [ ] 🔴 Set up hosting VPS (4 vCPU, 8GB RAM, 200GB NVMe SSD)
- [ ] 🔴 Install Ubuntu 22.04 LTS + LEMP stack (Nginx, MariaDB, PHP 8.2)
- [ ] 🔴 Configure Cloudflare DNS (proxied mode)
- [ ] 🔴 Generate & install Cloudflare Origin CA SSL certificate
- [ ] 🟡 Force HTTPS (301 redirect all HTTP → HTTPS)
- [ ] 🟡 HSTS header preload (Strict-Transport-Security)

### Day 3-4: WordPress Installation
- [ ] 🔴 Download & install latest WordPress
- [ ] 🔴 Create MariaDB database + user (strong password)
- [ ] 🔴 Configure wp-config.php (salts, table prefix, SSL)
- [ ] 🔴 Set correct file permissions (755 dirs / 644 files)
- [ ] 🔴 Install & configure Wordfence (firewall, login lockout, 2FA)
- [ ] 🟡 Move wp-content outside web root or restrict access
- [ ] 🟡 Disable XML-RPC, file editor, theme/plugin install via wp-config

### Day 5-7: Server Optimization
- [ ] 🔴 Configure Nginx (see `code/security-nginx.conf`)
- [ ] 🔴 Install Redis + configure as object cache
- [ ] 🔴 PHP 8.2 tuning (opcache, memory, exec time)
- [ ] 🔴 MariaDB tuning (innodb buffer pool, query cache)
- [ ] 🟡 Install & configure WP Rocket (page cache, minification, CDN)
- [ ] 🟡 Set up BunnyCDN pull zone for static assets
- [ ] 🟡 Configure Cloudflare page rules (cache everything for static)

**Week 1 Success Metrics:**
- [ ] Server responds in < 200ms TTFB (unloaded)
- [ ] SSL grade A+ on SSLLabs
- [ ] WordPress admin accessible securely
- [ ] All security headers present (test: securityheaders.com)

---

## Week 2: Theme & Configuration (Days 8-14)

### Day 8-9: Theme Setup
- [ ] 🔴 Install Kadence WP as parent theme
- [ ] 🔴 Create `sincity-child` theme directory
- [ ] 🔴 Copy `code/sc-functions.php` → child `functions.php`
- [ ] 🔴 Copy `code/age-gate.php` → child `age-gate.php`
- [ ] 🔴 Copy `code/assets/css/main.css` → child `assets/css/main.css`
- [ ] 🔴 Copy `code/assets/js/player.js` → child `assets/js/player.js`
- [ ] 🔴 Copy `templates/single-sc_video.php` → child `single-sc_video.php`
- [ ] 🔴 Copy `templates/page-trending.php` → child `page-trending.php`

### Day 10-11: Plugin Installation
- [ ] 🔴 Install & activate: ACF Pro, WP All Import Pro, WP Rocket
- [ ] 🔴 Install & activate: WP-Script Mass Embedder
- [ ] 🔴 Install & activate: FacetWP (or ElasticPress)
- [ ] 🔴 Install & activate: Yoast SEO (or Rank Math)
- [ ] 🔴 Install & activate: wpDiscuz (comments)
- [ ] 🔴 Install & activate: Wordfence, CookieYes, Loco Translate
- [ ] 🟡 Install: MemberPress, ShortPixel, AdSanity
- [ ] 🟡 Configure Elasticsearch server (if using ElasticPress)

### Day 12-13: Taxonomy & Field Setup
- [ ] 🔴 Register `sc_video` post type (already in functions.php)
- [ ] 🔴 Register all categories (Normal, Hentai, JAV + subcategories)
- [ ] 🔴 Add 50 core tags (see SEO docs for tag list)
- [ ] 🔴 Verify ACF fields are registered (check video post edit screen)
- [ ] 🟡 Create test video post manually to verify all fields work
- [ ] 🟡 Test embed render — visit single video page

### Day 14: Design Polish
- [ ] 🔴 Upload logo to child theme `assets/img/logo.png`
- [ ] 🔴 Upload hero banner to `assets/img/hero-home.jpg`
- [ ] 🔴 Upload category banners (3 files, one per category)
- [ ] 🔴 Upload favicon (32x32 + 512x512)
- [ ] 🟡 Upload city-silhouette.png for hero background
- [ ] 🟡 Test age gate styling on mobile + desktop

**Week 2 Success Metrics:**
- [ ] Age gate shows on first visit, allows 18+ entry
- [ ] Age gate redirects underage to Google
- [ ] Video post type creates successfully with all meta
- [ ] Embed loads cleanly in player (no source ads)
- [ ] All theme templates render without PHP errors

---

## Week 3: Content & Import (Days 15-21)

### Day 15-16: Import Configuration
- [ ] 🔴 Prepare sample CSV (5 records) for WP All Import
- [ ] 🔴 Create WP All Import template for `sc_video`
- [ ] 🔴 Map all CSV columns to ACF fields and taxonomies
- [ ] 🔴 Configure auto-categorization rules (see imports/plugin-setup.md)
- [ ] 🔴 Test import with 5 sample records → verify output
- [ ] 🔴 Fix any mapping issues → re-import until perfect

### Day 17-18: Bulk Import (Phase 1)
- [ ] 🔴 Import first batch: 200 Normal Porn videos (from CSV/RSS)
- [ ] 🔴 Import first batch: 100 Hentai videos
- [ ] 🔴 Import first batch: 100 JAV videos
- [ ] 🔴 Verify: thumbnails downloaded for all
- [ ] 🔴 Verify: embed codes are clean and playable
- [ ] 🟡 Spot-check 20 random videos for category accuracy
- [ ] 🟡 Spot-check 20 random videos for title quality

### Day 19-20: Content Quality Pass
- [ ] 🔴 Review all 400 imported posts — fix broken ones
- [ ] 🔴 Bulk-update titles to "SinCity:" prefix format
- [ ] 🟡 Add meta descriptions to all posts (Yoast bulk)
- [ ] 🟡 Generate XML sitemap via Yoast → verify in Search Console
- [ ] 🟡 Submit sitemap to Google Search Console + Bing

### Day 21: Content Expansion
- [ ] 🟡 Import additional 200 videos (all categories)
- [ ] 🟡 Set up automatic daily import cron (3 AM)
- [ ] 🟡 Create 3 blog posts (reviews/top lists for SEO boost)
- [ ] 🟡 Set up related videos manually for 10 key posts

**Week 3 Success Metrics:**
- [ ] 600+ videos live on site (draft status OK, at least 400 published)
- [ ] 0 broken embeds (all load in player)
- [ ] All videos have thumbnails
- [ ] Age gate blocks non-18 correctly
- [ ] Import cron runs without errors

---

## Week 4: Legal, SEO & Launch Prep (Days 22-30)

### Day 22: Legal Pages
- [ ] 🔴 Create and publish `/dmca/` page with full DMCA policy
- [ ] 🔴 Create and publish `/privacy-policy/` (GDPR + CCPA compliant)
- [ ] 🔴 Create and publish `/2257/` compliance statement
- [ ] 🔴 Create and publish `/terms/` (Terms of Service)
- [ ] 🔴 Create and publish `/contact/` page with abuse report form
- [ ] 🔴 Register DMCA agent at dmca.com
- [ ] 🟡 Configure CookieYes cookie consent banner

### Day 23: SEO Finalization
- [ ] 🔴 Yoast: Set title format for video posts: `SinCity: %title%` 
- [ ] 🔴 Yoast: Set meta description templates
- [ ] 🔴 Verify all pages have unique meta titles/descriptions
- [ ] 🔴 Submit sitemap to Google Search Console
- [ ] 🔴 Submit sitemap to Bing Webmaster Tools
- [ ] 🟡 Add VideoObject schema (already in single-sc_video.php)
- [ ] 🟡 Add Organization + WebSite schema to homepage
- [ ] 🟡 Create robots.txt (allow all, block /wp-admin/)

### Day 24: Monetization Setup
- [ ] 🔴 Apply to ExoClick (or TrafficJunky) for adult ad network
- [ ] 🔴 Configure ad placements in theme
- [ ] 🟡 Set up MemberPress premium tiers ($9.99/$14.99/$199)
- [ ] 🟡 Configure payment gateway (CCBill/Segpay for adult)
- [ ] 🟡 Set up affiliate links (OnlyFans, Chaturbate, Adam & Eve)
- [ ] 🟡 Google Analytics 4 + Matomo for backup analytics

### Day 25: Mobile & Performance Testing
- [ ] 🔴 GTmetrix test — target: Load < 3s, Performance > 85
- [ ] 🔴 Google PageSpeed Insights — mobile + desktop > 70
- [ ] 🔴 Core Web Vitals check (LCP < 2.5s, FID < 100ms, CLS < 0.1)
- [ ] 🔴 Test on real devices: iPhone 14, Samsung S23, iPad
- [ ] 🔴 Test on Chrome, Firefox, Safari, Edge
- [ ] 🔴 Test age gate on mobile (touch, scrolling, form)
- [ ] 🟡 Test embed player on slow 3G connection
- [ ] 🟡 Verify lazy loading works (embeds don't load until scrolled)

### Day 26-27: Security Hardening
- [ ] 🔴 Wordfence: Enable firewall, set to Learning Mode → Active
- [ ] 🔴 Wordfence: Brute force protection, login CAPTCHA
- [ ] 🔴 SSL: Force HTTPS, verify no mixed content warnings
- [ ] 🔴 Block common exploit attempts (xmlrpc, wp-json abuse)
- [ ] 🔴 Set up automatic daily backups (off-server: BunnyCDN or B2)
- [ ] 🔴 Test backup restoration in staging environment
- [ ] 🟡 Set up UptimeRobot monitoring (5-min checks)
- [ ] 🟡 Configure Cloudflare WAF rules (SQLi, XSS, bad bots)

### Day 28: Final Testing
- [ ] 🔴 **Full user flow test**: Age gate → browse → filter → watch → comment → favorite
- [ ] 🔴 **Embed test**: Verify embeds from PH, XV, XH, RB, YP, TN
- [ ] 🔴 **Comment test**: Post comment, verify moderation works
- [ ] 🔴 **Search test**: Search by title, tag, category, actor
- [ ] 🔴 **Mobile test**: Every page on 375px viewport
- [ ] 🔴 **Legal test**: Verify all legal pages load and have correct content
- [ ] 🟡 **Load test**: 50 concurrent users → server stays under 70% CPU
- [ ] 🟡 **Ad test**: Verify ad placements don't break layout
- [ ] 🟡 **Premium flow**: Test signup → payment → content unlock

### Day 29: Staging → Production Cutover
- [ ] 🔴 Final DB backup of staging site
- [ ] 🔴 Sync staging to production
- [ ] 🔴 Update Cloudflare DNS if needed
- [ ] 🔴 Verify all URLs work in production
- [ ] 🔴 Flush all caches (Cloudflare, WP Rocket, Redis)
- [ ] 🟡 Run full GTmetrix test on production URL
- [ ] 🟡 Submit production URL to Google Search Console
- [ ] 🟡 Enable Cloudflare pro-rail or DDoS protection

### Day 30: LAUNCH DAY
- [ ] 🔴 **Go live**: Remove maintenance mode
- [ ] 🔴 Monitor server logs closely (first 4 hours)
- [ ] 🔴 Monitor error logs (PHP, Nginx, WP debug)
- [ ] 🟡 Submit to adult directories (ThePORN, HelloPorn)
- [ ] 🟡 Post on Reddit nsfw411 (if allowed, follow sub rules)
- [ ] 🟡 Share on Twitter / Telegram channels
- [ ] 🟡 Check Google Search Console for crawl errors
- [ ] 🟡 **Post-mortem**: Document issues, fix for Day 31

---

## Post-Launch: Week 5+ (Ongoing)

### Daily
- [ ] Check server load (CPU, RAM, disk I/O)
- [ ] Monitor import cron logs (did today's import run?)
- [ ] Review Wordfence alerts
- [ ] Approve/reject pending comments (10 min/day)
- [ ] Spot-check 5 random videos for embed health

### Weekly
- [ ] Review Google Analytics + Search Console performance
- [ ] Check DMCA inbox for takedown requests
- [ ] Ad performance review (RPM, fill rate per network)
- [ ] SEO audit (rank tracking for top 20 keywords)
- [ ] Add 5-10 new tags based on search queries
- [ ] Publish 1 blog post (review, list, or guide)

### Monthly
- [ ] Full security scan (Wordfence + manual check)
- [ ] Database optimization (WP Rocket or manual)
- [ ] Plugin updates (check compatibility first on staging)
- [ ] Legal review (DMCA stats, privacy policy updates)
- [ ] Revenue report (ads + premium + affiliate)
- [ ] Content audit (remove/redirect broken posts)
- [ ] Backup verification (test restore on staging)

---

## Testing Pass Reference (Quick Checklist)

### Age Gate
```
[ ] First visit → gate shows
[ ] Enter valid 18+ DOB → enters site
[ ] Enter underage DOB → redirected to Google
[ ] Refresh within 30 days → no gate (cookie works)
[ ] Clear cookies → gate shows again
[ ] Mobile: dropdowns easy to tap
[ ] Mobile: no zoom issues on form
```

### Video Player
```
[ ] Embed loads without source ads
[ ] Player is 16:9 responsive
[ ] Duration badge shows correct time
[ ] Related videos show (not empty set)
[ ] View counter increments (unique session)
[ ] Share button copies URL
[ ] Report link opens contact form
[ ] Schema markup validates (Google Rich Results Test)
```

### Performance
```
[ ] GTmetrix: Load < 3s, Perf > 85
[ ] PageSpeed: Mobile > 70, Desktop > 85
[ ] Embeds lazy load (check network tab)
[ ] Images in WebP format
[ ] No render-blocking resources
[ ] TTFB < 500ms (cached)
```

### Legal
```
[ ] /dmca/ — has agent contact + takedown procedure
[ ] /privacy-policy/ — GDPR + CCPA compliant
[ ] /2257/ — custodian of records named
[ ] /terms/ — age warning, jurisdiction, liability
[ ] Cookie banner appears on first visit
[ ] Cookie banner allows opt-out
[ ] All pages show copyright + "18+ only" footer
```

---

## Success Metrics (90-Day Targets)

| Metric | 30 Days | 60 Days | 90 Days |
|--------|---------|---------|---------|
| Total videos | 2,000 | 8,000 | 20,000 |
| Daily visitors | 500 | 5,000 | 25,000 |
| Page views/day | 2,500 | 30,000 | 175,000 |
| Bounce rate | < 55% | < 50% | < 45% |
| Avg session | > 3 min | > 4 min | > 5 min |
| Pages/session | > 4 | > 5 | > 6 |
| Ad RPM | $8 | $10 | $12 |
| Revenue/day | $4 | $50 | $300 |
| Premium subs | 0 | 10 | 50 |
| Indexed pages | 2,000 | 8,000 | 18,000 |
| Top 10 keywords ranking | 0 | 5 | 20 |
