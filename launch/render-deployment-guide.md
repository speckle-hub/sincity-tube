# SinCity — Render.com Deployment Guide

## Overview

This guide walks through deploying SinCity on Render's free tier using:
- **Docker-based Web Service** (Nginx + PHP-FPM 8.2 + WordPress)
- **Free PostgreSQL** database
- **cron-job.org** for scheduled tasks
- **Cloudflare** for domain + SSL (or Render's `onrender.com` subdomain)
- **GitHub** for code deployment

---

## Step 1: GitHub Repository Setup

### 1.1 Create the Repo

```
1. Go to https://github.com/new
2. Repository name: sincity
3. Visibility: Private (or Public — doesn't matter)
4. Initialize: NO (we'll push existing code)
5. Click "Create repository"
```

### 1.2 Prepare Local Files

Ensure your project folder has this exact structure:

```
sincity/
├── .gitignore
├── render/
│   ├── Dockerfile
│   ├── render.yaml
│   ├── .env.example
│   ├── nginx/default.conf
│   ├── php/opcache.ini
│   ├── php/php.ini
│   ├── supervisor/supervisord.conf
│   └── scripts/start.sh
├── sincity-child/          ← child theme
├── wp-content/
│   ├── mu-plugins/         ← PG4WP loader, R2 uploads
│   ├── plugins/            ← committed plugin ZIPs (see below)
│   ├── themes/             ← Kadence + sincity-child
│   └── uploads/            ← .gitkeep (empty dir)
├── wp-config-sample.php    ← original WP
├── wp-*.php                ← all WP core files
└── index.php               ← WP original
```

### 1.3 Create .gitignore

```
# SinCity .gitignore
.DS_Store
Thumbs.db
*.log
.env
wp-config.php
wp-content/uploads/*
!wp-content/uploads/.gitkeep
wp-content/cache/
wp-content/debug.log
node_modules/
```

### 1.4 Push to GitHub

```bash
cd /path/to/sincity
git init
git add .
git commit -m "Initial SinCity commit — Render deployment"
git remote add origin https://github.com/YOUR_USERNAME/sincity.git
git branch -M main
git push -u origin main
```

---

## Step 2: Render Dashboard Setup

### 2.1 Create PostgreSQL Database

```
1. Go to https://dashboard.render.com
2. Click "New +" → "PostgreSQL"
3. Fill in:
   - Name: sincity-db
   - Database: sincity
   - User: sincity
   - Region: Oregon (or closest to you)
   - Plan: Free ($0/month)
4. Click "Create Database"
5. Wait ~2-3 minutes for provisioning
6. Copy the "Internal Database URL" and all connection details
   ⚠ SAVE THESE — you'll need them for the Web Service
```

**Free tier limits**: 1GB storage, 256MB RAM. This is enough for ~50,000 video posts with metadata. Monitor usage monthly.

### 2.2 Create Web Service (via Docker)

```
1. Click "New +" → "Web Service"
2. Connect your GitHub repo (authorize Render to access sincity repo)
3. Configure:
   - Name: sincity-web
   - Branch: main
   - Runtime: Docker
   - Dockerfile Path: ./render/Dockerfile
   - Docker Context: ./
   - Region: Oregon (same as DB)
   - Plan: Free ($0/month)
4. Scroll to "Environment Variables" and add ALL of these:

   Key                       Value
   ───────────────────────────────────────────────────────
   WP_HOME                   https://sincity.onrender.com
   WP_SITEURL                https://sincity.onrender.com
   DB_HOST                   <Internal Database Host from Step 2.1>
   DB_NAME                   sincity
   DB_USER                   sincity
   DB_PASSWORD               <Database Password from Step 2.1>
   FORCE_SSL                 false  ← Render handles SSL at edge
   DISABLE_WP_CRON           true
   WP_DEBUG                  false
   WP_TABLE_PREFIX           sc_
   
   # WordPress Salts — generate at https://api.wordpress.org/secret-key/1.1/salt/
   AUTH_KEY                  <paste>
   SECURE_AUTH_KEY           <paste>
   LOGGED_IN_KEY             <paste>
   NONCE_KEY                 <paste>
   AUTH_SALT                 <paste>
   SECURE_AUTH_SALT          <paste>
   LOGGED_IN_SALT            <paste>
   NONCE_SALT                <paste>

5. Click "Advanced" → Health Check Path: /healthz.php
6. Click "Create Web Service"
7. Wait ~5-8 minutes for Docker build + deploy
8. Watch logs for any errors
```

### 2.3 Verify Deployment

```
1. Visit: https://sincity.onrender.com
2. Expected: WordPress installation page (language selector)
3. If you see "Error: ..." — check Render logs (see troubleshooting)
4. If blank page — wait 30s and refresh (cold start for free tier)
```

### 2.4 Run WordPress Installation

```
1. Go to https://sincity.onrender.com/wp-admin/install.php
2. Select language → Continue
3. Fill in:
   - Site Title: SinCity
   - Username: admin
   - Password: <STRONG PASSWORD>
   - Email: admin@yourdomain.com
4. Install WordPress
5. Log in
6. Go to Settings > Permalinks → "Post name" → Save

⚠ Important: Every time your service restarts (deploy, sleep cycle),
the wp-config.php is regenerated from env vars. WordPress settings
(permalinks, etc.) are stored in the database and persist.
```

---

## Step 3: Install Plugins & Theme

### 3.1 Plugin Strategy for Render

On Render free tier, `DISALLOW_FILE_MODS` is enabled — you CANNOT install plugins via WP admin. All plugins must be **committed to Git**.

**Required plugins to commit:**

```
wp-content/plugins/
├── advanced-custom-fields/     ← Download from ACF website
├── wordpress-seo/              ← Yoast SEO
├── wpdiscuz/                   ← Comments
├── profile-builder/            ← User registration
├── cookie-law-info/            ← GDPR consent
├── wp-all-import/              ← CSV import
└── postgresql-for-wordpress/   ← PG4WP (only if using PostgreSQL)
```

**How to add plugins (local machine, then push to Git):**

```bash
# From your local project root
cd wp-content/plugins

# Download ACF free
wget https://downloads.wordpress.org/plugin/advanced-custom-fields.latest-stable.zip
unzip advanced-custom-fields.latest-stable.zip -d .

# Download Yoast
wget https://downloads.wordpress.org/plugin/wordpress-seo.latest-stable.zip
unzip wordpress-seo.latest-stable.zip -d .

# ... repeat for each plugin

# Clean up ZIPs
rm *.zip

# Add to Git
git add wp-content/plugins/
git commit -m "Add required plugins"
git push
```

**After pushing, Render auto-deploys** (or you can trigger deploy manually).

### 3.2 Child Theme Deployment

The `sincity-child` theme is already in your repo at:

```
wp-content/themes/sincity-child/
```

After first deploy:
```
1. Go to WP Admin > Appearance > Themes
2. Find "SinCity Child" → Activate
3. If not visible: Run `git push` (force redeploy) or check your repo structure
```

### 3.3 Verify Everything

```
1. After deployment: check WP Admin > Appearance > Themes → SinCity Child active
2. Check: SinCity Videos menu appears in admin sidebar
3. Check: Video Details meta box appears on Add New video page
4. Visit site → should show dark theme (no catastrophic errors)
```

---

## Step 4: Custom Domain + SSL

### 4.1 Add Domain in Render

```
1. Render Dashboard > sincity-web > Settings > Custom Domain
2. Click "Add Domain"
3. Enter: sincity.porn (or your domain)
4. Render gives you a DNS target like: sincity-web.onrender.com
```

### 4.2 Configure DNS at Cloudflare (or your DNS provider)

```
If using Cloudflare:
  Type: CNAME
  Name: @
  Target: sincity-web.onrender.com
  Proxy: DNS only (gray cloud)  ← NOT proxied, let Render handle SSL

If using DNS-only provider:
  Type: CNAME
  Name: www
  Target: sincity-web.onrender.com
  Type: CNAME (ALIAS/ANAME if root)
  Name: @
  Target: sincity-web.onrender.com
```

### 4.3 SSL Certificate

```
1. Render auto-provisions a Let's Encrypt certificate for your domain
2. Wait up to 30 min after DNS propagation
3. Visit: https://sincity.porn → should show lock icon
4. Update env vars:
   - WP_HOME: https://sincity.porn
   - WP_SITEURL: https://sincity.porn
   - FORCE_SSL: true
5. Trigger redeploy (Settings > Manual Deploy > Deploy)
```

---

## Step 5: Cron Jobs (Free Tier Workaround)

Render free tier does NOT include Cron Jobs. Use **cron-job.org** (free):

### 5.1 Set Up cron-job.org

```
1. Go to https://cron-job.org
2. Sign up (free)
3. Click "Create Cronjob"

   ───────────────────────────────────────
   Title:        SinCity — WP Cron Trigger
   URL:          https://sincity.porn/wp-cron.php
   Interval:     Every 15 minutes
   ───────────────────────────────────────

4. Save — it starts immediately

   ───────────────────────────────────────
   Title:        SinCity — Daily Import
   URL:          https://sincity.porn/wp-cron.php?import=1
   Interval:     Daily at 3:00 AM
   ───────────────────────────────────────

5. Create both cron jobs
```

### 5.2 Why This Works

WordPress has `define('DISABLE_WP_CRON', true)` set, which means page visits won't trigger cron. Instead, cron-job.org pings `wp-cron.php` every 15 minutes, which runs any scheduled tasks (imports, cleanup, etc.).

---

## Step 6: Content Import on Render

### 6.1 Import Strategy

Render free tier has:
- **Ephemeral storage**: Any files uploaded via WP admin are lost on restart
- **No background processing**: Long imports may timeout

**Recommended workflow:**

```
1. Prepare CSV locally (use imports/sample-import-30.csv as template)
2. Use WP All Import's "URL" or "File Upload" method
   - Upload CSV via WP admin → runs the import
   - All thumbnails are downloaded from source URLs (not stored locally)
3. Import in batches of 50-100 videos max per run
4. Use cron-job.org to trigger daily imports

⚠ Never upload files directly to wp-content/uploads via admin.
Use external URLs for thumbnails (they load from source CDNs).
```

### 6.2 Thumbnail Storage

Thumbnails for imported videos come from source site URLs. They are NOT stored on your server. The `thumbnail_url` in the CSV points to the source CDN. This means:

- No disk space used for thumbnails (saves the 1GB DB-only limit)
- Thumbnails load from source CDNs (potentially faster)
- If a source thumbnail is deleted, the video on your site shows a placeholder

**For custom thumbnails (own content):** Use Cloudflare R2 (free 10GB) by setting the R2 env vars.

---

## Step 7: Free Tier Optimization

### 7.1 Cold Start Mitigation

Render free web services spin down after 15 minutes of inactivity. On the first request after sleep, there's a 5-30 second delay.

**To mitigate:**
```
1. Use a free uptime monitor like UptimeRobot (https://uptimerobot.com)
2. Set it to ping your site every 10 minutes (keeps it warm)
3. UptimeRobot free tier: 50 monitors × 5-min checks
```

### 7.2 Database Connection Limits

Render PostgreSQL free tier allows:
- Max 15 concurrent connections
- 1GB total storage

**Optimize for this:**
- WP All Import imports in small batches (50-100) to avoid connection spikes
- Disable plugins that maintain persistent DB connections
- Keep the total video post count under 50,000

### 7.3 Bandwidth

Render free tier includes 750 hours/month (1 service running 24/7 = ~730h).
This is enough for a single web service. No additional bandwidth limits.

---

## Troubleshooting — Render-Specific

| Issue | Likely Cause | Fix |
|-------|-------------|-----|
| 502 Bad Gateway | PHP-FPM not starting | Check Render logs; verify `start.sh` has correct paths |
| White screen (no error) | PHP error masked | Set `WP_DEBUG=true` env var → redeploy → see error |
| "Error: No such file" | wp-config.php not generated | Check `start.sh` runs before Nginx; verify permissions |
| Database connection refused | Wrong DB_HOST | Use Render's *Internal* Host (not external) from DB dashboard |
| Uploads disappear after restart | Ephemeral storage (expected) | Use R2 for persistent uploads or commit files to Git |
| Permalinks broken | .htaccess not supported | Using Nginx (no .htaccess) — go to Settings > Permalinks > Save |
| 504 Gateway Timeout | Import too large | Reduce batch size to 50; increase `max_execution_time` env var |
| Plugin "Install Now" disabled | DISALLOW_FILE_MODS (expected) | Must commit plugins via Git |
| Email not sending | Render blocks SMTP port 25 | Use a transactional email service (SendGrid free tier) |
| Age gate not showing on first visit | Cold start → cache headers | Normal — will show on second visit; UptimeRobot helps |

---

## Migration Notes: VPS → Render

### Why Render over Self-Managed VPS

| Aspect | VPS (Vultr/Linode/DigitalOcean) | Render Free |
|--------|----------------------------------|-------------|
| Cost | $6-12/month minimum | $0 |
| Maintenance | Manual (OS updates, security, patches) | Automatic |
| SSL | Manual certbot | Auto Let's Encrypt |
| Scaling | Manual VPS upgrade | Upgradable to paid plans |
| Uptime | 100% (if configured well) | Spins down after 15 min idle |
| Storage | 25GB+ SSD | 1GB DB + ephemeral |
| Setup time | 4-8 hours | 30 min |
| Cron | Native crontab | External (cron-job.org) |

### What Changes

1. **No .htaccess** → All rules moved to `render/nginx/default.conf`
2. **No root SSH** → Everything via Git push + Render dashboard
3. **No local file persistence** → Uploads via URL or R2
4. **DB is PostgreSQL** (not MySQL) → PG4WP mu-plugin handles translation
5. **No WP-Cron** → cron-job.org external pings
6. **No WP admin plugin installs** → Must commit plugins to Git

### What Stays the Same

- Same child theme code (functions.php, templates, CSS)
- Same embed handler (sc_filter_embed works unchanged)
- Same age gate (age-gate.php works unchanged)
- Same ACF fields (defined programmatically)
- Same 3 categories (Normal, Hentai, JAV)
- Same SEO structure (Yoast XML sitemaps)
- Same sample CSV import format
