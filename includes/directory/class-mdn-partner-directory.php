<?php
/**
 * Shortcode: [mdn_directory]
 * Renders the searchable, filterable grid of approved partner profiles.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class MDN_Partner_Directory {

    public static function init() {
        add_shortcode( 'mdn_directory', array( __CLASS__, 'render' ) );
    }

    public static function render() {
        ob_start();

        $search   = sanitize_text_field( $_GET['mdn_search'] ?? '' );
        $category = intval( $_GET['mdn_cat'] ?? 0 );
        $region   = intval( $_GET['mdn_region'] ?? 0 );

        $args = array(
            'post_type'      => 'partner',
            'post_status'    => 'publish',
            'posts_per_page' => 24,
            's'              => $search,
        );

        $tax_query = array();
        if ( $category ) {
            $tax_query[] = array(
                'taxonomy' => 'partner_category',
                'field'    => 'term_id',
                'terms'    => $category,
            );
        }
        if ( $region ) {
            $tax_query[] = array(
                'taxonomy' => 'partner_region',
                'field'    => 'term_id',
                'terms'    => $region,
            );
        }
        if ( ! empty( $tax_query ) ) {
            $tax_query['relation'] = 'AND';
            $args['tax_query']     = $tax_query;
        }

        $query      = new WP_Query( $args );
        $categories = get_terms( array( 'taxonomy' => 'partner_category', 'hide_empty' => true ) );
        $regions    = get_terms( array( 'taxonomy' => 'partner_region',   'hide_empty' => true ) );
        $total      = $query->found_posts;
        ?>

        <div class="mdn-directory">

            <form method="get" class="mdn-search-form">
                <input
                    type="text"
                    name="mdn_search"
                    value="<?php echo esc_attr( $search ); ?>"
                    placeholder="Search partners..."
                    aria-label="Search partners"
                />
                <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
                <select name="mdn_cat" aria-label="Filter by category">
                    <option value="">All categories</option>
                    <?php foreach ( $categories as $cat ) : ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"
                            <?php selected( $category, $cat->term_id ); ?>>
                            <?php echo esc_html( $cat->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <?php if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) : ?>
                <select name="mdn_region" aria-label="Filter by region">
                    <option value="">All regions</option>
                    <?php foreach ( $regions as $reg ) : ?>
                        <option value="<?php echo esc_attr( $reg->term_id ); ?>"
                            <?php selected( $region, $reg->term_id ); ?>>
                            <?php echo esc_html( $reg->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                <button type="submit" class="mdn-btn">Search</button>
                <?php if ( $search || $category || $region ) : ?>
                    <a href="?" class="mdn-clear-link">Clear filters</a>
                <?php endif; ?>
            </form>

            <p class="mdn-result-count">
                <?php if ( $search || $category || $region ) : ?>
                    <?php echo esc_html( $total ); ?> result<?php echo $total !== 1 ? 's' : ''; ?> found
                <?php else : ?>
                    <?php echo esc_html( $total ); ?> partner<?php echo $total !== 1 ? 's' : ''; ?> listed
                <?php endif; ?>
            </p>

            <?php if ( $query->have_posts() ) : ?>
            <div class="mdn-grid">
                <?php while ( $query->have_posts() ) : $query->the_post();
                    $id       = get_the_ID();
                    $location = get_post_meta( $id, '_mdn_location', true );
                    $website  = get_post_meta( $id, '_mdn_website',  true );
                    $terms    = get_the_terms( $id, 'partner_category' );
                    $term     = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : null;

                    $full_excerpt = get_the_excerpt();
                    $short        = wp_trim_words( $full_excerpt, 20, null );
                    $needs_more   = ( str_word_count( $full_excerpt ) > 20 );

                    // Initials for placeholder
                    $words    = explode( ' ', get_the_title() );
                    $initials = implode( '', array_map( function( $w ) {
                        return strtoupper( mb_substr( $w, 0, 1 ) );
                    }, array_slice( $words, 0, 3 ) ) );
                ?>
                <article class="mdn-card">

                    <a href="<?php the_permalink(); ?>" class="mdn-card-img-link" tabindex="-1" aria-hidden="true">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="mdn-card-img">
                                <?php the_post_thumbnail( 'medium', array( 'alt' => get_the_title() ) ); ?>
                            </div>
                        <?php else : ?>
                            <div class="mdn-card-img mdn-card-img--initials">
                                <?php echo esc_html( $initials ); ?>
                            </div>
                        <?php endif; ?>
                    </a>

                    <div class="mdn-card-body">

                        <?php if ( $term ) : ?>
                            <a href="?mdn_cat=<?php echo esc_attr( $term->term_id ); ?>"
                                class="mdn-tag"><?php echo esc_html( $term->name ); ?></a>
                        <?php endif; ?>

                        <h3 class="mdn-card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <p class="mdn-card-desc">
                            <?php echo esc_html( $short ); ?>
                            <?php if ( $needs_more ) : ?>
                                <a href="<?php the_permalink(); ?>" class="mdn-read-more">Read more</a>
                            <?php endif; ?>
                        </p>

                        <?php if ( $location ) : ?>
                            <p class="mdn-card-location">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                    <circle cx="12" cy="9" r="2.5"/>
                                </svg>
                                <?php echo esc_html( $location ); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ( $website ) : ?>
                        <div class="mdn-card-footer">
                            <a href="<?php echo esc_url( $website ); ?>"
                                class="mdn-card-link" target="_blank" rel="noopener">
                                Visit website ↗
                            </a>
                        </div>
                        <?php endif; ?>

                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <?php else : ?>
            <div class="mdn-empty">
                <p>No partners found<?php echo $search ? ' for "' . esc_html( $search ) . '"' : ''; ?>.</p>
                <?php if ( $search || $category || $region ) : ?>
                    <a href="?" class="mdn-btn mdn-btn-outline">Clear filters</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
        <?php
        return ob_get_clean();
    }
}
