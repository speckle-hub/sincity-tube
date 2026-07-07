<?php
/**
 * Template Name: Trending Videos
 * SinCity — top trending videos from the last 7 days.
 */
if (!defined('ABSPATH')) exit;
get_header();
$trending = sc_get_trending(24, 7);
?>

<div class="sc-single">
  <div class="sc-section-header">
    <h2>Trending Now</h2>
    <div class="sc-bar"></div>
  </div>

  <?php if ($trending->have_posts()): ?>
    <div class="sc-video-grid">
      <?php while ($trending->have_posts()): $trending->the_post(); ?>
        <?php echo sc_render_video_card(); ?>
      <?php endwhile; ?>
    </div>
    <?php wp_reset_postdata(); ?>
  <?php else: ?>
    <p style="color:var(--sc-text3);text-align:center;padding:3rem 0;">No trending videos yet. Check back soon.</p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
