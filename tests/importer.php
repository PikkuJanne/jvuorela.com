<?php
/**
 * Meaningful importer tests against a real disposable local WordPress database.
 * SPDX-License-Identifier: GPL-2.0-or-later
 * WP-CLI eval-file tests/importer.php --user=<administrator>
 */
if (!defined('WP_CLI') || !WP_CLI) {
    throw new RuntimeException('WP-CLI is required.');
}
$host = wp_parse_url(home_url('/'), PHP_URL_HOST);
if (!in_array($host, array('localhost', '127.0.0.1', '::1'), true) || !current_user_can('manage_options')) {
    WP_CLI::error('Tests require a disposable loopback WordPress site and an explicit administrator.');
}
define('JVT_IMPORT_NO_RUN', true);
require_once dirname(__DIR__) . '/scripts/import-content.php';

function jvt_test_assert($condition, $case) {
    global $jvt_test_cases;
    if (!$condition) {
        throw new RuntimeException('Failed: ' . $case);
    }
    $jvt_test_cases[] = $case;
}

function jvt_test_counts() {
    global $wpdb;
    return array('posts' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}"), 'postmeta' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta}"), 'options' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options}"), 'terms' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->terms}"), 'users' => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}"));
}

function jvt_test_options_snapshot() {
    global $wpdb;
    return jvt_import_hash($wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} ORDER BY option_name", ARRAY_A));
}

function jvt_test_restore_page($id, $state) {
    wp_update_post(wp_slash(array('ID' => $id, 'post_title' => $state['title'], 'post_name' => $state['slug'], 'post_excerpt' => $state['excerpt'], 'post_content' => $state['content'], 'post_status' => $state['status'], 'post_parent' => $state['parent'])));
    update_post_meta($id, '_wp_page_template', $state['template']);
    update_post_meta($id, '_jvt_social_image', $state['social_image']);
}

$GLOBALS['jvt_test_cases'] = array();
$owned_posts = array();
$owned_media = array();
$owned_menus = array();
$configuration_restore = null;
$root = dirname(__DIR__);
$prefix = 'jvt-test-' . strtolower(wp_generate_password(8, false, false));
$unrelated_option = $prefix . '-unrelated';
$error = null;
try {
    $full_manifest = jvt_import_json($root . '/assets/manifest.json');
    $source_slot = null;
    foreach ($full_manifest['slots'] as $slot) {
        if (!empty($slot['required_for_launch']) && !empty($slot['derivatives']) && $slot['derivatives'][0]['format'] === 'png') {
            $source_slot = $slot;
            break;
        }
    }
    if (!$source_slot) {
        throw new RuntimeException('A traced approved PNG derivative is needed for the real upload test.');
    }
    // Unique test markers prevent touching any actual imported page or artwork slot.
    $source_slot['slot_id'] = $prefix . '-art';
    $source_slot['required_for_launch'] = true;
    $manifest = array('slots' => array($source_slot));
    foreach ($full_manifest['slots'] as $slot) {
        if ($slot['slot_id'] === 'site-icon' && !empty($slot['derivatives'])) {
            $slot['slot_id'] = $prefix . '-icon';
            $manifest['slots'][] = $slot;
            break;
        }
    }
    if (count($manifest['slots']) !== 2) {
        throw new RuntimeException('Traced approved site-icon derivative is needed.');
    }
    foreach ($full_manifest['slots'] as $slot) {
        if ($slot['slot_id'] === 'site-favicon' && !empty($slot['derivatives'])) {
            $slot['slot_id'] = $prefix . '-favicon';
            $manifest['slots'][] = $slot;
            break;
        }
    }
    if (count($manifest['slots']) !== 3) {
        throw new RuntimeException('Traced theme favicon derivative is needed.');
    }
    $slot_id = $source_slot['slot_id'];
    $home_id = $prefix . '-home';
    $tool_id = $prefix . '-tool';
    $seed = array('pages' => array(
        array('id' => $home_id, 'slug' => $home_id, 'title' => 'Importer fixture home', 'excerpt' => 'Initial home excerpt', 'template' => 'default', 'social_slot' => $slot_id, 'content' => '<!-- wp:paragraph --><p>Initial tagline <a href="{{page:' . $tool_id . '}}">Read tool</a></p><!-- /wp:paragraph -->'),
        array('id' => $tool_id, 'slug' => $tool_id, 'title' => 'Importer fixture tool', 'excerpt' => 'Initial tool excerpt', 'template' => 'templates/tool.php', 'social_slot' => null, 'content' => '<!-- wp:image {"id":{{attachment:' . $slot_id . '}},"sizeSlug":"full","linkDestination":"none"} --><figure class="wp-block-image size-full"><img src="{{asset:' . $slot_id . '}}" alt="" class="wp-image-{{attachment:' . $slot_id . '}}"/></figure><!-- /wp:image --><!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Initial benefit</li><!-- /wp:list-item --><!-- wp:list-item --><li>Initial requirement</li><!-- /wp:list-item --></ul><!-- /wp:list --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://example.org/release">Download</a></div><!-- /wp:button --></div><!-- /wp:buttons -->'),
    ), 'configuration' => array('front_page' => $home_id));
    $seed['pages'][1]['content'] .= '<!-- wp:paragraph --><p><a href="{{page:' . $home_id . '}}">All tools</a></p><!-- /wp:paragraph -->';
    add_option($unrelated_option, 'keep owner preference', '', false);
    $unrelated_post = wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Unrelated owner fixture', 'post_name' => $prefix . '-owner', 'post_content' => '<!-- wp:paragraph --><p>Preserve this page.</p><!-- /wp:paragraph -->'));
    $owned_posts[] = $unrelated_post;
    $unrelated_state = jvt_import_page_state($unrelated_post);
    $before = jvt_test_counts();
    $reading_before = get_option('show_on_front');
    $options_before = jvt_test_options_snapshot();
    $plan = jvt_import_execute($seed, $manifest, $root);
    jvt_test_assert(!$plan['writes'] && !$plan['conflicts'] && $plan['pages'][0]['state'] === 'create' && $plan['media'][0]['state'] === 'upload', 'first plan describes drafts/media without writes');
    jvt_test_assert($before === jvt_test_counts(), 'plan leaves real database table counts unchanged');
    $applied = jvt_import_execute($seed, $manifest, $root, 'apply');
    foreach ($applied['pages'] as $row) {
        $owned_posts[] = $row['wordpress_id'];
    }
    foreach ($applied['media'] as $row) {
        if ($row['wordpress_id']) {
            $owned_media[] = $row['wordpress_id'];
        }
    }
    $page_map = array();
    foreach ($applied['pages'] as $row) {
        $page_map[$row['id']] = $row['wordpress_id'];
    }
    $image = $applied['media'][0]['wordpress_id'];
    jvt_test_assert($applied['writes'] && !$applied['conflicts'], 'explicit apply creates only fixture drafts and media');
    jvt_test_assert($applied['media'][2]['state'] === 'theme_runtime_file' && $applied['media'][2]['wordpress_id'] === null && !isset(jvt_import_marker_index('_jvt_asset_slot')[$prefix . '-favicon']), 'traced ICO stays a theme runtime file without bypassing upload MIME security');
    jvt_test_assert(jvt_test_options_snapshot() === $options_before, 'normal draft/media import leaves all existing options unchanged');
    jvt_test_assert(get_post_status($page_map[$home_id]) === 'draft' && get_post_status($page_map[$tool_id]) === 'draft', 'normal importer never publishes pages');
    jvt_test_assert((int) get_post_meta($page_map[$home_id], '_jvt_social_image', true) === $image, 'social slot resolves to native attachment metadata');
    $tool_content = get_post($page_map[$tool_id])->post_content;
    $blocks = parse_blocks($tool_content);
    jvt_test_assert($blocks[0]['blockName'] === 'core/image' && $blocks[0]['attrs']['id'] === $image, 'attachment token becomes numeric core image ID');
    jvt_test_assert(strpos($tool_content, '{{') === false && strpos($tool_content, wp_get_attachment_url($image)) !== false, 'approved image URL replaces portable content tokens');
    jvt_test_assert(strpos($tool_content, 'href="' . esc_url(home_url('/')) . '">All tools') !== false && get_option('show_on_front') === $reading_before, 'intended Home token uses root URL before explicit fixture front-page configuration');
    $home_content = get_post($page_map[$home_id])->post_content;
    $intended_tool_url = get_page_link($page_map[$tool_id], false, true);
    jvt_test_assert(strpos($home_content, $intended_tool_url) !== false, 'cross-page URL resolves after all drafts are allocated');
    jvt_test_assert(get_option('permalink_structure') && get_post_status($page_map[$tool_id]) === 'draft' && strpos(get_permalink($page_map[$tool_id]), '?page_id=') !== false && strpos($home_content, '?page_id=') === false && strpos($home_content, '/' . $tool_id . '/') !== false, 'draft Page token uses its intended native slug permalink instead of a query-ID link');
    jvt_test_assert(get_post_meta($page_map[$tool_id], '_wp_page_template', true) === 'templates/tool.php', 'native page template is stored explicitly');
    jvt_test_assert(hash_file('sha256', get_attached_file($image)) === $applied['media'][0]['sha256'], 'uploaded original equals traced public derivative bytes');
    $metadata = wp_get_attachment_metadata($image);
    $proportional = !empty($metadata['sizes']) && !empty($metadata['width']) && !empty($metadata['height']);
    foreach ($metadata['sizes'] ?? array() as $size) {
        $proportional = $proportional && abs(($size['width'] / $size['height']) - ($metadata['width'] / $metadata['height'])) < 0.02;
    }
    jvt_test_assert($proportional, 'WordPress generated sizes remain proportional without crops');
    $after_apply = jvt_test_counts();
    $rerun = jvt_import_execute($seed, $manifest, $root, 'apply');
    jvt_test_assert(!$rerun['writes'] && !$rerun['conflicts'] && $rerun['pages'][0]['state'] === 'unchanged' && $rerun['media'][0]['state'] === 'unchanged', 'unchanged apply is idempotent');
    jvt_test_assert($after_apply === jvt_test_counts(), 'idempotent apply creates no duplicate pages media or metadata');
    $baseline = jvt_import_page_state($page_map[$tool_id]);
    $edited_content = str_replace(array('Initial benefit', 'Initial requirement', 'https://example.org/release'), array('Owner edited benefit', 'Owner edited requirement', 'https://example.org/owner-release'), $baseline['content']);
    wp_update_post(wp_slash(array('ID' => $page_map[$tool_id], 'post_content' => $edited_content)));
    $edited = jvt_import_execute($seed, $manifest, $root, 'apply');
    jvt_test_assert(!$edited['writes'] && $edited['conflicts'] && in_array('content', $edited['pages'][1]['changed_fields'], true) && get_post($page_map[$tool_id])->post_content === $edited_content, 'edited benefit requirement and button URL survive rerun with field-level conflict');
    jvt_test_restore_page($page_map[$tool_id], $baseline);
    $home_baseline = jvt_import_page_state($page_map[$home_id]);
    wp_update_post(wp_slash(array('ID' => $page_map[$home_id], 'post_content' => str_replace('Initial tagline', 'Owner edited tagline', $home_baseline['content']))));
    $tagline = jvt_import_execute($seed, $manifest, $root);
    jvt_test_assert($tagline['conflicts'] && strpos(get_post($page_map[$home_id])->post_content, 'Owner edited tagline') !== false, 'editor tagline survives read-only plan');
    jvt_test_restore_page($page_map[$home_id], $home_baseline);
    foreach (array('post_title' => 'Owner title', 'post_excerpt' => 'Owner excerpt', 'post_status' => 'pending', 'post_name' => $prefix . '-renamed') as $field => $value) {
        wp_update_post(wp_slash(array('ID' => $page_map[$tool_id], $field => $value)));
        $result = jvt_import_execute($seed, $manifest, $root, 'apply');
        jvt_test_assert(!$result['writes'] && $result['conflicts'] && get_post($page_map[$tool_id])->$field === $value, 'edited ' . $field . ' is detected and preserved');
        jvt_test_restore_page($page_map[$tool_id], $baseline);
    }
    update_post_meta($page_map[$tool_id], '_wp_page_template', 'default');
    $template = jvt_import_execute($seed, $manifest, $root, 'apply');
    jvt_test_assert(!$template['writes'] && $template['conflicts'] && get_post_meta($page_map[$tool_id], '_wp_page_template', true) === 'default', 'edited page template is detected and preserved');
    jvt_test_restore_page($page_map[$tool_id], $baseline);
    update_post_meta($page_map[$tool_id], '_jvt_social_image', $image);
    $social = jvt_import_execute($seed, $manifest, $root, 'apply');
    jvt_test_assert(!$social['writes'] && $social['conflicts'] && (int) get_post_meta($page_map[$tool_id], '_jvt_social_image', true) === $image, 'edited social attachment is detected and preserved');
    jvt_test_restore_page($page_map[$tool_id], $baseline);
    $changed_seed = $seed;
    $changed_seed['pages'][0]['title'] = 'New seed title';
    $source = jvt_import_execute($changed_seed, $manifest, $root, 'apply');
    jvt_test_assert(!$source['writes'] && strpos(wp_json_encode($source['conflicts']), 'seed_changed') !== false && get_post($page_map[$home_id])->post_title === $home_baseline['title'], 'changed source seed cannot replace an existing page');
    $collision_id = $prefix . '-collision';
    $collision = wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Unmarked existing page', 'post_name' => $collision_id, 'post_content' => 'Original unmarked text'));
    $owned_posts[] = $collision;
    $collision_seed = $seed;
    $collision_seed['pages'][] = array('id' => $collision_id, 'slug' => $collision_id, 'title' => 'Would overwrite', 'excerpt' => '', 'template' => 'default', 'social_slot' => null, 'content' => 'new');
    $collision_result = jvt_import_execute($collision_seed, $manifest, $root, 'apply');
    jvt_test_assert(!$collision_result['writes'] && strpos(wp_json_encode($collision_result['conflicts']), 'slug_collision') !== false && get_post($collision)->post_content === 'Original unmarked text', 'unmarked slug collision is preserved and reported');
    $duplicate = wp_insert_post(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Duplicate marker fixture', 'post_name' => $prefix . '-duplicate'));
    $owned_posts[] = $duplicate;
    add_post_meta($duplicate, '_jvt_content_id', $home_id);
    $duplicates = jvt_import_execute($seed, $manifest, $root, 'apply');
    jvt_test_assert(!$duplicates['writes'] && strpos(wp_json_encode($duplicates['conflicts']), 'duplicate_or_wrong_type_marker') !== false, 'duplicate stable markers fail closed');
    delete_post_meta($duplicate, '_jvt_content_id');
    update_post_meta($image, '_wp_attachment_image_alt', 'Owner alternative text');
    $media_edit = jvt_import_execute($seed, $manifest, $root, 'apply');
    jvt_test_assert(!$media_edit['writes'] && $media_edit['conflicts'] && get_post_meta($image, '_wp_attachment_image_alt', true) === 'Owner alternative text', 'edited Media Library alternative text is preserved');
    update_post_meta($image, '_wp_attachment_image_alt', get_post_meta($image, '_jvt_asset_baseline', true)['alt']);
    $invalid_seed = $seed;
    $invalid_seed['pages'][0]['content'] .= '{{asset:bad.slot}}';
    $before_invalid = jvt_test_counts();
    $invalid_failed = false;
    try {
        jvt_import_execute($invalid_seed, $manifest, $root, 'apply');
    } catch (RuntimeException $expected) {
        $invalid_failed = true;
    }
    jvt_test_assert($invalid_failed && $before_invalid === jvt_test_counts(), 'malformed tokens fail validation before database writes');
    jvt_test_assert(get_option($unrelated_option) === 'keep owner preference' && jvt_import_page_state($unrelated_post) === $unrelated_state && jvt_test_counts()['users'] === $before['users'], 'normal importer preserves unrelated options pages and users');
    // A separate settings plan never changes the database.
    $seed['configuration'] = array('front_page' => $prefix . '-not-imported', 'menus' => array());
    $before_configuration = jvt_test_counts();
    $config = jvt_import_execute($seed, $manifest, $root, 'configure');
    jvt_test_assert(!$config['writes'] && strpos(wp_json_encode($config['conflicts']), 'page_not_imported') !== false && $before_configuration === jvt_test_counts(), 'separate configuration plan refuses a missing front page without writes');
    $configuration_restore = array('options' => array(), 'managed_state' => get_option('_jvt_configuration_state', null), 'locations_exist' => array_key_exists('nav_menu_locations', get_theme_mods() ?: array()), 'locations' => get_theme_mod('nav_menu_locations', array()));
    foreach (array('page_on_front', 'show_on_front', 'site_icon') as $option) {
        $configuration_restore['options'][$option] = get_option($option, null);
    }
    // Temporarily isolate only the explicitly tested settings, preserving the real
    // Pages, existing menu objects and complete prior management state for finally.
    delete_option('_jvt_configuration_state');
    update_option('page_on_front', 0);
    update_option('show_on_front', 'posts');
    update_option('site_icon', 0);
    $fixture_locations = $configuration_restore['locations'];
    unset($fixture_locations['primary'], $fixture_locations['footer']);
    set_theme_mod('nav_menu_locations', $fixture_locations);
    $seed['configuration'] = array('front_page' => $home_id, 'site_icon_slot' => $prefix . '-icon', 'menu_prefix' => $prefix, 'menus' => array('primary' => array(array('title' => 'Test tools', 'url' => '{{page:' . $home_id . '}}#tools')), 'footer' => array(array('title' => 'Test source', 'url' => 'https://example.org/source'))));
    $configuration = jvt_import_execute($seed, $manifest, $root, 'configure-apply');
    $managed = get_option('_jvt_configuration_state');
    $owned_menus = array_values($managed['baseline']['locations'] ?? array());
    jvt_test_assert($configuration['writes'] && !$configuration['conflicts'] && (int) get_option('page_on_front') === $page_map[$home_id] && get_option('show_on_front') === 'page' && get_post_status($page_map[$home_id]) === 'draft', 'explicit configuration selects draft Home without publishing it');
    $icon_id = $applied['media'][1]['wordpress_id'];
    jvt_test_assert((int) get_option('site_icon') === $icon_id && count($owned_menus) === 2 && count(wp_get_nav_menu_items($owned_menus[0])) === 1, 'explicit configuration sets native icon and primary footer menus');
    $configuration_noop = jvt_import_execute($seed, $manifest, $root, 'configure-apply');
    jvt_test_assert(!$configuration_noop['writes'] && !$configuration_noop['conflicts'] && $configuration_noop['configuration']['state'] === 'unchanged', 'configuration rerun is idempotent');
    update_option('show_on_front', 'posts');
    $configuration_edit = jvt_import_execute($seed, $manifest, $root, 'configure-apply');
    jvt_test_assert(!$configuration_edit['writes'] && strpos(wp_json_encode($configuration_edit['conflicts']), 'configuration_user_changed') !== false && get_option('show_on_front') === 'posts', 'owner Reading setting edit is retained as configuration conflict');
    update_option('show_on_front', 'page');
    $menu_item = wp_get_nav_menu_items($owned_menus[0])[0];
    wp_update_nav_menu_item($owned_menus[0], $menu_item->ID, array('menu-item-title' => 'Owner edited menu', 'menu-item-url' => 'https://example.org/owner', 'menu-item-type' => 'custom', 'menu-item-status' => 'publish'));
    $menu_edit = jvt_import_execute($seed, $manifest, $root, 'configure-apply');
    jvt_test_assert(!$menu_edit['writes'] && strpos(wp_json_encode($menu_edit['conflicts']), 'configuration_user_changed') !== false && wp_get_nav_menu_items($owned_menus[0])[0]->title === 'Owner edited menu', 'owner menu edit survives configuration rerun');
} catch (Throwable $caught) {
    $error = $caught->getMessage();
} finally {
    foreach (array_unique($owned_menus) as $id) {
        wp_delete_nav_menu($id);
    }
    if ($configuration_restore) {
        foreach ($configuration_restore['options'] as $option => $value) {
            if ($value === null) {
                delete_option($option);
            } else {
                update_option($option, $value);
            }
        }
        if ($configuration_restore['locations_exist']) {
            set_theme_mod('nav_menu_locations', $configuration_restore['locations']);
        } else {
            remove_theme_mod('nav_menu_locations');
        }
        if ($configuration_restore['managed_state'] === null) {
            delete_option('_jvt_configuration_state');
        } else {
            update_option('_jvt_configuration_state', $configuration_restore['managed_state'], false);
        }
    }
    // Only IDs allocated by this test are removed. Never delete by a broad query.
    foreach (array_unique($owned_posts) as $id) {
        wp_delete_post($id, true);
    }
    foreach (array_unique($owned_media) as $id) {
        wp_delete_attachment($id, true);
    }
    delete_option($unrelated_option);
}
WP_CLI::line(wp_json_encode(array('passes' => count($GLOBALS['jvt_test_cases']), 'cases' => $GLOBALS['jvt_test_cases'], 'php' => PHP_VERSION, 'wordpress' => get_bloginfo('version'), 'cleanup' => 'Allocated fixture page/attachment/menu IDs removed; one named fixture option removed; targeted Reading/icon/menu settings restored.', 'failure' => $error), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
if ($error) {
    WP_CLI::error($error);
}
