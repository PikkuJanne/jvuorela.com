<?php
/**
 * Explicit development-only WordPress draft/media importer.
 * SPDX-License-Identifier: GPL-2.0-or-later
 * Run with WP-CLI eval-file, never from theme activation or a web request.
 */

function jvt_import_hash($value) {
    return hash('sha256', wp_json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function jvt_import_json($path) {
    if (!is_readable($path)) {
        throw new RuntimeException('Input file is unavailable: ' . basename($path));
    }
    $value = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($value)) {
        throw new RuntimeException('Expected a JSON object.');
    }
    return $value;
}

function jvt_import_marker_index($key) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID, p.post_type, m.meta_value FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON p.ID=m.post_id WHERE m.meta_key=%s ORDER BY p.ID, m.meta_id",
        $key
    ), ARRAY_A);
    $index = array();
    foreach ($rows as $row) {
        $index[$row['meta_value']][] = array('id' => (int) $row['ID'], 'type' => $row['post_type']);
    }
    return $index;
}

function jvt_import_page_state($id) {
    $post = get_post($id);
    return array(
        'title' => $post->post_title,
        'slug' => $post->post_name,
        'excerpt' => $post->post_excerpt,
        'content' => $post->post_content,
        'status' => $post->post_status,
        'parent' => (int) $post->post_parent,
        'template' => get_post_meta($id, '_wp_page_template', true) ?: 'default',
        'social_image' => (int) get_post_meta($id, '_jvt_social_image', true),
    );
}

function jvt_import_asset_state($id) {
    $post = get_post($id);
    $file = get_attached_file($id);
    return array(
        'title' => $post->post_title,
        'caption' => $post->post_excerpt,
        'description' => $post->post_content,
        'status' => $post->post_status,
        'alt' => get_post_meta($id, '_wp_attachment_image_alt', true),
        'sha256' => is_readable($file) ? hash_file('sha256', $file) : null,
    );
}

function jvt_import_validate_pages($seed) {
    if (!isset($seed['pages']) || !is_array($seed['pages']) || !$seed['pages']) {
        throw new RuntimeException('Input must contain a nonempty pages array.');
    }
    $pages = array();
    $slugs = array();
    foreach ($seed['pages'] as $raw) {
        foreach (array('id', 'slug', 'title', 'excerpt', 'template', 'content') as $field) {
            if (!isset($raw[$field]) || !is_string($raw[$field])) {
                throw new RuntimeException('Every page needs string ' . $field . '.');
            }
        }
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,99}$/D', $raw['id']) || !preg_match('/^[a-z0-9][a-z0-9-]{0,199}$/D', $raw['slug'])) {
            throw new RuntimeException('Invalid stable page ID or root slug.');
        }
        if (isset($pages[$raw['id']]) || isset($slugs[$raw['slug']])) {
            throw new RuntimeException('Duplicate page ID or slug in seed.');
        }
        if (!in_array($raw['template'], array('', 'default', 'templates/tool.php'), true)) {
            throw new RuntimeException('Unknown page template.');
        }
        $page = array_intersect_key($raw, array_flip(array('id', 'slug', 'title', 'excerpt', 'template', 'content', 'social_slot')));
        $page['template'] = $page['template'] ?: 'default';
        $page['social_slot'] = $page['social_slot'] ?? null;
        if ($page['social_slot'] !== null && !is_string($page['social_slot'])) {
            throw new RuntimeException('social_slot must be a slot ID or null.');
        }
        $without_tokens = preg_replace('/\{\{(?:asset|attachment|page):[a-z0-9_-]+\}\}/', '', $page['content']);
        if (preg_match('/\{\{(?:asset|attachment|page):/', $without_tokens)) {
            throw new RuntimeException('Malformed content token in page ' . $page['id'] . '.');
        }
        $pages[$page['id']] = $page;
        $slugs[$page['slug']] = true;
    }
    return $pages;
}

function jvt_import_asset_specs($manifest, $pages, $root) {
    if (!isset($manifest['slots']) || !is_array($manifest['slots'])) {
        throw new RuntimeException('Asset manifest needs slots.');
    }
    $needed = array();
    foreach ($pages as $page) {
        preg_match_all('/\{\{(?:asset|attachment):([a-z0-9_-]+)\}\}/', $page['content'], $matches);
        foreach ($matches[1] as $slot) {
            $needed[$slot] = true;
        }
        if ($page['social_slot']) {
            $needed[$page['social_slot']] = true;
        }
    }
    $specs = array();
    $seen = array();
    foreach ($manifest['slots'] as $slot) {
        $id = $slot['slot_id'] ?? '';
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,99}$/D', $id) || isset($seen[$id])) {
            throw new RuntimeException('Invalid or duplicate manifest slot.');
        }
        $seen[$id] = true;
        if (empty($slot['required_for_launch']) && !isset($needed[$id])) {
            continue;
        }
        if (empty($slot['approval']['approved']) || empty($slot['source_sha256']) || empty($slot['derivatives'])) {
            throw new RuntimeException('Approved traced derivatives unavailable for slot ' . $id . '.');
        }
        $derivatives = $slot['derivatives'];
        usort($derivatives, function ($a, $b) { return ($b['width'] ?? 0) <=> ($a['width'] ?? 0); });
        $derivative = $derivatives[0];
        $path = $derivative['path'] ?? '';
        if (!preg_match('#^assets/approved/[a-zA-Z0-9._-]+\.(?:png|webp|jpg|jpeg|ico)$#D', $path)) {
            throw new RuntimeException('Media source must be an approved public derivative.');
        }
        $full = realpath($root . '/' . $path);
        $approved = realpath($root . '/assets/approved');
        if (!$full || !$approved || strpos(str_replace('\\', '/', $full), str_replace('\\', '/', $approved) . '/') !== 0 || !is_file($full)) {
            throw new RuntimeException('Missing or out-of-scope derivative for ' . $id . '.');
        }
        $sha = hash_file('sha256', $full);
        if (!isset($derivative['sha256'], $derivative['source_sha256']) || !hash_equals($derivative['sha256'], $sha) || !hash_equals($slot['source_sha256'], $derivative['source_sha256'])) {
            throw new RuntimeException('Derivative/provenance checksum mismatch for ' . $id . '.');
        }
        $runtime_only = ($slot['kind'] ?? '') === 'favicon';
        if ($runtime_only && isset($needed[$id])) {
            throw new RuntimeException('Favicon is a theme runtime file, not a Media Library slot.');
        }
        $specs[$id] = array('path' => $full, 'public_path' => $path, 'sha256' => $sha, 'alt' => (string) ($slot['alt_text'] ?? ''), 'runtime_only' => $runtime_only);
        unset($needed[$id]);
    }
    if ($needed) {
        throw new RuntimeException('Unknown referenced media slots: ' . implode(', ', array_keys($needed)));
    }
    return $specs;
}

function jvt_import_resolve($text, $pages, $attachments, $front_page = null) {
    $resolved = preg_replace_callback('/\{\{(asset|attachment|page):([a-z0-9_-]+)\}\}/', function ($match) use ($pages, $attachments, $front_page) {
        $id = $match[2];
        if ($match[1] === 'page') {
            if (!isset($pages[$id])) {
                throw new RuntimeException('Unresolved page token: ' . $id);
            }
            if ($id === $front_page) {
                return esc_url(home_url('/'));
            }
            // Core sample Page links honor the configured page permastruct for drafts.
            // Ordinary get_permalink() intentionally uses ?page_id for unpublished Pages.
            return esc_url(get_page_link($pages[$id], false, true));
        }
        if (!isset($attachments[$id])) {
            throw new RuntimeException('Unresolved attachment token: ' . $id);
        }
        if ($match[1] === 'attachment') {
            return (string) (int) $attachments[$id];
        }
        $url = wp_get_attachment_url($attachments[$id]);
        if (!$url) {
            throw new RuntimeException('Media URL unavailable: ' . $id);
        }
        return esc_url($url);
    }, $text);
    if (preg_match('/\{\{(?:asset|attachment|page):/', $resolved)) {
        throw new RuntimeException('Malformed unresolved content token.');
    }
    return $resolved;
}

function jvt_import_proportional_sizes($sizes) {
    foreach ($sizes as &$size) {
        $size['crop'] = false;
    }
    return $sizes;
}

function jvt_import_upload($slot, $spec) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $temporary = wp_tempnam(basename($spec['path']));
    if (!$temporary || !copy($spec['path'], $temporary)) {
        throw new RuntimeException('Unable to create an owned temporary upload.');
    }
    add_filter('intermediate_image_sizes_advanced', 'jvt_import_proportional_sizes');
    add_filter('big_image_size_threshold', '__return_false');
    try {
        $id = media_handle_sideload(array('name' => basename($spec['path']), 'tmp_name' => $temporary), 0, $slot);
    } finally {
        remove_filter('intermediate_image_sizes_advanced', 'jvt_import_proportional_sizes');
        remove_filter('big_image_size_threshold', '__return_false');
        if (file_exists($temporary)) {
            unlink($temporary);
        }
    }
    if (is_wp_error($id)) {
        throw new RuntimeException('Media upload failed: ' . $id->get_error_message());
    }
    add_post_meta($id, '_jvt_asset_slot', $slot, true);
    update_post_meta($id, '_jvt_asset_sha256', $spec['sha256']);
    update_post_meta($id, '_wp_attachment_image_alt', $spec['alt']);
    update_post_meta($id, '_jvt_asset_baseline', jvt_import_asset_state($id));
    return (int) $id;
}

function jvt_import_execute($seed, $manifest, $root, $action = 'plan') {
    global $wpdb;
    if (!in_array($action, array('plan', 'apply', 'configure', 'configure-apply'), true)) {
        throw new RuntimeException('Action must be plan, apply, configure or configure-apply.');
    }
    $pages = jvt_import_validate_pages($seed);
    $specs = jvt_import_asset_specs($manifest, $pages, $root);
    $page_index = jvt_import_marker_index('_jvt_content_id');
    $asset_index = jvt_import_marker_index('_jvt_asset_slot');
    $report = array('action' => $action, 'writes' => false, 'pages' => array(), 'media' => array(), 'conflicts' => array());
    foreach (array('page' => $page_index, 'media' => $asset_index) as $kind => $index) {
        foreach ($index as $marker => $matches) {
            if (count($matches) !== 1 || $matches[0]['type'] !== ($kind === 'page' ? 'page' : 'attachment')) {
                $report['conflicts'][] = array('kind' => $kind, 'id' => $marker, 'reason' => 'duplicate_or_wrong_type_marker');
            }
        }
    }
    $page_ids = array();
    foreach ($pages as $id => $page) {
        $existing = $page_index[$id] ?? array();
        $row = array('id' => $id, 'state' => 'create', 'wordpress_id' => null, 'reasons' => array(), 'changed_fields' => array());
        if (count($existing) === 1 && $existing[0]['type'] === 'page') {
            $post_id = $existing[0]['id'];
            $page_ids[$id] = $post_id;
            $row['wordpress_id'] = $post_id;
            $row['state'] = 'unchanged';
            $baseline = get_post_meta($post_id, '_jvt_content_baseline', true);
            $current = jvt_import_page_state($post_id);
            if (!is_array($baseline) || jvt_import_hash($baseline) !== jvt_import_hash($current)) {
                $row['reasons'][] = 'user_changed_or_missing_baseline';
                foreach ($current as $field => $value) {
                    if (!is_array($baseline) || !array_key_exists($field, $baseline) || $baseline[$field] !== $value) {
                        $row['changed_fields'][] = $field;
                    }
                }
            }
            $row['previous_seed_hash'] = get_post_meta($post_id, '_jvt_seed_source_hash', true);
            $row['proposed_seed_hash'] = jvt_import_hash($page);
            if ($row['previous_seed_hash'] !== $row['proposed_seed_hash']) {
                $row['reasons'][] = 'seed_changed';
            }
        } elseif ($existing) {
            $row['reasons'][] = 'duplicate_or_wrong_type_marker';
        }
        $slug_ids = array_map('intval', $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='page' AND post_name=%s", $page['slug'])));
        foreach ($slug_ids as $slug_id) {
            if ($slug_id !== ($row['wordpress_id'] ?? 0)) {
                $row['reasons'][] = 'slug_collision';
            }
        }
        preg_match_all('/\{\{page:([a-z0-9_-]+)\}\}/', $page['content'], $tokens);
        foreach ($tokens[1] as $token) {
            if (!isset($pages[$token])) {
                $row['reasons'][] = 'unknown_page_token:' . $token;
            }
        }
        if ($row['reasons']) {
            $row['state'] = 'conflict';
            $report['conflicts'][] = array('kind' => 'page', 'id' => $id, 'reason' => implode(',', array_unique($row['reasons'])));
        }
        $report['pages'][] = $row;
    }
    $attachment_ids = array();
    foreach ($specs as $slot => $spec) {
        $existing = $asset_index[$slot] ?? array();
        $row = array('id' => $slot, 'state' => 'upload', 'wordpress_id' => null, 'path' => $spec['public_path'], 'sha256' => $spec['sha256'], 'reasons' => array());
        if ($spec['runtime_only']) {
            $row['state'] = 'theme_runtime_file';
            if ($existing) {
                $row['state'] = 'conflict';
                $row['reasons'][] = 'unexpected_attachment_for_theme_favicon';
                $report['conflicts'][] = array('kind' => 'media', 'id' => $slot, 'reason' => 'unexpected_attachment_for_theme_favicon');
            }
            $report['media'][] = $row;
            continue;
        }
        if (count($existing) === 1 && $existing[0]['type'] === 'attachment') {
            $post_id = $existing[0]['id'];
            $attachment_ids[$slot] = $post_id;
            $row['wordpress_id'] = $post_id;
            $row['state'] = 'unchanged';
            if (get_post_meta($post_id, '_jvt_asset_sha256', true) !== $spec['sha256']) {
                $row['reasons'][] = 'derivative_changed';
            }
            $baseline = get_post_meta($post_id, '_jvt_asset_baseline', true);
            if (!is_array($baseline) || jvt_import_hash($baseline) !== jvt_import_hash(jvt_import_asset_state($post_id))) {
                $row['reasons'][] = 'media_user_changed_or_missing_file';
            }
        } elseif ($existing) {
            $row['reasons'][] = 'duplicate_or_wrong_type_marker';
        }
        if ($row['reasons']) {
            $row['state'] = 'conflict';
            $report['conflicts'][] = array('kind' => 'media', 'id' => $slot, 'reason' => implode(',', $row['reasons']));
        }
        $report['media'][] = $row;
    }
    if (strpos($action, 'configure') === 0) {
        return jvt_import_configure($seed['configuration'] ?? array(), $page_ids, $attachment_ids, $report, $action === 'configure-apply');
    }
    if ($action === 'plan' || $report['conflicts']) {
        return $report;
    }
    if (!current_user_can('edit_pages') || !current_user_can('upload_files')) {
        throw new RuntimeException('Apply requires an explicit WordPress user with edit_pages and upload_files.');
    }
    foreach ($report['media'] as &$row) {
        if ($row['state'] === 'upload') {
            $attachment_ids[$row['id']] = jvt_import_upload($row['id'], $specs[$row['id']]);
            $row['wordpress_id'] = $attachment_ids[$row['id']];
            $row['state'] = 'uploaded';
            $report['writes'] = true;
        }
    }
    unset($row);
    // Allocate every missing draft first, so cross-page links use real local IDs/URLs.
    foreach ($report['pages'] as &$row) {
        if ($row['state'] === 'create') {
            $page = $pages[$row['id']];
            $post_id = wp_insert_post(wp_slash(array('post_type' => 'page', 'post_status' => 'draft', 'post_title' => $page['title'], 'post_name' => $page['slug'], 'post_excerpt' => $page['excerpt'], 'post_content' => '', 'post_parent' => 0)), true);
            if (is_wp_error($post_id)) {
                throw new RuntimeException('Draft insertion failed: ' . $post_id->get_error_message());
            }
            add_post_meta($post_id, '_jvt_content_id', $page['id'], true);
            $page_ids[$page['id']] = (int) $post_id;
            $row['wordpress_id'] = (int) $post_id;
            $report['writes'] = true;
        }
    }
    unset($row);
    foreach ($report['pages'] as &$row) {
        if ($row['state'] === 'create') {
            $page = $pages[$row['id']];
            $post_id = $page_ids[$page['id']];
            $resolved = jvt_import_resolve($page['content'], $page_ids, $attachment_ids, $seed['configuration']['front_page'] ?? null);
            $updated = wp_update_post(wp_slash(array('ID' => $post_id, 'post_content' => $resolved)), true);
            if (is_wp_error($updated)) {
                throw new RuntimeException('Draft content save failed: ' . $updated->get_error_message());
            }
            update_post_meta($post_id, '_wp_page_template', $page['template']);
            update_post_meta($post_id, '_jvt_social_image', $page['social_slot'] ? $attachment_ids[$page['social_slot']] : 0);
            update_post_meta($post_id, '_jvt_seed_source_hash', jvt_import_hash($page));
            update_post_meta($post_id, '_jvt_content_baseline', jvt_import_page_state($post_id));
            $row['state'] = 'created_draft';
        }
    }
    unset($row);
    return $report;
}

function jvt_import_menu_state($id) {
    $menu = wp_get_nav_menu_object($id);
    $items = array();
    foreach (wp_get_nav_menu_items($id) ?: array() as $item) {
        $items[] = array('id' => (int) $item->ID, 'title' => $item->title, 'url' => $item->url, 'type' => $item->type, 'object_id' => (int) $item->object_id, 'parent' => (int) $item->menu_item_parent, 'order' => (int) $item->menu_order);
    }
    return array('name' => $menu ? $menu->name : null, 'items' => $items);
}

function jvt_import_configure($configuration, $pages, $attachments, $report, $apply) {
    if (!$configuration || !is_array($configuration)) {
        throw new RuntimeException('Explicit configuration object is missing.');
    }
    $menu_prefix = $configuration['menu_prefix'] ?? 'JVuorela';
    if (!is_string($menu_prefix) || !preg_match('/^[a-zA-Z0-9 _-]{1,80}$/D', $menu_prefix)) {
        throw new RuntimeException('Invalid reviewed menu name prefix.');
    }
    $options = array();
    foreach (array('front_page' => 'page_on_front', 'privacy_page' => 'wp_page_for_privacy_policy') as $key => $option) {
        if (isset($configuration[$key])) {
            if (!isset($pages[$configuration[$key]])) {
                $report['conflicts'][] = array('kind' => 'configuration', 'id' => $key, 'reason' => 'page_not_imported');
            } else {
                $options[$option] = $pages[$configuration[$key]];
            }
        }
    }
    if (isset($options['page_on_front'])) {
        $options['show_on_front'] = 'page';
    }
    if (isset($configuration['site_icon_slot'])) {
        if (!isset($attachments[$configuration['site_icon_slot']])) {
            $report['conflicts'][] = array('kind' => 'configuration', 'id' => 'site_icon_slot', 'reason' => 'media_not_imported');
        } else {
            $options['site_icon'] = $attachments[$configuration['site_icon_slot']];
        }
    }
    foreach (array('site_title' => 'blogname', 'site_tagline' => 'blogdescription') as $key => $option) {
        if (isset($configuration[$key])) {
            if (!is_string($configuration[$key])) {
                throw new RuntimeException('Site wording must be strings.');
            }
            $options[$option] = $configuration[$key];
        }
    }
    $desired_menus = array();
    foreach ($configuration['menus'] ?? array() as $location => $items) {
        if (!in_array($location, array('primary', 'footer'), true) || !is_array($items)) {
            throw new RuntimeException('Unknown menu location or invalid items.');
        }
        foreach ($items as $item) {
            if (!isset($item['title'], $item['url']) || !is_string($item['title']) || !is_string($item['url'])) {
                throw new RuntimeException('Menu items need title and URL strings.');
            }
            $url = jvt_import_resolve($item['url'], $pages, $attachments, $configuration['front_page'] ?? null);
            if (!preg_match('#^https?://#i', $url)) {
                throw new RuntimeException('Menu URLs must resolve to HTTP(S).');
            }
            $desired_menus[$location][] = array('title' => $item['title'], 'url' => $url);
        }
    }
    $state = get_option('_jvt_configuration_state', null);
    $locations = get_theme_mod('nav_menu_locations', array());
    $current = array('options' => array(), 'menus' => array(), 'locations' => array());
    foreach ($options as $option => $desired) {
        $current['options'][$option] = (string) get_option($option);
    }
    foreach ($desired_menus as $location => $items) {
        $id = (int) ($locations[$location] ?? 0);
        $current['locations'][$location] = $id;
        $current['menus'][$location] = $id ? jvt_import_menu_state($id) : null;
    }
    // Hash portable intent, not URLs whose canonical form can change after Home setup.
    $desired_hash = jvt_import_hash($configuration);
    if (is_array($state)) {
        if ($state['source_hash'] !== $desired_hash) {
            $report['conflicts'][] = array('kind' => 'configuration', 'id' => 'source', 'reason' => 'configuration_seed_changed');
        }
        if (jvt_import_hash($state['baseline']) !== jvt_import_hash($current)) {
            $report['conflicts'][] = array('kind' => 'configuration', 'id' => 'hosted', 'reason' => 'configuration_user_changed');
        }
    } else {
        $defaults = array('page_on_front' => array(0, '0', false, ''), 'wp_page_for_privacy_policy' => array(0, '0', false, ''), 'show_on_front' => array('posts'), 'site_icon' => array(0, '0', false, ''), 'blogname' => array('', false, 'WordPress', 'My WordPress', 'Website'), 'blogdescription' => array('', false, 'Just another WordPress site'));
        foreach ($options as $option => $desired) {
            if ((string) $current['options'][$option] !== (string) $desired && !in_array($current['options'][$option], $defaults[$option], true)) {
                $report['conflicts'][] = array('kind' => 'configuration', 'id' => $option, 'reason' => 'existing_nondefault_option');
            }
        }
        foreach ($desired_menus as $location => $items) {
            if ($current['locations'][$location] || wp_get_nav_menu_object($menu_prefix . ' ' . $location)) {
                $report['conflicts'][] = array('kind' => 'configuration', 'id' => $location, 'reason' => 'existing_menu_or_location');
            }
        }
    }
    $report['configuration'] = array('state' => $report['conflicts'] ? 'conflict' : (is_array($state) ? 'unchanged' : 'configure'), 'desired_options' => $options, 'menus' => $desired_menus);
    if (!$apply || $report['conflicts'] || is_array($state)) {
        return $report;
    }
    if (!current_user_can('manage_options') || !current_user_can('edit_theme_options')) {
        throw new RuntimeException('Configuration apply requires manage_options and edit_theme_options.');
    }
    foreach ($desired_menus as $location => $items) {
        $menu_id = wp_create_nav_menu($menu_prefix . ' ' . $location);
        if (is_wp_error($menu_id)) {
            throw new RuntimeException('Menu creation failed: ' . $menu_id->get_error_message());
        }
        add_term_meta($menu_id, '_jvt_menu_location', $location, true);
        foreach ($items as $position => $item) {
            $item_id = wp_update_nav_menu_item($menu_id, 0, array('menu-item-title' => $item['title'], 'menu-item-url' => html_entity_decode($item['url'], ENT_QUOTES, 'UTF-8'), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish', 'menu-item-position' => $position + 1));
            if (is_wp_error($item_id)) {
                throw new RuntimeException('Menu item creation failed.');
            }
        }
        $locations[$location] = (int) $menu_id;
        $current['locations'][$location] = (int) $menu_id;
        $current['menus'][$location] = jvt_import_menu_state($menu_id);
    }
    if ($desired_menus) {
        set_theme_mod('nav_menu_locations', $locations);
    }
    foreach ($options as $option => $value) {
        update_option($option, $value);
        $current['options'][$option] = (string) get_option($option);
    }
    update_option('_jvt_configuration_state', array('source_hash' => $desired_hash, 'baseline' => $current), false);
    $report['writes'] = true;
    $report['configuration']['state'] = 'configured';
    return $report;
}

function jvt_import_main($args) {
    if (!defined('WP_CLI') || !WP_CLI) {
        throw new RuntimeException('This development script requires WP-CLI.');
    }
    $root = dirname(__DIR__);
    try {
        $action = $args[0] ?? 'plan';
        $seed = jvt_import_json($args[1] ?? $root . '/content/pages.json');
        $manifest = jvt_import_json($root . '/assets/manifest.json');
        $report = jvt_import_execute($seed, $manifest, $root, $action);
        WP_CLI::line(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if ($report['conflicts']) {
            WP_CLI::error('Conflicts retained; no changes applied.');
        }
    } catch (Throwable $error) {
        WP_CLI::error($error->getMessage() . ' Inspect current markers before retrying an interrupted apply.');
    }
}

if (!defined('JVT_IMPORT_NO_RUN')) {
    jvt_import_main(isset($args) ? $args : array());
}
