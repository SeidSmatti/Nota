<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if ( nota_feature( 'dark_mode' ) ) : ?>
    <script>
        (function(){
            var t = localStorage.getItem('theme');
            if (t) { document.documentElement.setAttribute('data-theme', t); return; }
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <?php endif; ?>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <header class="site-header">
        <div class="header-inner container">
            
            <div class="branding">
                <?php if ( is_front_page() ) : ?>
                <h1 class="site-title"><a href="<?php echo esc_url( home_url() ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
                <?php else : ?>
                <p class="site-title"><a href="<?php echo esc_url( home_url() ); ?>"><?php bloginfo( 'name' ); ?></a></p>
                <?php endif; ?>
                <p class="site-description"><?php bloginfo( 'description' ); ?></p>
            </div>
            
            <button id="menu-toggle" class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                <span class="hamburger-icon">☰</span> <span class="menu-label">Menu</span>
            </button>

            <nav id="site-navigation" class="main-navigation" aria-label="<?php nota_e( 'Main navigation' ); ?>">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'menu-1',
                    'menu_id'        => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ) );
                ?>
            </nav>

            <?php if ( nota_feature( 'search' ) ) : ?>
            <button id="search-toggle" class="search-toggle" aria-label="<?php echo esc_attr( nota_t( 'Search' ) ); ?>" aria-expanded="false">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </button>
            <?php endif; ?>

            <?php if ( nota_feature( 'dark_mode' ) ) : ?>
            <button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme">☾</button>
            <?php endif; ?>

            <?php if ( nota_feature( 'lang_switcher' ) && function_exists( 'pll_the_languages' ) ) :
                $langs        = pll_the_languages( array( 'raw' => 1 ) );
                $ui_lang      = nota_ui_lang();
                $current_slug = strtoupper( $ui_lang );
            ?>
            <div class="lang-switcher" aria-label="Language">
                <div class="lang-trigger" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <span class="lang-code"><?php echo esc_html( $current_slug ); ?></span>
                </div>
                <div class="lang-dropdown">
                    <?php foreach ( $langs as $slug => $lang ) :
                        $is_active = ( $slug === $ui_lang );
                        // Always append ?ui_lang so every click refreshes the cookie.
                        // If no translation exists for this post, stay on the current URL.
                        $base_url = ( $lang['no_translation'] && is_singular() )
                            ? get_permalink()
                            : $lang['url'];
                        $url = add_query_arg( 'ui_lang', $slug, $base_url );
                    ?>
                    <a href="<?php echo esc_url( $url ); ?>"
                       class="lang-option<?php echo $is_active ? ' lang-active' : ''; ?>"
                       hreflang="<?php echo esc_attr( $slug ); ?>"
                       <?php echo $is_active ? 'aria-current="true"' : ''; ?>>
                        <?php echo esc_html( $lang['name'] ); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php if ( nota_feature( 'search' ) ) : ?>
        <div class="header-search-wrap" id="header-search-wrap" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="search-wrap-icon">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="header-search-form">
                <input type="search" name="s" id="header-search-input"
                       placeholder="<?php echo esc_attr( nota_t( 'Search' ) ); ?>…"
                       autocomplete="off" />
            </form>
            <button id="search-close" class="search-close" aria-label="Close search">&times;</button>
        </div>
        <?php endif; ?>

        </div>
    </header>

    <main class="site-main">
