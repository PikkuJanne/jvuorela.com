<?php
/** Theme bootstrap. SPDX-License-Identifier: GPL-2.0-or-later */
if (!defined('ABSPATH')) { exit; }
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/metadata.php';

function jvt_404_page_is_public($page) {
    return $page instanceof WP_Post && $page->post_type === 'page' && $page->post_status === 'publish' && $page->post_password === '';
}

function jvt_sanitize_404_page($value) {
    if (!is_scalar($value)) { return 0; }
    $id = filter_var($value, FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) { return 0; }
    return jvt_404_page_is_public(get_post($id)) ? $id : 0;
}

function jvt_404_content_page() {
    $selected = jvt_sanitize_404_page(get_option('jvt_404_page', 0));
    if ($selected) { return get_post($selected); }
    $imported = get_posts(['post_type'=>'page', 'post_status'=>'publish', 'meta_key'=>'_jvt_content_id', 'meta_value'=>'not-found', 'numberposts'=>2]);
    return count($imported) === 1 && jvt_404_page_is_public($imported[0]) ? $imported[0] : null;
}

function jvt_404_page_field() {
    $protected = get_posts(['post_type'=>'page', 'post_status'=>'publish', 'has_password'=>true, 'numberposts'=>-1, 'fields'=>'ids']);
    wp_dropdown_pages([
        'name'=>'jvt_404_page', 'id'=>'jvt_404_page', 'selected'=>get_option('jvt_404_page', 0),
        'post_status'=>'publish', 'exclude'=>$protected, 'sort_column'=>'post_title',
        'show_option_none'=>__('Use default Page not found content', 'jvuorela-tools'), 'option_none_value'=>0,
    ]);
    echo '<p class="description">' . esc_html__('Choose a published Page for missing addresses. Its title and blocks remain editable in Pages. Password-protected Pages cannot be used.', 'jvuorela-tools') . '</p>';
}

function jvt_register_404_setting() {
    register_setting('reading', 'jvt_404_page', ['type'=>'integer', 'default'=>0, 'sanitize_callback'=>'jvt_sanitize_404_page']);
    add_settings_field('jvt_404_page', __('Page for missing addresses (404)', 'jvuorela-tools'), 'jvt_404_page_field', 'reading', 'default', ['label_for'=>'jvt_404_page']);
}
add_action('admin_init', 'jvt_register_404_setting');
