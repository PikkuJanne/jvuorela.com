<?php
/** SPDX-License-Identifier: GPL-2.0-or-later
 * Read-only fresh editorial review snapshot. Numeric IDs/URLs are environment-local.
 * Redirect stdout to private storage; never publish a database or an unreviewed export.
 */
if (!defined('WP_CLI') || !WP_CLI) {
    throw new RuntimeException('This development script requires WP-CLI.');
}
if (!defined('JVT_IMPORT_NO_RUN')) { define('JVT_IMPORT_NO_RUN', true); }
require_once __DIR__ . '/import-content.php';
function jvt_export_snapshot() {
    $current_configuration = array('options' => array(), 'locations' => get_theme_mod('nav_menu_locations', array()), 'menus' => array());
    foreach (array('blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'wp_page_for_privacy_policy', 'site_icon', 'permalink_structure', 'blog_public', 'home', 'siteurl', 'jvt_404_page') as $option) {
        $current_configuration['options'][$option] = get_option($option);
    }
    foreach (wp_get_nav_menus(array('hide_empty' => false)) as $menu) {
        $current_configuration['menus'][] = array('wordpress_id' => (int) $menu->term_id, 'current' => jvt_import_menu_state((int) $menu->term_id));
    }
    // This full snapshot is intentionally private: current URLs/IDs and owner
    // wording must survive review. Publish only separately sanitized evidence.
    $export = array('schema_version' => 2, 'exported_at_utc' => gmdate('c'), 'site_url' => home_url('/'), 'pages' => array(), 'media' => array(), 'configuration' => array('current' => $current_configuration, 'import_baseline' => get_option('_jvt_configuration_state', null)));
    foreach (jvt_import_marker_index('_jvt_content_id') as $marker => $matches) {
        if (count($matches) !== 1 || $matches[0]['type'] !== 'page') {
            WP_CLI::error('Duplicate or invalid page marker: ' . $marker);
        }
        $id = $matches[0]['id'];
        $export['pages'][] = array('id' => $marker, 'wordpress_id' => $id, 'current' => jvt_import_page_state($id), 'seed_source_hash' => get_post_meta($id, '_jvt_seed_source_hash', true), 'baseline' => get_post_meta($id, '_jvt_content_baseline', true));
    }
    foreach (jvt_import_marker_index('_jvt_asset_slot') as $marker => $matches) {
        if (count($matches) !== 1 || $matches[0]['type'] !== 'attachment') {
            WP_CLI::error('Duplicate or invalid media marker: ' . $marker);
        }
        $id = $matches[0]['id'];
        $export['media'][] = array('slot_id' => $marker, 'wordpress_id' => $id, 'url' => wp_get_attachment_url($id), 'current' => jvt_import_asset_state($id), 'source_derivative_sha256' => get_post_meta($id, '_jvt_asset_sha256', true));
    }
    return $export;
}
if (!defined('JVT_EXPORT_NO_RUN') || !JVT_EXPORT_NO_RUN) {
    WP_CLI::line(wp_json_encode(jvt_export_snapshot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}
