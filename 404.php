<?php get_header(); ?>

<div class="container">

    <div class="error-404">
        <span class="error-code">404</span>
        <h1 class="error-title"><?php nota_e( 'Page not found' ); ?></h1>
        <p class="error-message"><?php nota_e( '404 message' ); ?></p>
        <?php get_search_form(); ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="error-home-link">
            <?php nota_e( 'Go back home' ); ?>
        </a>
    </div>

</div>

<?php get_footer(); ?>
