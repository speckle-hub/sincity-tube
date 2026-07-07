# SinCity — Site Architecture

## Overview

SinCity is a premium adult tube/aggregator site running WordPress with a custom dark cyberpunk theme. Three primary categories: Normal, Hentai, JAV. Content is sourced via bulk embed imports from major tube sites.

---

## Tech Stack

| Layer | Choice | Rationale |
|-------|--------|-----------|
| CMS | WordPress | Largest plugin ecosystem, mature, SEO-friendly |
| Theme | Custom child theme (GeneratePress or Kadence base) | Lightweight, fast, easy to customize |
| Cache | WP Rocket + Cloudflare | Full-page cache, CDN, DDoS protection |
| DB | MariaDB 10.11 | Better performance than MySQL for read-heavy loads |
| PHP | PHP 8.2+ | Required for modern plugins |
| Object Cache | Redis (via WP Redis) | Reduce DB queries for embed-heavy pages |
| CDN | BunnyCDN or Cloudflare | Global edge caching for thumbnails, CSS, JS |
| Video Storage | None (embeds only) | No local video storage = no bandwidth costs |
| Embed Imports | WP-Script Mass Embedder + Ken Importer | Bulk CSV/XML import from tube sites |
| Search | ElasticPress (Elasticsearch backend) | Fuzzy search, facet filtering, instant results |
| Image Optimization | ShortPixel or Imagify | Auto WebP + lazy load |
| Security | Wordfence + Cloudflare WAF + SSL | Anti-DDoS, firewall, login protection |

---

## Page Structure

### Core Pages

| URL | Page | Function |
|-----|------|----------|
| `/` | Homepage | Hero banner, category cards, trending grid, latest videos |
| `/age-verification` | Age Gate | Interstitial before homepage access |
| `/category/normal/` | Normal Porn | Filterable grid of normal porn |
| `/category/hentai/` | Hentai | Filterable grid of hentai/anime |
| `/category/jav/` | JAV | Filterable grid of Japanese AV |
| `/video/{slug}/` | Single Video | Embed player, meta, tags, comments, related |
| `/trending/` | Trending | Most views/engagement this week |
| `/tags/{tag}/` | Tag Archive | All videos for a tag |
| `/search/` | Search Results | Live search with filters |
| `/user/favorites/` | User Favorites | Saved videos (requires registration) |
| `/user/playlists/` | Playlists | User-curated collections |
| `/blog/` | Blog/Reviews | SEO articles, top lists, reviews |
| `/dmca/` | DMCA | Copyright takedown policy |
| `/privacy-policy/` | Privacy | GDPR/privacy compliance |
| `/2257/` | 2257 Compliance | Record-keeping statement |
| `/terms/` | Terms of Service | User agreement |
| `/contact/` | Contact | Abuse/report form |

### Additional Sections

- `/categories/` — Browse all subcategories
- `/newest/` — Latest uploaded videos
- `/top-rated/` — Highest user-rated
- `/most-viewed/` — All-time most viewed
- `/recommended/` — Algorithm-based suggestions

---

## Database Schema (Custom Post Types)

Custom post type: `sc_video`

| Meta Key | Type | Description |
|----------|------|-------------|
| `sc_embed_url` | text | Source embed URL |
| `sc_embed_code` | longtext | Raw iframe/embed HTML |
| `sc_duration` | varchar | Duration (e.g., "25:30") |
| `sc_source` | varchar | Source site (ph, xv, xh, etc.) |
| `sc_views` | int | View counter |
| `sc_rating_avg` | float | Average user rating |
| `sc_rating_count` | int | Number of ratings |
| `sc_featured` | bool | Featured in hero/spotlight |
| `sc_external_id` | varchar | Source video ID |
| `sc_thumbnail` | url | Custom thumbnail URL |

Taxonomies:

- `sc_category` (Normal, Hentai, JAV + subcategories)
- `sc_tag` (fetish, actress, studio, duration, quality)
- `sc_actor` (performer name, linked across videos)

---

## Navigation Flow

```
[Age Gate] → [Homepage]
               ├── Normal Porn → subcats → video grid → single video
               ├── Hentai      → subcats → video grid → single video
               ├── JAV         → subcats → video grid → single video
               ├── Trending    → video grid → single video
               ├── Search      → results → single video
               ├── Blog        → articles
               └── Legal       → DMCA / Privacy / 2257

Single Video Page:
  Embed Player (full width)
  ↓
  Metadata (title, views, rating, duration, source)
  ↓
  Action Bar (like, favorite, share, report)
  ↓
  Tags / Categories
  ↓
  Comments (logged-in users, moderated)
  ↓
  Related Videos (from same category/tags)
```

---

## User Roles & Permissions

| Role | Capabilities |
|------|-------------|
| Administrator | Full access |
| Editor | Publish/edit videos, moderate comments, manage blog |
| Author | Submit videos, write blog posts (pending review) |
| Contributor | Suggest videos (pending) |
| Subscriber (free) | Favorite, playlist, comment, rate |
| Subscriber (premium) | No ads, HD embeds, early access |

---

## Plugin Architecture

### MUST-HAVE

| Plugin | Purpose |
|--------|---------|
| WP-Script Mass Embedder | Bulk embed import engine |
| Ken Importer (or Tube Ace) | Alternative/supplemental import |
| Advanced Custom Fields (ACF) | Custom meta boxes for video data |
| FacetWP | Advanced faceted search/filter |
| ElasticPress | Elasticsearch integration |
| WP Rocket | Caching + minification |
| ShortPixel | Image optimization |
| Wordfence | Security |
| WP All Import | CSV/XML bulk post creation |
| bbPress or wpDiscuz | Comments system |
| User Profile Builder | Registration/Login |
| Yoast SEO | SEO titles, meta, sitemaps |
| Loco Translate | Theme/plugin translation |
| WP User Frontend | Frontend video submissions |

### NICE-TO-HAVE

| Plugin | Purpose |
|--------|---------|
| AffiliateWP | Track affiliate links |
| MemberPress | Premium subscription management |
| AdSanity | Ad management |
| TablePress | Data tables for blog posts |
| WP Scheduled Posts | Auto-publish content calendar |
| GA Google Analytics | Analytics |
| Cookie Notice | GDPR cookie consent |
