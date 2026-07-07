<?php
/**
 * SinCity — Render Health Check
 * Location: /var/www/html/render/scripts/healthz.php
 * Copied to root during Docker build
 */
header('Content-Type: text/plain');
http_response_code(200);
echo 'OK';
