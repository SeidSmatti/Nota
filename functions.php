<?php

// ============================================================
// 1. THEME SETUP
// ============================================================
function nota_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    register_nav_menus( array(
        'menu-1' => 'Main Menu',
    ) );
}
add_action( 'after_setup_theme', 'nota_setup' );


// ============================================================
// 2. TRANSLATION HELPERS
// UI strings are managed in nota_translations() below — one
// array entry per non-English language. nota_ui_lang() tracks
// the user's chosen UI language independently of the content
// language, so French UI can wrap an untranslated English post.
// ============================================================

/**
 * All theme UI string translations.
 * English is the source language (the key); add translated values here.
 * To add a language, add a top-level key matching its Polylang slug.
 */
function nota_translations() {
    return array(
        'fr' => array(
            'Author'                  => 'Auteur',
            'Published on'            => 'Publié le',
            'Updated on'              => 'Mis à jour le',
            'Cite this article'       => 'Citer cet article',
            'Print / PDF'             => 'Imprimer / PDF',
            'References'              => 'Références',
            'Reading mode'            => 'Mode lecture',
            'Exit reading mode'       => 'Quitter le mode lecture',
            'Cite this article modal' => 'Citer cet article',
            'Cite selected passage'   => 'Citer le passage sélectionné',
            'Copy'                    => 'Copier',
            'Unknown Author'          => 'Auteur inconnu',
            'Accessed'                => 'Consulté le',
            'Copied!'                 => 'Copié !',
            'passage'                 => 'passage',
            'Read more'               => 'Lire la suite →',
            'Previous'                => '← Précédent',
            'Next'                    => 'Suivant →',
            'No articles found'       => 'Aucun article trouvé. Rendez-vous dans l\'admin pour en publier un !',
            'Main navigation'         => 'Navigation principale',
            'Footer credit'           => 'Tous droits réservés.',
            'Download EPUB'           => 'Télécharger l\'EPUB',
            'Category'                => 'Catégorie',
            'Tag'                     => 'Étiquette',
            'Archive'                 => 'Archives',
            'Search'                  => 'Recherche',
            'Results for'             => 'Résultats pour',
            'No results found'        => 'Aucun résultat',
            'Page not found'          => 'Page introuvable',
            '404 message'             => 'La page que vous cherchez n\'existe pas ou a été déplacée.',
            'Go back home'            => '← Retour à l\'accueil',
            'Team'                    => 'Équipe',
            'Principal Investigator'  => 'Responsable scientifique',
            'Period'                  => 'Période',
            'Funding'                 => 'Financement',
            'Institution'             => 'Institution',
            'Research Themes'         => 'Thèmes de recherche',
            'Publications'            => 'Publications',
            'Journal articles'        => 'Articles de revue',
            'Books'                   => 'Ouvrages',
            'Book chapters'           => 'Chapitres d\'ouvrage',
            'Conference papers'       => 'Communications',
            'Thesis'                  => 'Thèse',
            'Habilitation'            => 'Habilitation à diriger des recherches (HDR)',
            'Reports'                 => 'Rapports',
            'Other publications'      => 'Autres publications',
            'Abstract'                => 'Résumé',
            'HAL Publications'        => 'Publications HAL',
            'Other Publications'      => 'Autres publications',
            'Key Information'         => 'Informations clés',
            'Links'                   => 'Liens',
            'All posts'               => 'Tous les articles',
            'Blog'                    => 'Blog',
            'HAL API unavailable'     => 'Publications temporairement indisponibles.',
            'No publications found'   => 'Aucune publication trouvée.',
        ),
    );
}

/**
 * Returns the user's preferred UI language.
 * Priority: ?ui_lang param → Polylang current language → cookie → 'en'
 *
 * Polylang knows the page language from the URL, so it takes priority over a
 * potentially stale cookie (e.g. user switched to FR earlier but is now
 * browsing an EN page without ?ui_lang=).
 */
function nota_ui_lang() {
    if ( ! empty( $_GET['ui_lang'] ) ) return sanitize_key( $_GET['ui_lang'] );
    if ( function_exists( 'pll_current_language' ) ) {
        $pll = pll_current_language();
        if ( $pll ) return $pll;
    }
    if ( ! empty( $_COOKIE['nota_ui_lang'] ) ) return sanitize_key( $_COOKIE['nota_ui_lang'] );
    return 'en';
}

/**
 * Persist the UI language as a cookie early (before output).
 * Sources (in priority order): ?ui_lang= GET param, then Polylang URL language.
 * This keeps the cookie in sync so ambiguous pages (404, search) show the last
 * correct language rather than a stale value.
 */
add_action( 'init', function() {
    $lang = '';

    if ( ! empty( $_GET['ui_lang'] ) ) {
        $lang = sanitize_key( $_GET['ui_lang'] );
    } elseif ( function_exists( 'pll_current_language' ) ) {
        $pll = pll_current_language();
        if ( $pll ) $lang = $pll;
    }

    if ( ! $lang ) return;

    $allowed = array_merge( array( 'en' ), array_keys( nota_translations() ) );
    if ( ! in_array( $lang, $allowed, true ) ) return;

    // Only write the cookie when the value actually changes (avoids needless Set-Cookie headers)
    if ( ( $_COOKIE['nota_ui_lang'] ?? '' ) !== $lang ) {
        setcookie( 'nota_ui_lang', $lang, time() + 30 * DAY_IN_SECONDS, COOKIEPATH, '', is_ssl(), true );
        $_COOKIE['nota_ui_lang'] = $lang;
    }
}, 1 );

function nota_t( $string ) {
    $lang         = nota_ui_lang();
    $translations = nota_translations();
    if ( $lang !== 'en' && isset( $translations[ $lang ][ $string ] ) ) {
        return $translations[ $lang ][ $string ];
    }
    return $string; // English source or untranslated fallback
}
function nota_e( $string ) {
    echo nota_t( $string );
}


// ============================================================
// 3. SIDENOTE SYSTEM (via WordPress native Footnotes — WP 6.3+)
// Authors use the built-in Footnote button in the block editor
// toolbar. On the frontend the footnotes are silently converted
// to sidenotes; the default footnote list is suppressed.
// ============================================================

/**
 * Returns true if the post has footnotes (= sidenotes on the frontend).
 */
function nota_post_has_notes( $post_id = null ) {
    $id = $post_id ?: get_the_ID();
    return ! empty( get_post_meta( $id, 'footnotes', true ) );
}

/**
 * Intercept WordPress footnote output and convert to sidenotes.
 *
 * What this does:
 *   1. Reads footnote content from the _wp_footnotes post meta (JSON).
 *   2. Replaces every <sup data-fn="UUID"> marker with our .note-ref element.
 *   3. Strips the default <ol class="wp-block-footnotes"> list from content.
 *   4. Appends a .sidenotes-container with all note bodies at the end.
 */
add_filter( 'the_content', function( $content ) {
    if ( ! nota_feature( 'sidenotes' ) ) return $content;
    global $post;
    if ( ! $post ) return $content;

    $raw = get_post_meta( $post->ID, 'footnotes', true );
    if ( ! $raw ) return $content;

    $footnotes = json_decode( $raw, true );
    if ( ! is_array( $footnotes ) || empty( $footnotes ) ) return $content;

    // Map each footnote UUID to its sequential number and content
    $fn_map = array();
    foreach ( $footnotes as $i => $fn ) {
        if ( ! empty( $fn['id'] ) ) {
            $fn_map[ $fn['id'] ] = array(
                'number'  => $i + 1,
                'content' => isset( $fn['content'] ) ? $fn['content'] : '',
            );
        }
    }

    // Replace each inline <sup data-fn="UUID">…</sup> with our note-ref.
    // WP renders: <sup data-fn="UUID" class="fn"><a href="#UUID" id="UUID-link">N</a></sup>
    $content = preg_replace_callback(
        '/<sup[^>]+data-fn="([^"]+)"[^>]*>[^<]*<a[^>]*>[^<]*<\/a>[^<]*<\/sup>/',
        function( $m ) use ( $fn_map ) {
            $id = $m[1];
            if ( ! isset( $fn_map[ $id ] ) ) return $m[0];
            $n = $fn_map[ $id ]['number'];
            return '<sup class="note-ref" id="ref-' . $n . '" data-note="' . $n
                 . '" tabindex="0" role="button" aria-label="Note ' . $n . '">'
                 . $n . '</sup>';
        },
        $content
    );

    // Remove the default footnote list rendered by core/footnotes block
    $content = preg_replace(
        '/<ol\b[^>]*class="[^"]*wp-block-footnotes[^"]*"[^>]*>[\s\S]*?<\/ol>/U',
        '',
        $content
    );

    // Append sidenote elements (JS will position them vertically)
    $html = '<div class="sidenotes-container">';
    foreach ( $footnotes as $i => $fn ) {
        $n    = $i + 1;
        $body = isset( $fn['content'] ) ? wp_kses_post( $fn['content'] ) : '';
        $html .= '<aside class="sidenote" id="note-' . $n . '" data-note="' . $n . '" role="note">'
               . '<span class="sidenote-number">' . $n . '</span>'
               . $body
               . '</aside>';
    }
    $html .= '</div>';

    return $content . $html;
}, 15 );


// ============================================================
// 4. FONTS & SCRIPTS
// ============================================================
/**
 * Build the Google Fonts URL based on the selected Customizer fonts.
 * Returns empty string if system fonts are selected (no Google Fonts needed).
 */
function nota_google_fonts_url() {
    $serif = get_theme_mod( 'nota_font_serif', "'Libre Caslon Text', Georgia, serif" );
    $sans  = get_theme_mod( 'nota_font_sans', "'Cabin', sans-serif" );

    // Map Customizer values → Google Fonts family strings
    $gf_map = array(
        "'Libre Caslon Text', Georgia, serif" => 'Libre+Caslon+Text:ital,wght@0,400;0,700;1,400',
        "'Lora', Georgia, serif"              => 'Lora:ital,wght@0,400;0,700;1,400',
        "'Source Serif 4', Georgia, serif"     => 'Source+Serif+4:ital,wght@0,400;0,700;1,400',
        "'Merriweather', Georgia, serif"      => 'Merriweather:ital,wght@0,300;0,400;0,700;1,400',
        "'EB Garamond', Garamond, serif"      => 'EB+Garamond:ital,wght@0,400;0,700;1,400',
        "'Cabin', sans-serif"                 => 'Cabin:wght@400;500;600',
        "'Inter', sans-serif"                 => 'Inter:wght@400;500;600',
        "'Source Sans 3', sans-serif"          => 'Source+Sans+3:wght@400;500;600',
        "'Work Sans', sans-serif"             => 'Work+Sans:wght@400;500;600',
        "'Nunito Sans', sans-serif"           => 'Nunito+Sans:wght@400;500;600',
    );

    $families = array();
    if ( isset( $gf_map[ $serif ] ) ) $families[] = $gf_map[ $serif ];
    if ( isset( $gf_map[ $sans ] ) )  $families[] = $gf_map[ $sans ];

    if ( empty( $families ) ) return '';

    return 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $families ) . '&display=swap';
}

function nota_preconnect_fonts() {
    if ( ! nota_google_fonts_url() ) return;
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'nota_preconnect_fonts', 1 );

function nota_scripts() {
    $fonts_url = nota_google_fonts_url();
    $deps = array();

    if ( $fonts_url ) {
        wp_enqueue_style( 'nota-fonts', $fonts_url, array(), null );
        $deps[] = 'nota-fonts';
    }

    wp_enqueue_style( 'nota-style', get_stylesheet_uri(), $deps );

    wp_enqueue_script( 'nota-scripts', get_template_directory_uri() . '/assets/js/scripts.js', array(), '1.1', true );

    wp_localize_script( 'nota-scripts', 'nota', array(
        'siteName' => get_bloginfo( 'name' ),
        'lang'     => nota_ui_lang(),
        'features' => array(
            'citations'   => nota_feature( 'citations' ),
            'epub'        => nota_feature( 'epub' ),
            'readingMode' => nota_feature( 'reading_mode' ),
            'sidenotes'   => nota_feature( 'sidenotes' ),
            'darkMode'    => nota_feature( 'dark_mode' ),
        ),
        'i18n'     => array(
            'focusEnter'    => nota_t( 'Reading mode' ),
            'focusExit'     => nota_t( 'Exit reading mode' ),
            'unknownAuthor' => nota_t( 'Unknown Author' ),
            'accessed'      => nota_t( 'Accessed' ),
            'copied'        => nota_t( 'Copied!' ),
            'passage'       => nota_t( 'passage' ),
            'citePassage'   => nota_t( 'Cite selected passage' ),
        ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'nota_scripts' );


// ============================================================
// 5. EPUB EXPORT
// Generates a valid EPUB 3 file using only PHP core (no ZipArchive).
// The ZIP binary is assembled with a minimal pure-PHP writer.
// Served at ?epub=1 on any singular post URL.
// ============================================================

add_action( 'template_redirect', function() {
    if ( ! nota_feature( 'epub' ) ) return;
    if ( ! is_singular() || empty( $_GET['epub'] ) ) return;
    $post = get_queried_object();
    if ( ! $post instanceof WP_Post ) return;
    nota_stream_epub( $post );
    exit;
} );

/**
 * Build and stream an EPUB 3 file for the given post.
 */
function nota_stream_epub( WP_Post $post ) {

    // --- Metadata ---
    $title    = get_the_title( $post );
    $lang     = function_exists( 'pll_get_post_language' ) ? ( pll_get_post_language( $post->ID ) ?: 'en' ) : 'en';
    $raw_auth = get_field( 'custom_author', $post->ID );
    $author   = $raw_auth ?: get_the_author_meta( 'display_name', $post->post_author );
    $date_str = get_the_date( 'Y-m-d', $post );
    $modified = get_the_modified_date( 'Y-m-d\TH:i:sP', $post );
    $site     = get_bloginfo( 'name' );
    $url      = get_permalink( $post );
    $uuid     = nota_epub_uuid();

    // --- Process content through the same filter pipeline as the frontend ---
    $GLOBALS['post'] = $post;
    setup_postdata( $post );
    $content = apply_filters( 'the_content', $post->post_content );
    wp_reset_postdata();

    // Extract sidenotes before stripping the container
    $sidenotes = array();
    if ( preg_match_all(
        '/<aside[^>]+class="sidenote"[^>]+data-note="(\d+)"[^>]*>(.*?)<\/aside>/s',
        $content, $matches, PREG_SET_ORDER
    ) ) {
        foreach ( $matches as $m ) {
            $body = preg_replace( '/<span[^>]+class="sidenote-number"[^>]*>\d+<\/span>/', '', $m[2] );
            $sidenotes[ (int) $m[1] ] = trim( $body );
        }
    }

    // Strip the sidenotes container div (we'll rebuild as EPUB footnotes)
    $content = preg_replace( '/<div[^>]+class="sidenotes-container"[^>]*>.*?<\/div>/s', '', $content );

    // Strip the translation notice (not relevant in a downloaded file)
    $content = preg_replace( '/<p[^>]+class="lang-notice"[^>]*>.*?<\/p>/s', '', $content );

    // Convert note-ref sups → EPUB 3 noteref links
    $content = preg_replace_callback(
        '/<sup[^>]+class="note-ref"[^>]+data-note="(\d+)"[^>]*>\d+<\/sup>/',
        function( $m ) {
            $n = (int) $m[1];
            return '<sup><a href="#epub-note-' . $n . '" epub:type="noteref">' . $n . '</a></sup>';
        },
        $content
    );

    // XHTML requires void elements to be self-closing
    $content = preg_replace( '/(<(?:br|hr|img|input|col|wbr)\b[^>]*?)(?<!\/)>/', '$1/>', $content );

    // --- Build EPUB footnotes section ---
    $footnotes_html = '';
    if ( ! empty( $sidenotes ) ) {
        $footnotes_html = '<section class="endnotes" epub:type="footnotes">' . "\n";
        foreach ( $sidenotes as $n => $body ) {
            // Ensure body void elements are also self-closed
            $body = preg_replace( '/(<(?:br|hr|img|input|col|wbr)\b[^>]*?)(?<!\/)>/', '$1/>', $body );
            $footnotes_html .= '<aside id="epub-note-' . $n . '" epub:type="footnote">'
                . '<p><strong>' . $n . '.</strong> ' . trim( $body ) . '</p>'
                . '</aside>' . "\n";
        }
        $footnotes_html .= '</section>' . "\n";
    }

    // --- Escape metadata for XML output ---
    $x_title    = esc_html( $title );
    $x_author   = esc_html( $author );
    $x_site     = esc_html( $site );
    $x_url      = esc_url( $url );
    $x_lang     = esc_attr( $lang );
    $x_pub      = esc_html( get_the_date( '', $post ) );
    $x_updated  = esc_html( get_the_modified_date( '', $post ) );
    $x_download = esc_html( date_i18n( get_option( 'date_format' ) ) );

    // --- Build references from ACF fields (mirrors single.php sidebar-biblio) ---
    $references_html = '';
    $ref_items = '';
    for ( $i = 1; $i <= 10; $i++ ) {
        $ref = get_field( 'ref_' . $i, $post->ID );
        if ( empty( $ref ) ) continue;
        $mode = isset( $ref['mode'] ) ? $ref['mode'] : 'auto';
        if ( $mode === 'auto'   && empty( $ref['titre'] ) )          continue;
        if ( $mode === 'manuel' && empty( $ref['citation_libre'] ) ) continue;

        $ref_items .= '<li>';
        if ( $mode === 'manuel' ) {
            $body = wp_kses_post( $ref['citation_libre'] );
            // Self-close void elements for XHTML
            $body = preg_replace( '/(<(?:br|hr|img|input|col|wbr)\b[^>]*?)(?<!\/)>/', '$1/>', $body );
            $ref_items .= $body;
        } else {
            $ref_items .= '<span class="bib-author">' . esc_html( $ref['auteur'] ) . '</span>'
                . ' (' . esc_html( $ref['annee'] ) . '). ';
            $titre = esc_html( $ref['titre'] );
            if ( ! empty( $ref['lien'] ) ) {
                $ref_items .= '<a href="' . esc_url( $ref['lien'] ) . '"><em>' . $titre . '</em></a>.';
            } else {
                $ref_items .= '<em>' . $titre . '</em>.';
            }
        }
        $ref_items .= '</li>' . "\n";
    }
    if ( $ref_items ) {
        $references_html = '<section class="references">'
            . '<h2>' . esc_html( nota_t( 'References' ) ) . '</h2>'
            . '<ol>' . "\n" . $ref_items . '</ol>'
            . '</section>' . "\n";
    }

    // --- article.xhtml ---
    $article = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<html xmlns="http://www.w3.org/1999/xhtml"'
        . ' xmlns:epub="http://www.idpf.org/2007/ops"'
        . ' xml:lang="' . $x_lang . '">' . "\n"
        . '<head><meta charset="UTF-8"/>'
        . '<title>' . $x_title . '</title>'
        . '<link rel="stylesheet" href="style.css"/></head>' . "\n"
        . '<body><article>' . "\n"
        . '<h1>' . $x_title . '</h1>' . "\n"
        . '<section class="meta">'
        . '<p><span class="meta-label">' . esc_html( nota_t( 'Author' ) ) . '</span> ' . $x_author . '</p>'
        . '<p><span class="meta-label">' . esc_html( nota_t( 'Published on' ) ) . '</span> ' . $x_pub . '</p>'
        . '<p><span class="meta-label">' . esc_html( nota_t( 'Updated on' ) ) . '</span> ' . $x_updated . '</p>'
        . '<p><span class="meta-label">' . esc_html( nota_t( 'Accessed' ) ) . '</span> ' . $x_download . '</p>'
        . '<p><a href="' . $x_url . '">' . $x_site . '</a></p>'
        . '</section>' . "\n"
        . '<section class="content">' . "\n" . $content . "\n" . '</section>' . "\n"
        . $footnotes_html
        . $references_html
        . '</article></body>' . "\n"
        . '</html>';

    // --- META-INF/container.xml ---
    $container = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<container version="1.0"'
        . ' xmlns="urn:oasis:names:tc:opendocument:xmlns:container">' . "\n"
        . '<rootfiles>'
        . '<rootfile full-path="OEBPS/content.opf"'
        . ' media-type="application/oebps-package+xml"/>'
        . '</rootfiles>' . "\n"
        . '</container>';

    // --- OEBPS/content.opf ---
    $opf = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<package xmlns="http://www.idpf.org/2007/opf" version="3.0"'
        . ' unique-identifier="uid" xml:lang="' . $x_lang . '">' . "\n"
        . '<metadata xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n"
        . '<dc:identifier id="uid">urn:uuid:' . $uuid . '</dc:identifier>' . "\n"
        . '<dc:title>' . $x_title . '</dc:title>' . "\n"
        . '<dc:creator>' . $x_author . '</dc:creator>' . "\n"
        . '<dc:language>' . $x_lang . '</dc:language>' . "\n"
        . '<dc:date>' . esc_html( $date_str ) . '</dc:date>' . "\n"
        . '<dc:publisher>' . $x_site . '</dc:publisher>' . "\n"
        . '<dc:source>' . $x_url . '</dc:source>' . "\n"
        . '<meta property="dcterms:modified">' . esc_html( $modified ) . '</meta>' . "\n"
        . '<meta property="dcterms:dateSubmitted">' . esc_html( date( 'Y-m-d' ) ) . '</meta>' . "\n"
        . '</metadata>' . "\n"
        . '<manifest>' . "\n"
        . '<item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/>' . "\n"
        . '<item id="article" href="article.xhtml" media-type="application/xhtml+xml"/>' . "\n"
        . '<item id="css" href="style.css" media-type="text/css"/>' . "\n"
        . '</manifest>' . "\n"
        . '<spine><itemref idref="article"/></spine>' . "\n"
        . '</package>';

    // --- OEBPS/nav.xhtml ---
    $nav = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<html xmlns="http://www.w3.org/1999/xhtml"'
        . ' xmlns:epub="http://www.idpf.org/2007/ops" xml:lang="' . $x_lang . '">' . "\n"
        . '<head><meta charset="UTF-8"/><title>' . $x_title . '</title></head>' . "\n"
        . '<body><nav epub:type="toc" id="toc">'
        . '<ol><li><a href="article.xhtml">' . $x_title . '</a></li></ol>'
        . '</nav></body>' . "\n"
        . '</html>';

    // --- OEBPS/style.css (minimal print-ready EPUB stylesheet) ---
    $css = 'body{font-family:Georgia,serif;font-size:1em;line-height:1.65;margin:1em 1.5em;}' . "\n"
        . 'h1{font-size:1.6em;line-height:1.2;margin:0 0 .4em;}' . "\n"
        . 'h2{font-size:1.2em;margin:1.4em 0 .4em;}' . "\n"
        . 'h3{font-size:1.05em;margin:1.2em 0 .3em;}' . "\n"
        . 'p{margin:0 0 .8em;}' . "\n"
        . 'blockquote{margin:1em 2em;font-style:italic;}' . "\n"
        . 'section.meta{font-size:.82em;color:#555;border-bottom:1px solid #ccc;' . "\n"
        . '  margin-bottom:1.5em;padding-bottom:.8em;}' . "\n"
        . 'section.meta p{margin:.15em 0;}' . "\n"
        . 'sup a{font-size:.7em;color:#555;text-decoration:none;}' . "\n"
        . 'section.endnotes{margin-top:2em;border-top:1px solid #ccc;padding-top:1em;}' . "\n"
        . 'section.endnotes aside{font-size:.85em;margin:.5em 0;line-height:1.55;}' . "\n"
        . 'section.references{margin-top:2em;border-top:1px solid #ccc;padding-top:1em;}' . "\n"
        . 'section.references h2{font-size:.75em;text-transform:uppercase;letter-spacing:.05em;color:#888;margin:0 0 .8em;}' . "\n"
        . 'section.references ol{padding-left:1.4em;font-size:.88em;line-height:1.6;}' . "\n"
        . 'section.references li{margin-bottom:.5em;}' . "\n"
        . '.meta-label{font-size:.75em;text-transform:uppercase;letter-spacing:.04em;color:#888;margin-right:.3em;}' . "\n"
        . 'a{color:#333;}' . "\n"
        . 'img{max-width:100%;height:auto;}' . "\n";

    // --- Assemble ZIP (EPUB 3 = ZIP with specific file order) ---
    $zip_data = nota_epub_zip( array(
        'mimetype'               => array( 'data' => 'application/epub+zip', 'compress' => false ),
        'META-INF/container.xml' => array( 'data' => $container,            'compress' => true  ),
        'OEBPS/content.opf'      => array( 'data' => $opf,                  'compress' => true  ),
        'OEBPS/nav.xhtml'        => array( 'data' => $nav,                  'compress' => true  ),
        'OEBPS/article.xhtml'    => array( 'data' => $article,              'compress' => true  ),
        'OEBPS/style.css'        => array( 'data' => $css,                  'compress' => true  ),
    ) );

    // --- Stream to browser ---
    $filename = sanitize_title( $title ) . '.epub';
    header( 'Content-Type: application/epub+zip' );
    header( 'Content-Disposition: attachment; filename="' . rawurlencode( $filename ) . '"' );
    header( 'Content-Length: ' . strlen( $zip_data ) );
    header( 'Cache-Control: no-store' );
    echo $zip_data; // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Generate a version-4 UUID (random).
 */
function nota_epub_uuid() {
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

/**
 * Minimal pure-PHP ZIP builder.
 *
 * Builds a ZIP binary string from an ordered array of files.
 * The first entry should always be stored (compress => false) for EPUB mimetype compliance.
 *
 * @param array $files  Ordered map of zip path => ['data' => string, 'compress' => bool]
 * @return string       Raw ZIP binary
 */
function nota_epub_zip( array $files ) {
    $local   = '';  // local file headers + data
    $central = '';  // central directory entries
    $offset  = 0;
    $count   = 0;

    foreach ( $files as $name => $file ) {
        $data      = $file['data'];
        $do_deflate = ! empty( $file['compress'] );

        $crc        = crc32( $data );
        $size_orig  = strlen( $data );

        if ( $do_deflate ) {
            $compressed = gzdeflate( $data, 6 );
            $method     = 8; // DEFLATE
        } else {
            $compressed = $data;
            $method     = 0; // STORE
        }
        $size_comp = strlen( $compressed );
        $name_len  = strlen( $name );

        // Local file header (30 bytes fixed) + filename + data
        $lfh  = pack( 'VvvvvvVVVvv',
            0x04034b50, // signature
            20,         // version needed (2.0)
            0,          // general purpose flags
            $method,    // compression method
            0,          // last mod time
            0,          // last mod date
            $crc,
            $size_comp,
            $size_orig,
            $name_len,
            0           // extra field length
        );
        $local  .= $lfh . $name . $compressed;

        // Central directory file header (46 bytes fixed) + filename
        $cfh  = pack( 'VvvvvvvVVVvvvvvVV',
            0x02014b50, // signature
            20,         // version made by
            20,         // version needed
            0,          // flags
            $method,
            0,          // mod time
            0,          // mod date
            $crc,
            $size_comp,
            $size_orig,
            $name_len,
            0,          // extra field length
            0,          // file comment length
            0,          // disk number start
            0,          // internal attributes
            0,          // external attributes
            $offset     // offset of local header
        );
        $central .= $cfh . $name;

        $offset += 30 + $name_len + $size_comp;
        $count++;
    }

    $cd_size   = strlen( $central );
    $cd_offset = $offset;

    // End of central directory record (22 bytes)
    $eocd = pack( 'VvvvvVVv',
        0x06054b50, // signature
        0,          // disk number
        0,          // disk with central dir start
        $count,     // entries on this disk
        $count,     // total entries
        $cd_size,
        $cd_offset,
        0           // comment length
    );

    return $local . $central . $eocd;
}


// ============================================================
// 6. PROJECT PAGE — ACF FIELD REGISTRATION
// Fields are registered in code so no JSON import is needed.
// Visible only on pages using the "Project Page" template.
// ============================================================

add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    $location = array( array( array(
        'param'    => 'page_template',
        'operator' => '==',
        'value'    => 'page-project.php',
    ) ) );

    // --- Project information ---
    acf_add_local_field_group( array(
        'key'      => 'group_nota_project',
        'title'    => 'Project Information',
        'fields'   => array(
            array( 'key' => 'field_project_tagline',    'label' => 'Tagline',                  'name' => 'project_tagline',    'type' => 'text' ),
            array( 'key' => 'field_project_pi',         'label' => 'Principal Investigator',   'name' => 'project_pi',         'type' => 'text' ),
            array( 'key' => 'field_project_period',     'label' => 'Period (e.g. 2024–2029)',  'name' => 'project_period',     'type' => 'text' ),
            array( 'key' => 'field_project_funding',    'label' => 'Funding body',             'name' => 'project_funding',    'type' => 'text' ),
            array( 'key' => 'field_project_institution','label' => 'Institution',               'name' => 'project_institution','type' => 'text' ),
            // Links repeater removed — handled by native WP meta box (section 8)
        ),
        'location'   => $location,
        'menu_order' => 10,
    ) );

    // Team members and research themes are handled by native WP meta boxes (see section 8).
    // Repeater fields require ACF Pro; the native boxes below work with free ACF.

    // --- HAL configuration ---
    acf_add_local_field_group( array(
        'key'    => 'group_nota_hal',
        'title'  => 'HAL Publications',
        'fields' => array(
            array(
                'key'          => 'field_hal_author_id',
                'label'        => 'HAL Author ID',
                'name'         => 'hal_author_id',
                'type'         => 'text',
                'instructions' => 'HAL author slug, e.g. james-costa. Used when no collection code is set.',
            ),
            array(
                'key'          => 'field_hal_collection',
                'label'        => 'HAL Collection Code',
                'name'         => 'hal_collection',
                'type'         => 'text',
                'instructions' => 'Optional. If set, filters by collection instead of author ID.',
            ),
        ),
        'location'   => $location,
        'menu_order' => 40,
    ) );
} );


// ============================================================
// 7. PROJECT PAGE — HAL API
// Fetches publications from the HAL open-access repository and
// caches the result as a WordPress transient (12-hour TTL).
// ============================================================

/**
 * Fetch and group publications for a given HAL author or collection.
 *
 * @param  string      $author_id  HAL author slug (authIdHal_s value).
 * @param  string      $collection Optional HAL collection code (collCode_s).
 * @return array|null  Array keyed by docType_s, or null on API failure.
 */
function nota_hal_publications( $author_id, $collection = '' ) {
    $cache_key = 'nota_hal_' . md5( $author_id . $collection );
    if ( isset( $_GET['flush_hal'] ) ) delete_transient( $cache_key );
    $cached    = get_transient( $cache_key );
    if ( $cached !== false ) return $cached;

    // api.hal.science rejects fq; api.archives-ouvertes.fr is reliable — author filter goes in q=
    $q   = $collection ? 'collCode_s:' . $collection : 'authIdHal_s:' . $author_id;
    // No sort param — producedYear_i is not a valid HAL sort field; sort by year in PHP instead
    $fl  = 'title_s,authFullName_s,producedYear_i,producedDateY_i,docType_s,journalTitle_s,publisher_s,bookTitle_s,doiId_s,uri_s,halId_s,page_s,volume_s,issue_s,abstract_s';
    $url = 'https://api.archives-ouvertes.fr/search/?q=' . $q
         . '&fl=' . $fl . '&wt=json&rows=500';

    $response = wp_remote_get( $url, array( 'timeout' => 15 ) );

    if ( isset( $_GET['hal_debug'] ) && current_user_can( 'manage_options' ) ) {
        $code = is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_response_code( $response );
        $raw  = is_wp_error( $response ) ? '' : substr( wp_remote_retrieve_body( $response ), 0, 3000 );
        wp_die( 'HTTP: ' . esc_html( $code ) . '<br><code>' . esc_html( $url ) . '</code><pre>' . esc_html( $raw ) . '</pre>' );
    }

    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return null;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    $docs  = isset( $body['response']['docs'] ) ? $body['response']['docs'] : array();

    // Group by document type; normalise NOTICE → COUV
    $grouped = array();
    foreach ( $docs as $doc ) {
        $type = isset( $doc['docType_s'] ) ? $doc['docType_s'] : 'OTHER';
        if ( 'NOTICE' === $type ) $type = 'COUV';
        $grouped[ $type ][] = $doc;
    }

    // Sort each group by year descending
    foreach ( $grouped as &$group ) {
        usort( $group, function( $a, $b ) {
            $ya = $a['producedYear_i'] ?? $a['producedDateY_i'] ?? 0;
            $yb = $b['producedYear_i'] ?? $b['producedDateY_i'] ?? 0;
            return $yb - $ya;
        } );
    }
    unset( $group );

    set_transient( $cache_key, $grouped, 12 * HOUR_IN_SECONDS );
    return $grouped;
}

/**
 * Build the HTML for a single HAL publication entry.
 * Returns a citation paragraph + link badges + expandable abstract.
 *
 * @param  array  $doc   Single document array from the HAL API.
 * @param  string $type  Normalised docType_s value.
 * @return string        Safe HTML string.
 */
function nota_hal_entry_html( array $doc, $type ) {

    // Manual entry: plain text citation, optional DOI/URL links
    if ( ! empty( $doc['_manual'] ) ) {
        $year  = esc_html( $doc['producedYear_i'] ?? '' );
        $text  = esc_html( is_array( $doc['title_s'] ) ? $doc['title_s'][0] : ( $doc['title_s'] ?? '' ) );
        $links = '';
        if ( ! empty( $doc['doiId_s'] ) ) {
            $links .= '<a href="https://doi.org/' . esc_attr( $doc['doiId_s'] ) . '" target="_blank" rel="noopener" class="hal-badge hal-badge-doi">DOI</a>';
        }
        if ( ! empty( $doc['uri_s'] ) ) {
            $links .= '<a href="' . esc_url( $doc['uri_s'] ) . '" target="_blank" rel="noopener" class="hal-badge hal-badge-hal">↗</a>';
        }
        $footer = $links ? '<div class="hal-entry-footer"><span class="hal-badges">' . $links . '</span></div>' : '';
        return '<span class="hal-entry-year">' . ( $year ?: '—' ) . '</span>'
             . '<div class="hal-entry-body"><p class="hal-citation">' . $text . '</p>' . $footer . '</div>';
    }

    // --- Authors: "Last, F." format ---
    $authors_html = '';
    if ( ! empty( $doc['authFullName_s'] ) ) {
        $parts = array_map( function( $full ) {
            $words = explode( ' ', trim( $full ) );
            $last  = array_pop( $words );
            $first = implode( ' ', $words );
            $init  = $first ? mb_strtoupper( mb_substr( $first, 0, 1 ) ) . '.' : '';
            return esc_html( $init ? $last . ',&#160;' . $init : $last );
        }, $doc['authFullName_s'] );
        $authors_html = implode( ', ', $parts );
    }

    $raw_year = $doc['producedYear_i'] ?? $doc['producedDateY_i'] ?? '';
    $year     = $raw_year ? esc_html( $raw_year ) : '';
    $raw_title = ! empty( $doc['title_s'] ) ? $doc['title_s'] : '';
    $title = $raw_title ? esc_html( is_array( $raw_title ) ? $raw_title[0] : $raw_title ) : '';

    // --- Citation string ---
    $cite = '';
    if ( $authors_html ) $cite .= $authors_html;
    if ( $year )         $cite .= ' (' . $year . ').';

    if ( in_array( $type, array( 'OUV', 'THESE', 'HDR' ), true ) ) {
        $cite .= ' <em>' . $title . '</em>.';
    } else {
        $cite .= ' &#171;&#160;' . $title . '&#160;&#187;.';
    }

    if ( 'ART' === $type ) {
        if ( ! empty( $doc['journalTitle_s'] ) ) {
            $cite .= ' <em>' . esc_html( $doc['journalTitle_s'] ) . '</em>';
        }
        $vol   = ! empty( $doc['volume_s'] ) ? esc_html( is_array( $doc['volume_s'] ) ? $doc['volume_s'][0] : $doc['volume_s'] ) : '';
        $issue = ! empty( $doc['issue_s'] )  ? esc_html( is_array( $doc['issue_s'] )  ? $doc['issue_s'][0]  : $doc['issue_s']  ) : '';
        if ( $vol )   $cite .= ',&#160;' . $vol;
        if ( $issue ) $cite .= '(' . $issue . ')';
        if ( ! empty( $doc['page_s'] ) ) $cite .= ', p.&#160;' . esc_html( $doc['page_s'] );
        $cite .= '.';
    } elseif ( 'COUV' === $type ) {
        if ( ! empty( $doc['bookTitle_s'] ) ) $cite .= ' In <em>' . esc_html( $doc['bookTitle_s'] ) . '</em>';
        if ( ! empty( $doc['page_s'] ) )      $cite .= ' (p.&#160;' . esc_html( $doc['page_s'] ) . ')';
        if ( ! empty( $doc['publisher_s'] ) ) {
            $pub   = is_array( $doc['publisher_s'] ) ? $doc['publisher_s'][0] : $doc['publisher_s'];
            $cite .= '. ' . esc_html( $pub );
        }
        $cite .= '.';
    } elseif ( 'OUV' === $type ) {
        if ( ! empty( $doc['publisher_s'] ) ) {
            $pub   = is_array( $doc['publisher_s'] ) ? $doc['publisher_s'][0] : $doc['publisher_s'];
            $cite .= ' ' . esc_html( $pub ) . '.';
        }
    } elseif ( 'COMM' === $type ) {
        if ( ! empty( $doc['bookTitle_s'] ) ) $cite .= ' <em>' . esc_html( $doc['bookTitle_s'] ) . '</em>.';
    }

    // --- Link badges ---
    $links = '';
    if ( ! empty( $doc['doiId_s'] ) ) {
        $links .= '<a href="https://doi.org/' . esc_attr( $doc['doiId_s'] ) . '" target="_blank" rel="noopener" class="hal-badge hal-badge-doi">DOI</a>';
    }
    if ( ! empty( $doc['uri_s'] ) ) {
        $links .= '<a href="' . esc_url( $doc['uri_s'] ) . '" target="_blank" rel="noopener" class="hal-badge hal-badge-hal">HAL</a>';
    }

    // --- Abstract (expandable) ---
    $abstract_html = '';
    if ( ! empty( $doc['abstract_s'] ) ) {
        $raw      = $doc['abstract_s'];
        $abstract = esc_html( is_array( $raw ) ? $raw[0] : $raw );
        $abstract_html = '<details class="hal-abstract">'
            . '<summary>' . esc_html( nota_t( 'Abstract' ) ) . '</summary>'
            . '<p>' . $abstract . '</p>'
            . '</details>';
    }

    $footer = '';
    if ( $links || $abstract_html ) {
        $footer = '<div class="hal-entry-footer">'
                . ( $links ? '<span class="hal-badges">' . $links . '</span>' : '' )
                . $abstract_html
                . '</div>';
    }

    return '<span class="hal-entry-year">' . ( $year ?: '—' ) . '</span>'
         . '<div class="hal-entry-body">'
         . '<p class="hal-citation">' . $cite . '</p>'
         . $footer
         . '</div>';
}


// ============================================================
// 8. PROJECT PAGE — NATIVE META BOXES
// Replaces ACF repeaters (Pro-only) with native WP meta boxes.
// Handles: project links, team members, research themes,
// and manually-entered publications.
// ============================================================

add_action( 'add_meta_boxes_page', function( $post ) {
    if ( get_post_meta( $post->ID, '_wp_page_template', true ) !== 'page-project.php' ) return;

    add_meta_box( 'nota_links',  'Project Links',          'nota_mb_links',  'page', 'normal', 'default' );
    add_meta_box( 'nota_team',   'Team Members',           'nota_mb_team',   'page', 'normal', 'default' );
    add_meta_box( 'nota_themes', 'Research Themes',        'nota_mb_themes', 'page', 'normal', 'default' );
    add_meta_box( 'nota_pubs',   'Manual Publications',    'nota_mb_pubs',   'page', 'normal', 'default' );
} );

add_action( 'save_post_page', function( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( ! isset( $_POST['nota_project_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['nota_project_nonce'], 'nota_project_meta_' . $post_id ) ) return;

    // Links
    $links = array();
    foreach ( (array) ( $_POST['ll_links'] ?? array() ) as $row ) {
        $label = sanitize_text_field( $row['label'] ?? '' );
        $url   = esc_url_raw( $row['url'] ?? '' );
        if ( $label || $url ) $links[] = compact( 'label', 'url' );
    }
    update_post_meta( $post_id, '_ll_links', $links );

    // Team
    $team = array();
    foreach ( (array) ( $_POST['ll_team'] ?? array() ) as $row ) {
        $name = sanitize_text_field( $row['name'] ?? '' );
        if ( ! $name ) continue;
        $team[] = array(
            'name'        => $name,
            'role'        => sanitize_text_field( $row['role'] ?? '' ),
            'institution' => sanitize_text_field( $row['institution'] ?? '' ),
            'bio'         => sanitize_textarea_field( $row['bio'] ?? '' ),
            'links'       => array_values( array_filter(
                array_map( function( $l ) {
                    $label = sanitize_text_field( $l['label'] ?? '' );
                    $url   = esc_url_raw( $l['url'] ?? '' );
                    return ( $label && $url ) ? compact( 'label', 'url' ) : null;
                }, (array) ( $row['links'] ?? array() ) )
            ) ),
        );
    }
    update_post_meta( $post_id, '_ll_team', $team );

    // Themes
    $themes = array();
    foreach ( (array) ( $_POST['ll_themes'] ?? array() ) as $row ) {
        $title = sanitize_text_field( $row['title'] ?? '' );
        if ( ! $title ) continue;
        $themes[] = array(
            'title'       => $title,
            'description' => sanitize_textarea_field( $row['desc'] ?? '' ),
        );
    }
    update_post_meta( $post_id, '_ll_themes', $themes );

    // Manual publications
    $pubs = array();
    foreach ( (array) ( $_POST['ll_pubs'] ?? array() ) as $row ) {
        $text = sanitize_textarea_field( $row['text'] ?? '' );
        if ( ! $text ) continue;
        $pubs[] = array(
            'text' => $text,
            'year' => sanitize_text_field( $row['year'] ?? '' ),
            'type' => sanitize_text_field( $row['type'] ?? 'OTHER' ),
            'doi'  => sanitize_text_field( $row['doi'] ?? '' ),
            'url'  => esc_url_raw( $row['url'] ?? '' ),
        );
    }
    update_post_meta( $post_id, '_ll_pubs', $pubs );
} );

/** Shared nonce + repeater JS (output once per page load) */
function nota_mb_nonce( $post ) {
    static $done = false;
    if ( ! $done ) {
        wp_nonce_field( 'nota_project_meta_' . $post->ID, 'nota_project_nonce' );
        echo '<style>
.ll-repeater{border:1px solid #ddd;border-radius:3px;margin-top:6px}
.ll-row{display:grid;gap:8px;padding:10px 12px;border-bottom:1px solid #eee;position:relative}
.ll-row:last-child{border-bottom:none}
.ll-row label{display:flex;flex-direction:column;font-size:12px;color:#555;gap:3px}
.ll-row input,.ll-row textarea,.ll-row select{font-size:13px;padding:4px 6px;border:1px solid #ccc;border-radius:2px;width:100%;box-sizing:border-box}
.ll-row textarea{resize:vertical;min-height:52px}
.ll-remove{position:absolute;top:8px;right:10px;background:none;border:none;color:#a00;cursor:pointer;font-size:18px;line-height:1;padding:0}
.ll-add{margin-top:8px;cursor:pointer}
.ll-row-cols-2{grid-template-columns:1fr 1fr}
.ll-row-cols-3{grid-template-columns:1fr 1fr 1fr}
</style>
<script>
function llAddRow(btn){
  var wrap=btn.previousElementSibling;
  var tpl=wrap.querySelector(".ll-tpl");
  var idx=wrap.querySelectorAll(".ll-row:not(.ll-tpl)").length;
  var clone=tpl.cloneNode(true);
  clone.classList.remove("ll-tpl");
  clone.style.display="";
  clone.querySelectorAll("[name]").forEach(function(el){
    el.name=el.name.replace(/\[__\]/g,"["+idx+"]");
    el.value="";
  });
  wrap.appendChild(clone);
}
function llRemove(btn){btn.closest(".ll-row").remove();}
</script>';
        $done = true;
    }
}

/** Render a repeater row template (hidden) */
function nota_mb_tpl( $prefix, $cols_class, $fields ) {
    // $fields: array of [name, label, type, options?]
    echo '<div class="ll-row ll-tpl ' . esc_attr( $cols_class ) . '" style="display:none">';
    echo '<button type="button" class="ll-remove" onclick="llRemove(this)" title="Remove">×</button>';
    foreach ( $fields as $f ) {
        echo '<label>' . esc_html( $f['label'] );
        $name = esc_attr( $prefix . '[__][' . $f['name'] . ']' );
        if ( $f['type'] === 'textarea' ) {
            echo '<textarea name="' . $name . '" rows="3"></textarea>';
        } elseif ( $f['type'] === 'select' ) {
            echo '<select name="' . $name . '">';
            foreach ( $f['options'] as $v => $l ) echo '<option value="' . esc_attr($v) . '">' . esc_html($l) . '</option>';
            echo '</select>';
        } else {
            echo '<input type="' . esc_attr( $f['type'] ?? 'text' ) . '" name="' . $name . '">';
        }
        echo '</label>';
    }
    echo '</div>';
}

/** Render existing rows from saved data */
function nota_mb_rows( $prefix, $cols_class, $fields, $items ) {
    foreach ( $items as $i => $row ) {
        echo '<div class="ll-row ' . esc_attr( $cols_class ) . '">';
        echo '<button type="button" class="ll-remove" onclick="llRemove(this)" title="Remove">×</button>';
        foreach ( $fields as $f ) {
            $val  = $row[ $f['name'] ] ?? '';
            $name = esc_attr( $prefix . '[' . $i . '][' . $f['name'] . ']' );
            echo '<label>' . esc_html( $f['label'] );
            if ( $f['type'] === 'textarea' ) {
                echo '<textarea name="' . $name . '" rows="3">' . esc_textarea( $val ) . '</textarea>';
            } elseif ( $f['type'] === 'select' ) {
                echo '<select name="' . $name . '">';
                foreach ( $f['options'] as $v => $l ) {
                    echo '<option value="' . esc_attr($v) . '"' . selected( $val, $v, false ) . '>' . esc_html($l) . '</option>';
                }
                echo '</select>';
            } else {
                echo '<input type="' . esc_attr( $f['type'] ?? 'text' ) . '" name="' . $name . '" value="' . esc_attr( $val ) . '">';
            }
            echo '</label>';
        }
        echo '</div>';
    }
}

function nota_mb_links( $post ) {
    nota_mb_nonce( $post );
    $items = get_post_meta( $post->ID, '_ll_links', true ) ?: array();
    $fields = array(
        array( 'name' => 'label', 'label' => 'Label', 'type' => 'text' ),
        array( 'name' => 'url',   'label' => 'URL',   'type' => 'url' ),
    );
    echo '<div class="ll-repeater"><div id="ll-links-rows">';
    nota_mb_tpl( 'll_links', 'll-row-cols-2', $fields );
    nota_mb_rows( 'll_links', 'll-row-cols-2', $fields, $items );
    echo '</div></div>';
    echo '<button type="button" class="button ll-add" onclick="llAddRow(this)">+ Add Link</button>';
}

/** Render a member's links sub-repeater */
function nota_mb_member_links( $prefix, $links ) {
    $count = count( $links );
    echo '<details class="ll-member-links"><summary>Links (' . $count . ')</summary>';
    echo '<div class="ll-link-rows">';
    // Template row (hidden)
    echo '<div class="ll-link-row ll-link-tpl" style="display:none">';
    echo '<input type="text"  data-fname="label" placeholder="Label" style="flex:1;font-size:12px;padding:3px 5px;border:1px solid #ccc;border-radius:2px">';
    echo '<input type="url"   data-fname="url"   placeholder="https://" style="flex:2;font-size:12px;padding:3px 5px;border:1px solid #ccc;border-radius:2px">';
    echo '<button type="button" onclick="llRemoveLink(this)" style="background:none;border:none;color:#a00;cursor:pointer;font-size:16px;padding:0 4px">×</button>';
    echo '</div>';
    // Existing rows
    foreach ( $links as $j => $link ) {
        $lname = esc_attr( $prefix . '[links][' . $j . '][label]' );
        $uname = esc_attr( $prefix . '[links][' . $j . '][url]'   );
        echo '<div class="ll-link-row">';
        echo '<input type="text" name="' . $lname . '" value="' . esc_attr( $link['label'] ) . '" placeholder="Label" style="flex:1;font-size:12px;padding:3px 5px;border:1px solid #ccc;border-radius:2px">';
        echo '<input type="url"  name="' . $uname . '" value="' . esc_attr( $link['url']   ) . '" placeholder="https://" style="flex:2;font-size:12px;padding:3px 5px;border:1px solid #ccc;border-radius:2px">';
        echo '<button type="button" onclick="llRemoveLink(this)" style="background:none;border:none;color:#a00;cursor:pointer;font-size:16px;padding:0 4px">×</button>';
        echo '</div>';
    }
    echo '</div>';
    echo '<button type="button" class="button button-small" onclick="llAddLink(this)" data-prefix="' . esc_attr( $prefix ) . '" style="margin-top:4px">+ Add Link</button>';
    echo '</details>';
}

function nota_mb_team( $post ) {
    nota_mb_nonce( $post );
    $items = get_post_meta( $post->ID, '_ll_team', true ) ?: array();

    // Inline JS for link sub-repeater
    echo '<script>
function llAddLink(btn){
  var prefix=btn.dataset.prefix;
  var wrap=btn.previousElementSibling;
  var tpl=wrap.querySelector(".ll-link-tpl");
  var idx=wrap.querySelectorAll(".ll-link-row:not(.ll-link-tpl)").length;
  var clone=tpl.cloneNode(true);
  clone.classList.remove("ll-link-tpl");
  clone.style.display="";
  clone.querySelectorAll("[data-fname]").forEach(function(el){
    el.name=prefix+"[links]["+idx+"]["+el.dataset.fname+"]";
  });
  wrap.appendChild(clone);
  // update summary count
  var det=btn.closest("details");
  det.querySelector("summary").textContent="Links ("+(idx+1)+")";
}
function llRemoveLink(btn){
  var row=btn.closest(".ll-link-row");
  var wrap=row.parentElement;
  row.remove();
  var det=btn.closest("details");
  var cnt=wrap.querySelectorAll(".ll-link-row:not(.ll-link-tpl)").length;
  det.querySelector("summary").textContent="Links ("+cnt+")";
}
</script>';

    echo '<style>.ll-link-rows{display:flex;flex-direction:column;gap:4px;margin:6px 0}.ll-link-row{display:flex;gap:6px;align-items:center}.ll-member-links{margin-top:8px;font-size:12px}.ll-member-links summary{cursor:pointer;color:#666;padding:2px 0}</style>';

    echo '<div class="ll-repeater"><div id="ll-team-rows">';

    // Template row (hidden) — includes link sub-repeater placeholder
    echo '<div class="ll-row ll-tpl" style="display:none">';
    echo '<button type="button" class="ll-remove" onclick="llRemove(this)" title="Remove">×</button>';
    foreach ( array(
        array( 'n' => 'name',        'l' => 'Name',        't' => 'text'     ),
        array( 'n' => 'role',        'l' => 'Role',        't' => 'text'     ),
        array( 'n' => 'institution', 'l' => 'Institution', 't' => 'text'     ),
        array( 'n' => 'bio',         'l' => 'Short bio',   't' => 'textarea' ),
    ) as $f ) {
        echo '<label>' . esc_html( $f['l'] );
        $name = 'll_team[__][' . $f['n'] . ']';
        if ( $f['t'] === 'textarea' ) echo '<textarea name="' . $name . '" rows="2"></textarea>';
        else echo '<input type="text" name="' . $name . '">';
        echo '</label>';
    }
    // Links placeholder in template (data-prefix updated by llAddRow override below)
    echo '<div class="ll-member-links-wrap" style="grid-column:1/-1">';
    echo '<details class="ll-member-links"><summary>Links (0)</summary>';
    echo '<div class="ll-link-rows"><div class="ll-link-tpl" style="display:none"></div></div>';
    echo '<button type="button" class="button button-small ll-link-add-btn" data-prefix="ll_team[__]" onclick="llAddLink(this)" style="margin-top:4px">+ Add Link</button>';
    echo '</details></div>';
    echo '</div>'; // .ll-row.ll-tpl

    // Override llAddRow to fix link btn prefix after cloning
    echo '<script>
var _origAddRow=llAddRow;
llAddRow=function(btn){
  var wrap=btn.previousElementSibling;
  var before=wrap.querySelectorAll(".ll-row:not(.ll-tpl)").length;
  _origAddRow(btn);
  var newRow=wrap.querySelectorAll(".ll-row:not(.ll-tpl)")[before];
  if(newRow){
    var linkBtn=newRow.querySelector(".ll-link-add-btn");
    if(linkBtn) linkBtn.dataset.prefix="ll_team["+before+"]";
  }
};
</script>';

    // Existing rows
    foreach ( $items as $i => $m ) {
        $prefix = 'll_team[' . $i . ']';
        echo '<div class="ll-row">';
        echo '<button type="button" class="ll-remove" onclick="llRemove(this)" title="Remove">×</button>';
        foreach ( array(
            array( 'n' => 'name',        'l' => 'Name',        't' => 'text',     'v' => $m['name'] ?? ''        ),
            array( 'n' => 'role',        'l' => 'Role',        't' => 'text',     'v' => $m['role'] ?? ''        ),
            array( 'n' => 'institution', 'l' => 'Institution', 't' => 'text',     'v' => $m['institution'] ?? '' ),
            array( 'n' => 'bio',         'l' => 'Short bio',   't' => 'textarea', 'v' => $m['bio'] ?? ''         ),
        ) as $f ) {
            $fname = esc_attr( $prefix . '[' . $f['n'] . ']' );
            echo '<label>' . esc_html( $f['l'] );
            if ( $f['t'] === 'textarea' ) echo '<textarea name="' . $fname . '" rows="2">' . esc_textarea( $f['v'] ) . '</textarea>';
            else echo '<input type="text" name="' . $fname . '" value="' . esc_attr( $f['v'] ) . '">';
            echo '</label>';
        }
        echo '<div class="ll-member-links-wrap" style="grid-column:1/-1">';
        nota_mb_member_links( $prefix, $m['links'] ?? array() );
        echo '</div>';
        echo '</div>';
    }

    echo '</div></div>';
    echo '<button type="button" class="button ll-add" onclick="llAddRow(this)">+ Add Member</button>';
}

function nota_mb_themes( $post ) {
    nota_mb_nonce( $post );
    $items = get_post_meta( $post->ID, '_ll_themes', true ) ?: array();
    $fields = array(
        array( 'name' => 'title', 'label' => 'Theme title',  'type' => 'text' ),
        array( 'name' => 'desc',  'label' => 'Description',  'type' => 'textarea' ),
    );
    echo '<div class="ll-repeater"><div id="ll-themes-rows">';
    nota_mb_tpl( 'll_themes', 'll-row-cols-2', $fields );
    nota_mb_rows( 'll_themes', 'll-row-cols-2', $fields, $items );
    echo '</div></div>';
    echo '<button type="button" class="button ll-add" onclick="llAddRow(this)">+ Add Theme</button>';
}

function nota_mb_pubs( $post ) {
    nota_mb_nonce( $post );
    $items = get_post_meta( $post->ID, '_ll_pubs', true ) ?: array();
    $type_opts = array(
        'ART'    => 'Journal article',
        'OUV'    => 'Book',
        'COUV'   => 'Book chapter',
        'COMM'   => 'Conference paper',
        'REPORT' => 'Report',
        'OTHER'  => 'Other',
    );
    $fields = array(
        array( 'name' => 'text', 'label' => 'Full citation (plain text)',         'type' => 'textarea' ),
        array( 'name' => 'year', 'label' => 'Year',                               'type' => 'text' ),
        array( 'name' => 'type', 'label' => 'Type',                               'type' => 'select', 'options' => $type_opts ),
        array( 'name' => 'doi',  'label' => 'DOI (without https://doi.org/)',      'type' => 'text' ),
        array( 'name' => 'url',  'label' => 'URL (PDF or publisher page)',         'type' => 'url' ),
    );
    echo '<p style="color:#666;font-size:12px;margin-top:0">These publications are shown on the page alongside HAL publications, grouped by type.</p>';
    echo '<div class="ll-repeater"><div id="ll-pubs-rows">';
    nota_mb_tpl( 'll_pubs', 'll-row-cols-2', $fields );
    nota_mb_rows( 'll_pubs', 'll-row-cols-2', $fields, $items );
    echo '</div></div>';
    echo '<button type="button" class="button ll-add" onclick="llAddRow(this)">+ Add Publication</button>';
}


// ============================================================
// 9. WORDPRESS CUSTOMIZER
// Allows users to configure the theme from Appearance → Customize.
// Sections: General, Colors, Typography, Features.
// ============================================================

add_action( 'customize_register', function( $wp_customize ) {

    // ── Panel ──
    $wp_customize->add_panel( 'nota_panel', array(
        'title'    => 'Nota Theme',
        'priority' => 30,
    ) );

    // ── Section: General ──
    $wp_customize->add_section( 'nota_general', array(
        'title' => 'General',
        'panel' => 'nota_panel',
    ) );

    $wp_customize->add_setting( 'nota_footer_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'nota_footer_text', array(
        'label'       => 'Footer text',
        'description' => 'Additional text shown in the footer after the copyright. Leave empty for none.',
        'section'     => 'nota_general',
        'type'        => 'text',
    ) );

    $wp_customize->add_setting( 'nota_footer_copyright', array(
        'default'           => '© {year} {site}',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'nota_footer_copyright', array(
        'label'       => 'Copyright format',
        'description' => 'Use {year} and {site} as placeholders. Example: © {year} {site}',
        'section'     => 'nota_general',
        'type'        => 'text',
    ) );

    // ── Section: Colors ──
    $wp_customize->add_section( 'nota_colors', array(
        'title' => 'Theme Colors',
        'panel' => 'nota_panel',
    ) );

    $wp_customize->add_setting( 'nota_accent_color', array(
        'default'           => '#163316',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'nota_accent_color', array(
        'label'   => 'Accent color',
        'section' => 'nota_colors',
    ) ) );

    $wp_customize->add_setting( 'nota_bg_color', array(
        'default'           => '#fcfbf9',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'nota_bg_color', array(
        'label'   => 'Background color',
        'section' => 'nota_colors',
    ) ) );

    $wp_customize->add_setting( 'nota_text_color', array(
        'default'           => '#0f140f',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'nota_text_color', array(
        'label'   => 'Text color',
        'section' => 'nota_colors',
    ) ) );

    $wp_customize->add_setting( 'nota_bg_pattern', array(
        'default'           => true,
        'sanitize_callback' => 'nota_sanitize_checkbox',
    ) );
    $wp_customize->add_control( 'nota_bg_pattern', array(
        'label'   => 'Show dotted background pattern',
        'section' => 'nota_colors',
        'type'    => 'checkbox',
    ) );

    // ── Section: Typography ──
    $wp_customize->add_section( 'nota_typography', array(
        'title' => 'Typography',
        'panel' => 'nota_panel',
    ) );

    $wp_customize->add_setting( 'nota_font_serif', array(
        'default'           => "'Libre Caslon Text', Georgia, serif",
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'nota_font_serif', array(
        'label'       => 'Body font (serif)',
        'description' => 'CSS font-family value for body text.',
        'section'     => 'nota_typography',
        'type'        => 'select',
        'choices'     => array(
            "'Libre Caslon Text', Georgia, serif" => 'Libre Caslon Text (default)',
            "Georgia, 'Times New Roman', serif"   => 'Georgia',
            "'Lora', Georgia, serif"              => 'Lora',
            "'Source Serif 4', Georgia, serif"     => 'Source Serif 4',
            "'Merriweather', Georgia, serif"      => 'Merriweather',
            "'EB Garamond', Garamond, serif"      => 'EB Garamond',
        ),
    ) );

    $wp_customize->add_setting( 'nota_font_sans', array(
        'default'           => "'Cabin', sans-serif",
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'nota_font_sans', array(
        'label'       => 'Heading font (sans-serif)',
        'description' => 'CSS font-family value for headings and UI.',
        'section'     => 'nota_typography',
        'type'        => 'select',
        'choices'     => array(
            "'Cabin', sans-serif"            => 'Cabin (default)',
            "'Inter', sans-serif"            => 'Inter',
            "'Source Sans 3', sans-serif"     => 'Source Sans 3',
            "'Work Sans', sans-serif"        => 'Work Sans',
            "'Nunito Sans', sans-serif"      => 'Nunito Sans',
            "system-ui, -apple-system, sans-serif" => 'System UI (no Google Fonts)',
        ),
    ) );

    // ── Section: Features ──
    $wp_customize->add_section( 'nota_features', array(
        'title' => 'Features',
        'panel' => 'nota_panel',
    ) );

    foreach ( array(
        'nota_enable_citations'   => array( 'Enable citation tools',    true ),
        'nota_enable_epub'        => array( 'Enable EPUB export',       true ),
        'nota_enable_reading_mode'=> array( 'Enable reading mode',      true ),
        'nota_enable_sidenotes'   => array( 'Enable sidenotes',         true ),
        'nota_enable_search'      => array( 'Enable header search',     true ),
        'nota_enable_dark_mode'   => array( 'Enable dark mode toggle',  true ),
        'nota_enable_lang_switcher' => array( 'Enable language switcher (requires Polylang)', true ),
    ) as $id => $meta ) {
        $wp_customize->add_setting( $id, array(
            'default'           => $meta[1],
            'sanitize_callback' => 'nota_sanitize_checkbox',
        ) );
        $wp_customize->add_control( $id, array(
            'label'   => $meta[0],
            'section' => 'nota_features',
            'type'    => 'checkbox',
        ) );
    }
} );

/** Sanitize checkbox values */
function nota_sanitize_checkbox( $input ) {
    return (bool) $input;
}

/**
 * Output Customizer CSS overrides.
 * Hooked into wp_head so custom colors/fonts take effect.
 */
add_action( 'wp_head', function() {
    $accent = get_theme_mod( 'nota_accent_color', '#163316' );
    $bg     = get_theme_mod( 'nota_bg_color', '#fcfbf9' );
    $text   = get_theme_mod( 'nota_text_color', '#0f140f' );
    $serif  = get_theme_mod( 'nota_font_serif', "'Libre Caslon Text', Georgia, serif" );
    $sans   = get_theme_mod( 'nota_font_sans', "'Cabin', sans-serif" );
    $pattern = get_theme_mod( 'nota_bg_pattern', true );

    // Only output if values differ from defaults
    $css = ':root {';
    if ( $accent !== '#163316' ) $css .= '--accent:' . esc_attr( $accent ) . ';';
    if ( $bg !== '#fcfbf9' )     $css .= '--bg-color:' . esc_attr( $bg ) . ';';
    if ( $text !== '#0f140f' )   $css .= '--text-color:' . esc_attr( $text ) . ';';
    if ( $serif !== "'Libre Caslon Text', Georgia, serif" ) $css .= '--font-serif:' . esc_attr( $serif ) . ';';
    if ( $sans !== "'Cabin', sans-serif" ) $css .= '--font-sans:' . esc_attr( $sans ) . ';';
    $css .= '}';

    if ( ! $pattern ) {
        $css .= 'body{background-image:none;}';
    }

    // Strip empty :root{}
    if ( $css === ':root {}' ) $css = '';

    if ( $css ) {
        echo '<style id="nota-customizer-css">' . $css . '</style>' . "\n";
    }
}, 20 );

/**
 * Helper: check if a Nota feature is enabled (via Customizer toggle).
 */
function nota_feature( $key ) {
    return (bool) get_theme_mod( 'nota_enable_' . $key, true );
}
