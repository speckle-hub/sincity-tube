# SinCity — Day 1-3 Execution Checklist (Ultra-Detailed)

**Scope**: Foundation setup, first batch content import, and basic testing on a live or staging server (or local).

---

## Day 1: Server & WordPress Foundation (~6-8 hours)

### 1.1 Domain & DNS Configuration

```
□ 1.1.1 Register domain at Namecheap (NOT GoDaddy — they drop adult domains).
       Domain suggestion: sincity.porn or sincityxxx.com
       Wait for activation (~5 min).

□ 1.1.2 Create Cloudflare account (free plan).
       Add your domain → Cloudflare scans DNS records.

□ 1.1.3 Update nameservers at Namecheap → point to Cloudflare's:
       ns1.cloudflare.com / ns2.cloudflare.com / etc.
       Wait for propagation (~5-30 min).

□ 1.1.4 In Cloudflare DNS: add A record pointing to your server IP:
       Type: A | Name: @ | Content: <your-server-ip> | Proxy: Proxied (orange cloud)
       Type: A | Name: www | Content: <your-server-ip> | Proxy: Proxied

□ 1.1.5 Verify: ping sincity.porn → resolves to Cloudflare IP
```

### 1.2 VPS Provisioning

```
□ 1.2.1 Choose provider:
       - KnownHost (adult-friendly, managed)
       - Koddos (adult-friendly, unmanaged)
       - Vultr / Linode (check TOS — adult allowed with compliance)
       Minimum specs: 4 vCPU, 8GB RAM, 200GB NVMe SSD

□ 1.2.2 Deploy Ubuntu 22.04 LTS.
       Wait for provisioning (~2-5 min).

□ 1.2.3 SSH into server:
       ssh root@<server-ip>
       (use password from provider, or set up SSH key)

□ 1.2.4 Initial server hardening:
  ```
  # Update packages
  sudo apt update && sudo apt upgrade -y

  # Create admin user (non-root)
  sudo adduser sincityadmin
  usermod -aG sudo sincityadmin

  # Copy SSH key for new user
  rsync --archive --chown=sincityadmin:sincityadmin ~/.ssh /home/sincityadmin/

  # Disable root SSH login
  sudo sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
  sudo systemctl restart sshd

  # Set hostname
  sudo hostnamectl set-hostname sincity

  # Install fail2ban
  sudo apt install fail2ban -y
  sudo systemctl enable fail2ban --now

  # Install UFW
  sudo apt install ufw -y
  sudo ufw allow 22/tcp
  sudo ufw allow 80/tcp
  sudo ufw allow 443/tcp
  sudo ufw --force enable
  ```
```

### 1.3 LEMP Stack Installation

```
□ 1.3.1 Install Nginx:
  ```
  sudo apt install nginx -y
  sudo systemctl enable nginx --now
  curl -I http://localhost  # Should return 200 OK
  ```

□ 1.3.2 Install MariaDB 10.11:
  ```
  sudo apt install mariadb-server -y
  sudo systemctl enable mariadb --now

  # Secure installation
  sudo mysql_secure_installation
  # Set root password, remove anonymous users, disallow root login remotely, remove test DB, reload privileges

  # Verify:
  sudo mysql -u root -p -e "SELECT VERSION();"
  ```

□ 1.3.3 Install PHP 8.2 + extensions:
  ```
  sudo apt install php8.2-fpm php8.2-mysql php8.2-curl php8.2-gd \
    php8.2-mbstring php8.2-xml php8.2-zip php8.2-redis php8.2-bcmath \
    php8.2-intl php8.2-imagick -y

  sudo systemctl enable php8.2-fpm --now

  # Tune PHP (edit /etc/php/8.2/fpm/php.ini):
  sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 64M/' /etc/php/8.2/fpm/php.ini
  sudo sed -i 's/post_max_size = 8M/post_max_size = 64M/' /etc/php/8.2/fpm/php.ini
  sudo sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini
  sudo sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.2/fpm/php.ini
  sudo sed -i 's/;max_input_vars = 1000/max_input_vars = 5000/' /etc/php/8.2/fpm/php.ini

  sudo systemctl restart php8.2-fpm
  ```

□ 1.3.4 Install Redis (object cache):
  ```
  sudo apt install redis-server -y
  sudo systemctl enable redis --now
  redis-cli ping  # Should return "PONG"
  ```

□ 1.3.5 MariaDB performance tuning (edit /etc/mysql/mariadb.conf.d/50-server.cnf):
  ```
  # Under [mysqld] section, add:
  # innodb_buffer_pool_size = 2G      (use 25% of RAM)
  # innodb_log_file_size    = 512M
  # query_cache_size        = 0
  # query_cache_type        = 0
  # max_connections         = 200

  sudo systemctl restart mariadb
  ```

### 1.4 SSL Certificate

```
□ 1.4.1 Generate Cloudflare Origin CA certificate:
       Cloudflare Dashboard > SSL/TLS > Origin Server > Create Certificate
       - Private key type: RSA (2048)
       - Hostnames: *.sincity.porn, sincity.porn
       - Validity: 15 years
       Copy the Origin Certificate (PEM) and Private Key content.

□ 1.4.2 Save cert files:
  ```
  sudo mkdir -p /etc/ssl/certs /etc/ssl/keys
  sudo nano /etc/ssl/certs/cloudflare-origin.pem    # paste cert
  sudo nano /etc/ssl/keys/cloudflare-origin-key.pem  # paste key
  sudo chmod 644 /etc/ssl/certs/cloudflare-origin.pem
  sudo chmod 600 /etc/ssl/keys/cloudflare-origin-key.pem
  ```

□ 1.4.3 Set Cloudflare SSL mode: Full (Strict)
       Cloudflare Dashboard > SSL/TLS > Overview > Full (Strict)
```

### 1.5 WordPress Installation

```
□ 1.5.1 Download and install:
  ```
  cd /var/www
  sudo wget https://wordpress.org/latest.tar.gz
  sudo tar -xzf latest.tar.gz
  sudo mv wordpress sincity
  sudo rm latest.tar.gz
  ```

□ 1.5.2 Set permissions:
  ```
  sudo chown -R www-data:www-data /var/www/sincity
  sudo find /var/www/sincity -type d -exec chmod 755 {} \;
  sudo find /var/www/sincity -type f -exec chmod 644 {} \;
  sudo chmod 600 /var/www/sincity/wp-config.php  # after creation
  ```

□ 1.5.3 Create database:
  ```
  sudo mysql -u root -p
  MariaDB> CREATE DATABASE sincity_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  MariaDB> CREATE USER 'sincity_user'@'localhost' IDENTIFIED BY 'GENERATE-A-STRONG-PASSWORD-HERE';
  MariaDB> GRANT ALL ON sincity_db.* TO 'sincity_user'@'localhost';
  MariaDB> FLUSH PRIVILEGES;
  MariaDB> EXIT;
  ```

□ 1.5.4 Configure wp-config.php:
  ```
  sudo -u www-data cp /var/www/sincity/wp-config-sample.php /var/www/sincity/wp-config.php
  sudo nano /var/www/sincity/wp-config.php
  ```
  Update:
  - DB_NAME: sincity_db
  - DB_USER: sincity_user
  - DB_PASSWORD: <generated-password>
  - DB_HOST: localhost

  Add these defines ABOVE the "stop editing" line:
  ```php
  define('WP_AUTO_UPDATE_CORE', false);
  define('DISALLOW_FILE_EDIT', true);
  define('DISABLE_WP_CRON', true);
  define('WP_MEMORY_LIMIT', '256M');
  define('WP_MAX_MEMORY_LIMIT', '512M');
  define('FORCE_SSL_ADMIN', true);
  define('WP_REDIS_HOST', '127.0.0.1');
  define('WP_REDIS_PORT', 6379);

  // Unique salts — generate at https://api.wordpress.org/secret-key/1.1/salt/
  // and paste the output here
  ```

### 1.6 Nginx Server Block

```
□ 1.6.1 Create Nginx site config:
       Copy the full content from code/security-nginx.conf to /etc/nginx/sites-available/sincity

□ 1.6.2 Update server_name in the config:
       server_name sincity.porn www.sincity.porn;

□ 1.6.3 Update root path:
       root /var/www/sincity;

□ 1.6.4 Enable site:
  ```
  sudo ln -s /etc/nginx/sites-available/sincity /etc/nginx/sites-enabled/
  sudo rm /etc/nginx/sites-enabled/default  # remove default
  sudo nginx -t                              # test config
  sudo systemctl reload nginx               # apply
  ```

□ 1.6.5 Verify site loads:
       Visit http://sincity.porn in browser.
       Expected: WordPress installation page (not 502, not 404).

### 1.7 Complete WordPress Installation (via Browser)

```
□ 1.7.1 Browse to https://sincity.porn (or http — will 301 to https if Cloudflare set)

□ 1.7.2 Select language → Continue

□ 1.7.3 Fill in:
       Site Title: SinCity
       Username: admin (or a secure custom name)
       Password: GENERATE A STRONG PASSWORD (use 1Password/Bitwarden)
       Email: admin@sincity.porn
       Search Engine Visibility: ☐ unchecked (we want indexing)

□ 1.7.4 Click "Install WordPress"
       Expected: "Success!" page with login button

□ 1.7.5 Log in to wp-admin

□ 1.7.6 Change permalinks:
       Settings > Permalinks > Custom Structure: /%postname%/
       Click "Save Changes"

□ 1.7.7 Delete default content:
       Delete default "Hello World" post, "Sample Page", default comment
```

### End of Day 1 — Success Check

```
[✓] Domain resolves to Cloudflare IP
[✓] HTTPS works (SSL valid, no warnings)
[✓] WordPress admin accessible at /wp-admin/
[✓] Nginx config applied without errors
[✓] Database created + user configured
[✓] Permalinks set to /%postname%/
[✓] UFW firewall enabled with correct ports
[✓] Root SSH login disabled
```

---

## Day 2: Theme, Plugins & First Import (~6-8 hours)

### 2.1 Install Kadence Parent Theme

```
□ 2.1.1 WP Admin > Appearance > Themes > Add New
       Search "Kadence" → Install → Activate

□ 2.1.2 Verify Kadence activated (you'll activate child theme next)
```

### 2.2 Upload & Activate SinCity Child Theme

```
□ 2.2.1 Archive the sincity-child folder:
       On your local machine (not server):
       cd sincity-child
       zip -r ../sincity-child.zip .

□ 2.2.2 Upload via WP Admin:
       WP Admin > Appearance > Themes > Add New > Upload Theme
       Choose sincity-child.zip → Install Now → Activate

□ 2.2.3 Verify: Appearance > Themes → "SinCity Child" is active

□ 2.2.4 Verify functions loaded — check browser console for no errors
```

### 2.3 Install Required Plugins

```
□ 2.3.1 WP Admin > Plugins > Add New

□ 2.3.2 Search, install & activate each (in order):

  1. "Advanced Custom Fields" (free) — REQUIRED for video meta
  2. "Yoast SEO" (free) — sitemaps, schema, titles
  3. "wpDiscuz" (free) — comment system
  4. "Wordfence Security" (free) — firewall + login protection
  5. "User Profile Builder" (free) — registration
  6. "CookieYes | GDPR Cookie Consent" (free) — legal compliance
  7. "Redis Object Cache" (free) — enable after activation
  8. "WP All Import" (free) — CSV import (use free for small batch)

□ 2.3.3 Configure Wordfence:
       Wordfence > Dashboard > "Click to Start Wordfence"
       Firewall: Learning Mode → wait 7 days → switch to Enabled
       Login Security: Enable 2FA for admin account

□ 2.3.4 Enable Redis cache:
       Plugins > Redis Object Cache > Enable Object Cache
       Expected: "Status: Connected"
```

### 2.4 Verify ACF Fields

```
□ 2.4.1 Go to SinCity Videos > Add New
       Expected: "Video Details" meta box below the content editor
       Expected fields: Embed URL, Embed Code, Duration, Source Site,
       External ID, View Count, Rating, Featured

□ 2.4.2 If not visible:
       - Confirm ACF plugin is active
       - Go to Custom Fields > Field Groups > check "Video Details" exists
       - Verify location rule: Post Type = sc_video
```

### 2.5 Seed Categories

```
□ 2.5.1 Trigger category seeding:
       Browse to: https://sincity.porn/?force_seed=1
       (or: WP Admin > SinCity Videos > Categories — check if they exist)

□ 2.5.2 Verify categories:
       Go to SinCity Videos > Categories
       Expected tree:
       ├── Normal Porn
       │   ├── Amateur, Professional, Lesbian, MILF, Teen, Anal, Gangbang, POV, Casting, VR
       ├── Hentai
       │   ├── 2D Animation, 3D CGI, Doujin, Game Hentai, Futanari, Tentacle, Vanilla, NTR, Yaoi / Yuri
       └── JAV
           ├── Uncensored, Censored, Idol Solo, Studio, Compilation, Amateur JAV, Classic
```

### 2.6 Test Embed Handler

```
□ 2.6.1 Create a test video manually:

  1. SinCity Videos > Add New
  2. Title: "TEST: Pornhub Embed — Amateur Couple"
  3. Embed URL: https://www.pornhub.com/view_video.php?viewkey=ph5e2b8e1b2c3d4
  4. Source Site: Pornhub (ph)
  5. Duration: 15:00
  6. Category: Normal Porn > Amateur
  7. Tag: test-embed
  8. Publish (Save Draft is fine)
  9. View the post at /video/test-pornhub-embed-amateur-couple/

□ 2.6.2 Expected on the video page:
   - Breadcrumbs: Home / Amateur / Title
   - Player shows the embedded video (no source ads)
   - Watermark "SINCITY" in bottom-left
   - Title, views (0), duration, source badge
   - Tags section shows "test-embed"
   - Related videos section (may be empty — no other videos yet)
   - Comments section (if enabled)

□ 2.6.3 Test additional sources (repeat for each):
   - XVideos: https://www.xvideos.com/video123456/example
   - xHamster: https://xhamster.com/videos/example-title-1234567
```

### 2.7 Run Small Import (10 videos)

```
□ 2.7.1 Download sample CSV:
       Use imports/sample-import-30.csv from the project files
       (or create a 10-row subset for a quick test)

□ 2.7.2 WP All Import import:
  1. WP Admin > All Import > New Import
  2. Upload CSV file
  3. Step 1: Choose "sc_video" as post type
  4. Step 2: Drag-and-drop map:
     - post_title → {post_title[1]}
     - post_content → {post_content[1]}
     - sc_category → Taxonomy sc_category (check "Create new terms")
     - sc_tag → Taxonomy sc_tag (check "Create new terms")
     - embed_url → Custom Field embed_url
     - duration → Custom Field duration
     - source_site → Custom Field source_site
     - thumbnail_url → Download & import as featured image
     - external_id → Custom Field external_id
  5. Step 3: Options
     - Post Status: Draft
     - Duplicate detection: By external_id
     - Save configuration
  6. Click "Run Import"

□ 2.7.3 Verify results:
   - Go to SinCity Videos > All Videos
   - Should show 10 new posts (Draft status)
   - Check thumbnails on list view (featured images)
   - Open 3 random posts → verify embed loads
   - Check category assignment
```

### 2.8 Age Gate Test

```
□ 2.8.1 Open incognito/private window → visit site
       Expected: age gate overlay (full-screen, dark, centered)
       Enter: 15 / June / 1990 → "I Am 18+"
       Expected: redirect to homepage, shows site content

□ 2.8.2 Refresh → should go straight to content (cookie works)

□ 2.8.3 Clear cookies → shows gate again

□ 2.8.4 Underage test: enter 1 / Jan / 2015
       Expected: "ACCESS DENIED" → redirects to Google after 5s
```

### End of Day 2 — Success Check

```
[✓] Child theme active, no PHP errors
[✓] All required plugins installed & configured
[✓] ACF fields visible on video post type
[✓] Categories seeded correctly (Normal, Hentai, JAV + subs)
[✓] At least 1 embed plays correctly from Pornhub
[✓] At least 1 embed from XVideos plays correctly
[✓] At least 1 embed from xHamster plays correctly
[✓] 10 test videos imported via CSV (all with thumbnails)
[✓] Age gate: 18+ enters, underage redirects
[✓] Cookie persists on refresh
```

---

## Day 3: Content Scaling & Basic Optimization (~6-8 hours)

### 3.1 Full Import Batch (50-100 videos)

```
□ 3.1.1 Prepare a larger CSV with 50-100 entries (use sample CSV as template).
       Mix categories: 40% Normal, 30% Hentai, 30% JAV.

□ 3.1.2 WP All Import: upload and run.
       Expected: posts created, duplicates skipped.

□ 3.1.3 After import, bulk edit to publish:
       WP Admin > SinCity Videos > All Videos
       Select all (checkbox) → Bulk Actions: Edit → Apply
       Set Status: Published → Update
```

### 3.2 Verify Site Performance

```
□ 3.2.1 GTmetrix test (https://gtmetrix.com):
       Enter your URL → Test
       Expected: Load time < 4s (first visit), < 2s (cached)

□ 3.2.2 PageSpeed Insights (https://pagespeed.web.dev):
       Enter URL → Analyze
       Expected: Mobile > 65, Desktop > 80

□ 3.2.3 Review and fix if below targets:
   - Enable WP Rocket (if installed)
   - Enable Cloudflare cache
   - Check for large images → resize/compress
   - Verify lazy loading works (embeds should not load on page load)
```

### 3.3 Mobile Testing

```
□ 3.3.1 Chrome DevTools > Device Toolbar (Ctrl+Shift+M)
       Select "iPhone 14" (390x844)

□ 3.3.2 Check each page:
   - Homepage: hero scales, category cards stack, video grid is 2 columns
   - Video page: player full-width, meta text wraps, buttons stack
   - Category page: grid adapts, filters readable
   - Age gate: full-screen, dropdowns tappable, button visible

□ 3.3.3 Check navigation:
   - Hamburger menu shows (no desktop nav links)
   - Tapping hamburger opens menu
   - Search input is usable on small screen
```

### 3.4 Yoast SEO Configuration

```
□ 3.4.1 WP Admin > Yoast SEO > Settings

□ 3.4.2 General > Site representation:
   - Organization or Person: Organization
   - Name: SinCity
   - Logo: upload your SinCity logo

□ 3.4.3 Content Types > sc_video:
   - Title template: "%%title%% %%sep%% SinCity"
   - Meta description template: "Watch %%title%% on SinCity. Free HD streaming. %%excerpt%%"
   - Schema: VideoObject (automatic)
   - Show in search results: Yes

□ 3.4.4 Taxonomies:
   - sc_category: Show in search results: Yes
   - sc_tag: Show in search results: Yes
   - Generate separate sitemap: Yes

□ 3.4.5 XML Sitemaps:
   - Enable: Yes
   - Visit https://sincity.porn/sitemap_index.xml → should show pages and videos

□ 3.4.6 Submit to Google Search Console:
   https://search.google.com/search-console
   Add property → URL prefix: https://sincity.porn
   Verify via DNS TXT record (add record in Cloudflare) or HTML tag
   Submit sitemap URL
```

### 3.5 Legal Pages (Basic)

```
□ 3.5.1 Create /dmca/ page:
   Content: Full DMCA takedown policy (use template from legal resources)
   Include: designated agent name, email, physical address, procedure

□ 3.5.2 Create /privacy-policy/ page:
   Content: GDPR + CCPA compliant privacy policy
   Cover: what data is collected, cookies, third-party sharing, rights

□ 3.5.3 Create /2257/ page:
   Content: 18 U.S.C. §2257 record-keeping compliance statement
   Include: custodian of records name and address

□ 3.5.4 Create /terms/ page:
   Content: Terms of Service — age requirement, prohibited content,
   limitation of liability, jurisdiction

□ 3.5.5 Add legal links to footer:
   Verify footer template includes links to all legal pages
```

### 3.6 Final Verification

```
□ 3.6.1 Full user flow (3x — once per category):
   - Visit homepage → click category → browse videos → filter
   - Click video → watch embed → check related → check tags

□ 3.6.2 Full mobile flow (same as above on 375px viewport)

□ 3.6.3 Check all template files render:
   - / (homepage)
   - /category/normal/ (and subcats)
   - /category/hentai/ (and subcats)
   - /category/jav/ (and subcats)
   - /video/{slug} (single video)
   - /trending/
   - /tag/{tagname}/
   - /search/?s=test
   - /dmca/, /privacy-policy/, /2257/, /terms/
```

### 3.7 Nightly Import Cron Setup

```
□ 3.7.1 Add server cron jobs (sudo crontab -e):
  ```
  # SinCity imports — daily at 3 AM and 3:30 AM
  0 3 * * * /usr/bin/curl -s -o /dev/null "https://sincity.porn/wp-cron.php?import=1"
  30 3 * * * /usr/bin/curl -s -o /dev/null "https://sincity.porn/wp-cron.php?import=2"

  # Weekly database optimization — Sunday 6 AM
  0 6 * * 0 /usr/bin/php /var/www/sincity/wp-content/plugins/wordfence/waf.php?optimize=1 >/dev/null 2>&1
  ```

□ 3.7.2 Verify cron runs (check next day for new posts)
```

### End of Day 3 — Success Check

```
[✓] 50-100+ videos imported, published, and viewable
[✓] Site loads in < 4s (GTmetrix/PageSpeed)
[✓] Mobile responsive on 375px viewport (all pages)
[✓] Yoast SEO configured with title/meta templates
[✓] XML sitemap accessible and submitted to Google
[✓] Legal pages (DMCA, Privacy, 2257, Terms) published
[✓] Age gate works on all pages
[✓] Embed handler works for PH, XV, XH
[✓] Cron job set up for daily imports
[✓] User registration + comments configured
```

---

## Troubleshooting Commands Reference

```bash
# Check PHP errors in real-time
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.2-fpm.log

# Check WordPress debug log (if WP_DEBUG enabled in wp-config)
tail -f /var/www/sincity/wp-content/debug.log

# Check if services are running
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mariadb
sudo systemctl status redis-server

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

# Check disk space
df -h

# Check memory usage
free -m
top -bn1 | head -20

# Test PHP execution
echo "<?php phpinfo(); ?>" | sudo tee /var/www/sincity/test.php
# Visit https://sincity.porn/test.php → remove after testing

# Flush Redis cache
redis-cli FLUSHALL

# Flush WP Rocket cache
wp rocket clean --confirm  # if WP-CLI available
```

**Next logical milestone**: Staging server setup with full 600-video import strategy, real ad network integration, and submission to adult search directories.
