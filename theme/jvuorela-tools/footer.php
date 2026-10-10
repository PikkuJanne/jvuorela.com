<?php /** SPDX-License-Identifier: GPL-2.0-or-later */ ?>
<footer class="site-footer shell">
<p><?php echo esc_html(get_bloginfo('description')); ?></p>
<nav aria-label="<?php esc_attr_e('Footer navigation', 'jvuorela-tools'); ?>"><?php wp_nav_menu(['theme_location' => 'footer', 'container' => false, 'fallback_cb' => false, 'depth' => 1]); ?></nav>
</footer>
<?php wp_footer(); ?></body></html>
