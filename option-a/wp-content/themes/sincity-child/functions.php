<?php
/**
 * SinCity Child Theme — Functions (simplified for Render PHP Web Service)
 * Uses MySQL (PlanetScale) — no PG4WP needed. No Docker dependencies.
 */
if (!defined('ABSPATH')) exit;

// ─── 1. ENVIRONMENT DETECTION ───────────────────────────
function sc_is_render() {
    return !empty(getenv('RENDER'));
}

add_action('init', function () {
    if (sc_is_render() && !defined('DISALLOW_FILE_MODS')) {
        define('DISALLOW_FILE_MODS', true);
    }
    if (sc_is_render() && !defined('DISABLE_WP_CRON')) {
        define('DISABLE_WP_CRON', true);
    }
}, 0);

// Override site URL from env vars
add_filter('option_siteurl', function ($url) {
    return sc_is_render() && getenv('WP_SITEURL') ? getenv('WP_SITEURL') : $url;
});
add_filter('option_home', function ($url) {
    return sc_is_render() && getenv('WP_HOME') ? getenv('WP_HOME') : $url;
});

// ─── 2. VIDEO POST TYPE ─────────────────────────────────
function sc_register_video_post_type() {
    register_post_type('sc_video', [
        'labels' => [
            'name'               => 'Videos',
            'singular_name'      => 'Video',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Video',
            'edit_item'          => 'Edit Video',
            'view_item'          => 'View Video',
            'search_items'       => 'Search Videos',
            'not_found'          => 'No videos found',
            'featured_image'     => 'Video Thumbnail',
            'set_featured_image' => 'Set thumbnail',
            'menu_name'          => 'SinCity Videos',
            'all_items'          => 'All Videos',
        ],
        'public'            => true,
        'publicly_queryable' => true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'rewrite'           => ['slug' => 'video', 'with_front' => false],
        'has_archive'       => true,
        'menu_icon'         => 'dashicons-format-video',
        'supports'          => ['title', 'editor', 'thumbnail', 'comments', 'author', 'excerpt'],
        'taxonomies'        => ['sc_category', 'sc_tag', 'sc_actor'],
        'show_in_rest'      => true,
    ]);
}
add_action('init', 'sc_register_video_post_type');
add_action('after_switch_theme', function () { flush_rewrite_rules(); });

// ─── 3. TAXONOMIES ───────────────────────────────────────
function sc_register_taxonomies() {
    register_taxonomy('sc_category', 'sc_video', [
        'hierarchical' => true, 'public' => true, 'show_in_rest' => true,
        'rewrite' => ['slug' => 'category', 'with_front' => false, 'hierarchical' => true],
        'labels' => ['name' => 'Categories', 'singular_name' => 'Category'],
    ]);
    register_taxonomy('sc_tag', 'sc_video', [
        'hierarchical' => false, 'public' => true, 'show_in_rest' => true,
        'rewrite' => ['slug' => 'tag', 'with_front' => false],
        'labels' => ['name' => 'Tags', 'singular_name' => 'Tag'],
    ]);
    register_taxonomy('sc_actor', 'sc_video', [
        'hierarchical' => false, 'public' => true, 'show_in_rest' => true,
        'rewrite' => ['slug' => 'actor', 'with_front' => false],
        'labels' => ['name' => 'Actors', 'singular_name' => 'Actor'],
    ]);
}
add_action('init', 'sc_register_taxonomies');

// ─── 4. SEED CATEGORIES ──────────────────────────────────
function sc_seed_categories() {
    if (get_option('sc_categories_seeded')) return;
    $parents = ['normal' => 'Normal Porn', 'hentai' => 'Hentai', 'jav' => 'JAV'];
    $children = [
        'normal' => ['Amateur','Professional','Lesbian','MILF','Teen','Anal','Gangbang','POV','Casting','VR'],
        'hentai' => ['2D Animation','3D CGI','Doujin','Game Hentai','Futanari','Tentacle','Vanilla','NTR','Yaoi / Yuri'],
        'jav'    => ['Uncensored','Censored','Idol Solo','Studio','Compilation','Amateur JAV','Classic'],
    ];
    foreach ($parents as $key => $name) {
        $pid = wp_insert_term($name, 'sc_category', ['slug' => $key]);
        if (!is_wp_error($pid) && isset($children[$key])) {
            foreach ($children[$key] as $c) {
                wp_insert_term($c, 'sc_category', ['slug' => sanitize_title($c), 'parent' => $pid['term_id']]);
            }
        }
    }
    update_option('sc_categories_seeded', true);
}
add_action('init', 'sc_seed_categories');

// ─── 5. ACF FIELDS ───────────────────────────────────────
function sc_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;
    acf_add_local_field_group([
        'key' => 'group_video_details', 'title' => 'Video Details',
        'fields' => [
            ['key' => 'field_embed_url', 'label' => 'Embed URL', 'name' => 'embed_url', 'type' => 'url', 'required' => 1],
            ['key' => 'field_embed_code', 'label' => 'Raw Embed Code', 'name' => 'embed_code', 'type' => 'textarea', 'rows' => 4],
            ['key' => 'field_duration', 'label' => 'Duration', 'name' => 'duration', 'type' => 'text', 'placeholder' => '25:30'],
            ['key' => 'field_source_site', 'label' => 'Source Site', 'name' => 'source_site', 'type' => 'select',
                'choices' => ['ph' => 'Pornhub','xv' => 'XVideos','xh' => 'xHamster','rb' => 'RedTube','tn' => 'Tube8','yp' => 'YouPorn','ep' => 'Eporner','ot' => 'Other']],
            ['key' => 'field_external_id', 'label' => 'External ID', 'name' => 'external_id', 'type' => 'text'],
            ['key' => 'field_views_count', 'label' => 'View Count', 'name' => 'views_count', 'type' => 'number', 'default_value' => 0],
            ['key' => 'field_rating_avg', 'label' => 'Avg Rating', 'name' => 'rating_avg', 'type' => 'number', 'default_value' => 0, 'max' => 10, 'step' => 0.1],
            ['key' => 'field_rating_count', 'label' => 'Rating Count', 'name' => 'rating_count', 'type' => 'number', 'default_value' => 0],
            ['key' => 'field_featured', 'label' => 'Featured', 'name' => 'featured', 'type' => 'true_false', 'ui' => true],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'sc_video']]],
        'style' => 'seamless', 'position' => 'acf_after_title',
    ]);
}
add_action('acf/init', 'sc_register_acf_fields');

// ─── 6. EMBED HANDLER ────────────────────────────────────
function sc_filter_embed($embed_code, $embed_url, $source_site = '') {
    if (empty($embed_url) && empty($embed_code)) return '';
    $clean = ''; $id = '';

    if (!empty($embed_url)) {
        $host = wp_parse_url($embed_url, PHP_URL_HOST) ?? '';
        if (empty($source_site) || $source_site === 'ot') {
            if (str_contains($host, 'pornhub')) $source_site = 'ph';
            elseif (str_contains($host, 'xvideos')) $source_site = 'xv';
            elseif (str_contains($host, 'xhamster')) $source_site = 'xh';
            elseif (str_contains($host, 'redtube')) $source_site = 'rb';
            elseif (str_contains($host, 'tube8')) $source_site = 'tn';
            elseif (str_contains($host, 'youporn')) $source_site = 'yp';
            elseif (str_contains($host, 'eporner')) $source_site = 'ep';
        }
    }

    switch ($source_site) {
        case 'ph':
            preg_match('/(?:viewkey=)([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) $clean = '<iframe src="https://www.pornhub.com/embed/'.esc_attr($id).'" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            break;
        case 'xv':
            preg_match('/(?:video|embed)\/([a-zA-Z0-9\-_]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) $clean = '<iframe src="https://www.xvideos.com/embedframe/'.esc_attr($id).'" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            break;
        case 'xh':
            preg_match('/xhamster\.com\/(?:videos|embed)\/([a-zA-Z0-9\-]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) $clean = '<iframe src="https://xhamster.com/embed/'.esc_attr($id).'" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            break;
        case 'rb':
            preg_match('/(?:video|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) $clean = '<iframe src="https://www.redtube.com/embed/'.esc_attr($id).'" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            break;
        case 'tn':
            preg_match('/(?:embed\/)?([a-zA-Z0-9]+)/', wp_parse_url($embed_url, PHP_URL_PATH) ?? '', $m);
            $id = $m[1] ?? '';
            if ($id) $clean = '<iframe src="https://www.tube8.com/embed/'.esc_attr($id).'" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            break;
        case 'yp':
            preg_match('/(?:watch|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) $clean = '<iframe src="https://www.youporn.com/embed/'.esc_attr($id).'" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            break;
        case 'ep':
            preg_match('/(?:video|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
            $id = $m[1] ?? '';
            if ($id) $clean = '<iframe src="https://www.eporner.com/embed/'.esc_attr($id).'" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" loading="lazy"></iframe>';
            break;
        default:
            if (preg_match('/<iframe[^>]+src="([^"]+)"[^>]*>/i', $embed_code, $m)) {
                $src = remove_query_arg(['utm_source','utm_medium','utm_campaign','ref','_ga','_gac'], $m[1]);
                $clean = '<iframe src="'.esc_url($src).'" frameborder="0" allowfullscreen allow="autoplay; encrypted-media; gyroscope" loading="lazy"></iframe>';
            }
            break;
    }
    if (empty($clean) && !empty($embed_code)) {
        $clean = wp_kses($embed_code, ['iframe' => ['src'=>[],'frameborder'=>[],'allowfullscreen'=>[],'allow'=>[],'loading'=>[]]]);
    }
    return $clean;
}

// ─── 7. RENDER PLAYER ─────────────────────────────────────
function sc_render_player($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    if (!$post_id) return '';
    $embed_url  = get_field('embed_url', $post_id);
    $embed_code = get_field('embed_code', $post_id);
    $source     = get_field('source_site', $post_id);
    $clean      = sc_filter_embed($embed_code, $embed_url, $source);
    if (empty($clean)) return '<div class="sc-error">Video unavailable.</div>';
    ob_start(); ?>
    <div class="sc-player-wrapper" itemscope itemtype="https://schema.org/VideoObject">
        <meta itemprop="name" content="<?php echo esc_attr(get_the_title($post_id)); ?>" />
        <meta itemprop="duration" content="<?php echo sc_duration_to_iso(get_field('duration', $post_id)); ?>" />
        <meta itemprop="thumbnailUrl" content="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full') ?: ''); ?>" />
        <div class="sc-player-container"><?php echo $clean; ?></div>
        <div class="sc-player-watermark">
            <svg viewBox="0 0 120 20"><text x="0" y="16" font-family="Cinzel,serif" font-size="14" font-weight="700" fill="white" opacity="0.15">SINCITY</text></svg>
        </div>
    </div>
    <?php return ob_get_clean();
}

// ─── 8. VIEW COUNTER ─────────────────────────────────────
function sc_track_view() {
    if (!is_singular('sc_video') || wp_doing_ajax() || wp_doing_cron()) return;
    $pid = get_the_ID();
    if (!isset($_COOKIE['sc_viewed_'.$pid])) {
        update_field('views_count', (int) get_field('views_count', $pid) + 1, $pid);
        setcookie('sc_viewed_'.$pid, '1', time()+3600, '/', '', !empty($_SERVER['HTTPS']), true);
    }
}
add_action('wp', 'sc_track_view');

// ─── 9. HELPERS ──────────────────────────────────────────
function sc_duration_to_iso($d) {
    if (!$d) return '';
    $p = explode(':', $d); $c = count($p);
    if ($c === 2) return sprintf('PT%02dM%02dS', (int)$p[0], (int)$p[1]);
    if ($c === 3) return sprintf('PT%02dH%02dM%02dS', (int)$p[0], (int)$p[1], (int)$p[2]);
    return '';
}
function sc_format_views($c) { $c=(int)$c; return $c>=1000000?number_format($c/1000000,1).'M':($c>=1000?number_format($c/1000,1).'K':(string)$c); }

// ─── 10. VIDEO CARD ──────────────────────────────────────
function sc_render_video_card($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();
    if (!$post_id) return '';
    global $post; $saved = $post ?? null; $post = get_post($post_id); setup_postdata($post);
    ob_start(); ?>
    <div class="sc-video-card"><a href="<?php the_permalink(); ?>">
        <div class="thumb-wrap">
            <?php if (has_post_thumbnail($post_id)): the_post_thumbnail('medium', ['loading'=>'lazy','alt'=>esc_attr(get_the_title($post_id))]); else: ?><div class="thumb-placeholder"><span>SinCity</span></div><?php endif; ?>
            <?php $d=get_field('duration'); if($d): ?><span class="duration"><?php echo esc_html($d); ?></span><?php endif; ?>
            <?php $s=get_field('source_site'); if($s): ?><span class="source-badge"><?php echo esc_html(strtoupper($s)); ?></span><?php endif; ?>
        </div>
        <div class="card-body"><h3 class="card-title"><?php the_title(); ?></h3>
            <div class="card-meta"><span><?php echo sc_format_views(get_field('views_count')?:0); ?> views</span></div>
        </div>
    </a></div>
    <?php $out = ob_get_clean(); wp_reset_postdata();
    if ($saved) { $post = $saved; setup_postdata($post); }
    return $out;
}

// ─── 11. TRENDING QUERY ──────────────────────────────────
function sc_get_trending($limit = 12, $days = 7) {
    return new WP_Query([
        'post_type'=>'sc_video','posts_per_page'=>absint($limit),
        'meta_key'=>'views_count','orderby'=>'meta_value_num','order'=>'DESC',
        'date_query'=>[['after'=>absint($days).' days ago']],'no_found_rows'=>true,
    ]);
}

// ─── 12. RELATED QUERY ───────────────────────────────────
function sc_get_related($post_id, $limit = 8) {
    $cats = wp_get_post_terms($post_id, 'sc_category', ['fields'=>'ids']);
    $tags = wp_get_post_terms($post_id, 'sc_tag', ['fields'=>'ids']);
    $args = ['post_type'=>'sc_video','posts_per_page'=>absint($limit),'post__not_in'=>[$post_id],'orderby'=>'rand','no_found_rows'=>true];
    $tq = ['relation'=>'OR'];
    if (!empty($cats)) $tq[] = ['taxonomy'=>'sc_category','field'=>'term_id','terms'=>$cats];
    if (!empty($tags)) $tq[] = ['taxonomy'=>'sc_tag','field'=>'term_id','terms'=>$tags];
    if (count($tq) > 1) $args['tax_query'] = $tq;
    return new WP_Query($args);
}

// ─── 13. SHORTCODES ──────────────────────────────────────
add_shortcode('sincity_trending', function($a){
    $a=shortcode_atts(['count'=>12],$a); $q=sc_get_trending(absint($a['count']));
    if(!$q->have_posts()) return '<p>No trending.</p>';
    $o='<div class="sc-video-grid">'; while($q->have_posts()){$q->the_post();$o.=sc_render_video_card();} $o.='</div>';
    wp_reset_postdata(); return $o;
});
add_shortcode('sincity_category', function($a){
    $a=shortcode_atts(['slug'=>'','count'=>8],$a); if(empty($a['slug'])) return '<p>Specify slug.</p>';
    $q=new WP_Query(['post_type'=>'sc_video','posts_per_page'=>absint($a['count']),'no_found_rows'=>true,
        'tax_query'=>[['taxonomy'=>'sc_category','field'=>'slug','terms'=>sanitize_title($a['slug'])]]]);
    if(!$q->have_posts()) return '<p>No videos.</p>';
    $o='<div class="sc-video-grid">'; while($q->have_posts()){$q->the_post();$o.=sc_render_video_card();} $o.='</div>';
    wp_reset_postdata(); return $o;
});

// ─── 14. AGE GATE ────────────────────────────────────────
function sc_start_session() { if(!session_id()&&!headers_sent()) session_start(); }
add_action('init','sc_start_session',1);

function sc_age_gate_check() {
    if(is_admin()||wp_doing_ajax()||wp_doing_cron()) return;
    $v=!empty($_COOKIE['sincity_age_verified'])||(!empty($_SESSION['age_verified'])&&$_SESSION['age_verified']===true);
    if(!$v){ $f=get_stylesheet_directory().'/age-gate.php'; if(file_exists($f)){include $f;exit;} }
}
add_action('template_redirect','sc_age_gate_check',1);

// ─── 15. TRENDING PAGE TEMPLATE ─────────────────────────
add_filter('theme_page_templates', function($t){ $t['page-trending.php']='Trending Videos'; return $t; });
add_filter('template_include', function($t){
    if(is_page_template('page-trending.php')){ $f=get_stylesheet_directory().'/page-trending.php'; if(file_exists($f)) return $f; }
    return $t;
});

// ─── 16. ENQUEUE ─────────────────────────────────────────
add_action('wp_enqueue_scripts', function() {
    $v=wp_get_theme()->get('Version')?:'1.0';
    wp_enqueue_style('sincity-fonts','https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Cinzel+Decorative:wght@700&family=Inter:wght@400;500;600&display=swap',[],null);
    wp_enqueue_style('sincity-main', get_stylesheet_directory_uri().'/assets/css/main.css', ['sincity-fonts'], $v);
    $js=get_stylesheet_directory().'/assets/js/player.js';
    if(file_exists($js)) wp_enqueue_script('sincity-player', get_stylesheet_directory_uri().'/assets/js/player.js', [], $v, ['in_footer'=>true, 'strategy'=>'defer']);
});

// ─── 17. DEDUP ────────────────────────────────────────────
add_filter('wp_all_import_is_post_to_create', function($create, $data) {
    if (!empty($data['external_id'])) {
        $e=get_posts(['post_type'=>'sc_video','meta_key'=>'external_id','meta_value'=>$data['external_id'],'fields'=>'ids','posts_per_page'=>1]);
        if(!empty($e)) return false;
    }
    if (!empty($data['embed_url'])) {
        $h=md5($data['embed_url']); $e=get_posts(['post_type'=>'sc_video','meta_key'=>'embed_url_hash','meta_value'=>$h,'fields'=>'ids','posts_per_page'=>1]);
        if(!empty($e)) return false;
    }
    return $create;
},10,2);

add_action('save_post_sc_video', function($pid){
    $u=get_field('embed_url',$pid); if($u) update_post_meta($pid,'embed_url_hash',md5($u));
});
