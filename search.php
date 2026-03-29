<?php get_header(); ?>

<div class="container">

    <header class="search-header">
        <?php if ( have_posts() ) : ?>
            <p class="archive-type"><?php nota_e( 'Search' ); ?></p>
            <h1 class="archive-title">
                <?php echo esc_html( nota_t( 'Results for' ) ) . ' '; ?>
                <em><?php echo esc_html( get_search_query() ); ?></em>
            </h1>
        <?php else : ?>
            <p class="archive-type"><?php nota_e( 'Search' ); ?></p>
            <h1 class="archive-title"><?php nota_e( 'No results found' ); ?></h1>
        <?php endif; ?>
        <?php get_search_form(); ?>
    </header>

    <?php if ( have_posts() ) : ?>

        <div class="post-grid">
            <?php while ( have_posts() ) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>

                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>" class="post-thumbnail">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </a>
                    <?php endif; ?>

                    <div class="card-content">
                        <h2 class="entry-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="entry-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="read-more"><?php nota_e( 'Read more' ); ?></a>
                    </div>

                </article>

            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php the_posts_pagination( array(
                'prev_text' => nota_t( 'Previous' ),
                'next_text' => nota_t( 'Next' ),
            ) ); ?>
        </div>

    <?php else : ?>
        <p class="search-no-results"><?php nota_e( 'No articles found' ); ?></p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
