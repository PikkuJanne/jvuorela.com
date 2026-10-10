<?php /** SPDX-License-Identifier: GPL-2.0-or-later */ get_header(); ?>
<main id="main-content" class="shell not-found"><p class="eyebrow">404</p>
<?php
$jvt_404_copy = jvt_404_content_page();
if ($jvt_404_copy) {
    echo '<h1>' . esc_html($jvt_404_copy->post_title) . '</h1>';
    // Native dynamic blocks need this editable Page's context. Preserve the
    // surrounding 404 query context rather than replacing the main query.
    $jvt_previous_context = [];
    foreach (['post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages'] as $jvt_context_key) {
        $jvt_previous_context[$jvt_context_key] = ['exists' => array_key_exists($jvt_context_key, $GLOBALS), 'value' => $GLOBALS[$jvt_context_key] ?? null];
    }
    try {
        $GLOBALS['post'] = $jvt_404_copy;
        setup_postdata($jvt_404_copy);
        echo apply_filters('the_content', $jvt_404_copy->post_content);
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
