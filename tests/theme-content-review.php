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
function jvt_review_insert_fixture($properties, &$owned) {
    $id = wp_insert_post(wp_slash($properties), true);
    if (is_wp_error($id)) { throw new RuntimeException($id->get_error_message()); }
    $owned[] = $id;
    return $id;
}
function jvt_review_render_404($root) {
    ob_start();
    try { include $root . '/theme/jvuorela-tools/404.php'; return ob_get_clean(); }
    catch (Throwable $error) { ob_end_clean(); throw $error; }
}

$GLOBALS['jvt_review_cases'] = array();
$root = dirname(__DIR__);
$original_context = jvt_review_context();
$copy = get_posts(array('post_type' => 'page', 'post_status' => 'publish', 'meta_key' => '_jvt_content_id', 'meta_value' => 'not-found', 'numberposts' => 2));
if (count($copy) > 1) { WP_CLI::error('Duplicate published not-found markers require review before this check.'); }
if (!function_exists('jvt_register_404_setting')) { WP_CLI::error('Activate the current candidate theme before these checks.'); }
global $wpdb;
$previous_404_serialized = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", 'jvt_404_page'));
$previous_404_exists = $previous_404_serialized !== null;
$previous_404 = $previous_404_exists ? get_option('jvt_404_page') : null;
$previous_description = get_option('blogdescription', null);
$previous_locations_exist = array_key_exists('nav_menu_locations', get_theme_mods() ?: array());
$previous_locations = get_theme_mod('nav_menu_locations', array());
$previous_baseline = get_option('_jvt_configuration_state', null);
$menu_id = null;
$content_filter = null;
$excerpt_filter = null;
$owned_posts = array();
$error = null;
try {
    require_once ABSPATH . 'wp-admin/includes/template.php';
    jvt_register_404_setting();
    $registered = get_registered_settings()['jvt_404_page'] ?? null;
    jvt_review_assert($registered && $registered['group'] === 'reading' && $registered['type'] === 'integer' && $registered['default'] === 0 && $registered['sanitize_callback'] === 'jvt_sanitize_404_page' && empty($registered['show_in_rest']) && has_action('admin_init', 'jvt_register_404_setting') !== false, '404 selection uses the native Reading settings group and integer validation without a public REST write path');
    if (!$copy) {
        $default_id = jvt_review_insert_fixture(array('post_type'=>'page', 'post_status'=>'publish', 'post_title'=>'Imported 404 review fixture', 'post_content'=>'<!-- wp:paragraph --><p>Imported editable 404 fixture.</p><!-- /wp:paragraph -->'), $owned_posts);
        update_post_meta($default_id, '_jvt_content_id', 'not-found');
        $copy = array(get_post($default_id));
    }
    update_option('jvt_404_page', 0);
    jvt_review_assert(jvt_404_content_page()->ID === $copy[0]->ID, 'unset explicit selection retains the unique published imported 404 Page');
    // Render native dynamic blocks through the actual template while a
    // deliberately different surrounding post exists. Existing Pages are read-only.
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
    $html = jvt_review_render_404($root);
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

    $selected_id = jvt_review_insert_fixture(array('post_type'=>'page', 'post_status'=>'publish', 'post_title'=>'Manually selected 404 Page fixture', 'post_excerpt'=>'Selected Page dynamic excerpt fixture', 'post_content'=>'<!-- wp:paragraph --><p>Manually authored native 404 body fixture.</p><!-- /wp:paragraph --><!-- wp:post-title /--><!-- wp:post-excerpt /-->'), $owned_posts);
    $draft_id = jvt_review_insert_fixture(array('post_type'=>'page', 'post_status'=>'draft', 'post_title'=>'Draft 404 selector fixture'), $owned_posts);
    $post_id = jvt_review_insert_fixture(array('post_type'=>'post', 'post_status'=>'publish', 'post_title'=>'Non-Page 404 selector fixture'), $owned_posts);
    $protected_id = jvt_review_insert_fixture(array('post_type'=>'page', 'post_status'=>'publish', 'post_password'=>'fixture-only-secret', 'post_title'=>'Protected 404 selector fixture'), $owned_posts);
    jvt_review_assert(!get_post_meta($selected_id, '_jvt_content_id', true) && sanitize_option('jvt_404_page', (string)$selected_id) === $selected_id, 'an unmarked public published Page can be selected through the standard settings sanitizer');
    jvt_review_assert(sanitize_option('jvt_404_page', $draft_id) === 0 && sanitize_option('jvt_404_page', $post_id) === 0 && sanitize_option('jvt_404_page', PHP_INT_MAX) === 0 && sanitize_option('jvt_404_page', array($selected_id)) === 0 && sanitize_option('jvt_404_page', '-' . $selected_id) === 0 && sanitize_option('jvt_404_page', $selected_id . 'junk') === 0, 'draft non-Page missing malformed and negative selections resolve only to zero');
    jvt_review_assert(sanitize_option('jvt_404_page', $protected_id) === 0, 'password-protected Page content cannot become publicly visible through the 404 selector');
    update_option('jvt_404_page', $selected_id);
    jvt_review_assert(jvt_404_content_page()->ID === $selected_id && get_post_status($selected_id) === 'publish', 'explicit unmarked selection takes precedence without changing Page publication');
    ob_start();
    jvt_404_page_field();
    $field = ob_get_clean();
    $field_tags = new WP_HTML_Tag_Processor($field);
    $native_selector = false;
    $selected_choice = false;
    $disallowed_choice = false;
    while ($field_tags->next_tag()) {
        if ($field_tags->get_tag() === 'SELECT' && $field_tags->get_attribute('name') === 'jvt_404_page' && $field_tags->get_attribute('id') === 'jvt_404_page') { $native_selector = true; }
        if ($field_tags->get_tag() !== 'OPTION') { continue; }
        $value = (int)$field_tags->get_attribute('value');
        if ($value === $selected_id && $field_tags->get_attribute('selected') !== null) { $selected_choice = true; }
        if ($value === $draft_id || $value === $protected_id) { $disallowed_choice = true; }
    }
    jvt_review_assert($native_selector && $selected_choice && !$disallowed_choice, 'native Reading selector displays the chosen Page and excludes draft or protected choices');
    jvt_register_404_setting();
    jvt_review_assert((int)get_option('jvt_404_page') === $selected_id && jvt_404_content_page()->ID === $selected_id, 'registering theme settings again preserves the existing operator selection');
    $GLOBALS['post'] = $sentinel;
    $GLOBALS['id'] = -314;
    $GLOBALS['more'] = 'preserved review context';
    $GLOBALS['page'] = null;
    $selected_context = jvt_review_context();
    $selected_html = jvt_review_render_404($root);
    jvt_review_assert(strpos($selected_html, 'Manually authored native 404 body fixture.') !== false && strpos($selected_html, 'Selected Page dynamic excerpt fixture') !== false && strpos($selected_html, 'wp-block-post-title') !== false && strpos($selected_html, 'Wrong surrounding Page context') === false, 'manually selected Page renders editable content and dynamic blocks with its own context');
    jvt_review_assert(jvt_review_context() === $selected_context, 'selected 404 Page rendering restores every surrounding post context global');
    jvt_review_restore_context($original_context);
    wp_update_post(array('ID'=>$selected_id, 'post_status'=>'draft'));
    jvt_review_assert((int)get_option('jvt_404_page') === $selected_id && jvt_404_content_page()->ID === $copy[0]->ID, 'a selection later changed to draft is preserved while rendering falls back safely to imported content');
    wp_update_post(array('ID'=>$selected_id, 'post_status'=>'publish'));

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
    jvt_review_assert((int)$export['configuration']['current']['options']['jvt_404_page'] === $selected_id, 'fresh export includes the current native 404 Page selection without changing import baselines');
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
    // Restore even an already stale operator value exactly; validation applies
    // to intentional settings saves, not to test cleanup or theme activation.
    remove_filter('sanitize_option_jvt_404_page', 'jvt_sanitize_404_page');
    try {
        if ($previous_404_exists) { update_option('jvt_404_page', $previous_404); }
        else { delete_option('jvt_404_page'); }
    } finally { add_filter('sanitize_option_jvt_404_page', 'jvt_sanitize_404_page'); }
    foreach ($owned_posts as $owned_id) { wp_delete_post($owned_id, true); }
}
if (!$error) {
    jvt_review_assert(get_option('blogdescription', null) === $previous_description && get_theme_mod('nav_menu_locations', array()) === $previous_locations && get_option('_jvt_configuration_state', null) === $previous_baseline && (!$menu_id || !wp_get_nav_menu_object($menu_id)), 'review fixture cleans up its allocated menu and restores all targeted native settings');
    $restored_404_serialized = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", 'jvt_404_page'));
    jvt_review_assert($restored_404_serialized === $previous_404_serialized && jvt_review_context() === $original_context && !array_filter($owned_posts, static function ($id) { return get_post($id); }), 'review fixtures restore the exact original 404 option and globals and delete only allocated Pages/posts');
}
WP_CLI::line(wp_json_encode(array('passes' => count($GLOBALS['jvt_review_cases']), 'cases' => $GLOBALS['jvt_review_cases'], 'php' => PHP_VERSION, 'wordpress' => get_bloginfo('version'), 'cleanup' => 'No existing Page or Media Library writes; only allocated fixture Pages/posts/menu deleted; original settings, locations, 404 option and post context restored.', 'failure' => $error), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
if ($error) { WP_CLI::error($error); }
