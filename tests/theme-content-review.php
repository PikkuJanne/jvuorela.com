<?php
/** Focused real-WordPress review regression checks. SPDX-License-Identifier: GPL-2.0-or-later */
if (!defined('WP_CLI') || !WP_CLI) { throw new RuntimeException('WP-CLI is required.'); }
$host = wp_parse_url(home_url('/'), PHP_URL_HOST);
if (!in_array($host, array('localhost', '127.0.0.1', '::1'), true) || !current_user_can('manage_options')) {
    WP_CLI::error('These review checks require a disposable loopback site and an explicit administrator.');
}
$_SERVER['SERVER_NAME'] = $host; // Supply the loopback request context missing in WP-CLI.
define('JVT_EXPORT_NO_RUN', true);
require_once dirname(__DIR__) . '/scripts/export-content.php';

function jvt_review_assert($condition, $case) {
    if (!$condition) { throw new RuntimeException('Failed: ' . $case); }
    $GLOBALS['jvt_review_cases'][] = $case;
}
function jvt_review_context() {
    $context = array();
    foreach (array('post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages') as $key) {
        $context[$key] = array('exists' => array_key_exists($key, $GLOBALS), 'value' => $GLOBALS[$key] ?? null);
    }
    return $context;
}
function jvt_review_restore_context($context) {
    foreach ($context as $key => $previous) {
        if ($previous['exists']) { $GLOBALS[$key] = $previous['value']; }
        else { unset($GLOBALS[$key]); }
    }
}

$GLOBALS['jvt_review_cases'] = array();
$root = dirname(__DIR__);
$original_context = jvt_review_context();
$copy = get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'meta_key' => '_jvt_content_id', 'meta_value' => 'not-found', 'numberposts' => 2));
if (count($copy) !== 1) { WP_CLI::error('An existing published native not-found Page is required for the focused template check.'); }
$previous_description = get_option('blogdescription', null);
$previous_locations_exist = array_key_exists('nav_menu_locations', get_theme_mods() ?: array());
$previous_locations = get_theme_mod('nav_menu_locations', array());
$previous_baseline = get_option('_jvt_configuration_state', null);
$menu_id = null;
$content_filter = null;
$excerpt_filter = null;
$error = null;
try {
    // No Page or Media Library writes: render native dynamic blocks through the
    // actual template while a deliberately different surrounding post exists.
    $sentinel = clone $copy[0];
    $sentinel->ID = 0;
    $sentinel->post_title = 'Wrong surrounding Page context';
    $GLOBALS['post'] = $sentinel;
    $GLOBALS['id'] = -314;
    $GLOBALS['more'] = 'preserved review context';
    $GLOBALS['page'] = null; // Core title rendering declares this pagination global.
    $surrounding_context = jvt_review_context();
    $content_filter = static function ($content) { return '<!-- wp:post-title /--><!-- wp:post-excerpt /-->'; };
    $copy_id = $copy[0]->ID;
    $excerpt_filter = static function ($excerpt, $post) use ($copy_id) { return $post->ID === $copy_id ? 'Native 404 Page context probe' : 'Wrong surrounding Page context'; };
    add_filter('the_content', $content_filter, 1);
    add_filter('get_the_excerpt', $excerpt_filter, 1, 2);
    ob_start();
    include $root . '/theme/jvuorela-tools/404.php';
    $html = ob_get_clean();
    remove_filter('the_content', $content_filter, 1);
    remove_filter('get_the_excerpt', $excerpt_filter, 1);
    $content_filter = null;
    $excerpt_filter = null;
    jvt_review_assert(strpos($html, 'wp-block-post-title') !== false && strpos($html, 'Native 404 Page context probe') !== false && strpos($html, 'Wrong surrounding Page context') === false, 'editable 404 Page renders native dynamic title/excerpt blocks in its own context');
    $restored_context = jvt_review_context();
    $changed_context = array();
    foreach ($surrounding_context as $key => $value) {
        if ($restored_context[$key] !== $value) { $changed_context[] = $key; }
    }
    jvt_review_assert(!$changed_context, '404 rendering restores every original WordPress post context global' . ($changed_context ? ': ' . implode(', ', $changed_context) : ''));
    jvt_review_restore_context($original_context);

    // Make one genuine native setting edit and allocate one unique menu; never
    // edit an existing menu. All original settings/locations are restored below.
    $description = 'Owner-edited configuration review fixture';
    update_option('blogdescription', $description);
    $menu_id = wp_create_nav_menu('jvt-review-' . strtolower(wp_generate_password(10, false, false)));
    if (is_wp_error($menu_id)) { throw new RuntimeException($menu_id->get_error_message()); }
    $item_id = wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => 'Owner-edited menu review fixture', 'menu-item-url' => 'https://example.invalid/review', 'menu-item-type' => 'custom', 'menu-item-status' => 'publish'));
    if (is_wp_error($item_id)) { throw new RuntimeException($item_id->get_error_message()); }
    $locations = $previous_locations;
    $locations['primary'] = (int) $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
    $export = jvt_export_snapshot();
    jvt_review_assert($export['schema_version'] === 2 && $export['configuration']['current']['options']['blogdescription'] === $description, 'fresh export includes the current edited native site wording');
    jvt_review_assert($export['configuration']['current']['locations']['primary'] === (int) $menu_id, 'fresh export includes current native menu assignments');
    $exported_menu = null;
    foreach ($export['configuration']['current']['menus'] as $menu) {
        if ($menu['wordpress_id'] === (int) $menu_id) { $exported_menu = $menu['current']; }
    }
    jvt_review_assert($exported_menu && $exported_menu['items'][0]['title'] === 'Owner-edited menu review fixture' && $exported_menu['items'][0]['url'] === 'https://example.invalid/review', 'fresh export includes actual current menu entries and URLs');
    jvt_review_assert($export['configuration']['import_baseline'] === $previous_baseline && get_option('_jvt_configuration_state', null) === $previous_baseline, 'fresh current configuration is distinct from its unchanged import baseline');
} catch (Throwable $caught) {
    $error = $caught->getMessage();
} finally {
    if ($content_filter) { remove_filter('the_content', $content_filter, 1); }
    if ($excerpt_filter) { remove_filter('get_the_excerpt', $excerpt_filter, 1); }
    jvt_review_restore_context($original_context);
    if ($previous_description === null) { delete_option('blogdescription'); }
    else { update_option('blogdescription', $previous_description); }
    if ($previous_locations_exist) { set_theme_mod('nav_menu_locations', $previous_locations); }
    else { remove_theme_mod('nav_menu_locations'); }
    if ($menu_id && !is_wp_error($menu_id)) { wp_delete_nav_menu($menu_id); }
}
if (!$error) {
    jvt_review_assert(get_option('blogdescription', null) === $previous_description && get_theme_mod('nav_menu_locations', array()) === $previous_locations && get_option('_jvt_configuration_state', null) === $previous_baseline && (!$menu_id || !wp_get_nav_menu_object($menu_id)), 'review fixture cleans up its allocated menu and restores all targeted native settings');
}
WP_CLI::line(wp_json_encode(array('passes' => count($GLOBALS['jvt_review_cases']), 'cases' => $GLOBALS['jvt_review_cases'], 'php' => PHP_VERSION, 'wordpress' => get_bloginfo('version'), 'cleanup' => 'No existing Page or Media Library writes; allocated fixture menu deleted; original settings, locations and post context restored.', 'failure' => $error), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
if ($error) { WP_CLI::error($error); }
