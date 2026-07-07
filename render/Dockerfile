# ═══════════════════════════════════════════════════════════════════════
# SinCity — Render.com Dockerfile (PHP 8.2 + Nginx + WordPress)
# ═══════════════════════════════════════════════════════════════════════

FROM php:8.2-fpm-bookworm AS base

# ─── System dependencies ──────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libonig-dev \
    curl \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# ─── PHP extensions ───────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        zip \
        gd \
        mbstring \
        xml \
        bcmath \
        intl \
        opcache

# ─── OPcache config ───────────────────────────────────────────────
RUN docker-php-ext-enable opcache
COPY render/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY render/php/php.ini /usr/local/etc/php/conf.d/sincity.ini

# ─── Nginx config ─────────────────────────────────────────────────
COPY render/nginx/default.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/ \
    && rm -f /etc/nginx/conf.d/default.conf

# ─── Supervisor config ────────────────────────────────────────────
COPY render/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ─── Application ──────────────────────────────────────────────────
COPY . /var/www/html/

RUN set -ex \
    && mkdir -p /var/www/html/wp-content/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod +x /var/www/html/render/scripts/start.sh \
    && chmod +x /var/www/html/render/scripts/wp-config-dynamic.php

WORKDIR /var/www/html

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/healthz.php || exit 1

EXPOSE 80

CMD ["/var/www/html/render/scripts/start.sh"]
