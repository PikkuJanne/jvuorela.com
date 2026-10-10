<?php
/**
 * Template Name: Tool
 * Template Post Type: page
 * SPDX-License-Identifier: GPL-2.0-or-later
 */
get_header(); ?>
<main id="main-content" class="shell tool-page">
<?php while (have_posts()) : the_post(); ?><h1 class="tool-title"><?php echo preg_replace('/(?<=[a-z])(?=[A-Z])/', '<wbr>', esc_html(get_the_title())); ?></h1><div class="page-content"><?php the_content(); ?></div><?php endwhile; ?>
</main><?php get_footer(); ?>
