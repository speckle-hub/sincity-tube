# SinCity — Marketing & Monetization Roadmap

---

## Content Strategy

### Sourcing
- **Primary**: Auto-import via WP-Script Mass Embedder + Ken Importer from PH, XV, XH
- **Secondary**: User submissions (frontend upload form, reviewed before publish)
- **Tertiary**: Manual curation — hand-pick 5-10 premium videos daily for homepage features
- **Blog Articles**: Weekly editorial calendar — reviews, top-10 lists, actor profiles, industry news

### Unique SEO Titles
Every imported video gets a rewritten title via import template:

```
SinCity: [Adjective] [Category] [Action] with [Actor/Idol]
```

Examples:
- `SinCity: Steamy MILF Threesome with Brandi Love & Friends`
- `SinCity: Uncensored JAV Creampie Session with Yua Mikami`
- `SinCity: 4K Hentai Futanari Domination — Full Episode`

### Tag Expansion Strategy
Start with 50 core tags, add 5-10 weekly based on:

- Trending search terms (Google Trends, Pornhub Insights)
- User search queries (ElasticPress search logs)
- New fetish/niche discovery

Core tag categories:

| Group | Tags |
|-------|------|
| Categories | amateur, professional, lesbian, milf, teen, anal, gangbang, pov, casting, vr |
| Hentai | 2D, 3D, doujin, game, futanari, tentacle, vanilla, ntr, yaoi, yuri |
| JAV | uncensored, censored, idol, studio, compilation, amateur jav, classic |
| Body | big tits, petite, ass, shaved, natural, tattoo, piercing, muscular |
| Acts | blowjob, creampie, facial, dp, deepthroat, handjob, titfuck, rimming |
| Quality | 4K, HD, 1080p, 720p, 60fps, 3D, VR |
| Durations | short (<10min), medium (10-30min), long (30-60min), feature (>60min) |

---

## SEO Roadmap

### Keywords by Category

**Normal Porn Keywords:**
- `sincity normal porn` (branded)
- `free hd amateur porn videos`
- `best milf threesome scenes`
- `pov blowjob compilation sin city`
- `lesbian scissoring videos online`

**Hentai Keywords:**
- `sincity hentai stream` (branded)
- `english subbed hentai free`
- `3d cgi hentai 4k`
- `futanari hentai no watermark`
- `best yuri hentai collection`

**JAV Keywords:**
- `sincity jav tube` (branded)
- `uncensored jav free streaming`
- `yua mikami jav scenes`
- `japanese av idols compilation`
- `best jav studios s1 moodyz`

### Technical SEO Checklist

- [ ] XML sitemap via Yoast (videos, categories, tags, pages)
- [ ] VideoObject schema on single video pages
- [ ] BreadcrumbList schema on archive pages
- [ ] Organization schema on homepage
- [ ] WebSite search action schema
- [ ] Open Graph tags (title, description, image, video)
- [ ] Twitter Card tags
- [ ] robots.txt — allow all crawl except /wp-admin/, /age-verification/
- [ ] Canonical URLs on all pages
- [ ] Noindex on tag archives with < 3 posts
- [ ] 301 redirects for deleted/moved videos
- [ ] Google Search Console + Bing Webmaster Tools

### Link Building

- Adult directory submissions (ThePORN, HelloPorn, AdultSEO)
- Blog comments on adult industry blogs (value-add only)
- Forum participation (GFY, AdultWebmasterForum)
- Social signals (Reddit nsfw411, Twitter, Telegram groups)
- Embed backlinks: encourage users to embed SinCity videos with attribution
- Press releases for site milestones

---

## Monetization Roadmap

### Phase 1: Display Ads (Launch → Month 6)

| Network | Type | Est. RPM | Notes |
|---------|------|----------|-------|
| ExoClick | Pop-unders + Banners | $8-15 | Adult-friendly, high fill rate |
| TrafficJunky | Display + Pre-roll | $10-20 | Premium adult network |
| JuicyAds | Banners + Interstitials | $5-12 | Pay per click/view |
| PlugRush | Pop-unders + Content Lock | $6-10 | High converting for tube traffic |

**Ad Placement Strategy:**
- Pre-roll: 15-second skippable before video (10% of plays)
- Mid-roll: After 5 minutes of video length (30% of plays)
- Display: 300x250 in sidebar, 728x90 above/below player
- Native: In-feed ads between video cards (every 6th card)
- Pop-under: 1 per session max (after 30s on site)

### Phase 2: Premium Subscriptions (Month 3+)

Using MemberPress:

| Tier | Price | Features |
|------|-------|----------|
| Free | $0 | Ads, 720p max, limited favorites |
| Silver | $9.99/mo | No ads, 1080p, unlimited favorites, playlists |
| Gold | $14.99/mo | No ads, 4K/60fps, playlists, early access, priority support |
| Lifetime | $199 | Gold tier forever |

**Premium hooks:**
- "Remove all ads — just $9.99/month"
- "Watch in 4K — Go Gold"
- "Save your favorites — Create an account"

### Phase 3: Affiliate Marketing (Month 1+)

| Program | Commission | Placement |
|---------|------------|-----------|
| OnlyFans | 5-20% rev share | Model profile links in video pages |
| Chaturbate | 25-40% rev share | "Live Cams" section sidebar |
| Adam & Eve | 30% + bounty | Toys/accessories in blog posts |
| Fleshlight | 15% | Product reviews |
| Top adult cam sites | Varies | "Live now" banners |

### Phase 4: Sponsored Content (Month 6+)

- Studio spotlight posts ($500-2000 per post)
- Banner ads direct to studios/onlyfans models
- Video sponsorship (pre-roll ad replacement)
- Newsletter sponsorship

### Phase 5: Diversification (Month 12+)

- SinCity branded merchandise (clothing, posters)
- Cross-site promotion (sister sites: SinCityCams, SinCityToys)
- Mobile app (PWA with push notifications)
- Content licensing (aggregate and license embed data)

---

## Traffic Projections

| Month | Visitors (est) | Page Views | RPM | Revenue (est) |
|-------|---------------|------------|-----|---------------|
| 1 | 5,000 | 25,000 | $8 | $200 |
| 3 | 25,000 | 150,000 | $10 | $1,500 |
| 6 | 100,000 | 700,000 | $12 | $8,400 |
| 12 | 500,000 | 4,000,000 | $15 | $60,000 |

*Realistic goals — depends on SEO + import volume + ad optimization*

---

## Analytics Setup

**Primary:** Google Analytics 4
- Track: Page views, video plays, session duration, bounce rate, ad clicks
- Goals: Signup (premium), ad click, video play, pages per session
- Events: `video_play`, `video_pause`, `video_complete`, `ad_click`, `premium_signup`

**Secondary:** Matomo (self-hosted, privacy-focused)
- No data sharing
- Heatmaps + session recordings
- Better for conversion tracking

**Ad Analytics:** ExoClick / TrafficJunky internal dashboards
- Track RPM, fill rate, CTR per placement
- A/B test ad positions weekly
