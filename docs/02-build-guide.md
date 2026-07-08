# SinCity — Step-by-Step Build Guide

## Phase 1: Foundation (Week 1-2)

### 1.1 Domain & Hosting

- **Domain**: Register `sincity.porn` or `sincityxxx.com` via Namecheap (adult-friendly) — *do NOT use GoDaddy, they drop adult domains*
- **Hosting**: Start with KnownHost (adult-friendly VPS) or Koddos
  - Initial: 4 vCPU, 8GB RAM, 200GB SSD NVMe
  - Scale: Upgrade as traffic grows
- **Cloudflare**: Free plan for DNS + DDoS protection (proxied mode)
- **SSL**: Cloudflare Origin CA + Edge SSL (free)

### 1.2 Server Setup

```bash
# Ubuntu 22.04 LEMP Stack (with PostgreSQL support)
sudo apt update && sudo apt upgrade -y
sudo apt install nginx php8.2-fpm php8.2-pgsql \
  php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip \
  php8.2-redis redis-server -y

# PHP Config (php.ini)
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
memory_limit = 512M
```

### 1.3 WordPress Installation

```bash
# Download WP
cd /var/www
sudo wget https://wordpress.org/latest.tar.gz
sudo tar -xzf latest.tar.gz
sudo mv wordpress sincity
sudo chown -R www-data:www-data sincity/
sudo chmod -R 755 sincity/

# Database (Supabase PostgreSQL)

**How to find your connection details in Supabase:**
1. Go to https://supabase.com and create a new project. Keep the database password safe; you will need it later. Wait a few minutes for the database to provision.
2. At the very top of your Supabase dashboard, click the green **"Connect"** button.
3. In the modal that pops up, look at the **Connection string** tab. Select the **URI** or **Parameters** option.
   - *Host*: Looks like `aws-0-eu-central-1.pooler.supabase.com` or `db.xxxxxxxxxxxxxx.supabase.co`
   - *Port*: Usually `6543` (for connection pooling) or `5432` (for direct connection). Use `5432` for WordPress.
   - *User*: Usually `postgres.xxxxxxxxxxxxxx` or `postgres`
   - *Password*: This is the database password you created in step 1.

**Render Environment Variables:**
Add the following to your Render dashboard (Environment tab) or your `.env` file:
- `DB_HOST`: *(Paste the Host from Supabase)*
- `DB_PORT`: `5432`
- `DB_NAME`: `postgres`
- `DB_USER`: *(Paste the User from Supabase)*
- `DB_PASSWORD`: *(Type the password you created in Step 1)*
```

### 1.4 Nginx Config

```nginx
server {
    listen 443 ssl http2;
    server_name sincity.porn www.sincity.porn;

    root /var/www/sincity;
    index index.php;

    # SSL (Cloudflare origin certs)
    ssl_certificate /etc/ssl/certs/cloudflare-origin.pem;
    ssl_certificate_key /etc/ssl/keys/cloudflare-origin-key.pem;

    # WP Rocket cache
    set $cache_uri $request_uri;
    include /var/www/sincity/wp-content/plugins/wp-rocket/inc/nginx/rewrite.conf;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|webp|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\.(ht|git|env) { deny all; }
    location = /wp-config.php { deny all; }
    location = /xmlrpc.php { deny all; }
}
```

---

## Phase 2: WordPress Configuration (Week 2-3)

### 2.1 Install Plugins

1. **Install & activate** all MUST-HAVE plugins from architecture doc
2. **Configure each** in order:

#### ACF Setup
- Create Field Group: `Video Details`
  - `embed_url` (URL field)
  - `embed_code` (Textarea)
  - `duration` (Text)
  - `source_site` (Select: PH, XV, XH, Other)
  - `external_id` (Text)
  - `views_count` (Number)
  - `featured` (True/False)

#### WP All Import Configuration
- Create import template for CSV with columns:
  `post_title, post_content, post_category, tags, embed_url, duration, source_site, thumbnail_url, external_id`
- Set `sc_video` post type

### 2.2 Theme Setup

**Use Kadence WP as base** (fast, accessible, customizable):

1. Install Kadence WP (free)
2. Create child theme: `sincity-child`
3. Customize via Kadence Theme Options:
   - Dark palette (see Design System)
   - Full-width layout
   - Disable comments on pages (enable on videos only)
   - Custom post type support for `sc_video`

### 2.3 Taxonomy Setup

Create the following categories (hierarchical):

```
Normal Porn
├── Amateur
├── Professional
├── Lesbian
├── MILF
├── Teen
├── Anal
├── Gangbang
├── POV
├── Casting
└── VR

Hentai
├── 2D Animation
├── 3D CGI
├── Doujin
├── Game Hentai
├── Futanari
├── Tentacle
├── Vanilla
├── NTR
├── Loli (DISABLED — legal risk)
└── Yaoi / Yuri

JAV
├── Uncensored
├── Censored
├── Idol Solo
├── Studio (S1, IPPA, Moodyz, SOD, etc.)
├── Compilation
├── Amateur JAV
└── Classic
```

---

## Phase 3: Content Import (Week 3-4)

### 3.1 Source Strategy

| Source | Method | Volume/Day |
|--------|--------|------------|
| Pornhub | WP-Script Mass Embedder (RSS feeds) | 100-200 |
| XVideos | Ken Importer (scrape) | 100-200 |
| xHamster | WP-Script (RSS) | 50-100 |
| Manually curated | Admin upload (quality picks) | 10-20 |

### 3.2 Import Workflow

1. **Collect source URLs** in CSV format
2. **Map fields** via WP All Import:
   - `sc_video` post type
   - Title, content, embed URL, categories, tags
3. **Set featured image** from source (auto-download thumbnail)
4. **Generate unique meta description** (use template, see Content Templates)
5. **Schedule import** — daily cron job at 3 AM (off-peak)

### 3.3 Cron Import Script

Add to `wp-config.php` or via WP-CLI:

```bash
# WP-CLI import command example
wp all-import run 1 --cron
```

---

## Phase 4: Frontend Polish (Week 4-5)

### 4.1 Video Page Template

Override `single-sc_video.php` in child theme:

- Full-width responsive embed container (16:9)
- Custom player wrapper (logo overlay, no source ads)
- Metadata bar (views, duration, rating, source badge)
- Action buttons (favorite, share, report)
- Tag/category list
- Related videos grid below player
- Comment section (wpDiscuz)

### 4.2 Archive/Grid Templates

Create custom templates for:
- Category archives (`taxonomy-sc_category.php`)
- Tag archives (`taxonomy-sc_tag.php`)
- Search results
- Trending page (custom query: `meta_key=views_count`, `orderby=meta_value_num`)

### 4.3 Age Gate Implementation

See Custom Code section for implementation. The age gate must:
- Show overlay before any content
- Verify age (date picker, minimum 18/21)
- Store session cookie (no re-verify for 30 days)
- Deny access redirects to `google.com`

---

## Phase 5: SEO & Analytics (Week 5-6)

- Configure Yoast SEO with SinCity branding templates
- Submit XML sitemaps to Google Search Console
- Create `robots.txt` with correct crawler rules
- Set up Google Analytics 4 + Matomo (privacy-focused backup)
- Add schema markup (VideoObject, Organization, WebSite)

---

## Phase 6: Legal & Launch Prep (Week 6-7)

- Upload legal pages (DMCA, Privacy, 2257, TOS)
- Set up DMCA agent registration (dmca.com)
- Add cookie consent banner (CookieYes or Complianz)
- Configure Wordfence firewall + login security
- Final load testing (GTmetrix target: <3s load time)
- Staging → Production cutover
