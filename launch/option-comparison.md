# SinCity — Deployment Option Comparison

---

## Option A: Render PHP Web Service (Recommended for Beginners)

**No Docker. No YAML. Just files + a few clicks.**

### How It Works

```
You push:    WordPress files + theme + plugins + composer.json + wp-config.php
Render:      Detects PHP from composer.json → runs composer install → starts Nginx + PHP-FPM
You manage:  MySQL via PlanetScale (free), cron via cron-job.org
```

### Architecture Diagram

```
Browser → Render Web Service (Nginx + PHP-FPM + WP)
                  │
         ┌────────┴────────┐
         │                 │
   PlanetScale        cron-job.org
   (MySQL free)       (WP-Cron pings)
```

### Pros

| Pro | Why It Matters |
|-----|---------------|
| **No Docker knowledge needed** | No Dockerfile, no Docker concepts, no container debugging |
| **Fast builds** | ~30 seconds (no Docker image to build) |
| **Standard WordPress** | Works exactly like any WordPress host — MySQL, standard config |
| **Smaller repo** | No Docker infrastructure files |
| **Plugins work normally** | PG4WP is optional; standard MySQL means 100% plugin compatibility |
| **Render dashboard is simpler** | Just 1 service to manage (no Docker context settings) |
| **Debugging is easier** | PHP errors show in standard Render logs |

### Cons

| Con | Mitigation |
|-----|-----------|
| **Need PlanetScale account** (separate from Render) | Free tier, 5-min setup, works great |
| **No custom Nginx config** | Render's default is fine for WP — handles permalinks, caching, security |
| **No custom PHP config** | Acceptable for free tier — 128MB memory limit is fine |
| **PlanetScale has limits** | 1GB storage, 1B row reads/mo — plenty for 50K videos |
| **No Redis caching** | Not available on free tier — WP object cache uses DB directly |

### Cost: $0/month

| Service | What | Cost |
|---------|------|------|
| Render | PHP Web Service (free tier) | Free |
| PlanetScale | MySQL 1GB (free tier) | Free |
| cron-job.org | Cron pings | Free |
| UptimeRobot | Keep-alive pings | Free |
| **Total** | | **$0.00** |

---

## Option B: Render Docker Web Service (Power Users)

**Full control. Everything in one Render account. More complex.**

### How It Works

```
You push:    WordPress files + theme + plugins + Dockerfile + Nginx config + start.sh
Render:      Build Docker image → run container with Supervisor (Nginx + PHP-FPM)
You manage:  PostgreSQL via Render (built-in), cron via cron-job.org
```

### Architecture Diagram

```
Browser → Render Docker Container
                  │
          Supervisor
         ┌────┴────┐
      Nginx      PHP-FPM
         │           │
         └────┬──────┘
        Render PostgreSQL
        (built-in, free)
```

### Pros

| Pro | Why It Matters |
|-----|---------------|
| **Everything in one account** | Render handles DB + web service |
| **Full Nginx control** | Custom security headers, rate limiting, uploads proxy |
| **Full PHP control** | Custom php.ini, OPcache tuning |
| **Same server for DB+app** | Lower latency (internal network) |
| **No third-party services** | No PlanetScale, no separate account |

### Cons

| Con | Why It's Harder |
|-----|-----------------|
| **Docker knowledge required** | Need to understand Dockerfile, image builds, containers |
| **Slower builds** | 3-5 min per deploy (building Docker image) |
| **PostgreSQL instead of MySQL** | PG4WP needed; some WP plugins may have issues |
| **More complex debugging** | Need to check container logs, FPM status |
| **More files to maintain** | Dockerfile, nginx config, supervisor config, start script |
| **Build context is larger** | Render sends entire repo to Docker build (longer upload) |

### Cost: $0/month

| Service | What | Cost |
|---------|------|------|
| Render | Docker Web Service (free tier) | Free |
| Render | PostgreSQL 1GB (free tier) | Free |
| cron-job.org | Cron pings | Free |
| UptimeRobot | Keep-alive pings | Free |
| **Total** | | **$0.00** |

---

## Side-by-Side Comparison

| Criteria | Option A (PHP) | Option B (Docker) | Winner |
|----------|---------------|-------------------|--------|
| **Setup time** | 15 minutes | 30 minutes | **A** |
| **Difficulty** | Beginner | Intermediate | **A** |
| **Database** | MySQL (PlanetScale) | PostgreSQL (Render) | **A** (MySQL is native WP) |
| **Plugin compat** | 100% | 95% (PG4WP needed) | **A** |
| **Custom Nginx** | No (default config) | Yes | **B** |
| **Custom PHP** | No (default config) | Yes | **B** |
| **Single account** | 2 (Render + PlanetScale) | 1 (Render only) | **B** |
| **Build speed** | ~30 seconds | ~3-5 minutes | **A** |
| **Debugging** | Simple | Moderate | **A** |
| **Scalability** | Limited by free tier | Limited by free tier | Tie |
| **Files to maintain** | ~3 config files | ~8 config files | **A** |
| **Long-term flexibility** | Switch hosts easily | Locked to Docker | **A** |

**Score: Option A wins 8-3**

---

## Recommendation

### Choose Option A (PHP Web Service) if:
- You want the site live in < 30 minutes
- You've never used Docker
- You want standard MySQL compatibility
- You prefer fewer files to maintain
- You want faster builds/deploys

### Choose Option B (Docker) if:
- You're comfortable with Docker
- You need custom Nginx rules (rate limiting, security headers)
- You want everything in one Render account
- You want maximum control over the server environment
- You plan to scale and need custom PHP/nginx tuning

---

## Migration: A → B (If You Start With A and Switch Later)

If you start with Option A and later want Docker's control:

1. Export your WordPress data (WP All Export or native WP export)
2. Set up the Docker repo (use the `render/` folder in this project)
3. Deploy to Render as Docker Web Service
4. Import your data into the new PostgreSQL database
5. Point your domain to the new service

The theme, templates, embeds, and age gate work identically in both options.
