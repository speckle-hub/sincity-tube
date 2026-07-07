# SinCity — Plugin & Import Configuration Guide

---

## Recommended Plugin Stack (Updated)

| Role | Plugin | Cost | Notes |
|------|--------|------|-------|
| Bulk embed import | **WP-Script Mass Embedder** | $47/yr | Best for RSS/feed-based auto-import |
| Bulk embed import | **Tube Ace Auto Embedder** | $27 one-time | Alternative — simpler setup, handles PH/XV/XH |
| Bulk content import | **WP All Import Pro** | $99/yr | CSV/XML mapping for post creation |
| Custom fields | **Advanced Custom Fields Pro** | $49/yr | Needed for repeaters if used |
| Search | **ElasticPress** (free) + Elasticsearch | $0 + server | Must-have for fuzzy search |
| Caching | **WP Rocket** | $59/yr | Single-site license |
| SEO | **Yoast SEO** or **Rank Math Pro** | $0/$59/yr | Rank Math has better schema handling |
| Comments | **wpDiscuz** | $0 | Best free comment system |
| Security | **Wordfence Premium** | $99/yr | Real-time IP blocking |
| Membership | **MemberPress** | $179/yr | Premium tier gating |

---

## WP-Script Mass Embedder — Setup Guide

### Step 1: Install & Activate
1. Plugins > Add New > Upload `wp-script-mass-embedder.zip`
2. Activate
3. Go to WP-Script > Settings

### Step 2: Global Settings
```
Proxy:                 [ ] Disabled (no proxy for small/medium scale)
Auto-embed:            [x] On (auto-fetch embed codes)
Number per request:    20 (lower = more reliable)
Delay between:         1000ms (avoid rate limiting)
Cache embeds:          [x] Yes (3600s)
Fallback image:        [ ] Use site's default
```

### Step 3: Create Import Feeds (per source)

#### Feed 1 — Pornhub Normal
```
Feed Name:              PH — Normal Porn
Source URL:             https://www.pornhub.com/video?page=1
Import type:            URL list (scrape)
Categories:             Normal Porn (and auto-detect sub)
Target post type:       sc_video
Post status:            draft
Fetch method:           RSS + Scrape
Update schedule:        Daily at 03:00 UTC
Max per import:         200
Duplicate check:        External ID (from field_external_id)
```

#### Feed 2 — Pornhub Hentai
```
Feed Name:              PH — Hentai
Source URL:             https://www.pornhub.com/video?c=58
                        (category 58 = Hentai)
Categories:             Hentai
Post status:            draft
Max per import:         100
```

#### Feed 3 — XVideos General
```
Feed Name:              XV — All
Source URL:             https://www.xvideos.com/
                        https://www.xvideos.com/amateur
                        https://www.xvideos.com/hentai
                        https://www.xvideos.com/jav
Import type:            URL list (scrape)
Categories:             Auto-map based on source URL path
Post status:            draft
Max per import:         200
```

#### Feed 4 — xHamster
```
Feed Name:              XH — All
Source URL:             https://xhamster.com/
Import type:            URL list (scrape)
Post status:            draft
Max per import:         100
```

### Step 4: Template Mapping (WP All Import)

Create a CSV with these columns (sample provided below):

```
post_title, post_content, sc_category, sc_tag, embed_url, duration, source_site, thumbnail_url, external_id
```

**WP All Import config:**

```
Import Type:            New items
Post Type:              sc_video
Update Existing:        Update existing items (match by external_id)

Field Mapping:
  post_title            → {title[1]}
  post_content          → {content[1]}
  sc_category           → Taxonomy 'sc_category' (create new terms)
  sc_tag                → Taxonomy 'sc_tag' (create new terms)
  embed_url             → Custom Field 'embed_url'
  duration              → Custom Field 'duration'
  source_site           → Custom Field 'source_site' (value: ph/xv/xh)
  thumbnail_url         → Download as Featured Image
  external_id           → Custom Field 'external_id'
  views_count           → Custom Field 'views_count' (default: 0)
```

### Step 5: Auto-Categorization Rules

Use WP All Import's PHP function evaluation to auto-map:

```php
// Auto-detect category from title/content
function sc_auto_category($title, $content) {
    $text = strtolower($title . ' ' . $content);

    // Hentai keywords
    if (preg_match('/hentai|anime|2d|3d cgi|futanari|tentacle|doujin|yaoi|yuri|ntr|vanilla|game hentai/i', $text)) {
        return ['Hentai'];
    }

    // JAV keywords
    if (preg_match('/jav|japanese|uncensored|mosaic|idol|japan|studio|s1 |moodyz|ippa|sod |creampie jav/i', $text)) {
        return ['JAV'];
    }

    // Default to Normal Porn
    return ['Normal Porn'];
}
```

**Subcategory auto-detect (add to same function):**

```php
// Subcategory detection
if (preg_match('/amateur|homemade|real couple|casting couch/i', $text))
    $subs[] = 'Amateur';
if (preg_match('/milf|mature|cougar|older/i', $text))
    $subs[] = 'MILF';
if (preg_match('/lesbian|scissoring|strapon/i', $text))
    $subs[] = 'Lesbian';
if (preg_match('/anal|ass|gaping|creampie anal/i', $text))
    $subs[] = 'Anal';
if (preg_match('/gangbang|dp |double penetration|group|bukkake/i', $text))
    $subs[] = 'Gangbang';
if (preg_match('/pov/i', $text))
    $subs[] = 'POV';
if (preg_match('/teen|18 |young|petite/i', $text))
    $subs[] = 'Teen';
return array_merge([$main], $subs);
```

---

## Ken Importer / Tube Ace — Config Reference

### Ken Importer Setup (if using WP-Script alternative)

1. Install Ken Importer
2. Go to Ken Importer > Add Source

```
Source Name:            Pornhub
Source URL:             https://www.pornhub.com
Import Type:            Videos
Post Type:              sc_video
Post Status:            Draft
Category:               Auto-detect
Tags:                   Auto-detect
Thumbnail:              Download
Embed Code:             Yes (clean mode)
Auto-import:            Daily
Max Videos:             500 total, 50 per run
```

### Tube Ace Auto Embedder Setup

```
General Settings:
  Post Type:            sc_video
  Post Status:          Draft
  Enable Autoblog:      Yes
  Max Posts:            50 per run
  Cron Schedule:        Daily
 
Sites to scrape:
  [x] Pornhub
  [x] XVideos
  [x] xHamster
  [x] RedTube

Filter settings:
  Min Duration:         180 seconds (3 min)
  Max Duration:         3600 seconds (60 min)
  Keywords:             (leave blank, import all)
  Exclude Keywords:     (block list)
```

---

## Duplicate Prevention

### External ID Dedup Logic

Add to `functions.php`:

```php
/**
 * Check for duplicates by external_id before saving
 * Hook into wp_all_import_is_post_to_create
 */
add_filter('wp_all_import_is_post_to_create', function ($to_create, $data, $import_id) {
    if (empty($data['external_id'])) return $to_create;

    $existing = get_posts([
        'post_type'      => 'sc_video',
        'meta_key'       => 'external_id',
        'meta_value'     => $data['external_id'],
        'fields'         => 'ids',
        'posts_per_page' => 1,
    ]);

    return empty($existing);
}, 10, 3);

/**
 * Also check by embed_url hash
 */
add_filter('wp_all_import_is_post_to_create', function ($to_create, $data, $import_id) {
    if (empty($data['embed_url'])) return $to_create;

    $hash = md5($data['embed_url']);
    $existing = get_posts([
        'post_type'      => 'sc_video',
        'meta_key'       => 'embed_url_hash',
        'meta_value'     => $hash,
        'fields'         => 'ids',
        'posts_per_page' => 1,
    ]);

    return empty($existing);
}, 10, 3);
```

Add to import template — store URL hash:

```php
update_field('embed_url_hash', md5($embed_url), $post_id);
```

---

## Cron Job Configuration

### WP-Cron (for lower traffic — built in)

No extra config — WP-Script and WP All Import handle their own scheduling.
Set visits to trigger cron: any real visit will trigger scheduled imports.

### Server Cron (recommended for > 10K visits/day)

Add to crontab (`crontab -e`):

```bash
# ─── SinCity Import Cron Jobs ────────────────────────────

# Run WP-Script import daily at 3 AM
0 3 * * * /usr/bin/php /var/www/sincity/wp-cron.php?import_id=1 >/dev/null 2>&1

# Run WP All Import daily at 3:30 AM
30 3 * * * /usr/bin/php /var/www/sincity/wp-cron.php?import_id=2 >/dev/null 2>&1

# Flush WP Rocket cache daily at 5 AM (so fresh content appears)
0 5 * * * /usr/bin/curl -s -o /dev/null "https://sincity.porn/wp-rocket-cache-purge"

# Weekly DB optimization (Sunday 6 AM)
0 6 * * 0 /usr/bin/php /var/www/sincity/wp-content/plugins/wordfence/waf.php?optimize=1 >/dev/null 2>&1
```

### Disable WP-Cron (when using server cron)

In `wp-config.php`:

```php
define('DISABLE_WP_CRON', true);
```

---

## Sample Import CSV

```
post_title,post_content,sc_category,sc_tag,embed_url,duration,source_site,thumbnail_url,external_id
"SinCity: Hot MILF Seduces Young Neighbor","Watch this stunning MILF seduce her young neighbor in this steamy encounter. Full HD.",Normal Porn>MILF,MILF,young,neighbor,big tits,seduction,https://www.pornhub.com/view_video.php?viewkey=ph123456,25:30,ph,https://thumb.cdn.ph/image.jpg,ph123456
"SinCity: Uncensored JAV Creampie with Yua","Yua Mikami in an uncensored raw creampie scene. No mosaic.",JAV>Uncensored,JAV,uncensored,yua mikami,creampie,https://www.xvideos.com/video123456/jav_creampie,60:00,xv,https://thumb.cdn.xv/image.jpg,xv123456
"SinCity: 4K Hentai Futa Domination","Full color 4K futanari hentai animation. English subtitles.",Hentai>Futanari,hentai,futanari,4K,subtitled,domination,https://xhamster.com/videos/abc789,22:00,xh,https://thumb.cdn.xh/image.jpg,abc789
```

---

## Post-Import Quality Checks

After each import run, verify:

1. **Thumbnails**: Are all present? Broken thumbnails = auto-draft the post
2. **Embed codes**: Do embeds load? Run `sc_render_player()` test on 5 random posts
3. **Duration**: Is it populated? If not, flag for manual edit
4. **Category**: Is auto-categorization correct? Adjust keyword rules if needed
5. **Title**: Contains "SinCity:" prefix? If not, add it via WP All Import

### Auto-draft posts with broken embeds

```php
add_action('save_post_sc_video', function ($post_id) {
    // Skip if no embed URL — auto-draft
    if (!get_field('embed_url', $post_id) && !get_field('embed_code', $post_id)) {
        remove_action('save_post_sc_video', __FUNCTION__);
        wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
        add_action('save_post_sc_video', __FUNCTION__);
    }
});
```
