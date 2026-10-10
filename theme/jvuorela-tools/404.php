<?php /** SPDX-License-Identifier: GPL-2.0-or-later */ get_header(); ?>
<main id="main-content" class="shell not-found"><p class="eyebrow">404</p>
<?php
$copy = get_posts(['post_type'=>'page', 'post_status'=>'publish', 'meta_key'=>'_jvt_content_id', 'meta_value'=>'not-found', 'numberposts'=>2]);
if (count($copy) === 1) {
    echo '<h1>' . esc_html($copy[0]->post_title) . '</h1>';
    // Native dynamic blocks need this editable Page's context. Preserve the
    // surrounding 404 query context rather than replacing the main query.
    $jvt_previous_context = [];
    foreach (['post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages'] as $jvt_context_key) {
        $jvt_previous_context[$jvt_context_key] = ['exists' => array_key_exists($jvt_context_key, $GLOBALS), 'value' => $GLOBALS[$jvt_context_key] ?? null];
    }
    try {
        $GLOBALS['post'] = $copy[0];
        setup_postdata($copy[0]);
        echo apply_filters('the_content', $copy[0]->post_content);
    } finally {
        foreach ($jvt_previous_context as $jvt_context_key => $jvt_previous_value) {
            if ($jvt_previous_value['exists']) {
                $GLOBALS[$jvt_context_key] = $jvt_previous_value['value'];
            } else {
                unset($GLOBALS[$jvt_context_key]);
            }
        }
    }
} else {
    echo '<h1>' . esc_html__('Page not found', 'jvuorela-tools') . '</h1><p><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Back to the tools', 'jvuorela-tools') . '</a></p>';
}
?></main>
<?php get_footer(); ?>
