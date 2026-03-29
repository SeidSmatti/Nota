<?php get_header(); ?>

<?php
// Build a clean, bilingual archive heading without WordPress's default "Category: " prefixes
if ( is_category() ) {
    $archive_type  = nota_t( 'Category' );
    $archive_title = single_cat_title( '', false );
} elseif ( is_tag() ) {
    $archive_type  = nota_t( 'Tag' );
    $archive_title = single_tag_title( '', false );
} elseif ( is_author() ) {
    $archive_type  = nota_t( 'Author' );
    $archive_title = get_the_author();
} elseif ( is_year() ) {
    $archive_type  = nota_t( 'Archive' );
    $archive_title = get_the_date( 'Y' );
} elseif ( is_month() ) {
    $archive_type  = nota_t( 'Archive' );
    $archive_title = get_the_date( 'F Y' );
} else {
    $archive_type  = nota_t( 'Archive' );
    $archive_title = get_the_archive_title();
}
?>

<div class="container">

    <header class="archive-header">
        <?php if ( ! is_category() ) : ?>
            <p class="archive-type"><?php echo esc_html( $archive_type ); ?></p>
        <?php endif; ?>
        <h1 class="archive-title"><?php echo esc_html( $archive_title ); ?></h1>
        <?php if ( term_description() ) : ?>
            <div class="archive-description"><?php echo term_description(); ?></div>
        <?php endif; ?>
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
        <p><?php nota_e( 'No articles found' ); ?></p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
