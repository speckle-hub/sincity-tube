<?php
/**
 * SinCity — Category Archive
 * Template: taxonomy-sc_category.php
 *
 * Used for: /category/normal/, /category/hentai/, /category/jav/
 * and all subcategories like /category/normal/amateur/
 */
get_header();

$queried   = get_queried_object();
$cat_name  = single_term_title('', false);
$cat_desc  = term_description();
$children  = get_terms([
    'taxonomy'   => 'sc_category',
    'parent'     => $queried->term_id,
    'hide_empty' => false,
]);
$count = $queried->count;
?>
<div class="sc-container">
    <nav class="sc-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span class="sep">/</span>
        <?php
        // Build breadcrumb trail for hierarchical cats
        $ancestors = get_ancestors($queried->term_id, 'sc_category');
        $ancestors = array_reverse($ancestors);
        foreach ($ancestors as $ancestor_id):
            $anc = get_term($ancestor_id);
            ?><a href="<?php echo get_term_link($anc); ?>"><?php echo esc_html($anc->name); ?></a><span class="sep">/</span><?php
        endforeach;
        ?>
        <span class="current"><?php echo esc_html($cat_name); ?></span>
    </nav>

    <div class="sc-cat-header">
        <h1><?php echo esc_html($cat_name); ?></h1>
        <?php if ($cat_desc): ?><div class="cat-desc"><?php echo wp_kses_post($cat_desc); ?></div><?php endif; ?>
        <p class="subtitle"><?php echo number_format($count); ?> videos</p>
    </div>

    <?php if (!empty($children)): ?>
        <div class="sc-tags" style="margin-bottom:20px;">
            <?php foreach ($children as $child): ?>
                <a href="<?php echo get_term_link($child); ?>" class="sc-tag"><?php echo esc_html($child->name); ?> (<?php echo $child->count; ?>)</a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="sc-video-grid">
        <?php if (have_posts()):
            while (have_posts()): the_post();
                echo sc_render_video_card(get_the_ID());
            endwhile;
        else: ?>
            <div class="sc-empty"><h3>No Videos in <?php echo esc_html($cat_name); ?></h3><p>Videos coming soon.</p></div>
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
