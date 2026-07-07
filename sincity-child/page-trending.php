<?php
/**
 * SinCity — Trending Videos Page
 * Template Name: Trending Videos
 */
get_header();

$days = isset($_GET['days']) ? absint($_GET['days']) : 7;
$trending = sc_get_trending(24, $days);
?>
<div class="sc-container">
    <nav class="sc-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span class="sep">/</span>
        <span class="current">Trending</span>
    </nav>

    <div class="sc-section-header">
        <h2>&#128293; Trending <span class="accent">This Week</span><span class="badge">LIVE</span></h2>
        <div class="trending-filters">
            <a href="?days=1"  class="sc-tag <?php echo $days === 1 ? 'active' : ''; ?>">Today</a>
            <a href="?days=7"  class="sc-tag <?php echo $days === 7 ? 'active' : ''; ?>">Week</a>
            <a href="?days=30" class="sc-tag <?php echo $days === 30 ? 'active' : ''; ?>">Month</a>
        </div>
    </div>

    <?php if ($trending->have_posts()): ?>
        <div class="sc-video-grid">
            <?php while ($trending->have_posts()): $trending->the_post(); echo sc_render_video_card(get_the_ID()); endwhile; ?>
        </div>
        <?php
        $total = $trending->max_num_pages;
        if ($total > 1):
        ?><div class="sc-pagination"><?php
            echo paginate_links([
                'total'    => $total,
                'current'  => max(1, get_query_var('paged')),
                'mid_size' => 2,
                'prev_text' => '&laquo; Prev',
                'next_text' => 'Next &raquo;',
            ]);
        ?></div><?php endif; ?>
    <?php else: ?>
        <div class="sc-empty"><h3>No Trending Videos</h3><p>Check back soon as new videos are added daily.</p></div>
    <?php endif; wp_reset_postdata(); ?>
</div>

<style>
.trending-filters{display:flex;gap:6px}
.trending-filters .sc-tag.active{background:rgba(0,240,255,0.12);border-color:var(--accent-cyan);color:var(--text-primary)}
</style>

<?php get_footer(); ?>
