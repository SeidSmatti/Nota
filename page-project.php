<?php
/**
 * Template Name: Project Page
 *
 * A structured profile page for the research project: about text, team,
 * research themes, and a live HAL publications list.
 */
get_header();

$tagline     = get_field( 'project_tagline' );
$pi          = get_field( 'project_pi' );
$period      = get_field( 'project_period' );
$funding     = get_field( 'project_funding' );
$institution = get_field( 'project_institution' );
$links       = get_post_meta( get_the_ID(), '_ll_links',  true ) ?: array();
$team        = get_post_meta( get_the_ID(), '_ll_team',   true ) ?: array();
$themes      = get_post_meta( get_the_ID(), '_ll_themes', true ) ?: array();
$manual_pubs = get_post_meta( get_the_ID(), '_ll_pubs',   true ) ?: array();
$hal_author  = get_field( 'hal_author_id' ) ?: '';
$hal_coll    = get_field( 'hal_collection' ) ?: '';

// Detect whether the page has real body content
$has_content = false;
if ( have_posts() ) {
    the_post();
    $has_content = ! empty( trim( strip_tags( get_the_content() ) ) );
    rewind_posts();
}

$has_info    = $pi || $period || $funding || $institution || $links;
$has_sidebar = $has_info || $themes;
?>

<div class="container project-container">

    <header class="project-header">
        <h1 class="project-title"><?php the_title(); ?></h1>
        <?php if ( $tagline && $tagline !== get_the_title() ) : ?>
            <p class="project-tagline"><?php echo esc_html( $tagline ); ?></p>
        <?php endif; ?>
    </header>

    <?php if ( $has_sidebar && ! $has_content ) : ?>
        <!-- No body text: key info + themes in one box -->
        <h2 class="project-section-label"><?php nota_e( 'Key Information' ); ?></h2>
        <div class="project-infobar">
            <?php if ( $pi ) : ?>
                <div class="infobar-item">
                    <span class="info-label"><?php nota_e( 'Principal Investigator' ); ?></span>
                    <span class="info-value"><?php echo esc_html( $pi ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $period ) : ?>
                <div class="infobar-item">
                    <span class="info-label"><?php nota_e( 'Period' ); ?></span>
                    <span class="info-value"><?php echo esc_html( $period ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $funding ) : ?>
                <div class="infobar-item">
                    <span class="info-label"><?php nota_e( 'Funding' ); ?></span>
                    <span class="info-value"><?php echo esc_html( $funding ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $institution ) : ?>
                <div class="infobar-item">
                    <span class="info-label"><?php nota_e( 'Institution' ); ?></span>
                    <span class="info-value"><?php echo esc_html( $institution ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( $links ) : ?>
                <div class="infobar-item infobar-links">
                    <?php foreach ( $links as $link ) : ?>
                        <a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener">
                            <?php echo esc_html( $link['label'] ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ( $themes ) : ?>
                <div class="infobar-sep">
                    <span class="info-label"><?php nota_e( 'Research Themes' ); ?></span>
                </div>
                <?php foreach ( $themes as $theme ) : ?>
                    <div class="infobar-item infobar-theme">
                        <span class="info-label"><?php echo esc_html( $theme['title'] ); ?></span>
                        <?php if ( ! empty( $theme['desc'] ) ) : ?>
                            <span class="info-value"><?php echo esc_html( $theme['desc'] ); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <?php elseif ( $has_content && $has_sidebar ) : ?>
        <!-- Body text + sidebar -->
        <div class="project-grid">
            <div class="project-main">
                <div class="project-about page-content">
                    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                        <?php the_content(); ?>
                    <?php endwhile; endif; ?>
                </div>
            </div>
            <aside class="project-sidebar">
                <?php if ( $has_info || $themes ) : ?>
                    <h3 class="project-section-label"><?php nota_e( 'Key Information' ); ?></h3>
                    <div class="project-info-block">
                        <?php if ( $pi ) : ?>
                            <div class="info-row">
                                <span class="info-label"><?php nota_e( 'Principal Investigator' ); ?></span>
                                <span class="info-value"><?php echo esc_html( $pi ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $period ) : ?>
                            <div class="info-row">
                                <span class="info-label"><?php nota_e( 'Period' ); ?></span>
                                <span class="info-value"><?php echo esc_html( $period ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $funding ) : ?>
                            <div class="info-row">
                                <span class="info-label"><?php nota_e( 'Funding' ); ?></span>
                                <span class="info-value"><?php echo esc_html( $funding ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $institution ) : ?>
                            <div class="info-row">
                                <span class="info-label"><?php nota_e( 'Institution' ); ?></span>
                                <span class="info-value"><?php echo esc_html( $institution ); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $links ) : ?>
                            <div class="info-row info-links">
                                <?php foreach ( $links as $link ) : ?>
                                    <a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener">
                                        <?php echo esc_html( $link['label'] ); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ( $themes ) : ?>
                            <div class="info-row info-themes-header">
                                <span class="info-label"><?php nota_e( 'Research Themes' ); ?></span>
                            </div>
                            <?php foreach ( $themes as $theme ) : ?>
                                <div class="info-row info-theme">
                                    <span class="info-label"><?php echo esc_html( $theme['title'] ); ?></span>
                                    <?php if ( ! empty( $theme['desc'] ) ) : ?>
                                        <span class="info-value"><?php echo esc_html( $theme['desc'] ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </aside>
        </div>

    <?php elseif ( $has_content ) : ?>
        <!-- Body text only, no sidebar -->
        <div class="project-about page-content">
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
                <?php the_content(); ?>
            <?php endwhile; endif; ?>
        </div>
    <?php endif; ?>

    <?php if ( $team ) : ?>
        <hr class="project-sep">
        <h2 class="project-section-label"><?php nota_e( 'Team' ); ?></h2>
        <section class="project-team">
            <ul class="team-list">
                <?php foreach ( $team as $member ) : ?>
                    <li class="team-member">
                        <div class="member-name"><?php echo esc_html( $member['name'] ); ?></div>
                        <?php if ( ! empty( $member['role'] ) ) : ?>
                            <div class="member-role"><?php echo esc_html( $member['role'] ); ?></div>
                        <?php endif; ?>
                        <?php if ( ! empty( $member['institution'] ) ) : ?>
                            <div class="member-institution"><?php echo esc_html( $member['institution'] ); ?></div>
                        <?php endif; ?>
                        <?php if ( ! empty( $member['bio'] ) ) : ?>
                            <p class="member-bio"><?php echo esc_html( $member['bio'] ); ?></p>
                        <?php endif; ?>
                        <?php if ( ! empty( $member['links'] ) ) : ?>
                            <details class="member-links-details">
                                <summary class="member-links-summary"><?php nota_e( 'Links' ); ?></summary>
                                <div class="member-links">
                                    <?php foreach ( $member['links'] as $ml ) : ?>
                                        <a href="<?php echo esc_url( $ml['url'] ); ?>" target="_blank" rel="noopener" class="member-link">
                                            <?php echo esc_html( $ml['label'] ); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php
    $type_order  = array( 'ART', 'OUV', 'COUV', 'COMM', 'THESE', 'HDR', 'REPORT', 'OTHER' );
    $type_labels = array(
        'ART'    => nota_t( 'Journal articles' ),
        'OUV'    => nota_t( 'Books' ),
        'COUV'   => nota_t( 'Book chapters' ),
        'COMM'   => nota_t( 'Conference papers' ),
        'THESE'  => nota_t( 'Thesis' ),
        'HDR'    => nota_t( 'Habilitation' ),
        'REPORT' => nota_t( 'Reports' ),
        'OTHER'  => nota_t( 'Other publications' ),
    );

    // Group manual publications by type
    $manual_grouped = array();
    foreach ( $manual_pubs as $pub ) {
        $type = $pub['type'] ?? 'OTHER';
        $manual_grouped[ $type ][] = array(
            '_manual'        => true,
            'title_s'        => array( $pub['text'] ),
            'producedYear_i' => $pub['year'] ?? '',
            'doiId_s'        => $pub['doi'] ?? '',
            'uri_s'          => $pub['url'] ?? '',
        );
    }
    foreach ( $manual_grouped as &$grp ) {
        usort( $grp, function( $a, $b ) {
            return intval( $b['producedYear_i'] ) - intval( $a['producedYear_i'] );
        } );
    }
    unset( $grp );

    // Only show HAL section if a HAL author or collection is configured
    $hal_publications = ( $hal_author || $hal_coll ) ? nota_hal_publications( $hal_author, $hal_coll ) : null;
    $show_hal = $hal_author || $hal_coll;
    ?>

    <?php if ( $show_hal ) : ?>
    <hr class="project-sep">

    <?php /* ── HAL Publications ── */ ?>
    <h2 class="project-section-label project-pub-label">
        <?php nota_e( 'HAL Publications' ); ?>
        <?php if ( $hal_author ) : ?>
        <a href="https://cv.hal.science/<?php echo esc_attr( $hal_author ); ?>" target="_blank" rel="noopener" class="hal-source-link">cv.hal.science ↗</a>
        <?php endif; ?>
    </h2>
    <section class="project-publications">

        <?php if ( $hal_publications === null ) : ?>
            <p class="hal-notice"><?php nota_e( 'HAL API unavailable' ); ?></p>
        <?php elseif ( empty( $hal_publications ) ) : ?>
            <p class="hal-notice"><?php nota_e( 'No publications found' ); ?></p>
        <?php else :
            foreach ( $type_order as $type ) :
                if ( empty( $hal_publications[ $type ] ) ) continue;
        ?>
                <details class="hal-type-section">
                    <summary class="hal-type-title">
                        <?php echo esc_html( $type_labels[ $type ] ?? $type ); ?>
                        <span class="hal-type-count"><?php echo count( $hal_publications[ $type ] ); ?></span>
                    </summary>
                    <div class="hal-list">
                        <?php foreach ( $hal_publications[ $type ] as $pub ) : ?>
                            <div class="hal-entry">
                                <?php echo nota_hal_entry_html( $pub, $type ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
        <?php
            endforeach;
        endif; ?>
    </section>
    <?php endif; ?>

    <?php /* ── Manual Publications ── */ ?>
    <?php if ( ! empty( $manual_grouped ) ) : ?>
        <?php if ( ! $show_hal ) : ?><hr class="project-sep"><?php endif; ?>
        <h2 class="project-section-label project-pub-label"><?php nota_e( 'Other Publications' ); ?></h2>
        <section class="project-publications">
            <?php foreach ( $type_order as $type ) :
                if ( empty( $manual_grouped[ $type ] ) ) continue;
            ?>
                <details class="hal-type-section">
                    <summary class="hal-type-title">
                        <?php echo esc_html( $type_labels[ $type ] ?? $type ); ?>
                        <span class="hal-type-count"><?php echo count( $manual_grouped[ $type ] ); ?></span>
                    </summary>
                    <div class="hal-list">
                        <?php foreach ( $manual_grouped[ $type ] as $pub ) : ?>
                            <div class="hal-entry">
                                <?php echo nota_hal_entry_html( $pub, $type ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
