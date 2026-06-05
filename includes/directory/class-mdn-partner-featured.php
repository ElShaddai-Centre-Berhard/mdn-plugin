<?php
/**
 * Shortcode: [mdn_featured]
 * Usage: [mdn_featured count="6" title="Featured Partners"]
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class MDN_Partner_Featured {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
        add_action( 'save_post',      array( __CLASS__, 'save_meta' ) );
        add_filter( 'manage_partner_posts_columns',        array( __CLASS__, 'add_column' ) );
        add_action( 'manage_partner_posts_custom_column',  array( __CLASS__, 'column_content' ), 10, 2 );
        add_shortcode( 'mdn_featured', array( __CLASS__, 'render' ) );
    }

    public static function add_meta_box() {
        add_meta_box(
            'mdn_featured',
            'Featured Listing',
            array( __CLASS__, 'meta_box_html' ),
            'partner',
            'side',
            'high'
        );
    }

    public static function meta_box_html( $post ) {
        wp_nonce_field( 'mdn_save_featured', 'mdn_featured_nonce' );
        $is_featured = get_post_meta( $post->ID, '_mdn_featured', true );
        ?>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
            <input type="checkbox" name="mdn_featured" value="1"
                <?php checked( $is_featured, '1' ); ?> />
            <span>Show in featured section</span>
        </label>
        <p style="margin:8px 0 0;font-size:12px;color:#666;">
            Featured partners appear wherever the <code>[mdn_featured]</code> shortcode is placed.
        </p>
        <?php
    }

    public static function save_meta( $post_id ) {
        if ( ! isset( $_POST['mdn_featured_nonce'] ) ||
             ! wp_verify_nonce( $_POST['mdn_featured_nonce'], 'mdn_save_featured' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $value = isset( $_POST['mdn_featured'] ) ? '1' : '0';
        update_post_meta( $post_id, '_mdn_featured', $value );
    }

    public static function add_column( $columns ) {
        $columns['mdn_featured'] = 'Featured';
        return $columns;
    }

    public static function column_content( $column, $post_id ) {
        if ( $column === 'mdn_featured' ) {
            echo get_post_meta( $post_id, '_mdn_featured', true ) === '1' ? '&#11088;' : '&mdash;';
        }
    }

    public static function render( $atts ) {
        $atts = shortcode_atts( array(
            'count' => 6,
            'title' => 'Featured Partners',
        ), $atts, 'mdn_featured' );

        $query = new WP_Query( array(
            'post_type'      => 'partner',
            'post_status'    => 'publish',
            'posts_per_page' => intval( $atts['count'] ),
            'meta_query'     => array( array( 'key' => '_mdn_featured', 'value' => '1' ) ),
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( ! $query->have_posts() ) return '';

        ob_start();
        ?>
        <div class="mdn-featured-wrap">
            <?php if ( $atts['title'] ) : ?>
                <div class="mdn-featured-header">
                    <span class="mdn-featured-badge">&#11088; Featured</span>
                    <h2 class="mdn-featured-title"><?php echo esc_html( $atts['title'] ); ?></h2>
                </div>
            <?php endif; ?>

            <div class="mdn-featured-grid">
            <?php while ( $query->have_posts() ) : $query->the_post();
                $id       = get_the_ID();
                $location = get_post_meta( $id, '_mdn_location', true );
                $tagline  = get_post_meta( $id, '_mdn_tagline',  true );
                $terms    = get_the_terms( $id, 'partner_category' );
                $term     = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : null;

                $words    = explode( ' ', get_the_title() );
                $initials = implode( '', array_map( fn($w) => strtoupper( mb_substr( $w, 0, 1 ) ), array_slice( $words, 0, 3 ) ) );
            ?>
                <article class="mdn-featured-card">
                    <a href="<?php the_permalink(); ?>" class="mdn-featured-card-inner">

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="mdn-featured-avatar mdn-featured-avatar--img">
                                <?php the_post_thumbnail( 'thumbnail', array( 'alt' => get_the_title() ) ); ?>
                            </div>
                        <?php else : ?>
                            <div class="mdn-featured-avatar mdn-featured-avatar--initials"><?php echo esc_html( $initials ); ?></div>
                        <?php endif; ?>

                        <div class="mdn-featured-card-body">
                            <?php if ( $term ) : ?>
                                <span class="mdn-tag"><?php echo esc_html( $term->name ); ?></span>
                            <?php endif; ?>
                            <h3 class="mdn-featured-name"><?php the_title(); ?></h3>
                            <?php if ( $tagline ) : ?>
                                <p class="mdn-featured-tagline"><?php echo esc_html( $tagline ); ?></p>
                            <?php elseif ( $location ) : ?>
                                <p class="mdn-featured-tagline"><?php echo esc_html( $location ); ?></p>
                            <?php endif; ?>
                        </div>

                        <span class="mdn-featured-arrow">&rarr;</span>
                    </a>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <div class="mdn-featured-footer">
                <?php
                $dir_page = get_page_by_path( 'directory' );
                $dir_url  = $dir_page ? get_permalink( $dir_page ) : home_url( '/' );
                ?>
                <a href="<?php echo esc_url( $dir_url ); ?>" class="mdn-btn mdn-btn-outline">
                    Browse all partners &rarr;
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
