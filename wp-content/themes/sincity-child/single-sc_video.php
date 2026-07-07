<?php
/**
 * SinCity — Single Video Page
 * Template: single-sc_video.php
 */
get_header();
the_post();

$post_id  = get_the_ID();
$views    = (int) get_field('views_count', $post_id);
$duration = get_field('duration', $post_id);
$rating   = (float) get_field('rating_avg', $post_id);
$source   = get_field('source_site', $post_id);
$embed_url= get_field('embed_url', $post_id);
$cats     = wp_get_post_terms($post_id, 'sc_category');
$tags     = wp_get_post_terms($post_id, 'sc_tag');
$actors   = wp_get_post_terms($post_id, 'sc_actor');
?>
<div itemscope itemtype="https://schema.org/VideoObject">
    <meta itemprop="name" content="<?php echo esc_attr(get_the_title()); ?>" />
    <meta itemprop="description" content="<?php echo esc_attr(get_the_excerpt() ?: wp_trim_words(get_the_content(), 30)); ?>" />
    <meta itemprop="thumbnailUrl" content="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full') ?: ''); ?>" />
    <meta itemprop="uploadDate" content="<?php echo get_the_date('c'); ?>" />
    <meta itemprop="duration" content="<?php echo sc_duration_to_iso($duration); ?>" />
    <meta itemprop="interactionStatistic" content="<?php echo $views; ?> UserPlays" />
    <?php if ($embed_url): ?><meta itemprop="embedUrl" content="<?php echo esc_url($embed_url); ?>" /><?php endif; ?>
    <?php foreach ($tags as $t): ?><meta itemprop="keywords" content="<?php echo esc_attr($t->name); ?>" /><?php endforeach; ?>
</div>

<div class="sc-container">
    <nav class="sc-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span class="sep">/</span>
        <?php if (!empty($cats)): ?>
            <a href="<?php echo get_term_link($cats[0]); ?>"><?php echo esc_html($cats[0]->name); ?></a><span class="sep">/</span>
        <?php endif; ?>
        <span class="current"><?php the_title(); ?></span>
    </nav>

    <?php echo sc_render_player($post_id); ?>

    <div class="video-meta-bar">
        <h1 class="video-title"><?php the_title(); ?></h1>
        <div class="meta-row">
            <span class="views">&#9679; <?php echo number_format($views); ?> views</span>
            <?php if ($duration): ?><span class="duration">&#9679; <?php echo esc_html($duration); ?></span><?php endif; ?>
            <?php if ($rating > 0): ?><span class="rating">&#9679; &#9733; <?php echo number_format($rating, 1); ?></span><?php endif; ?>
            <?php if ($source): ?><span class="source-link">&#9679; Source: <?php echo esc_html(strtoupper($source)); ?></span><?php endif; ?>
        </div>
        <div class="action-bar" role="group" aria-label="Video actions">
            <button class="btn-like" data-post="<?php echo $post_id; ?>" aria-label="Like this video">&#10084; Like</button>
            <button class="btn-fav" data-post="<?php echo $post_id; ?>" aria-label="Add to favorites">&#9734; Favorite</button>
            <button class="btn-share" data-url="<?php the_permalink(); ?>" aria-label="Share" onclick="navigator.clipboard.writeText(this.dataset.url);this.textContent='Copied!';">&#8593; Share</button>
            <a href="/contact/?report=<?php echo $post_id; ?>" class="btn-report" aria-label="Report this video">&#9873; Report</a>
        </div>
    </div>

    <?php if (get_the_content()): ?><div class="video-desc"><?php the_content(); ?></div><?php endif; ?>
</div>

<div class="sc-container">
    <?php if (!empty($actors)): ?>
        <div class="sc-tags"><strong style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-right:5px;">Actors:</strong>
        <?php foreach ($actors as $actor): ?><a href="<?php echo get_term_link($actor); ?>" class="sc-tag" itemprop="actor"><?php echo esc_html($actor->name); ?></a><?php endforeach; ?></div>
    <?php endif; ?>
    <?php if (!empty($tags)): ?>
        <div class="sc-tags"><strong style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-right:5px;">Tags:</strong>
        <?php foreach ($tags as $tag): ?><a href="<?php echo get_term_link($tag); ?>" class="sc-tag"><?php echo esc_html($tag->name); ?></a><?php endforeach; ?></div>
    <?php endif; ?>
    <?php if (!empty($cats)): ?>
        <div class="sc-tags"><strong style="color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;margin-right:5px;">Categories:</strong>
        <?php foreach ($cats as $cat): ?><a href="<?php echo get_term_link($cat); ?>" class="sc-tag"><?php echo esc_html($cat->name); ?></a><?php endforeach; ?></div>
    <?php endif; ?>
</div>

<div class="sc-container sc-section">
    <div class="sc-section-header"><h2>Related <span class="accent">Videos</span></h2></div>
    <div class="sc-video-grid">
        <?php
        $related = sc_get_related($post_id, 8);
        if ($related->have_posts()):
            while ($related->have_posts()): $related->the_post(); echo sc_render_video_card(get_the_ID()); endwhile;
            wp_reset_postdata();
        else:
            $latest = new WP_Query(['post_type'=>'sc_video','posts_per_page'=>8,'post__not_in'=>[$post_id],'no_found_rows'=>true]);
            while ($latest->have_posts()): $latest->the_post(); echo sc_render_video_card(get_the_ID()); endwhile;
            wp_reset_postdata();
        endif;
        ?>
    </div>
</div>

<div class="sc-container">
    <div class="video-comments">
        <?php if (comments_open() || get_comments_number()): comments_template();
        else: ?><p style="color:var(--text-muted);font-size:0.85rem;">Comments are disabled for this video.</p><?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
