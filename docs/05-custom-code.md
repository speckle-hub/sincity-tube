# SinCity — Custom Code Snippets

## 1. Embed Handler (functions.php)

Add to `sincity-child/functions.php` — sanitizes embed URLs, removes source ads/tracking, wraps in custom player shell.

```php
<?php
/**
 * SinCity Embed Handler
 * Strips source ads, wraps in custom player with logo overlay
 */

// Register video post type
function sc_register_video_post_type() {
    $labels = array(
        'name'               => 'Videos',
        'singular_name'      => 'Video',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Video',
        'edit_item'          => 'Edit Video',
        'view_item'          => 'View Video',
        'search_items'       => 'Search Videos',
        'not_found'          => 'No videos found',
        'not_found_in_trash' => 'No videos found in trash',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'video'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-format-video',
        'supports'           => array('title', 'editor', 'thumbnail', 'comments', 'author'),
        'taxonomies'         => array('sc_category', 'sc_tag', 'sc_actor'),
    );

    register_post_type('sc_video', $args);
}
add_action('init', 'sc_register_video_post_type');

// Register taxonomies
function sc_register_taxonomies() {
    // Categories (hierarchical)
    register_taxonomy('sc_category', 'sc_video', array(
        'hierarchical' => true,
        'labels' => array(
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
        ),
        'rewrite' => array('slug' => 'category'),
    ));

    // Tags (non-hierarchical)
    register_taxonomy('sc_tag', 'sc_video', array(
        'hierarchical' => false,
        'labels' => array(
            'name'          => 'Tags',
            'singular_name' => 'Tag',
        ),
        'rewrite' => array('slug' => 'tag'),
    ));

    // Actors (hierarchical for linking)
    register_taxonomy('sc_actor', 'sc_video', array(
        'hierarchical' => false,
        'labels' => array(
            'name'          => 'Actors',
            'singular_name' => 'Actor',
        ),
        'rewrite' => array('slug' => 'actor'),
    ));
}
add_action('init', 'sc_register_taxonomies');

// ACF field group (programmatic)
function sc_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group(array(
        'key'    => 'group_video_details',
        'title'  => 'Video Details',
        'fields' => array(
            array(
                'key'   => 'field_embed_url',
                'label' => 'Embed URL',
                'name'  => 'embed_url',
                'type'  => 'url',
            ),
            array(
                'key'   => 'field_embed_code',
                'label' => 'Embed Code',
                'name'  => 'embed_code',
                'type'  => 'textarea',
                'rows'  => 4,
            ),
            array(
                'key'   => 'field_duration',
                'label' => 'Duration',
                'name'  => 'duration',
                'type'  => 'text',
                'placeholder' => 'e.g. 25:30',
            ),
            array(
                'key'   => 'field_source_site',
                'label' => 'Source Site',
                'name'  => 'source_site',
                'type'  => 'select',
                'choices' => array(
                    'ph' => 'Pornhub',
                    'xv' => 'XVideos',
                    'xh' => 'xHamster',
                    'ot' => 'Other',
                ),
            ),
            array(
                'key'   => 'field_external_id',
                'label' => 'External ID',
                'name'  => 'external_id',
                'type'  => 'text',
            ),
            array(
                'key'   => 'field_views_count',
                'label' => 'Views',
                'name'  => 'views_count',
                'type'  => 'number',
                'default_value' => 0,
            ),
            array(
                'key'   => 'field_featured',
                'label' => 'Featured Video',
                'name'  => 'featured',
                'type'  => 'true_false',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'sc_video',
                ),
            ),
        ),
    ));
}
add_action('acf/init', 'sc_register_acf_fields');

/**
 * Clean embed: strip source site tracking, ads, and wrap in SinCity player
 */
function sc_filter_embed($embed_code, $embed_url, $source_site) {
    // Remove iframe if present and rebuild cleanly
    $clean = '';

    if ($embed_url) {
        // Parse source and build clean embed
        switch ($source_site) {
            case 'ph':
                // Pornhub: extract video ID
                preg_match('/(?:viewkey=)([a-zA-Z0-9]+)/', $embed_url, $m);
                $id = $m[1] ?? '';
                $clean = '<iframe src="https://www.pornhub.com/embed/' . esc_attr($id) 
                       . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" '
                       . 'loading="lazy"></iframe>';
                break;

            case 'xv':
                // XVideos
                preg_match('/(?:video|embed)\/(\d+)/', $embed_url, $m);
                $id = $m[1] ?? '';
                $clean = '<iframe src="https://www.xvideos.com/embedframe/' . esc_attr($id)
                       . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" '
                       . 'loading="lazy"></iframe>';
                break;

            case 'xh':
                // xHamster
                preg_match('/xhamster\.com\/(?:videos|embed)\/([a-zA-Z0-9]+)/', $embed_url, $m);
                $id = $m[1] ?? '';
                $clean = '<iframe src="https://xhamster.com/embed/' . esc_attr($id)
                       . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" '
                       . 'loading="lazy"></iframe>';
                break;

            default:
                // Fallback: use raw embed code, strip non-iframe elements
                if (preg_match('/<iframe[^>]+src="([^"]+)"[^>]*>/i', $embed_code, $m)) {
                    $src = $m[1];
                    $clean = '<iframe src="' . esc_url($src) 
                           . '" frameborder="0" allowfullscreen allow="autoplay; encrypted-media" '
                           . 'loading="lazy"></iframe>';
                }
                break;
        }
    }

    return $clean;
}

/**
 * Render video player in theme
 */
function sc_render_player($post_id = null) {
    if (!$post_id) $post_id = get_the_ID();

    $embed_url  = get_field('embed_url', $post_id);
    $embed_code = get_field('embed_code', $post_id);
    $source     = get_field('source_site', $post_id);

    $clean_embed = sc_filter_embed($embed_code, $embed_url, $source);

    ob_start();
    ?>
    <div class="sc-player-wrapper">
        <div class="sc-player-container">
            <?php echo $clean_embed; ?>
        </div>
        <div class="sc-player-overlay">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/logo-watermark.png" 
                 alt="SinCity" class="sc-player-logo" />
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Increment view counter
 */
function sc_track_view() {
    if (is_singular('sc_video')) {
        $views = (int) get_field('views_count');
        update_field('views_count', $views + 1);
    }
}
add_action('wp', 'sc_track_view');

/**
 * Trending query (most viewed in last 7 days)
 */
function sc_get_trending($limit = 12) {
    $args = array(
        'post_type'      => 'sc_video',
        'posts_per_page' => $limit,
        'meta_key'       => 'views_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'date_query'     => array(
            array(
                'after' => '7 days ago',
            ),
        ),
    );
    return new WP_Query($args);
}
```

---

## 2. Age Gate / Homepage Warning

### Template: `sincity-child/age-gate.php`

```php
<?php
/**
 * Age Verification Gate
 * Place before homepage content. Redirects underage users away.
 * Sets 30-day cookie for returning visitors.
 */
session_start();

// Handle form submission
if (isset($_POST['age_verify'])) {
    $birth_year = intval($_POST['birth_year']);
    $birth_month = intval($_POST['birth_month']);
    $age = date('Y') - $birth_year;
    if (date('n') < $birth_month) $age--;

    if ($age >= 18) {
        setcookie('sincity_age_verified', '1', time() + 2592000, '/'); // 30 days
        $_SESSION['age_verified'] = true;
        wp_redirect(home_url());
        exit;
    } else {
        wp_redirect('https://www.google.com');
        exit;
    }
}

// Check if already verified
if (isset($_COOKIE['sincity_age_verified']) || isset($_SESSION['age_verified'])) {
    return; // Show normal content
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SinCity — Age Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0A0A0F;
            color: #F0F0F5;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .age-gate {
            text-align: center;
            max-width: 500px;
            padding: 40px;
        }
        .age-gate h1 {
            font-family: 'Cinzel', serif;
            font-size: 3rem;
            color: #DC143C;
            margin-bottom: 10px;
            letter-spacing: 4px;
        }
        .age-gate .subtitle {
            color: #A0A0B8;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        .age-gate .warning {
            background: rgba(220,20,60,0.1);
            border: 1px solid rgba(220,20,60,0.3);
            padding: 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #FF6B8A;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .age-gate form { display: flex; flex-direction: column; gap: 15px; }
        .age-gate .row { display: flex; gap: 10px; }
        .age-gate select {
            flex: 1;
            padding: 12px;
            background: #1A1A28;
            border: 1px solid #2A2A3E;
            color: #F0F0F5;
            border-radius: 6px;
            font-size: 1rem;
        }
        .age-gate button {
            padding: 14px;
            background: linear-gradient(135deg, #DC143C, #FF2D55);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: box-shadow 0.2s;
        }
        .age-gate button:hover {
            box-shadow: 0 0 20px rgba(220,20,60,0.6);
        }
        .age-gate .footer-links {
            margin-top: 20px;
            font-size: 0.75rem;
            color: #606078;
        }
        .age-gate .footer-links a {
            color: #606078;
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .age-gate h1 { font-size: 2rem; }
            .age-gate { padding: 20px; }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="age-gate">
        <h1>SINCITY</h1>
        <p class="subtitle">Where Sin Meets Pleasure</p>

        <div class="warning">
            ⚠ THIS WEBSITE CONTAINS ADULT CONTENT<br>
            You must be 18 years or older to enter.<br>
            If you are under 18, please leave immediately.
        </div>

        <form method="POST">
            <div class="row">
                <select name="birth_month" required>
                    <option value="">Month</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0,0,0,$m,1)); ?></option>
                    <?php endfor; ?>
                </select>
                <select name="birth_year" required>
                    <option value="">Year</option>
                    <?php for ($y = date('Y'); $y >= 1940; $y--): ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" name="age_verify">I AM 18+ — ENTER SINCITY</button>
        </form>

        <div class="footer-links">
            <a href="https://www.google.com">I AM UNDER 18 — LEAVE</a><br>
            <a href="/privacy-policy/">Privacy Policy</a> | <a href="/terms/">Terms of Service</a>
        </div>
    </div>
</body>
</html>
<?php exit; ?>
```

### Add to `header.php` (or via `wp_head` hook):

```php
// In functions.php — age gate check
function sc_age_gate_check() {
    if (is_admin() || wp_doing_ajax()) return;

    $verified = isset($_COOKIE['sincity_age_verified']) || 
                (isset($_SESSION['age_verified']) && $_SESSION['age_verified']);

    if (!$verified) {
        include get_stylesheet_directory() . '/age-gate.php';
        exit;
    }
}
add_action('template_redirect', 'sc_age_gate_check', 1);

// Ensure session starts early
function sc_start_session() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
}
add_action('init', 'sc_start_session', 1);
```

---

## 3. Lazy Load Embeds (Performance)

Add to `sincity-child/assets/js/player.js`:

```javascript
/**
 * SinCity — Lazy load video embeds using IntersectionObserver
 * Embeds only load when scrolled into viewport
 */
document.addEventListener('DOMContentLoaded', function() {
    const players = document.querySelectorAll('.sc-player-container iframe');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const iframe = entry.target;
                    const src = iframe.dataset.src;
                    if (src && !iframe.src) {
                        iframe.src = src;
                    }
                    observer.unobserve(iframe);
                }
            });
        }, { rootMargin: '200px 0px' });

        players.forEach(player => {
            // Store original src and clear it
            if (player.src) {
                player.dataset.src = player.src;
                player.src = '';
            }
            observer.observe(player);
        });
    } else {
        // Fallback: load all immediately
        players.forEach(player => {
            if (player.dataset.src) {
                player.src = player.dataset.src;
            }
        });
    }
});
```

---

## 4. CSS: SinCity Theme Core Styles

```css
/* sincity-child/assets/css/main.css */

:root {
    --bg-primary: #0A0A0F;
    --bg-secondary: #12121A;
    --bg-tertiary: #1A1A28;
    --border: #2A2A3E;
    --accent-primary: #DC143C;
    --accent-secondary: #FF2D55;
    --accent-cyan: #00F0FF;
    --accent-purple: #7B2FF7;
    --accent-amber: #FFB300;
    --text-primary: #F0F0F5;
    --text-secondary: #A0A0B8;
    --text-muted: #606078;
    --font-heading: 'Cinzel', serif;
    --font-body: 'Inter', sans-serif;
}

* { box-sizing: border-box; }

body {
    background: var(--bg-primary);
    color: var(--text-secondary);
    font-family: var(--font-body);
    margin: 0;
    line-height: 1.6;
}

/* Navigation */
.sc-nav {
    background: var(--bg-primary);
    border-bottom: 1px solid var(--border);
    padding: 0 20px;
    height: 60px;
    display: flex;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
}

.sc-nav-logo {
    font-family: var(--font-heading);
    font-size: 1.5rem;
    color: var(--accent-primary);
    text-decoration: none;
    letter-spacing: 3px;
    margin-right: 30px;
}

.sc-nav-links {
    display: flex;
    gap: 20px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.sc-nav-links a {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: color 0.2s;
}

.sc-nav-links a:hover,
.sc-nav-links a.active {
    color: var(--text-primary);
    border-bottom: 2px solid var(--accent-primary);
}

/* Search */
.sc-search {
    margin-left: auto;
    display: flex;
    align-items: center;
}

.sc-search input {
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    padding: 8px 15px;
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 0.85rem;
    width: 200px;
    transition: border-color 0.2s;
}

.sc-search input:focus {
    outline: none;
    border-color: var(--accent-primary);
}

/* Video Grid */
.sc-video-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    padding: 20px 0;
}

.sc-video-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.sc-video-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.5);
}

.sc-video-card .thumb-wrap {
    position: relative;
    aspect-ratio: 16 / 9;
    overflow: hidden;
}

.sc-video-card .thumb-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sc-video-card .duration {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(10,10,15,0.85);
    color: var(--text-primary);
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
}

.sc-video-card .card-body {
    padding: 12px;
}

.sc-video-card .card-title {
    color: var(--text-primary);
    font-size: 0.9rem;
    font-weight: 500;
    margin: 0 0 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.sc-video-card .card-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Player */
.sc-player-wrapper {
    position: relative;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
}

.sc-player-container {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
}

.sc-player-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.sc-player-logo {
    position: absolute;
    bottom: 15px;
    left: 15px;
    opacity: 0.15;
    height: 24px;
    pointer-events: none;
    z-index: 10;
}

/* Section Headers */
.sc-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0 15px;
}

.sc-section-header h2 {
    font-family: var(--font-heading);
    font-size: 1.25rem;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin: 0;
}

.sc-section-header h2 .accent {
    color: var(--accent-primary);
}

.sc-section-header a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.85rem;
    text-transform: uppercase;
}

.sc-section-header a:hover {
    color: var(--accent-primary);
}

/* Category Cards */
.sc-cat-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin: 20px 0;
}

.sc-cat-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 16 / 9;
    cursor: pointer;
}

.sc-cat-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.sc-cat-card:hover img {
    transform: scale(1.05);
}

.sc-cat-card .cat-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(10,10,15,0.9), transparent);
    display: flex;
    align-items: flex-end;
    padding: 20px;
}

.sc-cat-card .cat-name {
    font-family: var(--font-heading);
    font-size: 1.5rem;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Tags */
.sc-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 15px 0;
}

.sc-tag {
    display: inline-block;
    padding: 4px 12px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 4px;
    color: var(--accent-cyan);
    font-size: 0.7rem;
    text-transform: uppercase;
    text-decoration: none;
    letter-spacing: 0.5px;
    transition: all 0.2s;
}

.sc-tag:hover {
    border-color: var(--accent-cyan);
    background: rgba(0,240,255,0.1);
}

/* Hero Banner */
.sc-hero {
    position: relative;
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    overflow: hidden;
}

.sc-hero-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #0A0A0F 0%, #1A0A0F 50%, #0A0A1A 100%);
}

.sc-hero-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background: url('assets/images/city-silhouette.png') center bottom / cover no-repeat;
    opacity: 0.3;
}

.sc-hero-content {
    position: relative;
    z-index: 1;
}

.sc-hero h1 {
    font-family: var(--font-heading);
    font-size: 3.5rem;
    color: var(--accent-primary);
    text-transform: uppercase;
    letter-spacing: 6px;
    margin: 0;
    text-shadow: 0 0 30px rgba(220,20,60,0.5);
}

.sc-hero p {
    font-size: 1.25rem;
    color: var(--text-secondary);
    margin: 15px 0 30px;
}

.sc-hero .btn-enter {
    display: inline-block;
    padding: 16px 40px;
    background: linear-gradient(135deg, #DC143C, #FF2D55);
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    transition: box-shadow 0.2s;
}

.sc-hero .btn-enter:hover {
    box-shadow: 0 0 30px rgba(220,20,60,0.6);
}

/* Section padding */
.sc-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Footer */
.sc-footer {
    margin-top: 60px;
    padding: 40px 20px;
    border-top: 1px solid var(--border);
    text-align: center;
}

.sc-footer a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.8rem;
    margin: 0 10px;
}

.sc-footer a:hover {
    color: var(--text-secondary);
}

/* Responsive */
@media (max-width: 768px) {
    .sc-nav-links { display: none; } /* Mobile hamburger needed */
    .sc-search input { width: 140px; }
    .sc-video-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .sc-cat-cards { grid-template-columns: 1fr; }
    .sc-hero h1 { font-size: 2rem; }
}

@media (max-width: 480px) {
    .sc-video-grid { grid-template-columns: 1fr; }
}
```

---

## 5. Single Video Page Template

Create `sincity-child/single-sc_video.php`:

```php
<?php get_header(); the_post(); ?>

<div class="sc-container single-video">
    <!-- Player -->
    <div class="sc-player-wrapper">
        <div class="sc-player-container">
            <?php echo sc_render_player(); ?>
        </div>
    </div>

    <!-- Metadata -->
    <div class="video-meta-bar">
        <h1 class="video-title"><?php the_title(); ?></h1>

        <div class="meta-row">
            <span class="views">
                👁 <?php echo number_format(get_field('views_count') ?: 0); ?> views
            </span>
            <span class="duration">⏱ <?php the_field('duration'); ?></span>
            <span class="rating">
                ⭐ <?php echo number_format(get_field('rating_avg') ?: 0, 1); ?>
            </span>
            <span class="source-badge">
                Source: <?php echo get_field('source_site'); ?>
            </span>
        </div>

        <div class="action-bar">
            <button class="btn-like">❤ Like</button>
            <button class="btn-fav">★ Favorite</button>
            <button class="btn-share">↗ Share</button>
            <button class="btn-report">⚑ Report</button>
        </div>
    </div>

    <!-- Description -->
    <div class="video-desc">
        <?php the_content(); ?>
    </div>

    <!-- Tags -->
    <div class="sc-tags">
        <?php
        $tags = wp_get_post_terms(get_the_ID(), 'sc_tag');
        foreach ($tags as $tag):
            echo '<a href="' . get_term_link($tag) . '" class="sc-tag">' . esc_html($tag->name) . '</a>';
        endforeach;
        ?>
    </div>

    <!-- Categories -->
    <div class="video-cats">
        <strong>Categories:</strong>
        <?php
        $cats = wp_get_post_terms(get_the_ID(), 'sc_category');
        foreach ($cats as $cat):
            echo '<a href="' . get_term_link($cat) . '" class="sc-tag">' . esc_html($cat->name) . '</a>';
        endforeach;
        ?>
    </div>

    <!-- Related Videos -->
    <div class="sc-section-header">
        <h2>Related <span class="accent">Videos</span></h2>
    </div>

    <div class="sc-video-grid">
        <?php
        $cat_ids = wp_get_post_terms(get_the_ID(), 'sc_category', array('fields' => 'ids'));
        $related = new WP_Query(array(
            'post_type'      => 'sc_video',
            'posts_per_page' => 8,
            'post__not_in'   => array(get_the_ID()),
            'tax_query'      => array(
                array(
                    'taxonomy' => 'sc_category',
                    'field'    => 'term_id',
                    'terms'    => $cat_ids,
                ),
            ),
        ));

        while ($related->have_posts()): $related->the_post();
            ?>
            <div class="sc-video-card">
                <a href="<?php the_permalink(); ?>">
                    <div class="thumb-wrap">
                        <?php the_post_thumbnail('medium'); ?>
                        <span class="duration"><?php the_field('duration'); ?></span>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?php the_title(); ?></h3>
                        <div class="card-meta">
                            <span>👁 <?php echo number_format(get_field('views_count') ?: 0); ?></span>
                            <span>⏱ <?php the_field('duration'); ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
        ?>
    </div>

    <!-- Comments -->
    <div class="video-comments" style="margin-top: 30px;">
        <?php
        if (comments_open() || get_comments_number()) {
            comments_template();
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>
```

---

## 6. Functions: Trending Page Query

Add to `functions.php`:

```php
// Register Trending page template
function sc_trending_page_template($templates) {
    $templates['page-trending.php'] = 'Trending Videos';
    return $templates;
}
add_filter('theme_page_templates', 'sc_trending_page_template');

// Template loading
function sc_load_trending_template($template) {
    if (is_page_template('page-trending.php')) {
        $template = get_stylesheet_directory() . '/page-trending.php';
    }
    return $template;
}
add_filter('template_include', 'sc_load_trending_template');
```

Create `sincity-child/page-trending.php`:

```php
<?php /* Template Name: Trending Videos */ ?>
<?php get_header(); ?>

<div class="sc-container">
    <div class="sc-section-header">
        <h2>🔥 Trending <span class="accent">This Week</span></h2>
    </div>

    <div class="sc-video-grid">
        <?php
        $trending = sc_get_trending(24);
        if ($trending->have_posts()):
            while ($trending->have_posts()): $trending->the_post();
                // Card template here (same as single)
            endwhile;
            wp_reset_postdata();
        else:
            echo '<p>No trending videos this week.</p>';
        endif;
        ?>
    </div>
</div>

<?php get_footer(); ?>
```

---

## 7. .htaccess / Web.Config Security

**Apache (.htaccess):**
```apache
# Block bad bots
RewriteEngine On
RewriteCond %{HTTP_USER_AGENT} (ahrefs|majestic|rogerbot|semrush) [NC]
RewriteRule .* - [F,L]

# Block wp-admin access by IP
<Files wp-login.php>
    Order Deny,Allow
    Deny from all
    Allow from 123.456.789.0  # Your IP
</Files>

# Disable PHP execution in uploads
<Directory wp-content/uploads>
    php_flag engine off
</Directory>
```

**Nginx (in server block):**
```nginx
# Block bad bots
if ($http_user_agent ~* (ahrefs|majestic|rogerbot|semrush)) {
    return 403;
}

# Protect wp-admin
location /wp-admin {
    allow 123.456.789.0;
    deny all;
}
```
