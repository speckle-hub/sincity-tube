# SinCity — Risks, Mitigations & Launch Checklist

---

## Legal Checklist

### Pre-Launch (Critical Path)

- [ ] **Domain**: Register with Namecheap (adult-friendly registrar). Avoid GoDaddy, HostGator, DreamHost — they drop adult domains without warning.
- [ ] **Hosting**: KnownHost, Koddos, or Frantech/BuyVM (adult-friendly TOS). DO NOT use AWS, DigitalOcean, Linode without explicit adult-content permission.
- [ ] **SSL**: Cloudflare Origin CA + Edge SSL. No unencrypted traffic.
- [ ] **Age Gate**: Mandatory 18+ interstitial on entry (code provided in custom snippets). Store opt-in cookie for 30 days max (GDPR compliance).
- [ ] **2257 Compliance**: Create `/2257/` page with:
  - Statement of record-keeping compliance
  - Custodian of records: Name, business address
  - Statement that all models are 18+ at time of production
  - Reference to 18 U.S.C. §2257
- [ ] **DMCA Agent**: Register at dmca.com. Create `/dmca/` page with:
  - Designated DMCA agent contact (email, physical address)
  - Takedown procedure (48-hour response policy)
  - Counter-notification procedure
  - Repeat infringer policy
- [ ] **Privacy Policy**: Create `/privacy-policy/` meeting:
  - GDPR (EU visitors): Cookie consent, data processing, right to erasure
  - CCPA (California): Right to know, right to delete, opt-out of sale
  - Data controller contact
  - Third-party data sharing (ad networks, analytics)
- [ ] **Terms of Service**: Create `/terms/` including:
  - Age requirement (18+ / 21+ where applicable)
  - User conduct (no illegal content, no harassment)
  - Copyright policy
  - Limitation of liability
  - Jurisdiction clause
- [ ] **Cookie Consent**: Install CookieYes or Complianz. Required for EU traffic.
- [ ] **Content Moderation Policy**: Document criteria for:
  - Prohibited content (real violence, minors, non-consensual, animals, scat)
  - User comment moderation (auto-filter + manual review)
  - Uploaded video review process

### Ongoing Legal Operations

- [ ] Weekly review of DMCA takedowns received
- [ ] Monthly backup of all legal records (hosting logs, user data, content records)
- [ ] Quarterly review of category/tag names for problematic terms
- [ ] Maintain 2257 records for any original content (not embeds — but still state compliance)
- [ ] Monitor COPPA compliance (no underage-appearing content, clear adult gate)

---

## Risk Matrix

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|------------|
| Hosting termination (adult TOS violation) | Medium | Critical | Backups at 3 locations; use adult-friendly host from day 1; maintain migration playbook |
| DMCA takedown avalanche | High | Medium | 48-hour response team; automated takedown processing; remove embeds not videos stored |
| Google deindexing / penalties | Medium | Critical | White-hat SEO only; no doorway pages; no paid links; age gate may cause issues — use `noinline` not `noindex` |
| Ad network rejection | Medium | High | Apply to multiple networks; keep direct-sold ads as backup; maintain premium subscriptions |
| Server overload / scaling | Medium | High | Cloudflare caching; WP Rocket + Redis; horizontal scaling script ready; auto-scaling via Vultr/Linode |
| Data breach / hack | Low | Critical | Wordfence; Cloudflare WAF; 2FA on all admin accounts; regular security audits |
| Visa/Mastercard payment rejection | Medium | High | Use adult-friendly processors (CCBill, Epoch, Paxum, Segpay); crypto as fallback |
| Legal action (model/content) | Low | Critical | Only use embed content from major tubes (they handle rights); don't host videos; DMCA compliance |
| SEO competitor attacks | Medium | Medium | Monitor backlinks weekly; disavow toxic links; no negative SEO attack vectors |
| GDPR / CCPA fines | Low | Medium | Cookie consent; privacy policy; data deletion request process |
| Age gate bypass by minors | Medium | High | Age gate is good-faith effort; add additional CAPTCHA if needed; document all attempts at compliance |
| Plugin vulnerabilities | Medium | High | Keep all plugins updated; use WP auto-update for security; monthly audit of active plugins |

---

## Security Hardening Checklist

- [ ] **Cloudflare WAF**: Block known attack patterns, SQL injection, XSS, bad bots
- [ ] **Wordfence**: Enable firewall, login lockdown, 2FA for all admins
- [ ] **SSL**: Force HTTPS everywhere (HSTS header, preload)
- [ ] **PHP**: Disable file editing in WP admin (`define('DISALLOW_FILE_EDIT', true)`)
- [ ] **wp-config.php**: Move outside web root if possible; unique table prefix; salts auto-generated
- [ ] **Login protection**: Limit login attempts; CAPTCHA on login; XML-RPC disabled
- [ ] **File permissions**: 755 for directories, 644 for files, 600 for wp-config.php
- [ ] **Database**: Strong password; limit user privileges; disable remote root login
- [ ] **Backups**: Daily (automated) + weekly manual; stored off-server (BunnyCDN Storage or Backblaze B2)
- [ ] **Monitoring**: UptimeRobot (5-min checks); server resource monitoring (Netdata or similar)
- [ ] **DDoS**: Cloudflare under-attack mode; rate limiting on API endpoints
- [ ] **Email**: Use transactional email service (SendGrid, Mailgun) — don't send from your own server

---

## Performance / Scalability Checklist

- [ ] **Caching**: WP Rocket (page cache), Redis (object cache), Cloudflare (edge cache)
- [ ] **CDN**: BunnyCDN for images, CSS, JS (pull zone); Cloudflare for HTML cache
- [ ] **Images**: WebP format via ShortPixel; lazy loading; responsive srcset
- [ ] **CSS/JS**: Minified + combined; deferred/deferred non-critical; preload critical CSS
- [ ] **Database**: MariaDB 10.11 tuned; slow query log; weekly optimization
- [ ] **PHP**: OPcache enabled; PHP 8.2 with JIT
- [ ] **Embeds**: Lazy loaded (IntersectionObserver); never autoplay with sound
- [ ] **Scaling plan**: When hitting 70% CPU/memory consistently → upgrade VPS. Use Vultr auto-scale or Linode dynamic plans.
- [ ] **GTmetrix target**: Load < 3s, TTFB < 500ms, Performance score > 85
- [ ] **Core Web Vitals**: LCP < 2.5s, FID < 100ms, CLS < 0.1

---

## Launch Timeline (7 Weeks)

| Week | Phase | Tasks |
|------|-------|-------|
| 1 | Foundation | Domain, hosting, server setup, WordPress install |
| 2 | Configuration | Plugins install, ACF setup, taxonomies, theme install |
| 3 | Design | Theme customization, CSS, age gate, custom templates |
| 4 | Content | First 300 video imports, thumbnails, SEO titles |
| 5 | SEO & Ads | Yoast config, sitemaps, GSC, ad network applications |
| 6 | Legal & Security | All legal pages, DMCA agent, Wordfence, SSL audit |
| 7 | QA & Launch | Load testing, cross-browser check, mobile audit, GO LIVE |

### Post-Launch (Week 8+)

- Daily: Monitor server load, upload 50-200 new videos via cron import
- Weekly: SEO audit, ad performance review, comment moderation, backup verification
- Monthly: Security scan, plugin updates, quarterly legal review, revenue analysis

---

## Pornhub/Kennautos Import Disclaimer

For WP-Script Mass Embedder or Ken Importer:
- Always set `post_status` to `draft` initially, review batch, then publish
- Use `wp_remote_get()` with proper user-agent (don't hammer source sites)
- Rotate IPs if scraping (residential proxies for high volume)
- Respect `robots.txt` of source sites
- Never claim ownership of embedded content — always state "embedded from [source]"
- Watermark overlays on thumbnails only if legally permissible

### Recommended Import Config

```
Import Source: CSV file
Columns: title, content, category, tags, embed_url, duration, source, thumbnail
Post Type: sc_video
Post Status: draft (review → publish)
Featured Image: Download from URL
Duplicate Detection: by external_id or embed_url hash
Schedule: Daily at 03:00 UTC (low traffic)
Batch Size: 50 per cron run (prevent timeout)
```
