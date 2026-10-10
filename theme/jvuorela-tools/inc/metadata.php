<?php
/** Editable social metadata. SPDX-License-Identifier: GPL-2.0-or-later */
if (!defined('ABSPATH')) { exit; }
add_action('add_meta_boxes_page', function () {
    add_meta_box('jvt-social', __('Sharing image', 'jvuorela-tools'), function ($post) {
        wp_nonce_field('jvt_social_save', 'jvt_social_nonce');
        $id = absint(get_post_meta($post->ID, '_jvt_social_image', true));
        echo '<p><label for="jvt-social-id">' . esc_html__('Media Library attachment ID', 'jvuorela-tools') . '</label></p>';
        echo '<input type="number" min="0" id="jvt-social-id" name="jvt_social_image" value="' . esc_attr($id) . '">';
        echo '<p>' . esc_html__('Use the supplied sharing image. Edit the Page excerpt for its description.', 'jvuorela-tools') . '</p>';
        if ($id) { echo wp_get_attachment_image($id, 'medium'); }
    }, 'page', 'side');
});
add_action('save_post_page', function ($id) {
    if (wp_is_post_autosave($id) || wp_is_post_revision($id) || !current_user_can('edit_post', $id)) { return; }
    if (!isset($_POST['jvt_social_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['jvt_social_nonce'])), 'jvt_social_save')) { return; }
    $image = isset($_POST['jvt_social_image']) ? absint($_POST['jvt_social_image']) : 0;
    if ($image && !wp_attachment_is_image($image)) { return; }
    update_post_meta($id, '_jvt_social_image', $image);
});
add_action('wp_head', function () {
    if (!is_singular('page') || !apply_filters('jvt_emit_metadata', true)) { return; }
    $post = get_queried_object();
    $description = trim(wp_strip_all_tags($post->post_excerpt));
    $url = get_permalink($post);
    $image = wp_get_attachment_image_url(absint(get_post_meta($post->ID, '_jvt_social_image', true)), 'full');
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(get_the_title($post)) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    if ($description !== '') {
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    }
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    }
});
