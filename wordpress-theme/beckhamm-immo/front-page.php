<?php
declare(strict_types=1);
get_header();
?>

<section class="hero">
    <div class="container">
        <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
        <p><?php echo esc_html(get_bloginfo('description')); ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2><?php esc_html_e('Derniers articles', 'beckhamm-immo'); ?></h2>
        <div class="cards">
            <?php
            $query = new WP_Query([
                'post_type' => 'post',
                'posts_per_page' => 3,
            ]);
            if ($query->have_posts()) :
                while ($query->have_posts()) :
                    $query->the_post();
                    ?>
                    <article <?php post_class('card'); ?>>
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <p><?php echo esc_html(get_the_excerpt()); ?></p>
                    </article>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                get_template_part('template-parts/content', 'none');
            endif;
            ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
