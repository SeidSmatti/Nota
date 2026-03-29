<?php get_header(); ?>

<div class="container page-container">

    <?php while ( have_posts() ) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <div class="page-inner">

                <header class="page-header">
                    <?php the_title( '<h1 class="page-title">', '</h1>' ); ?>
                </header>

                <div class="page-content">
                    <?php
                    if ( has_post_thumbnail() ) {
                        echo '<div class="single-thumbnail">';
                        the_post_thumbnail( 'large' );
                        echo '</div>';
                    }
                    the_content();
                    ?>
                </div>

            </div>

        </article>

    <?php endwhile; ?>

</div>

<?php get_footer(); ?>
