<?php /** SPDX-License-Identifier: GPL-2.0-or-later */
get_header(); ?>
<main id="main-content" class="shell home-page">
<?php while (have_posts()) : the_post(); ?>
<div class="home-heading"><h1><?php the_title(); ?></h1></div>
<div class="page-content"><?php the_content(); ?></div>
<?php endwhile; ?>
</main><?php get_footer(); ?>
