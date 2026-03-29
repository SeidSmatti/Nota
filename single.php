<?php get_header(); ?>

<div id="print-header" aria-hidden="true"><?php bloginfo('name'); ?></div>

<div class="container single-container">

    <?php while ( have_posts() ) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class( nota_post_has_notes() ? 'has-sidenotes' : '' ); ?>>
            
            <div class="article-grid">
                
                <header class="entry-header main-header">
                    <?php the_title( '<h1 class="entry-title main-title">', '</h1>' ); ?>
                </header>

                <aside class="article-sidebar">
                    
                    <div class="sidebar-block sidebar-meta">
                        
                        <div class="meta-group">
                            <span class="meta-label"><?php nota_e( 'Author' ); ?></span>
                            <div class="author-name">
                                <?php 
                                $custom_author = get_field('custom_author');
                                echo $custom_author ? esc_html($custom_author) : get_the_author(); 
                                ?>
                            </div>
                        </div>

                        <div class="meta-group">
                            <span class="meta-label"><?php nota_e( 'Published on' ); ?></span>
                            <div class="meta-value"><?php echo get_the_date(); ?></div>
                        </div>

                        <div class="meta-group">
                            <span class="meta-label"><?php nota_e( 'Updated on' ); ?></span>
                            <div class="meta-value"><?php echo get_the_modified_date(); ?></div>
                        </div>

                    </div>

                    <div class="sidebar-block sidebar-tools">
                        <?php if ( nota_feature( 'citations' ) ) : ?>
                        <button class="tool-btn" id="btn-cite">
                            <span class="btn-icon">❝</span> <?php nota_e( 'Cite this article' ); ?>
                        </button>
                        <?php endif; ?>

                        <button class="tool-btn" id="btn-print" onclick="window.print()">
                            <span class="btn-icon">🖨</span> <?php nota_e( 'Print / PDF' ); ?>
                        </button>

                        <?php if ( nota_feature( 'epub' ) ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'epub', '1', get_permalink() ) ); ?>" class="tool-btn" id="btn-epub">
                            <span class="btn-icon">⬇</span> <?php nota_e( 'Download EPUB' ); ?>
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php
                    $ref1 = get_field('ref_1');
                    $has_content = ( !empty($ref1['titre']) || !empty($ref1['citation_libre']) );

                    if( $ref1 && $has_content ): 
                    ?>
                        <div class="sidebar-block sidebar-biblio">
                            <h4 class="biblio-title"><?php nota_e( 'References' ); ?></h4>
                            <ul class="biblio-list">
                            <?php 
                            for ($i = 1; $i <= 10; $i++) {
                                $ref = get_field('ref_' . $i);
                                if ( empty($ref) ) continue;
                                $mode = isset($ref['mode']) ? $ref['mode'] : 'auto';

                                if ( $mode == 'auto' && empty($ref['titre']) ) continue;
                                if ( $mode == 'manuel' && empty($ref['citation_libre']) ) continue;
                                ?>
                                <li class="biblio-item">
                                    <?php if ($mode == 'manuel'): ?>
                                        <div class="bib-manual"><?php echo wp_kses_post( $ref['citation_libre'] ); ?></div>
                                    <?php else: ?>
                                        <?php 
                                            $auteur = $ref['auteur'];
                                            $annee = $ref['annee'];
                                            $titre = $ref['titre'];
                                            $editeur = $ref['editeur'];
                                            $lien = $ref['lien'];
                                            $complement = isset($ref['complement']) ? $ref['complement'] : '';
                                        ?>
                                        <span class="bib-author"><?php echo esc_html($auteur); ?></span>
                                        <span class="bib-year">(<?php echo esc_html($annee); ?>)</span>. 
                                        <?php if($lien): ?>
                                            <a href="<?php echo esc_url($lien); ?>" target="_blank" class="bib-link"><em class="bib-title"><?php echo esc_html($titre); ?></em></a>.
                                        <?php else: ?>
                                            <em class="bib-title"><?php echo esc_html($titre); ?></em>.
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </li>
                            <?php } ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </aside>

                <div class="entry-content text-content">
                    <?php
                    // Display a notice when the post has no translation in the user's preferred language.
                    if ( function_exists( 'pll_get_post_language' ) && function_exists( 'pll_get_post' ) ) {
                        $ui_lang        = nota_ui_lang();
                        $post_lang      = pll_get_post_language( get_the_ID() );
                        $has_translation = $post_lang && pll_get_post( get_the_ID(), $ui_lang );

                        if ( $post_lang && $ui_lang && $post_lang !== $ui_lang && ! $has_translation ) {
                            $names = array(
                                'en' => array( 'en' => 'English', 'fr' => 'French'  ),
                                'fr' => array( 'en' => 'anglais', 'fr' => 'français' ),
                            );
                            $lang_name = isset( $names[ $ui_lang ][ $post_lang ] )
                                ? $names[ $ui_lang ][ $post_lang ]
                                : strtoupper( $post_lang );
                            $notices = array(
                                'en' => 'This page is currently only available in ' . $lang_name . '.',
                                'fr' => 'Cette page n\'est actuellement disponible qu\'en ' . $lang_name . '.',
                            );
                            $notice = isset( $notices[ $ui_lang ] ) ? $notices[ $ui_lang ] : $notices['en'];
                            echo '<p class="lang-notice">' . esc_html( $notice ) . '</p>';
                        }
                    }
                    if ( has_post_thumbnail() ) {
                        echo '<div class="single-thumbnail">';
                        the_post_thumbnail('large');
                        echo '</div>';
                    }
                    the_content();
                    ?>
                </div>

            </div>

        </article>

    <?php endwhile; ?>

</div>

<?php if ( nota_feature( 'reading_mode' ) ) : ?>
<button id="btn-focus" class="focus-float-btn" aria-label="<?php echo esc_attr( nota_t( 'Reading mode' ) ); ?>">
    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
    </svg>
    <span class="btn-label"><?php nota_e( 'Reading mode' ); ?></span>
</button>
<?php endif; ?>

<?php if ( nota_feature( 'citations' ) ) : ?>
<button id="selection-cite-btn" class="selection-cite-btn" aria-label="<?php echo esc_attr( nota_t( 'Cite selected passage' ) ); ?>">
    <span class="cite-star">✦</span> <?php nota_e( 'Cite selected passage' ); ?>
</button>

<div id="selection-cite-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="close-sel-modal" style="float:right;cursor:pointer;font-size:1.5rem;line-height:1;">&times;</span>
        <h3 class="modal-title"><?php nota_e( 'Cite selected passage' ); ?></h3>
        <div class="sel-quote-wrap">
            <blockquote id="sel-quote" class="selection-quote"></blockquote>
            <button onclick="copyToClipboard('sel-quote')" class="copy-btn"><?php nota_e( 'Copy' ); ?></button>
        </div>
        <div class="tab-container">
            <div class="tab-header">
                <button class="tab-btn active" onclick="showSelTab(event, 'sel-APA')">APA</button>
                <button class="tab-btn" onclick="showSelTab(event, 'sel-MLA')">MLA</button>
                <button class="tab-btn" onclick="showSelTab(event, 'sel-Chicago')">Chicago</button>
                <button class="tab-btn" onclick="showSelTab(event, 'sel-BibTeX')">BibTeX</button>
            </div>
            <div id="sel-APA" class="tab-content" style="display:block;">
                <p id="sel-apa-text"></p>
                <button onclick="copyToClipboard('sel-apa-text')" class="copy-btn"><?php nota_e( 'Copy' ); ?></button>
            </div>
            <div id="sel-MLA" class="tab-content" style="display:none;">
                <p id="sel-mla-text"></p>
                <button onclick="copyToClipboard('sel-mla-text')" class="copy-btn"><?php nota_e( 'Copy' ); ?></button>
            </div>
            <div id="sel-Chicago" class="tab-content" style="display:none;">
                <p id="sel-chicago-text"></p>
                <button onclick="copyToClipboard('sel-chicago-text')" class="copy-btn"><?php nota_e( 'Copy' ); ?></button>
            </div>
            <div id="sel-BibTeX" class="tab-content" style="display:none;">
                <code id="sel-bibtex-text"></code>
                <button onclick="copyToClipboard('sel-bibtex-text')" class="copy-btn"><?php nota_e( 'Copy' ); ?></button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php get_footer(); ?>
