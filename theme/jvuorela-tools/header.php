<?php /** SPDX-License-Identifier: GPL-2.0-or-later */ ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e('Skip to content', 'jvuorela-tools'); ?></a>
<header class="site-header shell">
<a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>">
<?php $icon = get_site_icon_url(96); if ($icon) : ?><img src="<?php echo esc_url($icon); ?>" alt="" width="44" height="44"><?php endif; ?>
<span><?php echo esc_html(get_bloginfo('name')); ?></span></a>
<nav aria-label="<?php esc_attr_e('Main navigation', 'jvuorela-tools'); ?>">
<?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'fallback_cb' => false, 'depth' => 1]); ?>
</nav></header>
