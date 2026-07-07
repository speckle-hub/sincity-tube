<?php
/**
 * SinCity Child Theme — Functions & Embed Handler
 * 
 * Integrates: CPT registration, taxonomies, ACF fields, embed handler,
 * age gate controller, view tracker, trending/related queries,
 * shortcodes, card renderers, and asset enqueue.
 *
 * @package SinCity
 * @version 1.0.0
 */

// ─── Safety: prevent direct access ─────────────────────────────────
if (!defined('ABSPATH')) exit;

// ─── Render.com compatibility layer ──────────────────────────────
require_once __DIR__ . '/render-compatibility.php';

// ─── 1. KADENCE PARENT THEME ───────────────────────────────────────
add_action('after_setup_theme', function () {
    // Load Kadence parent textdomain
    load_child_theme_textdomain('sincity', get_stylesheet_directory() . '/languages');
});

// ─── 2. VIDEO POST TYPE ────────────────────────────────────────────
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
        'remove_featured_image' => 'Remove thumbnail',
        'use_featured_image' => 'Use as thumbnail',
        'archives'          => 'Video Archives',
        'insert_into_item'  => 'Insert into video',
        'uploaded_to_this_item' => 'Uploaded to this video',
        'filter_items_list' => 'Filter videos list',
        'items_list_navigation' => 'Videos list navigation',
        'items_list'        => 'Videos list',
        'menu_name'         => 'SinCity Videos',
        'all_items'         => 'All Videos',
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
        'can_export'         => true,
        'delete_with_user'   => false,
    ]);
}
add_action('init', 'sc_register_video_post_type');
add_action('after_switch_theme', function () { flush_rewrite_rules(); });

// ─── 3. TAXONOMIES ─────────────────────────────────────────────────
function sc_register_taxonomies() {
    $cat_labels = [
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
        'menu_name'         => 'Categories',
    ];

    register_taxonomy('sc_category', 'sc_video', [
        'hierarchical'      => true,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'labels'            => $cat_labels,
        'rewrite'           => ['slug' => 'category', 'with_front' => false, 'hierarchical' => true],
    ]);

    $tag_labels = [
        'name'          => 'Tags',
        'singular_name' => 'Tag',
        'search_items'  => 'Search Tags',
        'all_items'     => 'All Tags',
        'edit_item'     => 'Edit Tag',
        'update_item'   => 'Update Tag',
        'add_new_item'  => 'Add New Tag',
        'new_item_name' => 'New Tag Name',
        'menu_name'     => 'Tags',
    ];

    register_taxonomy('sc_tag', 'sc_video', [
        'hierarchical'      => false,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'labels'            => $tag_labels,
        'rewrite'           => ['slug' => 'tag', 'with_front' => false],
    ]);

    $actor_labels = [
        'name'          => 'Actors',
        'singular_name' => 'Actor',
        'search_items'  => 'Search Actors',
        'all_items'     => 'All Actors',
        'edit_item'     => 'Edit Actor',
        'update_item'   => 'Update Actor',
        'add_new_item'  => 'Add New Actor',
        'new_item_name' => 'New Actor Name',
        'menu_name'     => 'Actors',
    ];

    register_taxonomy('sc_actor', 'sc_video', [
        'hierarchical'      => false,
        'public'            => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'labels'            => $actor_labels,
        'rewrite'           => ['slug' => 'actor', 'with_front' => false],
    ]);
}
add_action('init', 'sc_register_taxonomies');

// ─── 4. SEED DEFAULT CATEGORIES ────────────────────────────────────
function sc_seed_categories() {
    if (get_option('sc_categories_seeded')) return;

    $parents = [
        'normal' => 'Normal Porn',
        'hentai' => 'Hentai',
        'jav'    => 'JAV',
    ];

    $children = [
        'normal' => ['Amateur', 'Professional', 'Lesbian', 'MILF', 'Teen', 'Anal', 'Gangbang', 'POV', 'Casting', 'VR'],
        'hentai' => ['2D Animation', '3D CGI', 'Doujin', 'Game Hentai', 'Futanari', 'Tentacle', 'Vanilla', 'NTR', 'Yaoi / Yuri'],
        'jav'    => ['Uncensored', 'Censored', 'Idol Solo', 'Studio', 'Compilation', 'Amateur JAV', 'Classic'],
    ];

    foreach ($parents as $key => $parent_name) {
        $parent_id = wp_insert_term($parent_name, 'sc_category', ['slug' => $key]);
        if (!is_wp_error($parent_id) && isset($children[$key])) {
            foreach ($children[$key] as $child) {
                wp_insert_term($child, 'sc_category', [
                    'slug' => sanitize_title($child),
                    'parent' => $parent_id['term_id'],
                ]);
            }
        }
    }

    update_option('sc_categories_seeded', true);
}
add_action('init', 'sc_seed_categories');

// ─── 5. ACF FIELDS ─────────────────────────────────────────────────
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
                'required' => 1,
            ],
            [
                'key'   => 'field_embed_code',
                'label' => 'Raw Embed Code',
                'name'  => 'embed_code',
                'type'  => 'textarea',
                'rows'  => 4,
                'instructions' => 'Paste raw iframe code as fallback (auto-extracted if URL provided)',
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
                'instructions' => 'Unique ID on the source site (prevents duplicates)',
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
        'style'           => 'seamless',
        'position'        => 'acf_after_title',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
    ]);
}
add_action('acf/init', 'sc_register_acf_fields');

// ─── 6. EMBED HANDLER ──────────────────────────────────────────────
function sc_filter_embed($embed_code, $embed_url, $source_site = '') {
    if (empty($embed_url) && empty($embed_code)) return '';

    $clean = '';
    $id    = '';

    if (!empty($embed_url)) {
        $parsed = wp_parse_url($embed_url);
        $host   = $parsed['host'] ?? '';
        $path   = $parsed['path'] ?? '';
        $query  = $parsed['query'] ?? '';

        // Auto-detect source from URL if not provided or "Other"
        if (empty($source_site) || $source_site === 'ot') {
            if (str_contains($host, 'pornhub'))      $source_site = 'ph';
            elseif (str_contains($host, 'xvideos'))   $source_site = 'xv';
            elseif (str_contains($host, 'xhamster'))  $source_site = 'xh';
            elseif (str_contains($host, 'redtube'))   $source_site = 'rb';
            elseif (str_contains($host, 'tube8'))     $source_site = 'tn';
            elseif (str_contains($host, 'youporn'))   $source_site = 'yp';
            elseif (str_contains($host, 'eporner'))   $source_site = 'ep';
        }
    }

    switch ($source_site) {
        case 'ph':
            preg_match('/(?:viewkey=)([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.pornhub.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            }
            break;

        case 'xv':
            preg_match('/(?:video|embed)\/(\d+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if (!$id) {
                preg_match('/(?:video|embed)\/([a-zA-Z0-9\-_]+)/', $embed_url, $m);
                $id = $m[1] ?? '';
            }
            if ($id) {
                $clean = '<iframe src="https://www.xvideos.com/embedframe/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            }
            break;

        case 'xh':
            preg_match('/xhamster\.com\/(?:videos|embed)\/([a-zA-Z0-9\-]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://xhamster.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        case 'rb':
            preg_match('/(?:video|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.redtube.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        case 'tn':
            preg_match('/(?:embed\/)?([a-zA-Z0-9]+)/', $path, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.tube8.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        case 'yp':
            preg_match('/(?:watch|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.youporn.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        case 'ep':
            preg_match('/(?:video|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) {
                $clean = '<iframe src="https://www.eporner.com/embed/' . esc_attr($id)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            }
            break;

        default:
            if (preg_match('/<iframe[^>]+src="([^"]+)"[^>]*>/i', $embed_code, $m)) {
                $src = $m[1];
                $src = remove_query_arg([
                    'utm_source', 'utm_medium', 'utm_campaign',
                    'utm_term', 'utm_content', 'ref', '_ga', '_gac',
                ], $src);
                $clean = '<iframe src="' . esc_url($src)
                    . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            }
            break;
    }

    if (empty($clean) && !empty($embed_code)) {
        $clean = wp_kses($embed_code, [
            'iframe' => [
                'src' => [], 'width' => [], 'height' => [],
                'frameborder' => [], 'allowfullscreen' => [],
                'allow' => [], 'loading' => [], 'style' => [],
            ],
        ]);
    }

    return $clean;
}

// ─── 7. RENDER PLAYER ──────────────────────────────────────────────
function sc_render_player($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    if (!$post_id) return '';

    $embed_url  = get_field('embed_url', $post_id);
    $embed_code = get_field('embed_code', $post_id);
    $source     = get_field('source_site', $post_id);
    $clean      = sc_filter_embed($embed_code, $embed_url, $source);

    if (empty($clean)) return '<div class="sc-error">Video embed unavailable. <a href="' . esc_url($embed_url) . '">Watch on source site</a>.</div>';

    ob_start(); ?>
    <div class="sc-player-wrapper" itemscope itemtype="https://schema.org/VideoObject">
        <meta itemprop="name" content="<?php echo esc_attr(get_the_title($post_id)); ?>" />
        <meta itemprop="duration" content="<?php echo sc_duration_to_iso(get_field('duration', $post_id)); ?>" />
        <meta itemprop="thumbnailUrl" content="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full') ?: ''); ?>" />
        <meta itemprop="embedUrl" content="<?php echo esc_url($embed_url); ?>" />
        <meta itemprop="interactionStatistic" content="UserPlays: <?php echo (int) get_field('views_count', $post_id); ?>" />
        <div class="sc-player-container">
            <?php echo $clean; ?>
        </div>
        <div class="sc-player-watermark">
            <svg viewBox="0 0 120 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <text x="0" y="16" font-family="Cinzel, serif" font-size="14" font-weight="700" fill="white" opacity="0.15">SINCITY</text>
            </svg>
        </div>
    </div>
    <?php return ob_get_clean();
}

// ─── 8. VIEW COUNTER ───────────────────────────────────────────────
function sc_track_view() {
    if (!is_singular('sc_video')) return;
    if (wp_doing_ajax() || wp_doing_cron()) return;

    $post_id = get_the_ID();
    $session_key = 'sc_viewed_' . $post_id;

    if (!isset($_COOKIE[$session_key])) {
        $views = (int) get_field('views_count', $post_id);
        update_field('views_count', $views + 1, $post_id);
        setcookie($session_key, '1', time() + 3600, '/', '', !empty($_SERVER['HTTPS']), true);
    }
}
add_action('wp', 'sc_track_view');

// ─── 9. TRENDING QUERY ─────────────────────────────────────────────
function sc_get_trending($limit = 12, $days = 7) {
    return new WP_Query([
        'post_type'      => 'sc_video',
        'posts_per_page' => absint($limit),
        'meta_key'       => 'views_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'date_query'     => [['after' => absint($days) . ' days ago']],
        'no_found_rows'  => true,
    ]);
}

// ─── 10. RELATED VIDEOS ────────────────────────────────────────────
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

    $tax_query = ['relation' => 'OR'];
    if (!empty($cat_ids)) {
        $tax_query[] = ['taxonomy' => 'sc_category', 'field' => 'term_id', 'terms' => $cat_ids];
    }
    if (!empty($tag_ids)) {
        $tax_query[] = ['taxonomy' => 'sc_tag', 'field' => 'term_id', 'terms' => $tag_ids];
    }
    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    return new WP_Query($args);
}

// ─── 11. HELPERS ───────────────────────────────────────────────────
function sc_duration_to_iso($duration) {
    if (empty($duration)) return '';
    $parts = explode(':', $duration);
    $c = count($parts);
    if ($c === 2) return sprintf('PT%02dM%02dS', (int) $parts[0], (int) $parts[1]);
    if ($c === 3) return sprintf('PT%02dH%02dM%02dS', (int) $parts[0], (int) $parts[1], (int) $parts[2]);
    return '';
}

function sc_format_views($count) {
    $count = (int) $count;
    if ($count >= 1000000) return number_format($count / 1000000, 1) . 'M';
    if ($count >= 1000)    return number_format($count / 1000, 1) . 'K';
    return (string) $count;
}

function sc_format_rating($rating) {
    return number_format((float) $rating, 1);
}

// ─── 12. VIDEO CARD RENDERER ───────────────────────────────────────
function sc_render_video_card($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    if (!$post_id) return '';

    global $post;
    $post_saved = $post ?? null;
    $post = get_post($post_id);
    setup_postdata($post);

    ob_start(); ?>
    <div class="sc-video-card">
        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
            <div class="thumb-wrap">
                <?php if (has_post_thumbnail($post_id)):
                    the_post_thumbnail('medium', ['loading' => 'lazy', 'alt' => esc_attr(get_the_title($post_id))]);
                else: ?>
                    <div class="thumb-placeholder"><span>SinCity</span></div>
                <?php endif; ?>
                <?php $dur = get_field('duration', $post_id); if ($dur): ?>
                    <span class="duration"><?php echo esc_html($dur); ?></span>
                <?php endif; ?>
                <?php $src = get_field('source_site', $post_id); if ($src): ?>
                    <span class="source-badge"><?php echo esc_html(strtoupper($src)); ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h3 class="card-title"><?php the_title(); ?></h3>
                <div class="card-meta">
                    <span class="views"><?php echo sc_format_views(get_field('views_count') ?: 0); ?> views</span>
                    <?php $rat = (float) get_field('rating_avg', $post_id); if ($rat > 0): ?>
                        <span class="rating">&#9733; <?php echo sc_format_rating($rat); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
    </div>
    <?php
    $out = ob_get_clean();
    wp_reset_postdata();
    if ($post_saved) {
        $post = $post_saved;
        setup_postdata($post);
    }
    return $out;
}

// ─── 13. SHORTCODES ────────────────────────────────────────────────
add_shortcode('sincity_trending', function ($atts) {
    $atts = shortcode_atts(['count' => 12], $atts);
    $q = sc_get_trending(absint($atts['count']));
    if (!$q->have_posts()) return '<p class="sc-empty">No trending videos.</p>';
    $out = '<div class="sc-video-grid" data-sc="trending">';
    while ($q->have_posts()) { $q->the_post(); $out .= sc_render_video_card(get_the_ID()); }
    $out .= '</div>';
    wp_reset_postdata();
    return $out;
});

add_shortcode('sincity_category', function ($atts) {
    $atts = shortcode_atts(['slug' => '', 'count' => 8], $atts);
    if (empty($atts['slug'])) return '<p class="sc-empty">Specify a category slug.</p>';
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
    if (!$q->have_posts()) return '<p class="sc-empty">No videos in this category.</p>';
    $out = '<div class="sc-video-grid" data-sc="category">';
    while ($q->have_posts()) { $q->the_post(); $out .= sc_render_video_card(get_the_ID()); }
    $out .= '</div>';
    wp_reset_postdata();
    return $out;
});

// ─── 14. AGE GATE ──────────────────────────────────────────────────
function sc_start_session() {
    if (!session_id() && !headers_sent()) session_start();
}
add_action('init', 'sc_start_session', 1);

function sc_age_gate_check() {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) return;

    $verified = !empty($_COOKIE['sincity_age_verified'])
        || (!empty($_SESSION['age_verified']) && $_SESSION['age_verified'] === true);

    if (!$verified) {
        $file = get_stylesheet_directory() . '/age-gate.php';
        if (file_exists($file)) { include $file; exit; }
    }
}
add_action('template_redirect', 'sc_age_gate_check', 1);

// ─── 15. TRENDING PAGE TEMPLATE ────────────────────────────────────
add_filter('theme_page_templates', function ($templates) {
    $templates['page-trending.php'] = 'Trending Videos';
    return $templates;
});

add_filter('template_include', function ($template) {
    if (is_page_template('page-trending.php')) {
        $f = get_stylesheet_directory() . '/page-trending.php';
        if (file_exists($f)) return $f;
    }
    return $template;
});

// ─── 16. ENQUEUE ASSETS ────────────────────────────────────────────
add_action('wp_enqueue_scripts', function () {
    $ver = wp_get_theme()->get('Version') ?: '1.0.0';

    // Google Fonts (preload)
    wp_enqueue_style('sincity-fonts', 'https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Cinzel+Decorative:wght@700&family=Inter:wght@400;500;600&display=swap', [], null);

    // Main theme stylesheet
    wp_enqueue_style('sincity-main', get_stylesheet_directory_uri() . '/assets/css/main.css', ['sincity-fonts'], $ver);

    // Player JS (defer, footer)
    $js = get_stylesheet_directory() . '/assets/js/player.js';
    if (file_exists($js)) {
        wp_enqueue_script(
            'sincity-player',
            get_stylesheet_directory_uri() . '/assets/js/player.js',
            [],
            $ver,
            ['in_footer' => true, 'strategy' => 'defer']
        );
    }

    // Pass localized data to JS
    wp_localize_script('sincity-player', 'scData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'homeUrl' => home_url(),
    ]);
});

// ─── 17. AJAX HANDLERS (favorite, like) ────────────────────────────
add_action('wp_ajax_sc_favorite', function () {
    $post_id = absint($_POST['post_id'] ?? 0);
    $user_id = get_current_user_id();
    if (!$post_id || !$user_id) { wp_send_json_error(); }

    $favs = get_user_meta($user_id, 'sc_favorites', true) ?: [];
    if (in_array($post_id, $favs)) {
        $favs = array_diff($favs, [$post_id]);
        $status = 'removed';
    } else {
        $favs[] = $post_id;
        $status = 'added';
    }
    update_user_meta($user_id, 'sc_favorites', $favs);
    wp_send_json_success(['status' => $status]);
});

// ─── 18. KADENCE OVERRIDES ────────────────────────────────────────
// Remove Kadence default page title on video post types
add_filter('kadence_dynamic_post_title', function ($title) {
    if (is_singular('sc_video')) return '';
    return $title;
});

// ─── 19. WP ALL IMPORT DEDUP ──────────────────────────────────────
add_filter('wp_all_import_is_post_to_create', function ($to_create, $data) {
    if (empty($data['external_id']) && empty($data['embed_url'])) return $to_create;

    // Check by external_id
    if (!empty($data['external_id'])) {
        $exists = get_posts([
            'post_type' => 'sc_video',
            'meta_key'  => 'external_id',
            'meta_value' => $data['external_id'],
            'fields'    => 'ids',
            'posts_per_page' => 1,
        ]);
        if (!empty($exists)) return false;
    }

    // Check by embed_url hash
    if (!empty($data['embed_url'])) {
        $hash = md5($data['embed_url']);
        $exists = get_posts([
            'post_type' => 'sc_video',
            'meta_key'  => 'embed_url_hash',
            'meta_value' => $hash,
            'fields'    => 'ids',
            'posts_per_page' => 1,
        ]);
        if (!empty($exists)) return false;
    }

    return $to_create;
}, 10, 2);

// ─── 20. STORE EMBED URL HASH ON SAVE ─────────────────────────────
add_action('save_post_sc_video', function ($post_id) {
    $url = get_field('embed_url', $post_id);
    if ($url) {
        update_post_meta($post_id, 'embed_url_hash', md5($url));
    }
});
