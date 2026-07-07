<?php
/**
 * SinCity — Video Archive (fallback)
 * Template: archive-sc_video.php
 *
 * Used for /video/ archive, newest videos, or if no other archive template applies.
 */
get_header();
?>
<div class="sc-container">
    <nav class="sc-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span class="sep">/</span>
        <span class="current">All Videos</span>
    </nav>

    <div class="sc-cat-header">
        <h1>All <span class="accent">Videos</span></h1>
        <p class="subtitle">Browse the entire SinCity library</p>
    </div>

    <div class="sc-video-grid">
        <?php if (have_posts()):
            while (have_posts()): the_post();
                echo sc_render_video_card(get_the_ID());
            endwhile;
        else: ?>
            <div class="sc-empty"><h3>No Videos Found</h3><p>Check back soon.</p></div>
        <?php endif; ?>
    </div>

    <div class="sc-pagination">
        <?php echo paginate_links([
            'prev_text' => '&laquo; Prev',
            'next_text' => 'Next &raquo;',
            'mid_size'  => 3,
        ]); ?>
    </div>
</div>
<?php get_footer(); ?>
