# SinCity — Render.com Day 1-3 Execution Checklist

---

## Day 1: GitHub + Render Foundation (~4 hours)

### 1.1 GitHub Repo Setup

```
□ 1.1.1 Create GitHub repo: github.com/YOUR_USERNAME/sincity (Private)
□ 1.1.2 Clone to local machine: git clone git@github.com:YOUR_USERNAME/sincity.git
□ 1.1.3 Copy all SinCity project files into the repo folder
□ 1.1.4 Create .gitignore (see render-deployment-guide.md for content)
□ 1.1.5 Create folder structure:
  sincity/
  ├── render/              ← Dockerfile, nginx conf, scripts, etc.
  ├── wp-content/
  │   ├── mu-plugins/      ← 00-pg4wp-loader.php, 01-r2-uploads.php
  │   ├── plugins/         ← (empty — add plugins later)
  │   └── themes/
  │       └── sincity-child/ ← (full child theme)
  └── (WordPress core files)
□ 1.1.6 git add . && git commit -m "Initial commit" && git push
```

### 1.2 Render PostgreSQL

```
□ 1.2.1 Go to https://dashboard.render.com
□ 1.2.2 New + → PostgreSQL
□ 1.2.3 Name: sincity-db | Database: sincity | User: sincity
□ 1.2.4 Region: Oregon | Plan: Free
□ 1.2.5 Click "Create Database"
□ 1.2.6 Wait for provisioning (~2 min)
□ 1.2.7 Copy connection details (Internal Host, Port, DB name, User, Password)
       ⚠ Save these in a password manager — needed in Step 1.3
```

### 1.3 Render Web Service

```
□ 1.3.1 New + → Web Service
□ 1.3.2 Connect: GitHub → sincity repo → main branch
□ 1.3.3 Name: sincity-web
□ 1.3.4 Runtime: Docker
□ 1.3.5 Dockerfile Path: ./render/Dockerfile
□ 1.3.6 Docker Context: ./
□ 1.3.7 Region: Oregon (same as DB)
□ 1.3.8 Plan: Free
□ 1.3.9 Environment Variables — add ALL of these:

  Key               Value
  ───────────────────────────────────
  WP_HOME           https://sincity.onrender.com
  WP_SITEURL        https://sincity.onrender.com
  DB_HOST           <Internal DB Host>
  DB_NAME           sincity
  DB_USER           sincity
  DB_PASSWORD       <DB Password>
  FORCE_SSL         false
  DISABLE_WP_CRON   true
  WP_DEBUG          false
  WP_TABLE_PREFIX   sc_

  # Salts from https://api.wordpress.org/secret-key/1.1/salt/
  AUTH_KEY          ...
  SECURE_AUTH_KEY   ...
  ... (8 salts total)

□ 1.3.10 Advanced → Health Check Path: /healthz.php
□ 1.3.11 Click "Create Web Service"
□ 1.3.12 Wait for Docker build + deploy (~5-8 min)
□ 1.3.13 Watch logs (scroll up) for errors
```

### 1.4 WordPress Installation

```
□ 1.4.1 Visit https://sincity.onrender.com
       ⚠ First visit may take 10-30 seconds (cold start)
□ 1.4.2 You should see WordPress installation page (language selection)
□ 1.4.3 Select language → Continue
□ 1.4.4 Fill in:
   - Site Title: SinCity
   - Username: admin
   - Password: <generate a STRONG password>
   - Email: you@example.com
□ 1.4.5 Install WordPress
□ 1.4.6 Log in to /wp-admin/
□ 1.4.7 Settings > Permalinks > "Post name" > Save
□ 1.4.8 Delete default content: "Hello World" post, "Sample Page", example comment
```

### Day 1 Checklist

```
[✓] GitHub repo created and pushed
[✓] Render PostgreSQL database created (save credentials)
[✓] Render Web Service deployed (Docker build succeeded)
[✓] Site accessible at https://sincity.onrender.com
[✓] WordPress installation complete
[✓] Permalinks set to "Post name"
```

---

## Day 2: Theme + Plugins + First Import (~4 hours)

### 2.1 Deploy Plugins via Git

```
□ 2.1.1 On local machine, download required plugins:

   cd /path/to/sincity/wp-content/plugins/

   Plugin               URL to download
   ─────────────────────────────────────────────────────
   ACF                  https://downloads.wordpress.org/plugin/advanced-custom-fields.latest-stable.zip
   Yoast SEO            https://downloads.wordpress.org/plugin/wordpress-seo.latest-stable.zip
   wpDiscuz             https://downloads.wordpress.org/plugin/wpdiscuz.latest-stable.zip
   Profile Builder      https://downloads.wordpress.org/plugin/profile-builder.latest-stable.zip
   CookieYes            https://downloads.wordpress.org/plugin/cookie-law-info.latest-stable.zip
   WP All Import        https://downloads.wordpress.org/plugin/wp-all-import.latest-stable.zip

□ 2.1.2 Extract each: unzip <plugin>.zip && rm <plugin>.zip
□ 2.1.3 Verify folder structure:
   wp-content/plugins/advanced-custom-fields/
   wp-content/plugins/wordpress-seo/
   wp-content/plugins/wpdiscuz/
   ...etc

□ 2.1.4 git add wp-content/plugins/ && git commit -m "Add plugins" && git push

□ 2.1.5 Wait for auto-deploy on Render (~3-5 min)
□ 2.1.6 Verify plugins activated in WP Admin > Plugins
```

### 2.2 Activate Child Theme

```
□ 2.2.1 Go to WP Admin > Appearance > Themes
□ 2.2.2 Find "SinCity Child" → Activate
□ 2.2.3 If not visible, run: git push (trigger redeploy)
□ 2.2.4 Verify: site shows dark theme (not default WordPress)
```

### 2.3 Activate & Configure Plugins

```
□ 2.3.1 WP Admin > Plugins → Activate all:

   [x] Advanced Custom Fields
   [x] Yoast SEO
   [x] wpDiscuz
   [x] Profile Builder
   [x] CookieYes
   [x] WP All Import

□ 2.3.2 Yoast > General: Enable XML sitemaps
□ 2.3.3 Yoast > Content Types > sc_video: Set title template "%%title%% %%sep%% SinCity"
□ 2.3.4 CookieYes: Set to GDPR mode, accept button
□ 2.3.5 Profile Builder: Enable "Email Confirmation" for registration
```

### 2.4 Verify ACF + Categories

```
□ 2.4.1 Go to SinCity Videos > Add New
       Expected: "Video Details" meta box with Embed URL, Duration, Source, etc.
□ 2.4.2 If missing: check ACF plugin is active
□ 2.4.3 Visit https://sincity.onrender.com/?force_seed=1
       (triggers category creation)
□ 2.4.4 Verify categories exist: SinCity Videos > Categories
       3 parents + 26 subcategories
```

### 2.5 Test Embed Handler

```
□ 2.5.1 Create test video via WP Admin:

   - Title: "TEST: Pornhub Embed"
   - Embed URL: https://www.pornhub.com/view_video.php?viewkey=ph5e2b8e1b2c3d4
   - Source: Pornhub
   - Duration: 15:00
   - Category: Normal Porn > Amateur
   - Publish as Draft

□ 2.5.2 View the video page
   Expected: Embed player loads (no source site ads)
   Expected: SINCITY watermark in bottom-left
   Expected: Tags, meta, related videos sections

□ 2.5.3 Create 2 more test videos (XVideos + xHamster URLs)
```

### 2.6 Age Gate Test

```
□ 2.6.1 Open Private/Incognito window → visit site
       Expected: Age gate shows (18+ interstitial)
□ 2.6.2 Enter valid DOB (15/June/1990) → "I Am 18+"
       Expected: Enters site
□ 2.6.3 Refresh → no gate (cookie works)
□ 2.6.4 Clear cookies → gate shows again
□ 2.6.5 Enter underage DOB (1/Jan/2015)
       Expected: "ACCESS DENIED" → Google redirect after 5s
```

### 2.7 Small CSV Import

```
□ 2.7.1 Go to WP Admin > All Import > New Import
□ 2.7.2 Upload: imports/sample-import-30.csv (or create 10-row subset)
□ 2.7.3 Map: title → post_title, embed_url → Custom Field, etc.
□ 2.7.4 Post Status: Draft (safe for testing)
□ 2.7.5 Run import → should create 10-30 posts
□ 2.7.6 Verify: videos appear in SinCity Videos > All
□ 2.7.7 Open 2-3 → embeds load correctly
```

### Day 2 Checklist

```
[✓] Plugins committed to Git and deployed
[✓] Child theme active with dark design
[✓] ACF fields visible on video editor
[✓] Categories seeded (Normal, Hentai, JAV + subs)
[✓] Embed handler works (PH, XV, XH tested)
[✓] Age gate working (18+ → enter, underage → redirect)
[✓] Small CSV import succeeds (10+ videos)
```

---

## Day 3: Domain, Cron, SEO & Legal (~3 hours)

### 3.1 Custom Domain

```
□ 3.1.1 Render Dashboard > sincity-web > Settings > Custom Domain
□ 3.1.2 Add domain: sincity.porn (or your domain)
□ 3.1.3 Copy Render DNS target: sincity-web.onrender.com
□ 3.1.4 At your DNS provider (Cloudflare/Namecheap):
  - CNAME @ → sincity-web.onrender.com
  - CNAME www → sincity-web.onrender.com
□ 3.1.5 Wait for DNS propagation (5 min to 1 hour)
□ 3.1.6 Visit https://sincity.porn → should show site
□ 3.1.7 Update env vars in Render:
  - WP_HOME: https://sincity.porn
  - WP_SITEURL: https://sincity.porn
  - FORCE_SSL: true
□ 3.1.8 Manual Deploy → Deploy (to apply new env vars)
```

### 3.2 Set Up Cron Jobs

```
□ 3.2.1 Go to https://cron-job.org → Sign up (free)
□ 3.2.2 Create Cronjob #1:
   Title: SinCity WP-Cron
   URL: https://sincity.porn/wp-cron.php
   Interval: Every 15 minutes
   Save

□ 3.2.3 Create Cronjob #2:
   Title: SinCity Daily Import
   URL: https://sincity.porn/wp-cron.php?import=1
   Interval: Daily at 3:00 AM
   Save
```

### 3.3 Uptime Monitor (Keep Site Warm)

```
□ 3.3.1 Go to https://uptimerobot.com → Sign up (free)
□ 3.3.2 Add Monitor:
   Name: SinCity
   URL: https://sincity.porn
   Interval: 10 minutes
   Save
   (Keeps the site from sleeping completely)
```

### 3.4 SEO Setup

```
□ 3.4.1 Yoast > XML Sitemaps: Enable
□ 3.4.2 Visit: https://sincity.porn/sitemap_index.xml (verify it loads)
□ 3.4.3 Google Search Console: Add property → URL prefix
□ 3.4.4 Verify domain (DNS TXT record or HTML file)
□ 3.4.5 Submit sitemap URL in GSC
□ 3.4.6 Yoast > Title Template: "%%title%% %%sep%% SinCity"
```

### 3.5 Legal Pages

```
□ 3.5.1 Create page: /dmca/ (takedown policy, agent contact)
□ 3.5.2 Create page: /privacy-policy/ (GDPR + CCPA)
□ 3.5.3 Create page: /2257/ (record-keeping compliance)
□ 3.5.4 Create page: /terms/ (user agreement, 18+ clause)
□ 3.5.5 Create page: /contact/ (abuse report form)
□ 3.5.6 Add legal links to WP admin > Appearance > Menus (footer menu)
```

### Day 3 Checklist

```
[✓] Custom domain configured (SSL auto-provisioned)
[✓] cron-job.org set up (15-min WP-Cron + daily import)
[✓] UptimeRobot pings every 10 min (keeps site warm)
[✓] XML sitemap submitted to Google Search Console
[✓] 5 legal pages created and linked in footer
[✓] Site accessible via custom domain with HTTPS
```

---

## Free Tier Constraints Summary

| Resource | Limit | Impact on SinCity |
|----------|-------|-------------------|
| RAM | 512 MB | Enough for WP + small traffic |
| Cold start | 5-30s | Mitigate with UptimeRobot |
| DB storage | 1 GB | ~50,000 video posts |
| DB connections | 15 concurrent | Fine for small traffic |
| Ephemeral storage | Files lost on restart | Uploads via URL; plugins via Git |
| Disk space | N/A (container rebuilds) | All content in DB or external URLs |
| Build hours | 750/mo | Enough for 1 service 24/7 |
| PostgreSQL | ✓ (not MySQL) | PG4WP mu-plugin handles this |
| Redis | Not on free tier | Disable WP Redis; no object cache |
| Outbound email | Blocked (port 25) | Use SendGrid free tier for registration emails |
| Custom domain | ✓ (with SSL) | 1 custom domain per web service |
| Bandwidth | No explicit cap | Fair use; ~100GB/mo should be fine |
| Background workers | Not on free tier | Imports run via WP-Cron + cron-job.org |

---

## Render-Specific Workarounds

### Workaround 1: Plugins Must Be in Git

```
❌ DON'T: Install plugins via WP Admin > Plugins > Add New
✓ DO:    Download plugin ZIP → unzip → commit to wp-content/plugins/ → git push → deploy
```

### Workaround 2: File Uploads Don't Persist

```
❌ DON'T: Upload images to Media Library (lost on restart)
✓ DO 1:  Use external URLs for thumbnails (thumbs from source CDNs)
✓ DO 2:  Configure Cloudflare R2 for persistent file storage (optional, free 10GB)
```

### Workaround 3: No Background Jobs

```
❌ DON'T: Rely on WP-Cron triggering on page visits (they will, but unreliably)
✓ DO:    Use cron-job.org (free) to ping wp-cron.php every 15 min
```

### Workaround 4: No Root Access

```
❌ DON'T: Try to SSH into server (there is no shell access)
✓ DO:    Everything via Git push → auto-deploy
         Config changes via Render dashboard env vars
```

### Workaround 5: Handles Cold Starts

```
❌ DON'T: Expect instant first-page load after inactivity
✓ DO:    Use UptimeRobot (free) to ping every 10 min
         First visit still has ~3-5s cold start
```

---

## Quick Reference: Render Dashboard URLs

```
PostgreSQL dashboard: https://dashboard.render.com/d/<db-id>
Web Service dashboard: https://dashboard.render.com/web/<svc-id>
Deploy logs: Web Service > Events tab
Environment vars: Web Service > Environment tab
Custom domain: Web Service > Settings > Custom Domain
Manual deploy: Web Service > Manual Deploy > Deploy
Service restart: Web Service > Manual Deploy > Restart
```
