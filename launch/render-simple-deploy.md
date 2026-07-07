# SinCity — Option A: Render PHP Web Service Deployment

**Level**: Beginner-friendly. No Docker. No YAML. Just files + a few clicks.

---

## Overview

You will:
1. Download WordPress
2. Drop in the SinCity child theme + plugins
3. Set up a free MySQL database on PlanetScale
4. Push to GitHub
5. Connect to Render → site is live in 2 minutes
6. Run the WordPress 5-minute installer

**Total time**: ~30 minutes.

---

## Step 1: Download WordPress (5 min)

```bash
# Create your project folder
mkdir sincity
cd sincity

# Download WordPress
curl -L https://wordpress.org/latest.zip -o wp.zip

# Unzip
unzip -q wp.zip
mv wordpress/* .
rmdir wordpress
rm wp.zip

# Remove the default wp-content (we'll replace it)
rm -rf wp-content
```

## Step 2: Copy the SinCity Files (5 min)

```bash
# Copy the child theme
cp -r ../option-a/wp-content/wp-content/themes/sincity-child wp-content/themes/

# Copy the mu-plugins
cp -r ../option-a/wp-content/wp-content/mu-plugins wp-content/

# Copy composer.json
cp ../option-a/composer.json .

# Copy wp-config.php (this reads from env vars)
cp ../option-a/wp-config.php .

# Copy .gitignore
cp ../option-a/.gitignore .
```

Your folder should now look like:

```
sincity/
├── composer.json        ← Minimal, tells Render "this is PHP"
├── wp-config.php        ← Reads DB/salts from env vars
├── index.php            ← WordPress entry
├── wp-admin/            ← WordPress admin
├── wp-includes/         ← WordPress core
├── wp-content/
│   ├── themes/
│   │   └── sincity-child/   ← Dark cyberpunk theme
│   ├── plugins/              ← (empty — add in Step 3)
│   └── mu-plugins/           ← PG4WP & R2 handlers
├── wp-*.php             ← Other WordPress files
└── .gitignore
```

## Step 3: Add Plugins (5 min)

**Important**: You MUST add plugins BEFORE pushing to GitHub. Render's free tier DISALLOWS plugin installs from the admin dashboard.

```bash
cd wp-content/plugins

# List of plugins to download
PLUGINS=(
  "https://downloads.wordpress.org/plugin/advanced-custom-fields.latest-stable.zip"
  "https://downloads.wordpress.org/plugin/wordpress-seo.latest-stable.zip"
  "https://downloads.wordpress.org/plugin/wpdiscuz.latest-stable.zip"
  "https://downloads.wordpress.org/plugin/wp-all-import.latest-stable.zip"
  "https://downloads.wordpress.org/plugin/cookie-law-info.latest-stable.zip"
  "https://downloads.wordpress.org/plugin/profile-builder.latest-stable.zip"
)

# Download and extract each
for url in "${PLUGINS[@]}"; do
  curl -L "$url" -o plugin.zip
  unzip -q plugin.zip
  rm plugin.zip
done

cd ../..
```

## Step 4: Set Up Free MySQL Database (PlanetScale) — 5 min

WordPress needs MySQL. Render only provides PostgreSQL on free tier, so we use **PlanetScale** for a free MySQL database.

```
1. Go to https://planetscale.com
2. Sign up (GitHub login works)
3. Click "Create database"
   - Name: sincity
   - Region: us-east (closest to Render Oregon)
   - Plan: Free
4. Click "Create"
5. Wait ~30 seconds for provisioning
6. Click "Connect" → "Connect with" → "PHP"

   Copy these values:
   Host:     us-east.connect.psdb.cloud
   Username: xxxxxxxxxxxxxx
   Password: yyyyyyyyyyyyyy
   Database: sincity
   Port:     3306

7. IMPORTANT: Click "Branches" → main branch → "Add password"
   This generates your DB password. COPY IT NOW (you won't see it again).

8. Click "Settings" → enable "Safe migrations" = OFF
   (Render's WordPress needs direct table access)

⚠ If you prefer everything under one account, use Render PostgreSQL +
   PG4WP (our mu-plugin handles it). Set DB_DRIVER=pgsql as env var.
   But MySQL/PlanetScale is simpler and more compatible.
```

## Step 5: Push to GitHub (2 min)

```bash
# Create GitHub repo at https://github.com/new → name: sincity
# DON'T initialize with README or .gitignore

git init
git add .
git commit -m "SinCity on Render PHP Web Service"
git remote add origin https://github.com/YOUR_USERNAME/sincity.git
git branch -M main
git push -u origin main
```

## Step 6: Deploy to Render (5 min)

```
1. Go to https://dashboard.render.com
2. Click "New +" → "Web Service"
3. Connect Your GitHub account → select "sincity" repo
4. Fill in the form:

   ┌─────────────────────────────────────────────┐
   │ Name:               sincity-web              │
   │ Region:             Oregon                    │
   │ Branch:             main                      │
   │ Runtime:            PHP                       │  ← IMPORTANT: NOT Docker
   │ Build Command:      composer install          │
   │ Start Command:      (leave blank)             │
   │ Plan:               Free                      │
   └─────────────────────────────────────────────┘

5. Click "Advanced" → "Add Environment Variables"
   Add ALL of these:

   Key                 Value
   ───────────────────────────────────
   DB_HOST             us-east.connect.psdb.cloud
   DB_NAME             sincity
   DB_USER             <PlanetScale username>
   DB_PASSWORD         <PlanetScale password>
   WP_HOME             https://sincity.onrender.com
   WP_SITEURL          https://sincity.onrender.com
   FORCE_SSL           false
   DISABLE_WP_CRON     true
   WP_DEBUG            false
   WP_TABLE_PREFIX     sc_

   # Salts — generate at https://api.wordpress.org/secret-key/1.1/salt/
   # Add all 8 salt values
   AUTH_KEY            ...
   SECURE_AUTH_KEY     ...
   LOGGED_IN_KEY       ...
   NONCE_KEY           ...
   AUTH_SALT           ...
   SECURE_AUTH_SALT    ...
   LOGGED_IN_SALT      ...
   NONCE_SALT          ...

6. Click "Create Web Service"

7. Wait ~30 seconds for the build.
   Render detects PHP from composer.json, runs composer install (no-op),
   and starts Nginx + PHP-FPM automatically.
```

## Step 7: WordPress Installation (2 min)

```
1. Visit: https://sincity.onrender.com
   ⚠ First visit may take 3-8 seconds (cold start)

2. You should see the WordPress language selector.
   Select your language → "Continue"

3. Fill in:
   Site Title:   SinCity
   Username:     admin
   Password:     <GENERATE A STRONG PASSWORD>
   Email:        you@example.com

4. "Install WordPress"

5. Log in at /wp-admin/

6. Settings → Permalinks → "Post name" → Save

7. Delete default content:
   - Posts: "Hello World"
   - Pages: "Sample Page"
   - Comment: (the default one)
```

## Step 8: Activate Theme & Plugins (2 min)

```
1. Appearance → Themes → "SinCity Child" → Activate
2. Plugins → Installed Plugins
   Activate all:
   [x] Advanced Custom Fields
   [x] Yoast SEO
   [x] wpDiscuz
   [x] WP All Import
   [x] CookieYes
   [x] Profile Builder
```

## Step 9: Set Up Cron Jobs (2 min)

```
1. Go to https://cron-job.org → Sign up (free)
2. Create Cronjob #1:
   Title:     SinCity WP-Cron
   URL:       https://sincity.onrender.com/wp-cron.php
   Interval:  Every 15 minutes
   Save

3. Create Cronjob #2 (for daily imports later):
   Title:     SinCity Daily Import
   URL:       https://sincity.onrender.com/wp-cron.php?import=1
   Interval:  Daily at 3:00 AM
   Save
```

## Step 10: Custom Domain (Optional — 5 min)

```
1. Render Dashboard → sincity-web → Settings → Custom Domain
2. Add: sincity.porn (your domain)
3. Copy the DNS target: sincity-web.onrender.com
4. At your DNS provider:
   CNAME  @  →  sincity-web.onrender.com
   CNAME  www →  sincity-web.onrender.com
5. Wait for DNS propagation (5 min - 1 hour)
6. Update env vars:
   WP_HOME    → https://sincity.porn
   WP_SITEURL → https://sincity.porn
   FORCE_SSL  → true
7. Manual Deploy → Deploy (to apply new env vars)
```

## Step 11: Keep It Warm (Optional but Recommended)

Render free tier services spin down after 15 min of inactivity.

```
1. Go to https://uptimerobot.com → Sign up (free)
2. Add Monitor:
   Name:     SinCity
   URL:      https://sincity.onrender.com
   Interval: Every 10 minutes
   Save
```

The 10-minute ping keeps the service warm. First visit after idle will still be ~2-4s instead of 10-20s.

## Step 12: Import Your First Vídeos

```
1. WP Admin → All Import → New Import
2. Upload the file: imports/sample-import-30.csv
3. Map fields (or use saved template after first import)
4. Run import → 30 videos created
5. Visit /category/normal/ → should show 10 videos
6. Visit /category/hentai/ → should show 10 videos
7. Visit /category/jav/ → should show 10 videos
```

---

## Done! Your Site Is Live 🎉

```
Homepage:       https://sincity.onrender.com
Admin:          https://sincity.onrender.com/wp-admin/
Age Gate:       Visit in incognito → shows 18+ verification
Video Player:   Click any video → embed plays (no ads)
Three Sections: Normal | Hentai | JAV
Dark Theme:     Crimson + cyan cyber-noir
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| "Error establishing database connection" | Check DB_HOST, DB_NAME, DB_USER, DB_PASSWORD env vars in Render dashboard |
| White screen, no error | Set WP_DEBUG=true → redeploy → see the error |
| 404 on video pages | Go to Settings → Permalinks → "Post name" → Save |
| Can't install plugins | Normal on free tier — must commit to wp-content/plugins/ in Git |
| Site very slow first visit | Normal (cold start). Add UptimeRobot to keep it warm |
| "Sorry, you are not allowed to access this site." | Check FORCE_SSL setting — must match http/https of your URL |
