<?php /** SPDX-License-Identifier: GPL-2.0-or-later */ get_header(); ?>
<main id="main-content" class="shell support-page">
<?php while (have_posts()) : the_post(); ?><h1><?php the_title(); ?></h1><div class="page-content"><?php the_content(); ?></div><?php endwhile; ?>
</main><?php get_footer(); ?>
