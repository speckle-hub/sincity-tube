<?php
/**
 * SinCity — Core Functions & Embed Handler
 * Place in: sincity-child/functions.php (append to existing)
 */

// ─── 1. VIDEO POST TYPE ───────────────────────────────────────────────
function sc_register_video_post_type() {
    $labels = [
        'name'               => 'Videos',
        'singular_name'      => 'Video',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Video',
        'edit_item'          => 'Edit Video',
        'view_item'          => 'View Video',
        'search_items'       => 'Search Videos',
        'not_found'          => 'No videos found',
        'not_found_in_trash' => 'No videos found in trash',
        'featured_image'     => 'Video Thumbnail',
        'set_featured_image' => 'Set thumbnail',
    ];

    register_post_type('sc_video', [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'video', 'with_front' => false],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-format-video',
        'supports'           => ['title', 'editor', 'thumbnail', 'comments', 'author', 'excerpt'],
        'taxonomies'         => ['sc_category', 'sc_tag', 'sc_actor'],
        'show_in_rest'       => true,
    ]);
}
add_action('init', 'sc_register_video_post_type');

// Flush rewrites on theme switch only
add_action('after_switch_theme', function () { flush_rewrite_rules(); });

// ─── 2. TAXONOMIES ────────────────────────────────────────────────────
function sc_register_taxonomies() {
    // Categories
    register_taxonomy('sc_category', 'sc_video', [
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'labels'            => [
            'name'              => 'Video Categories',
            'singular_name'     => 'Category',
            'search_items'      => 'Search Categories',
            'all_items'         => 'All Categories',
            'parent_item'       => 'Parent Category',
            'parent_item_colon' => 'Parent Category:',
            'edit_item'         => 'Edit Category',
            'update_item'       => 'Update Category',
            'add_new_item'      => 'Add New Category',
            'new_item_name'     => 'New Category Name',
        ],
        'rewrite'           => ['slug' => 'category', 'with_front' => false, 'hierarchical' => true],
    ]);

    // Tags
    register_taxonomy('sc_tag', 'sc_video', [
        'hierarchical'      => false,
        'public'            => true,
        'show_in_rest'      => true,
        'labels'            => [
            'name'          => 'Tags',
            'singular_name' => 'Tag',
        ],
        'rewrite'           => ['slug' => 'tag', 'with_front' => false],
    ]);

    // Actors
    register_taxonomy('sc_actor', 'sc_video', [
        'hierarchical'      => false,
        'public'            => true,
        'show_in_rest'      => true,
        'labels'            => [
            'name'          => 'Actors',
            'singular_name' => 'Actor',
        ],
        'rewrite'           => ['slug' => 'actor', 'with_front' => false],
    ]);
}
add_action('init', 'sc_register_taxonomies');

// ─── 3. ACF FIELDS (programmatic, no ACF UI needed) ──────────────────
function sc_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group([
        'key'    => 'group_video_details',
        'title'  => 'Video Details',
        'fields' => [
            [
                'key'   => 'field_embed_url',
                'label' => 'Embed URL',
                'name'  => 'embed_url',
                'type'  => 'url',
                'instructions' => 'Full URL to the video on the source site',
            ],
            [
                'key'   => 'field_embed_code',
                'label' => 'Raw Embed Code',
                'name'  => 'embed_code',
                'type'  => 'textarea',
                'rows'  => 4,
                'instructions' => 'Paste the full iframe embed code as fallback',
            ],
            [
                'key'   => 'field_duration',
                'label' => 'Duration',
                'name'  => 'duration',
                'type'  => 'text',
                'placeholder' => '25:30',
            ],
            [
                'key'   => 'field_source_site',
                'label' => 'Source Site',
                'name'  => 'source_site',
                'type'  => 'select',
                'choices' => [
                    'ph' => 'Pornhub',
                    'xv' => 'XVideos',
                    'xh' => 'xHamster',
                    'rb' => 'RedTube',
                    'tn' => 'Tube8',
                    'yp' => 'YouPorn',
                    'ep' => 'Eporner',
                    'ot' => 'Other',
                ],
            ],
            [
                'key'   => 'field_external_id',
                'label' => 'External Video ID',
                'name'  => 'external_id',
                'type'  => 'text',
                'instructions' => 'Unique ID on the source site (used for dedup)',
            ],
            [
                'key'   => 'field_views_count',
                'label' => 'View Count',
                'name'  => 'views_count',
                'type'  => 'number',
                'default_value' => 0,
            ],
            [
                'key'   => 'field_rating_avg',
                'label' => 'Average Rating',
                'name'  => 'rating_avg',
                'type'  => 'number',
                'default_value' => 0,
                'min'   => 0,
                'max'   => 10,
                'step'  => 0.1,
            ],
            [
                'key'   => 'field_rating_count',
                'label' => 'Rating Count',
                'name'  => 'rating_count',
                'type'  => 'number',
                'default_value' => 0,
            ],
            [
                'key'   => 'field_featured',
                'label' => 'Featured in Hero',
                'name'  => 'featured',
                'type'  => 'true_false',
                'ui'    => true,
            ],
        ],
        'location' => [
            [['param' => 'post_type', 'operator' => '==', 'value' => 'sc_video']],
        ],
        'style' => 'seamless',
    ]);
}
add_action('acf/init', 'sc_register_acf_fields');

// ─── 4. EMBED HANDLER (clean, no ads, no tracking) ───────────────────
function sc_filter_embed($embed_code, $embed_url, $source_site) {
    if (empty($embed_url) && empty($embed_code)) return '';

    $clean = '';
    $id    = '';

    // Try URL-based extraction first
    if (!empty($embed_url)) {
        $parsed = wp_parse_url($embed_url);
        $host   = $parsed['host'] ?? '';
        $path   = $parsed['path'] ?? '';
        $query  = $parsed['query'] ?? '';

        // Detect source from URL if not provided
        if (empty($source_site) || $source_site === 'ot') {
            if (strpos($host, 'pornhub') !== false)       $source_site = 'ph';
            elseif (strpos($host, 'xvideos') !== false)   $source_site = 'xv';
            elseif (strpos($host, 'xhamster') !== false)  $source_site = 'xh';
            elseif (strpos($host, 'redtube') !== false)   $source_site = 'rb';
            elseif (strpos($host, 'tube8') !== false)     $source_site = 'tn';
            elseif (strpos($host, 'youporn') !== false)   $source_site = 'yp';
            elseif (strpos($host, 'eporner') !== false)   $source_site = 'ep';
        }
    }

    switch ($source_site) {
        case 'ph': // Pornhub
            preg_match('/(?:viewkey=)([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.pornhub.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            }
            break;

        case 'xv': // XVideos
            preg_match('/(?:video|embed)\/(\d+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            // Also check for alphanumeric IDs (new format)
            if (!$id) preg_match('/(?:video|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.xvideos.com/embedframe/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            }
            break;

        case 'xh': // xHamster
            preg_match('/xhamster\.com\/(?:videos|embed)\/([a-zA-Z0-9\-]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://xhamster.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        case 'rb': // RedTube
            preg_match('/(?:video|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.redtube.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        case 'tn': // Tube8
            preg_match('/tube8\.com\/(?:embed\/)?([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.tube8.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        case 'yp': // YouPorn
            preg_match('/(?:watch|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.youporn.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        case 'ep': // Eporner
            preg_match('/(?:video|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.eporner.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        default:
            // Fallback: extract iframe src from raw embed code, strip all tracking params
            if (preg_match('/<iframe[^>]+src="([^"]+)"[^>]*>/i', $embed_code, $m)) {
                $src = $m[1];
                // Remove tracking query params (utm_, ref_, _ga, etc.)
                $src = remove_query_arg(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'ref', '_ga', '_gac'], $src);
                $clean = '<iframe src="' . esc_url($src)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            }
            break;
    }

    // Ultimate fallback: just sanitize whatever embed code was provided
    if (empty($clean) && !empty($embed_code)) {
        $clean = wp_kses($embed_code, [
            'iframe' => [
                'src'             => [],
                'width'           => [],
                'height'          => [],
                'frameborder'     => [],
                'allowfullscreen' => [],
                'allow'           => [],
                'loading'         => [],
                'style'           => [],
            ],
        ]);
    }

    return $clean;
}

// ─── 5. RENDER PLAYER ─────────────────────────────────────────────────
function sc_render_player($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    if (!$post_id) return '';

    $embed_url  = get_field('embed_url', $post_id);
    $embed_code = get_field('embed_code', $post_id);
    $source     = get_field('source_site', $post_id);

    $clean_embed = sc_filter_embed($embed_code, $embed_url, $source);
    if (empty($clean_embed)) return '<p class="sc-error">Video unavailable</p>';

    ob_start(); ?>
    <div class="sc-player-wrapper" itemscope itemtype="https://schema.org/VideoObject">
        <meta itemprop="name" content="<?php echo esc_attr(get_the_title($post_id)); ?>" />
        <meta itemprop="duration" content="<?php echo sc_duration_to_iso(get_field('duration', $post_id)); ?>" />
        <meta itemprop="thumbnailUrl" content="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full')); ?>" />
        <meta itemprop="embedUrl" content="<?php echo esc_url($embed_url); ?>" />
        <meta itemprop="interactionStatistic" content="UserPlays: <?php echo (int) get_field('views_count', $post_id); ?>" />
        <div class="sc-player-container">
            <?php echo $clean_embed; ?>
        </div>
        <div class="sc-player-watermark">
            <svg viewBox="0 0 120 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <text x="0" y="16" font-family="Cinzel, serif" font-size="14" font-weight="700"
                    fill="white" opacity="0.15">SINCITY</text>
            </svg>
        </div>
    </div>
    <?php return ob_get_clean();
}

// ─── 6. VIEW COUNTER ──────────────────────────────────────────────────
function sc_track_view() {
    if (!is_singular('sc_video')) return;
    if (wp_doing_ajax() || wp_doing_cron()) return;

    $post_id = get_the_ID();
    $views   = (int) get_field('views_count', $post_id);
    $session_key = 'sc_viewed_' . $post_id;

    // Only count unique views per session
    if (!isset($_COOKIE[$session_key])) {
        update_field('views_count', $views + 1, $post_id);
        setcookie($session_key, '1', time() + 3600, '/');
    }
}
add_action('wp', 'sc_track_view');

// ─── 7. TRENDING QUERY ────────────────────────────────────────────────
function sc_get_trending($limit = 12, $days = 7) {
    $args = [
        'post_type'      => 'sc_video',
        'posts_per_page' => absint($limit),
        'meta_key'       => 'views_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'date_query'     => [['after' => absint($days) . ' days ago']],
        'no_found_rows'  => true,
    ];
    return new WP_Query($args);
}

// ─── 8. RELATED VIDEOS QUERY ──────────────────────────────────────────
function sc_get_related($post_id, $limit = 8) {
    $cat_ids = wp_get_post_terms($post_id, 'sc_category', ['fields' => 'ids']);
    $tag_ids = wp_get_post_terms($post_id, 'sc_tag', ['fields' => 'ids']);

    $args = [
        'post_type'      => 'sc_video',
        'posts_per_page' => absint($limit),
        'post__not_in'   => [$post_id],
        'orderby'        => 'rand',
        'no_found_rows'  => true,
    ];

    // Build tax query: match same category OR same tags
    $tax_query = ['relation' => 'OR'];
    if (!empty($cat_ids)) {
        $tax_query[] = [
            'taxonomy' => 'sc_category',
            'field'    => 'term_id',
            'terms'    => $cat_ids,
        ];
    }
    if (!empty($tag_ids)) {
        $tax_query[] = [
            'taxonomy' => 'sc_tag',
            'field'    => 'term_id',
            'terms'    => $tag_ids,
        ];
    }
    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    return new WP_Query($args);
}

// ─── 9. HELPERS ────────────────────────────────────────────────────────
function sc_duration_to_iso($duration) {
    // Convert "25:30" to "PT25M30S"
    if (empty($duration)) return '';
    $parts = explode(':', $duration);
    $count = count($parts);
    if ($count === 2) return sprintf('PT%02dM%02dS', (int) $parts[0], (int) $parts[1]);
    if ($count === 3) return sprintf('PT%02dH%02dM%02dS', (int) $parts[0], (int) $parts[1], (int) $parts[2]);
    return '';
}

function sc_format_views($count) {
    if ($count >= 1000000) return number_format($count / 1000000, 1) . 'M';
    if ($count >= 1000)    return number_format($count / 1000, 1) . 'K';
    return (string) $count;
}

// ─── 10. VIDEO CARD RENDER (reusable) ─────────────────────────────────
function sc_render_video_card($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    if (!$post_id) return '';
    setup_postdata($post_id);
    ob_start(); ?>
    <div class="sc-video-card">
        <a href="<?php the_permalink(); ?>">
            <div class="thumb-wrap">
                <?php if (has_post_thumbnail($post_id)):
                    the_post_thumbnail('medium', ['loading' => 'lazy', 'alt' => esc_attr(get_the_title($post_id))]);
                else: ?>
                    <div class="thumb-placeholder">
                        <span>No Thumbnail</span>
                    </div>
                <?php endif; ?>
                <span class="duration"><?php echo esc_html(get_field('duration', $post_id) ?: '0:00'); ?></span>
                <?php if (get_field('source_site', $post_id)): ?>
                    <span class="source-badge"><?php echo esc_html(strtoupper(get_field('source_site', $post_id))); ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h3 class="card-title"><?php the_title(); ?></h3>
                <div class="card-meta">
                    <span class="views"><?php echo sc_format_views(get_field('views_count') ?: 0); ?> views</span>
                    <span class="rating">
                        <span class="star">&#9733;</span>
                        <?php echo number_format((float) (get_field('rating_avg') ?: 0), 1); ?>
                    </span>
                </div>
            </div>
        </a>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

// ─── 11. TRENDING PAGE TEMPLATE ───────────────────────────────────────
add_filter('theme_page_templates', function ($templates) {
    $templates['page-trending.php'] = 'Trending Videos';
    return $templates;
});

add_filter('template_include', function ($template) {
    if (is_page_template('page-trending.php')) {
        $new = get_stylesheet_directory() . '/page-trending.php';
        if (file_exists($new)) return $new;
    }
    return $template;
});

// ─── 12. AGE GATE CONTROLLER ──────────────────────────────────────────
function sc_start_session() {
    if (!session_id() && !headers_sent()) session_start();
}
add_action('init', 'sc_start_session', 1);

function sc_age_gate_check() {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) return;

    $verified = !empty($_COOKIE['sincity_age_verified'])
        || (!empty($_SESSION['age_verified']) && $_SESSION['age_verified'] === true);

    if (!$verified) {
        $age_file = get_stylesheet_directory() . '/age-gate.php';
        if (file_exists($age_file)) {
            include $age_file;
            exit;
        }
    }
}
add_action('template_redirect', 'sc_age_gate_check', 1);

// ─── 13. SHORTCODE: [sincity_trending count="12"] ─────────────────────
add_shortcode('sincity_trending', function ($atts) {
    $atts = shortcode_atts(['count' => 12], $atts);
    $q    = sc_get_trending(absint($atts['count']));
    if (!$q->have_posts()) return '<p>No trending videos.</p>';

    $out = '<div class="sc-video-grid" data-sc="trending">';
    while ($q->have_posts()) { $q->the_post(); $out .= sc_render_video_card(get_the_ID()); }
    $out .= '</div>';
    wp_reset_postdata();
    return $out;
});

// ─── 14. SHORTCODE: [sincity_category slug="normal" count="8"] ────────
add_shortcode('sincity_category', function ($atts) {
    $atts = shortcode_atts(['slug' => '', 'count' => 8], $atts);
    if (empty($atts['slug'])) return '<p>Specify a category slug.</p>';

    $q = new WP_Query([
        'post_type'      => 'sc_video',
        'posts_per_page' => absint($atts['count']),
        'no_found_rows'  => true,
        'tax_query'      => [[
            'taxonomy' => 'sc_category',
            'field'    => 'slug',
            'terms'    => sanitize_title($atts['slug']),
        ]],
    ]);
    if (!$q->have_posts()) return '<p>No videos found.</p>';

    $out = '<div class="sc-video-grid" data-sc="category">';
    while ($q->have_posts()) { $q->the_post(); $out .= sc_render_video_card(get_the_ID()); }
    $out .= '</div>';
    wp_reset_postdata();
    return $out;
});

// ─── 15. ENQUEUE ASSETS ───────────────────────────────────────────────
add_action('wp_enqueue_scripts', function () {
    $theme = wp_get_theme();
    $ver   = $theme->get('Version') ?: '1.0';

    // Main CSS
    wp_enqueue_style('sincity-main', get_stylesheet_directory_uri() . '/assets/css/main.css', [], $ver);

    // Player JS (defer, in footer)
    $js_path = get_stylesheet_directory() . '/assets/js/player.js';
    if (file_exists($js_path)) {
        wp_enqueue_script('sincity-player', get_stylesheet_directory_uri() . '/assets/js/player.js', [], $ver, ['in_footer' => true, 'strategy' => 'defer']);
    }

    // Google Fonts
    wp_enqueue_style('sincity-fonts', 'https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Cinzel+Decorative:wght@700&family=Inter:wght@400;500;600&display=swap', [], null);
});
