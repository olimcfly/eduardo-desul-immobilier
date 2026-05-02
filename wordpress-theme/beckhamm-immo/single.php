<?php
declare(strict_types=1);
get_header();
?>

<section class="section">
    <div class="container container--narrow">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article <?php post_class('content'); ?>>
                <h1><?php the_title(); ?></h1>
                <p class="meta"><?php echo esc_html(get_the_date()); ?></p>
                <?php the_content(); ?>
            </article>
        <?php endwhile; endif; ?>
    </div>
</section>

<?php get_footer(); ?>
