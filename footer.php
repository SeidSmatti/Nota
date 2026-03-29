</main> <footer class="site-footer">
        <div class="footer-inner">
            <p>
                <?php
                $copyright = get_theme_mod( 'nota_footer_copyright', '© {year} {site}' );
                $copyright = str_replace(
                    array( '{year}', '{site}' ),
                    array( date( 'Y' ), get_bloginfo( 'name' ) ),
                    $copyright
                );
                echo wp_kses_post( $copyright );

                $footer_text = get_theme_mod( 'nota_footer_text', '' );
                if ( $footer_text ) {
                    echo ' ' . esc_html( $footer_text );
                }
                ?>
            </p>
        </div>
    </footer>

    <?php if ( nota_feature( 'citations' ) ) : ?>
    <div id="citation-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3 style="margin-top:0; margin-bottom: 20px;"><?php nota_e( 'Cite this article modal' ); ?></h3>
            
            <div class="citation-tabs">
                <button class="tab-btn active" onclick="showTab(event, 'APA')">APA</button>
                <button class="tab-btn" onclick="showTab(event, 'MLA')">MLA</button>
                <button class="tab-btn" onclick="showTab(event, 'Chicago')">Chicago</button>
                <button class="tab-btn" onclick="showTab(event, 'BibTeX')">BibTeX</button>
            </div>

            <div id="APA" class="tab-content" style="display:block;">
                <p id="apa-text" class="citation-text"></p>
                <button class="copy-btn" onclick="copyToClipboard('apa-text')"><?php nota_e( 'Copy' ); ?> APA</button>
            </div>

            <div id="MLA" class="tab-content">
                <p id="mla-text" class="citation-text"></p>
                <button class="copy-btn" onclick="copyToClipboard('mla-text')"><?php nota_e( 'Copy' ); ?> MLA</button>
            </div>

            <div id="Chicago" class="tab-content">
                <p id="chicago-text" class="citation-text"></p>
                <button class="copy-btn" onclick="copyToClipboard('chicago-text')"><?php nota_e( 'Copy' ); ?> Chicago</button>
            </div>

            <div id="BibTeX" class="tab-content">
                <pre id="bibtex-text" class="citation-code"></pre>
                <button class="copy-btn" onclick="copyToClipboard('bibtex-text')"><?php nota_e( 'Copy' ); ?> BibTeX</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php wp_footer(); ?>

</body>
</html>
