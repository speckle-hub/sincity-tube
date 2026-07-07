# SinCity — Render.com Import & Content Strategy

---

## Free Tier Constraints Impacting Import

| Constraint | Impact | Workaround |
|------------|--------|------------|
| 1GB PostgreSQL storage | ~50,000 video posts max | Prioritize quality over quantity; prune old content |
| 512MB RAM | Large imports may timeout | Batch at 50-100 rows per CSV |
| Ephemeral filesystem | Uploaded files lost on restart | Use external thumbnail URLs (never local uploads) |
| No background workers | Can't run long background imports | Imports run during web request (cron-job.org triggers) |
| Cold start delays | Import cron may timeout | Keep file sizes small; use UptimeRobot to keep warm |

---

## Import Workflow (Optimized for Render)

### Phase 1: Manual Seeding (Week 1)

```
Goal: 300 high-quality videos (100 per category)

Process:
  1. Prepare CSV with 50 rows (mix of all 3 categories)
  2. Import via WP All Import (takes ~2-5 min for 50 videos)
  3. Verify: spot-check 5 videos for embed + thumbnail
  4. Repeat 6 times over 3 days (total: 300 videos)

⚠ Don't import all 300 at once — Render will timeout the request.
   50 per batch is the sweet spot.
```

### Phase 2: Daily Auto-Import (Ongoing)

```
Goal: 25 videos/day (auto-populated via cron-job.org)

  1. Set up a recurring CSV generation script (Python/Node.js)
  2. Host the CSV at a public URL (GitHub Gist, Pastebin, or your site)
  3. WP All Import > Cron Import > Set URL to CSV location
  4. cron-job.org pings WP All Import cron endpoint daily at 3 AM

Expected: 175 new videos/week, 750/month
```

### Phase 3: Manual Curation (Weekly)

```
Goal: 10-20 hand-picked premium videos

Criteria:
  - Trending videos from source sites (picked manually)
  - High rating (> 4.5 stars on source)
  - Good thumbnail quality
  - Unique content (not already imported)

Process:
  1. Browse source sites → collect embed URLs
  2. Add to a small CSV (10-20 rows)
  3. Import manually via WP All Import
  4. Set as "Featured" for homepage hero
```

---

## CSV Format (Render-Compatible)

```csv
post_title,post_content,sc_category,sc_tag,embed_url,duration,source_site,thumbnail_url,external_id
"SinCity: Title Here","Description here.",Category>Subcategory,tag1,tag2,https://embed.url,25:00,ph,https://cdn.thumbnail.jpg,ext123
```

**Rules for Render CSV:**
- `thumbnail_url` MUST be a full URL (not local path) — files are not stored locally
- `embed_url` MUST be the full source URL (not just video ID)
- `external_id` is critical for dedup — use source video ID
- Keep rows under 2KB each (approx 50 rows = 100KB CSV)

---

## Dedup Strategy (Critical for Render DB space)

Add to `wp-config.php` (via env vars or commit):

```php
// Already in sc-functions.php — prevents duplicate imports
// Matches by: external_id OR md5(embed_url)
```

**This prevents:**
- Wasting DB space on duplicates
- Long import times re-processing existing videos
- Broken thumbnails from re-download attempts

---

## PostgreSQL Considerations

Since Render free tier uses PostgreSQL (not MySQL):

### Via PG4WP

The mu-plugin at `wp-content/mu-plugins/00-pg4wp-loader.php` handles this automatically when `DB_DRIVER=pgsql` is set.

**Limitations with PG4WP:**
- Some MySQL-specific queries may not work (rare in modern WP)
- Full-text search uses PostgreSQL syntax (not MySQL)
- WP All Import is tested with PG4WP and works

### Alternative: Use External MySQL

If you prefer MySQL, use a free MySQL provider:

```
1. PlanetScale (https://planetscale.com) — Free 1GB MySQL
2. aiven.io — Free 5GB PostgreSQL or MySQL (but requires credit card)
3. Always Data — Free 250MB MySQL

Then set env vars:
  DB_HOST = <PlanetScale host>
  DB_NAME = sincity
  DB_USER = ...
  DB_PASSWORD = ...

And DON'T set DB_DRIVER (or set to 'mysql').
The PG4WP mu-plugin will skip loading.
```

---

## Thumbnail Strategy (No Local Storage)

**Important**: On Render free tier, uploaded images disappear on restart.

### Best Approach: Source CDN URLs

```csv
thumbnail_url,https://di.phncdn.com/videos/202401/01/12345678/thumbnail.jpg
```

- No storage used
- Thumbnails load from source CDN (fast)
- If source deletes thumbnail → show SinCity placeholder

### Backup Approach: Cloudflare R2 (Free 10GB)

```
1. Create Cloudflare R2 bucket (https://r2.cloudflare.com)
2. Set bucket to public
3. Set env vars: R2_ACCOUNT_ID, R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY, R2_BUCKET, R2_PUBLIC_URL
4. The mu-plugin 01-r2-uploads.php handles upload redirection automatically
5. Upload thumbnails via WP admin Media Library → they go to R2
```

### Fallback: Placeholder Image

When no thumbnail is available, the theme shows:

```css
.thumb-placeholder {
    background: #12121A;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #606078;
    font-family: 'Cinzel', serif;
    content: 'SINCITY';
}
```

---

## Storage Budget Calculation

| Data Type | Size per Row | 1,000 Videos | 10,000 Videos | 50,000 Videos |
|-----------|-------------|--------------|---------------|---------------|
| Post data (title, content) | ~2 KB | 2 MB | 20 MB | 100 MB |
| Post meta (ACF fields) | ~1 KB | 1 MB | 10 MB | 50 MB |
| Taxonomy (categories, tags) | ~0.5 KB | 0.5 MB | 5 MB | 25 MB |
| Comments (avg 5/video) | ~5 KB | 5 MB | 50 MB | 250 MB |
| **Total** | ~8.5 KB | **8.5 MB** | **85 MB** | **425 MB** |

Render free PostgreSQL: **1 GB limit** → enough for ~100,000 video posts.

**Practical limit**: 50,000 videos (leaving room for DB overhead + WP core tables).

---

## Content Pruning Strategy

When approaching 40,000 videos, start pruning:

1. **Remove lowest-performing videos** (by views, < 50 views in 30 days)
2. **Remove broken embeds** (source deleted the video)
3. **Remove duplicates** (check by external_id hash)
4. **Archive old blog posts** (> 1 year, low traffic)

### Deletion SQL (run via phpMyAdmin or wp-admin SQL plugin):

```sql
-- Find posts with zero views (stale content)
SELECT ID, post_title FROM sc_posts p
LEFT JOIN sc_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = 'views_count'
WHERE p.post_type = 'sc_video' AND (pm.meta_value IS NULL OR pm.meta_value = '0')
AND p.post_date < NOW() - INTERVAL 30 DAY
LIMIT 100;
```
