<?php
/** Native theme setup. SPDX-License-Identifier: GPL-2.0-or-later */
if (!defined('ABSPATH')) { exit; }
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets']);
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('align-wide');
    add_post_type_support('page', 'excerpt');
    add_editor_style('assets/css/editor.css');
    add_image_size('jvt-character-small', 480, 0, false);
    add_image_size('jvt-avatar-small', 360, 0, false);
    register_nav_menus(['primary' => __('Header links', 'jvuorela-tools'), 'footer' => __('Footer links', 'jvuorela-tools')]);
});
// Generate only the appropriate proportional size for project character/avatar
// uploads. Unrelated uploads do not acquire these extra theme image sizes.
add_filter('intermediate_image_sizes_advanced', function ($sizes, $metadata, $id) {
    $slot = (string) get_post_meta($id, '_jvt_asset_slot', true);
    $file = basename((string) get_post_meta($id, '_wp_attached_file', true));
    // The explicit importer assigns the slot after normal core sideloading.
    $character = str_ends_with($slot, '-character') || preg_match('/^(?:winbooksplit|winpdfmerger|winvidcompress|universalpodcastdownloader|winaudioclean|winimagenormalizer|audiofileforphone)-character-v01-960(?:-\d+)?\.webp$/', $file);
    $avatar = str_ends_with($slot, '-avatar') || preg_match('/^(?:winbooksplit|winpdfmerger|winvidcompress|universalpodcastdownloader|winaudioclean|winimagenormalizer|audiofileforphone)-avatar-v01-640(?:-\d+)?\.webp$/', $file);
    if (!$character) { unset($sizes['jvt-character-small']); }
    if (!$avatar) { unset($sizes['jvt-avatar-small']); }
    return $sizes;
}, 10, 3);
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('jvt-theme', get_template_directory_uri() . '/assets/css/site.css', [], wp_get_theme()->get('Version'));
});
add_action('init', function () {
    register_block_pattern('jvuorela-tools/section', [
        'title' => __('Tool information section', 'jvuorela-tools'),
        'categories' => ['text'],
        'content' => '<!-- wp:group {"className":"tool-section","layout":{"type":"constrained"}} --><div class="wp-block-group tool-section"><!-- wp:heading --><h2 class="wp-block-heading">Section heading</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Write your tool information here.</p><!-- /wp:paragraph --></div><!-- /wp:group -->',
    ]);
});
add_filter('wp_content_img_tag', function ($tag, $context, $attachment_id) {
    $slot = (string) get_post_meta($attachment_id, '_jvt_asset_slot', true);
    if (str_ends_with($slot, '-character')) {
        $tag = preg_replace('/\s(?:loading|fetchpriority|sizes)="[^"]*"/', '', $tag);
        $tag = preg_replace('/<img\s/', '<img loading="eager" fetchpriority="high" sizes="(max-width: 600px) 70vw, (max-width: 1248px) 42vw, 530px" ', $tag, 1);
    } elseif (str_ends_with($slot, '-avatar')) {
        $tag = preg_replace('/\s(?:loading|fetchpriority|sizes)="[^"]*"/', '', $tag);
        $tag = preg_replace('/<img\s/', '<img loading="lazy" sizes="(max-width: 600px) 80vw, (max-width: 900px) 42vw, 340px" ', $tag, 1);
    } elseif (str_ends_with($slot, '-icon')) {
        $tag = preg_replace('/\sfetchpriority="[^"]*"/', '', $tag);
    }
    return $tag;
}, 20, 3);
add_filter('site_icon_meta_tags', function ($tags) {
    if (get_post_meta((int) get_option('site_icon'), '_jvt_asset_slot', true) !== 'site-icon') { return $tags; }
    $ico = get_template_directory_uri() . '/assets/images/site-favicon-v01-64.ico';
    foreach ($tags as &$tag) {
        if (str_contains($tag, 'sizes="32x32"')) { $tag = '<link rel="icon" href="' . esc_url($ico) . '" sizes="any">'; }
    }
    return $tags;
});
