<?php
/**
 * SinCity — Tag Archive
 * Template: taxonomy-sc_tag.php
 *
 * Used for: /tag/{tagname}/
 */
get_header();

$tag  = single_term_title('', false);
$desc = term_description();
$count = get_queried_object()->count;
?>
<div class="sc-container">
    <nav class="sc-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span class="sep">/</span>
        <span class="current">Tag: <?php echo esc_html($tag); ?></span>
    </nav>

    <div class="sc-cat-header">
        <h1>#<?php echo esc_html($tag); ?></h1>
        <?php if ($desc): ?><div class="cat-desc"><?php echo wp_kses_post($desc); ?></div><?php endif; ?>
        <p class="subtitle"><?php echo number_format($count); ?> videos tagged</p>
    </div>

    <div class="sc-video-grid">
        <?php if (have_posts()):
            while (have_posts()): the_post(); echo sc_render_video_card(get_the_ID()); endwhile;
        else: ?>
            <div class="sc-empty"><h3>No Videos Tagged</h3><p>Check back soon.</p></div>
        <?php endif; ?>
    </div>

    <div class="sc-pagination"><?php echo paginate_links(['prev_text'=>'&laquo; Prev','next_text'=>'Next &raquo;','mid_size'=>3]); ?></div>
</div>
<?php get_footer(); ?>
