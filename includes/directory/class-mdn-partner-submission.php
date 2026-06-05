<?php
/**
 * Shortcode: [mdn_submit]
 * Front-end form for partners to submit their profile.
 * Submissions are saved as 'pending' posts awaiting admin approval.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class MDN_Partner_Submission {

    public static function init() {
        add_shortcode( 'mdn_submit', array( __CLASS__, 'render' ) );
    }

    public static function render() {
        ob_start();

        if ( isset( $_POST['mdn_submit_form'] ) ) {
            self::handle();
            return ob_get_clean();
        }

        $categories = get_terms( array( 'taxonomy' => 'partner_category', 'hide_empty' => false ) );
        ?>
        <div class="mdn-form-wrap">
            <form method="post" enctype="multipart/form-data" class="mdn-submit-form">
                <?php wp_nonce_field( 'mdn_submit_form', 'mdn_submit_nonce' ); ?>
                <input type="hidden" name="mdn_submit_form" value="1" />

                <div class="mdn-form-section">
                    <h3 class="mdn-form-heading">Organisation</h3>

                    <div class="mdn-field">
                        <label for="mdn_org_name">Organisation name <span class="mdn-required">*</span></label>
                        <input type="text" id="mdn_org_name" name="mdn_org_name" required
                            placeholder="e.g. MDN Malaysia" />
                    </div>

                    <div class="mdn-field">
                        <label for="mdn_category">Category</label>
                        <select id="mdn_category" name="mdn_category">
                            <option value="">— Select a category —</option>
                            <?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
                                foreach ( $categories as $term ) : ?>
                                    <option value="<?php echo esc_attr( $term->term_id ); ?>">
                                        <?php echo esc_html( $term->name ); ?>
                                    </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="mdn-field">
                        <label for="mdn_description">About / description <span class="mdn-required">*</span></label>
                        <textarea id="mdn_description" name="mdn_description" rows="5" required
                            placeholder="Describe your organisation's mission and work..."></textarea>
                    </div>

                    <div class="mdn-field">
                        <label for="mdn_logo">Logo or profile photo</label>
                        <input type="file" id="mdn_logo" name="mdn_logo" accept="image/*" />
                        <p class="mdn-field-hint">JPG or PNG, max 2MB. Appears on your directory card.</p>
                    </div>
                </div>

                <div class="mdn-form-section">
                    <h3 class="mdn-form-heading">Location</h3>

                    <div class="mdn-field">
                        <label for="mdn_location">Address or city</label>
                        <input type="text" id="mdn_location" name="mdn_location"
                            placeholder="e.g. Kuala Lumpur, Malaysia" />
                    </div>
                </div>

                <div class="mdn-form-section">
                    <h3 class="mdn-form-heading">Contact</h3>

                    <div class="mdn-field">
                        <label for="mdn_website">Website</label>
                        <input type="url" id="mdn_website" name="mdn_website" placeholder="https://example.com" />
                    </div>

                    <div class="mdn-field-row">
                        <div class="mdn-field">
                            <label for="mdn_phone">Phone</label>
                            <input type="text" id="mdn_phone" name="mdn_phone" placeholder="+60 3-XXXX XXXX" />
                        </div>
                        <div class="mdn-field">
                            <label for="mdn_email">Organisation email</label>
                            <input type="email" id="mdn_email" name="mdn_email" placeholder="hello@example.com" />
                        </div>
                    </div>
                </div>

                <div class="mdn-form-section">
                    <h3 class="mdn-form-heading">Your contact <span class="mdn-field-hint-inline">(for approval notifications)</span></h3>

                    <div class="mdn-field">
                        <label for="mdn_submitter_email">Your email <span class="mdn-required">*</span></label>
                        <input type="email" id="mdn_submitter_email" name="mdn_submitter_email" required
                            placeholder="your@email.com" />
                        <p class="mdn-field-hint">We'll notify you when your submission is approved.</p>
                    </div>
                </div>

                <div class="mdn-form-footer">
                    <p class="mdn-field-hint">
                        <span class="mdn-required">*</span> Required fields.
                        Submissions are reviewed before appearing in the directory.
                    </p>
                    <button type="submit" class="mdn-btn mdn-btn-full">Submit for review</button>
                </div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    private static function handle() {
        if ( ! isset( $_POST['mdn_submit_nonce'] ) ||
             ! wp_verify_nonce( $_POST['mdn_submit_nonce'], 'mdn_submit_form' ) ) {
            echo '<p class="mdn-message mdn-error">Security check failed. Please refresh and try again.</p>';
            return;
        }

        $name        = sanitize_text_field( $_POST['mdn_org_name']    ?? '' );
        $description = sanitize_textarea_field( $_POST['mdn_description'] ?? '' );

        if ( empty( $name ) || empty( $description ) ) {
            echo '<p class="mdn-message mdn-error">Please fill in all required fields.</p>';
            return;
        }

        $post_id = wp_insert_post( array(
            'post_title'   => $name,
            'post_content' => $description,
            'post_excerpt' => wp_trim_words( $description, 25 ),
            'post_type'    => 'partner',
            'post_status'  => 'pending',
        ) );

        if ( is_wp_error( $post_id ) ) {
            echo '<p class="mdn-message mdn-error">Something went wrong. Please try again.</p>';
            return;
        }

        if ( ! empty( $_POST['mdn_category'] ) ) {
            wp_set_post_terms( $post_id, array( intval( $_POST['mdn_category'] ) ), 'partner_category' );
        }

        $meta_keys = array( 'mdn_location', 'mdn_lat', 'mdn_lng', 'mdn_website', 'mdn_phone', 'mdn_email', 'mdn_submitter_email' );
        foreach ( $meta_keys as $key ) {
            if ( ! empty( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, '_' . $key, sanitize_text_field( $_POST[ $key ] ) );
            }
        }

        if ( ! empty( $_FILES['mdn_logo']['name'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            $attachment_id = media_handle_upload( 'mdn_logo', $post_id );
            if ( ! is_wp_error( $attachment_id ) ) {
                set_post_thumbnail( $post_id, $attachment_id );
            }
        }

        do_action( 'mdn_new_submission', $post_id );

        echo '
        <div class="mdn-message mdn-success">
            <strong>Thank you for submitting!</strong>
            Your submission is under review and will appear in the directory once approved.
        </div>';
    }
}
