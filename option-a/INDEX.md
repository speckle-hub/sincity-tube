# SinCity — Option A: Render PHP Web Service (No Docker)

## What's in this folder

This is the complete repo structure for deploying SinCity as a **Render PHP Web Service**. No Docker needed. Uses **MySQL via PlanetScale** (not PostgreSQL).

```
sincity/
├── composer.json            ← Minimal, just for Render PHP detection
├── wp-config.php            ← Reads all config from environment variables
├── .gitignore
├── index.php                ← WordPress core (download from wordpress.org)
├── wp-*.php                 ← All WordPress core files
├── wp-admin/
├── wp-includes/
├── wp-content/
│   ├── themes/
│   │   └── sincity-child/   ← Full child theme (dark cyberpunk) — INCLUDED
│   └── plugins/             ← Add plugins here before push (see below)
└── imports/
    └── sample-import-30.csv ← 30 ready-to-import video rows
```

**No mu-plugins needed.** WordPress uses MySQL natively — no PG4WP compatibility layer.

## How to set up (~30 minutes)

### 1. Get WordPress files
```bash
# Download WordPress
wget https://wordpress.org/latest.zip
unzip latest.zip
# Move ALL files from wordpress/ into this folder
# (index.php, wp-admin/, wp-includes/, wp-content/, etc.)
# Then remove stock wp-content and replace with ours:
rm -rf wp-content
# Now copy/move our wp-content/ tree into place
```

### 2. Add required plugins
```bash
cd wp-content/plugins/
# Download these plugin ZIPs and extract into folders:
# - advanced-custom-fields (ACF Pro or free)
# - wordpress-seo (Yoast SEO)
# - wp-all-import (Pro for CSV import)
# - cookie-law-info (GDPR cookie consent)
# - profile-builder (age gate user management — optional)
# Then delete all ZIPs
```

### 3. Push to GitHub
```bash
git init
git add .
git commit -m "SinCity on Render PHP + MySQL"
git remote add origin https://github.com/YOUR_USERNAME/sincity.git
git branch -M main
git push -u origin main
```

### 4. Create PlanetScale database
1. Sign up at [planetscale.com](https://planetscale.com) (free tier)
2. Create a database named `sincity`
3. Go to Settings → **Safe migrations: OFF** (required for WordPress)
4. Click "Connect" → copy your database credentials

### 5. Deploy to Render
1. Go to [dashboard.render.com](https://dashboard.render.com) → New Web Service
2. Connect your GitHub repo
3. Settings:
   - **Runtime**: PHP (Render auto-detects from composer.json)
   - **Build Command**: `composer install`
   - **Start Command**: (leave blank — Render uses default)
   - **Plan**: Free
4. Add these **Environment Variables** from PlanetScale:
   - `DB_NAME` = `sincity`
   - `DB_USER` = (from PlanetScale)
   - `DB_PASSWORD` = (from PlanetScale)
   - `DB_HOST` = (from PlanetScale — e.g. `aws.connect.psdb.cloud`)
   - `WP_HOME` = `https://sincity.onrender.com`
   - `WP_SITEURL` = `https://sincity.onrender.com`
   - `FORCE_SSL` = `true`
   - `DISABLE_WP_CRON` = `true`
   - All 8 WP salts (generate at https://api.wordpress.org/secret-key/1.1/salt/)
5. Click **Create Web Service**

### 6. Run WordPress installer
1. Visit `https://sincity.onrender.com`
2. Run the 5-minute WordPress installer
3. Activate the **SinCity Child** theme
4. Install & activate all plugins

### 7. Import sample data
1. Install WP All Import Pro
2. Go to WP All Import → New Import
3. Upload `imports/sample-import-30.csv`
4. Map columns:
   - `title` → post title
   - `content` → post content
   - `slug` → post slug
   - `sc_category` → taxonomy `sc_category`
   - `sc_tag` → taxonomy `sc_tag` (explode by comma)
   - `embed_url` → ACF field `embed_url`
   - `duration` → ACF field `duration`
   - `source_site` → ACF field `source_site`
   - `external_id` → ACF field `external_id`
   - `views_count` → ACF field `views_count`
   - `featured` → ACF field `featured`
5. Run import

### 8. Set up cron-job.org
1. Go to [cron-job.org](https://cron-job.org) → New Cron Job
2. URL: `https://sincity.onrender.com/wp-cron.php`
3. Interval: Every 15 minutes
4. Save

### 9. Verify everything works
- Browse each category (Normal / Hentai / JAV) — videos load
- Click a video — embed player renders
- Open in incognito — age gate appears
- Check mobile layout
- Check cold start (wait 15 min, then visit)

---

## Files Reference

| File | Purpose |
|------|---------|
| `composer.json` | Makes Render detect PHP 8.2; runs `composer install` |
| `wp-config.php` | Reads DB + secrets from env vars |
| `.gitignore` | Excludes uploads, cache, logs, .env, vendor/ |
| `wp-content/themes/sincity-child/` | Full theme: dark cyber-noir, age gate, embeds, shortcodes, ACF |
| `imports/sample-import-30.csv` | 30 video entries (10 Normal + 10 Hentai + 10 JAV) |

## Cost Breakdown

| Service | Plan | Cost |
|---------|------|------|
| Render Web Service | Free (512MB RAM, 1 CPU) | $0 |
| PlanetScale MySQL | Free (1GB storage) | $0 |
| cron-job.org | Free (60 jobs) | $0 |
| UptimeRobot | Free (5 monitors × 5 min) | $0 |
| **TOTAL** | | **$0.00/month** |

## Troubleshooting

**500 Error on first load:** Render needs ~30s to cold-boot PHP. Wait and refresh.

**Database connection refused:** Check PlanetScale → Safe migrations is OFF. Verify DB_HOST matches PlanetScale's connection string.

**Plugins not showing:** Render free tier has `DISALLOW_FILE_MODS=true`. Must commit plugins to Git before deploy.

**Age gate not showing:** Check the `sc_age_gate_check` function is not being bypassed by caching. Flush permalinks.

**Embeds not loading:** Iframe src may be blocked by browser. Check browser console for mixed content warnings.
