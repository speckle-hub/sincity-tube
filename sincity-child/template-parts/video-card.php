<?php
/**
 * SinCity — Reusable Video Card Partial
 * Usage: include get_stylesheet_directory() . '/template-parts/video-card.php';
 *
 * Expects: $post_id (int) — optional, defaults to current post in loop
 */
$card_post_id = isset($post_id) ? $post_id : get_the_ID();
echo sc_render_video_card($card_post_id);
