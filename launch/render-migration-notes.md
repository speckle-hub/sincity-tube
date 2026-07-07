# SinCity — Vercel → Render Migration Notes & Comparison

---

## Why Render Won Over Vercel for This Project

The original plan was to use Vercel, but after analysis, **Render is the correct choice** for a PHP/WordPress-based adult tube site.

| Feature | Vercel | Render |
|---------|--------|--------|
| **PHP support** | ❌ No native PHP (Serverless Functions only — Node.js, Python, Go, Ruby) | ✅ Native PHP via Docker (any runtime) |
| **WordPress** | ❌ Impossible without headless + external WP hosting | ✅ Full WordPress in Docker container |
| **Database** | ❌ No database service (external only) | ✅ Free PostgreSQL (1GB) included |
| **Free tier** | ✅ Generous (100GB bandwidth, 6000 build min) | ✅ 512MB RAM, 750h/mo, free DB |
| **Cold start** | ~1-2 sec (serverless) | ~5-30 sec (container) |
| **Disk persistence** | ✅ `/tmp` writable (but ephemeral) | ❌ Ephemeral (same as Vercel) |
| **Custom domain** | ✅ Free + auto SSL | ✅ Free + auto SSL |
| **Cron jobs** | ✅ via Vercel Cron (3/day on Hobby) | ❌ Not on free tier (use cron-job.org) |
| **Adult content TOS** | ⚠️ Gray area — Vercel may suspend | ⚠️ Gray area — Render TOS allows legal content |
| **File-based CMS** | ❌ Static site generator needed | ✅ WordPress works natively |

**Verdict**: Vercel is great for static sites and Node.js apps. Render is the only practical free option for WordPress.

---

## Architecture Change Summary

### Before (VPS/Bare Metal)

```
Browser → Cloudflare → Nginx → PHP-FPM → MySQL (on same server)
                           ↕
                     wp-content/ (persistent SSD)
```

### After (Render)

```
Browser → Cloudflare → Render Web Service (Docker)
                           │
                           ├── Nginx (reverse proxy)
                           ├── PHP-FPM 8.2
                           └── WordPress (in container)
                           │
                     Render PostgreSQL (separate service, free tier)
```

---

## What Had to Change

### 1. Database: MySQL → PostgreSQL

```
Before: MySQL 8.0 / MariaDB 10.11
After:  PostgreSQL 16 (Render free tier)

Changes needed:
  ✅ PG4WP mu-plugin (handles SQL translation)
  ✅ DB_HOST now comes from Render (not localhost)
  ✅ No ALTER TABLE / ENGINE=InnoDB in custom SQL
  ✅ WP All Import works with PostgreSQL through PG4WP
```

### 2. File Storage: Local → External URLs

```
Before: Files stored on server SSD (/var/www/html/wp-content/uploads/)
After:  Thumbnails loaded from external source URLs

Changes needed:
  ✅ All CSV thumbnail_urls use full URLs (not local paths)
  ✅ Theme handles missing thumbnails with CSS placeholder
  ✅ Optional: Cloudflare R2 for custom uploads (free 10GB)
```

### 3. Process Management: systemd → Docker

```
Before: systemctl start nginx / systemctl start php-fpm
After:  Docker container with Supervisor (or start.sh script)

Changes needed:
  ✅ render/supervisor/supervisord.conf manages both services
  ✅ render/scripts/start.sh generates wp-config.php from env vars
```

### 4. Configuration: Files → Environment Variables

```
Before: wp-config.php edited directly on server
After:  wp-config.php generated from Render env vars on boot

Changes needed:
  ✅ render/scripts/start.sh writes wp-config.php using getenv()
  ✅ All secrets (DB password, salts) passed as env vars
  ✅ No hardcoded config in the repo
```

### 5. Deployment: SFTP/SSH → Git Push

```
Before: scp file to server, edit wp-config.php via SSH
After:  git push → Render auto-builds + deploys

Changes needed:
  ✅ All code changes must go through Git
  ✅ Plugins must be committed to wp-content/plugins/
  ✅ Theme updates are git push + wait for deploy
```

### 6. Cron Jobs: crontab → cron-job.org

```
Before: sudo crontab -e (runs on server)
After:  https://cron-job.org (external HTTP pings)

Changes needed:
  ✅ DISABLE_WP_CRON=true in env vars
  ✅ WP-Cron triggered by external HTTP requests to /wp-cron.php
  ✅ Daily imports run via cron-job.org → WP All Import cron URL
```

### 7. Security: nginx + .htaccess → Only Nginx

```
Before:
  - Nginx config + .htaccess (Apache fallback)
  - IP whitelist for wp-admin
  - fail2ban for brute force

After:
  - Only Nginx (no .htaccess — handled in Dockerfile)
  - Wordfence for WAF (works in Docker)
  - IP filtering via Cloudflare WAF (free tier)
  - No fail2ban (not needed — no SSH access)

Changes needed:
  ✅ All .htaccess rules moved to render/nginx/default.conf
  ✅ .htaccess file removed from repo (not supported by Nginx)
```

---

## File Changes Checklist

### Files Kept (No Changes Needed)

```
sincity-child/functions.php          ← Same CPT, ACF, embed handler code
sincity-child/age-gate.php           ← Same age gate
sincity-child/single-sc_video.php    ← Same template
sincity-child/page-trending.php      ← Same template
sincity-child/assets/css/main.css    ← Same CSS
sincity-child/assets/js/player.js    ← Same JS
```

### Files Modified

```
sincity-child/functions.php          ← Added: require render-compatibility.php
sincity-child/render-compatibility.php ← NEW: Render env detection, R2, health API
wp-content/mu-plugins/00-pg4wp-loader.php ← NEW: PostgreSQL support
wp-content/mu-plugins/01-r2-uploads.php   ← NEW: Cloudflare R2 uploads
```

### Files Created (New)

```
.gitignore                         ← Ignore uploads, config, logs
render/Dockerfile                  ← Container definition
render/nginx/default.conf          ← Nginx for Docker
render/php/opcache.ini             ← PHP performance
render/php/php.ini                 ← PHP config
render/supervisor/supervisord.conf ← Process manager
render/scripts/start.sh            ← Boot script
render/scripts/healthz.php         ← Health check
render/render.yaml                 ← Blueprint (optional)
render/.env.example                ← Env var reference
```

### Files Removed (No Longer Needed)

```
code/security-htaccess.conf          ← Nginx only (no Apache)
docs/01-site-architecture.md         ← Still useful as reference (not removed)
(Other docs kept for reference)
```

---

## Effort Summary

| Task | Effort | Risk |
|------|--------|------|
| Dockerfile creation | Low | Low — standard PHP+Nginx Docker |
| PostgreSQL via PG4WP | Medium | Medium — some WP features may behave differently |
| Env var config | Low | Low — standard 12-factor app pattern |
| Uploads strategy | Medium | Low — using external URLs avoids issue entirely |
| Git deployment switch | Low | Low — simpler than managing SSH |
| Cron migration | Low | Low — cron-job.org is reliable free service |
| **Total rewrite** | **~4-6 hours** | **Low risk** — core app logic unchanged |

---

## What to Test After Migration

1. **Can you install WordPress?** — DB connection via env vars
2. **Can you activate child theme?** — Theme files in repo
3. **Do embeds work?** — Test PH, XV, XH embed URLs
4. **Does age gate work?** — Cookie + DOB validation
5. **Does CSV import work?** — WP All Import with 10 rows
6. **Does cron work?** — cron-job.org pings wp-cron.php
7. **Do uploads persist?** — Test with R2 (or verify external URL thumbnails work)
8. **Does cold start work?** — Wait >15 min idle, then visit
9. **Does custom domain work?** — SSL Let's Encrypt auto-provision
10. **Are all legal pages accessible?** — DMCA, Privacy, 2257, Terms

---

## Next Immediate Milestone

**Create Render services + deploy the theme**:

1. Push the complete repo to GitHub (with all Render files)
2. Create Render PostgreSQL database
3. Create Render Web Service (Docker) connected to the repo
4. Run WordPress installation via browser
5. Verify child theme activates correctly
6. Run the 30-video CSV import test
7. Set up cron-job.org for WP-Cron
8. Configure custom domain (sincity.porn)
9. Submit sitemap to Google Search Console
10. Launch with UptimeRobot monitoring
