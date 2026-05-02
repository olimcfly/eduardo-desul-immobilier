<?php
declare(strict_types=1);
get_header();
?>

<section class="section">
    <div class="container">
        <h1><?php the_archive_title(); ?></h1>
        <p><?php the_archive_description(); ?></p>

        <?php if (have_posts()) : ?>
            <div class="cards">
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class('card'); ?>>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p><?php echo esc_html(get_the_excerpt()); ?></p>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <?php get_template_part('template-parts/content', 'none'); ?>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
