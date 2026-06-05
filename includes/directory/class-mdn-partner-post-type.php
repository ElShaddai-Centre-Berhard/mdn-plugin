<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MDN_Partner_Post_Type {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register' ) );
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_partner', array( __CLASS__, 'save_meta' ) );
        add_filter( 'the_content', array( __CLASS__, 'inject_profile' ) );
    }

    public static function register() {
        register_post_type( 'partner', array(
            'labels' => array(
                'name'               => 'Partners',
                'singular_name'      => 'Partner',
                'add_new'            => 'Add New',
                'add_new_item'       => 'Add New Partner',
                'edit_item'          => 'Edit Partner',
                'view_item'          => 'View Partner',
                'search_items'       => 'Search Partners',
                'not_found'          => 'No partners found',
                'not_found_in_trash' => 'No partners in trash',
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'mdn-dashboard',
            'show_in_rest'       => true,
            'has_archive'        => 'partners',
            'menu_icon'          => 'dashicons-groups',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'capability_type'    => 'post',
            'rewrite'            => array(
                'slug'       => 'partners',
                'with_front' => false,
            ),
        ) );

        register_taxonomy( 'partner_category', 'partner', array(
            'hierarchical' => true,
            'show_in_rest' => true,
            'labels'       => array(
                'name'          => 'Categories',
                'singular_name' => 'Category',
                'add_new_item'  => 'Add New Category',
                'edit_item'     => 'Edit Category',
                'search_items'  => 'Search Categories',
            ),
            'rewrite' => array( 'slug' => 'partner-category' ),
        ) );
    }

    // ── Field definitions ─────────────────────────────────────────────────────

    public static function get_fields() {
        return array(
            '_section_org' => array( 'type' => 'section', 'label' => 'Organisation' ),
            '_mdn_tagline' => array(
                'label'       => 'Tagline',
                'type'        => 'text',
                'placeholder' => 'e.g. Supporting the Malaysian diaspora community',
                'hint'        => 'Short description shown under the name on the profile page.',
            ),
            '_mdn_website' => array(
                'label'       => 'Website',
                'type'        => 'url',
                'placeholder' => 'https://example.com',
            ),
            '_mdn_email' => array(
                'label'       => 'Public email',
                'type'        => 'email',
                'placeholder' => 'hello@example.com',
            ),
            '_mdn_phone' => array(
                'label'       => 'Phone',
                'type'        => 'text',
                'placeholder' => '+60 3-XXXX XXXX',
            ),

            '_section_location' => array( 'type' => 'section', 'label' => 'Location' ),
            '_mdn_location' => array(
                'label'       => 'Address / City',
                'type'        => 'text',
                'placeholder' => 'e.g. Kuala Lumpur, Malaysia',
            ),
            '_mdn_area' => array(
                'label'       => 'Operating area',
                'type'        => 'text',
                'placeholder' => 'e.g. Kuala Lumpur, Selangor',
                'hint'        => 'The broader region this partner serves.',
            ),
            '_mdn_lat' => array(
                'label'       => 'Latitude',
                'type'        => 'text',
                'placeholder' => 'e.g. 3.1390',
            ),
            '_mdn_lng' => array(
                'label'       => 'Longitude',
                'type'        => 'text',
                'placeholder' => 'e.g. 101.6869',
            ),

            '_section_reg' => array( 'type' => 'section', 'label' => 'Registration' ),
            '_mdn_founded' => array(
                'label'       => 'Year founded',
                'type'        => 'text',
                'placeholder' => 'e.g. 2012',
            ),
            '_mdn_registered_as' => array(
                'label'       => 'Registered as',
                'type'        => 'text',
                'placeholder' => 'e.g. Yayasan (Foundation), Syarikat (Company)',
            ),
            '_mdn_reg_number' => array(
                'label'       => 'Registration number',
                'type'        => 'text',
                'placeholder' => 'e.g. PPM-001-14-23012012',
            ),
        );
    }

    // ── Meta box ──────────────────────────────────────────────────────────────

    public static function add_meta_boxes() {
        add_meta_box(
            'mdn_partner_details',
            'Partner Details',
            array( __CLASS__, 'meta_box_html' ),
            'partner',
            'normal',
            'high'
        );
    }

    public static function meta_box_html( $post ) {
        wp_nonce_field( 'mdn_save_partner_meta', 'mdn_partner_nonce' );
        $fields = self::get_fields();
        ?>
        <style>
            .mdn-mb-section { font-size:11px;font-weight:600;text-transform:uppercase;
                letter-spacing:.06em;color:#999;margin:22px 0 8px;padding-bottom:6px;
                border-bottom:1px solid #f0f0f0; }
            .mdn-mb-section:first-of-type { margin-top:6px; }
            .mdn-mb-field { margin-bottom:14px; }
            .mdn-mb-field label { display:block;font-weight:600;font-size:13px;
                margin-bottom:4px;color:#1d2327; }
            .mdn-mb-field input.regular-text { width:100%;max-width:480px; }
            .mdn-mb-hint { font-size:12px;color:#999;margin:4px 0 0;font-style:italic; }
        </style>
        <?php foreach ( $fields as $meta_key => $field ) :
            if ( $field['type'] === 'section' ) : ?>
                <div class="mdn-mb-section"><?php echo esc_html( $field['label'] ); ?></div>
                <?php continue;
            endif;
            $name  = ltrim( $meta_key, '_' );
            $value = get_post_meta( $post->ID, $meta_key, true );
        ?>
            <div class="mdn-mb-field">
                <label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                <input
                    type="<?php echo esc_attr( $field['type'] ); ?>"
                    id="<?php echo esc_attr( $name ); ?>"
                    name="<?php echo esc_attr( $name ); ?>"
                    value="<?php echo esc_attr( $value ); ?>"
                    placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
                    class="regular-text"
                />
                <?php if ( ! empty( $field['hint'] ) ) : ?>
                    <p class="mdn-mb-hint"><?php echo esc_html( $field['hint'] ); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach;
    }

    public static function save_meta( $post_id ) {
        if ( ! isset( $_POST['mdn_partner_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['mdn_partner_nonce'], 'mdn_save_partner_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        foreach ( self::get_fields() as $meta_key => $field ) {
            if ( $field['type'] === 'section' ) continue;
            $name  = ltrim( $meta_key, '_' );
            $raw   = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : '';
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $raw ) );
        }
    }

    // ── Single profile page injection ─────────────────────────────────────────

    public static function inject_profile( $content ) {
        if ( ! is_singular( 'partner' ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        $id       = get_the_ID();
        $title    = get_the_title();
        $tagline  = get_post_meta( $id, '_mdn_tagline',       true );
        $location = get_post_meta( $id, '_mdn_location',      true );
        $website  = get_post_meta( $id, '_mdn_website',       true );
        $phone    = get_post_meta( $id, '_mdn_phone',         true );
        $email    = get_post_meta( $id, '_mdn_email',         true );
        $founded  = get_post_meta( $id, '_mdn_founded',       true );
        $reg_num  = get_post_meta( $id, '_mdn_reg_number',    true );
        $reg_as   = get_post_meta( $id, '_mdn_registered_as', true );
        $area     = get_post_meta( $id, '_mdn_area',          true );
        $modified = get_the_modified_date( 'F Y' );
        $terms    = get_the_terms( $id, 'partner_category' );

        $words    = explode( ' ', $title );
        $initials = implode( '', array_map( function( $w ) {
            return strtoupper( mb_substr( $w, 0, 1 ) );
        }, array_slice( $words, 0, 3 ) ) );

        $dir_page      = get_page_by_path( 'directory' );
        $directory_url = $dir_page ? get_permalink( $dir_page ) : home_url( '/' );

        $details = array_filter( array(
            'Founded'        => $founded,
            'Location'       => $location,
            'Registered as'  => $reg_as,
            'Reg. number'    => $reg_num,
            'Operating area' => $area,
        ) );

        $contacts = array_filter( array(
            'Email'   => $email   ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '',
            'Phone'   => $phone   ? esc_html( $phone ) : '',
            'Address' => $location ? esc_html( $location ) : '',
            'Website' => $website ? '<a href="' . esc_url( $website ) . '" target="_blank" rel="noopener">'
                            . esc_html( preg_replace( '#^https?://#', '', rtrim( $website, '/' ) ) ) . '</a>' : '',
        ) );

        ob_start();
        ?>
        <div class="mdn-profile-wrap">

            <div class="mdn-profile-nav">
                <a href="<?php echo esc_url( $directory_url ); ?>" class="mdn-back-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Back to directory
                </a>
            </div>

            <div class="mdn-profile-header">
                <div class="mdn-profile-header-left">

                    <?php if ( has_post_thumbnail( $id ) ) : ?>
                        <div class="mdn-profile-avatar mdn-profile-avatar--img">
                            <?php echo get_the_post_thumbnail( $id, 'thumbnail', array( 'alt' => esc_attr( $title ) ) ); ?>
                        </div>
                    <?php else : ?>
                        <div class="mdn-profile-avatar mdn-profile-avatar--initials"><?php echo esc_html( $initials ); ?></div>
                    <?php endif; ?>

                    <div class="mdn-profile-header-text">
                        <h1 class="mdn-profile-name"><?php echo esc_html( $title ); ?></h1>
                        <?php if ( $tagline ) : ?>
                            <p class="mdn-profile-tagline"><?php echo esc_html( $tagline ); ?></p>
                        <?php elseif ( $location ) : ?>
                            <p class="mdn-profile-tagline"><?php echo esc_html( $location ); ?></p>
                        <?php endif; ?>

                        <?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) : ?>
                        <div class="mdn-profile-tags">
                            <?php foreach ( $terms as $term ) : ?>
                                <span class="mdn-profile-tag"><?php echo esc_html( $term->name ); ?></span>
                            <?php endforeach; ?>
                            <span class="mdn-profile-tag mdn-profile-tag--verified">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Listed partner
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ( $website ) : ?>
                    <a href="<?php echo esc_url( $website ); ?>" class="mdn-profile-btn" target="_blank" rel="noopener">Visit website</a>
                <?php endif; ?>
            </div>

            <hr class="mdn-divider">

            <?php if ( $content ) : ?>
            <section class="mdn-profile-section">
                <h2 class="mdn-profile-section-label">About</h2>
                <div class="mdn-profile-about"><?php echo wp_kses_post( wpautop( $content ) ); ?></div>
            </section>
            <hr class="mdn-divider">
            <?php endif; ?>

            <?php if ( $details ) : ?>
            <section class="mdn-profile-section">
                <h2 class="mdn-profile-section-label">Details</h2>
                <div class="mdn-profile-details-grid">
                    <?php foreach ( $details as $label => $value ) : ?>
                    <div class="mdn-profile-detail">
                        <span class="mdn-profile-detail-label"><?php echo esc_html( strtoupper( $label ) ); ?></span>
                        <span class="mdn-profile-detail-value"><?php echo esc_html( $value ); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <hr class="mdn-divider">
            <?php endif; ?>

            <?php if ( $contacts ) : ?>
            <section class="mdn-profile-section">
                <h2 class="mdn-profile-section-label">Contact</h2>
                <div class="mdn-profile-contact">
                    <?php foreach ( $contacts as $label => $value ) : ?>
                    <div class="mdn-profile-contact-row">
                        <span class="mdn-profile-contact-label"><?php echo esc_html( $label ); ?></span>
                        <span class="mdn-profile-contact-value"><?php echo wp_kses( $value, array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <div class="mdn-profile-footer">
                <span class="mdn-profile-updated">Last updated <?php echo esc_html( $modified ); ?></span>
                <a href="mailto:<?php echo esc_attr( get_option( 'admin_email' ) ); ?>?subject=Suggest an edit: <?php echo rawurlencode( $title ); ?>" class="mdn-profile-suggest">Suggest an edit</a>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }
}
