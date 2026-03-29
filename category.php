<?php
/**
 * Category archive template.
 *
 * If the current category has subcategories, renders the multi-category
 * overview (TOC-style: latest 3 posts per subcategory).
 * Otherwise falls through to the standard post grid (same as archive.php).
 */
get_header();

$current_cat = get_queried_object(); // WP_Term
$children    = get_categories( array(
    'parent'     => $current_cat->term_id,
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
) );
$has_children = ! empty( $children );
?>

<div class="container<?php echo $has_children ? ' blog-home-container' : ''; ?>">

    <header class="<?php echo $has_children ? 'blog-home-header' : 'archive-header'; ?>">
        <h1 class="<?php echo $has_children ? 'page-title' : 'archive-title'; ?>">
            <?php echo esc_html( $current_cat->name ); ?>
        </h1>
        <?php if ( $current_cat->description ) : ?>
            <p class="<?php echo $has_children ? 'blog-cat-desc' : 'archive-description'; ?>">
                <?php echo esc_html( $current_cat->description ); ?>
            </p>
        <?php endif; ?>
    </header>

    <?php if ( $has_children ) : ?>

        <?php foreach ( $children as $cat ) :
            $posts = get_posts( array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'cat'            => $cat->term_id,
                'posts_per_page' => 3,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ) );
            if ( empty( $posts ) ) continue;
        ?>

        <section class="blog-cat-section">
            <div class="blog-cat-header">
                <h2 class="blog-cat-name"><?php echo esc_html( $cat->name ); ?></h2>
                <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="blog-cat-all">
                    <?php nota_e( 'All posts' ); ?> →
                </a>
            </div>

            <?php if ( $cat->description ) : ?>
                <p class="blog-cat-desc"><?php echo esc_html( $cat->description ); ?></p>
            <?php endif; ?>

            <div class="blog-cat-posts">
                <?php foreach ( $posts as $post ) : ?>
                    <article class="blog-cat-entry">
                        <a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="blog-entry-title">
                            <?php echo esc_html( get_the_title( $post ) ); ?>
                        </a>
                        <span class="blog-entry-leader" aria-hidden="true"></span>
                        <time class="blog-entry-date" datetime="<?php echo esc_attr( get_the_date( 'c', $post ) ); ?>">
                            <?php echo esc_html( get_the_date( 'Y', $post ) ); ?>
                        </time>
                    </article>
                <?php endforeach;
                wp_reset_postdata(); ?>
            </div>
        </section>

        <?php endforeach; ?>

    <?php else : ?>

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
                            <div class="entry-excerpt"><?php the_excerpt(); ?></div>
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
            <p><?php nota_e( 'No articles found' ); ?></p>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php get_footer(); ?>
