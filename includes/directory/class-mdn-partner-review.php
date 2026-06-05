<?php
/**
 * Admin review page for partner submissions.
 * Handles approve, reject (delist), and email notifications.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class MDN_Partner_Review {

    public static function init() {
        add_action( 'mdn_new_submission', array( __CLASS__, 'notify_admin' ) );
        add_action( 'admin_menu',         array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_init',         array( __CLASS__, 'handle_actions' ) );
    }

    // ── Email admin on new submission ─────────────────────────────────────────

    public static function notify_admin( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'partner' || $post->post_status !== 'pending' ) return;

        $admin_email = get_option( 'admin_email' );
        $site_name   = get_bloginfo( 'name' );
        $review_url  = admin_url( 'admin.php?page=mdn-review' );

        $subject  = "[{$site_name}] New partner submission: {$post->post_title}";
        $message  = "A new partner has been submitted and is waiting for your review.\n\n";
        $message .= "Organisation: {$post->post_title}\n";
        $message .= "Submitted:    " . get_the_date( 'j F Y, g:i a', $post ) . "\n\n";
        $message .= "Review it here:\n{$review_url}\n\n";
        $message .= "— {$site_name}";

        wp_mail( $admin_email, $subject, $message );
    }

    // ── Admin menu ────────────────────────────────────────────────────────────

    public static function add_menu() {
        add_submenu_page(
            'mdn-dashboard',
            'Review Submissions',
            'Review Submissions',
            'manage_options',
            'mdn-review',
            array( __CLASS__, 'page_html' )
        );
    }

    // ── Handle approve / delist actions ──────────────────────────────────────

    public static function handle_actions() {
        if ( ! isset( $_GET['mdn_action'], $_GET['mdn_post'], $_GET['_wpnonce'] ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;

        $action  = sanitize_key( $_GET['mdn_action'] );
        $post_id = intval( $_GET['mdn_post'] );

        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'mdn_review_' . $post_id ) ) {
            wp_die( 'Security check failed.' );
        }

        if ( $action === 'approve' ) {
            wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
            self::notify_org_approved( $post_id );
            $notice = 'approved';
        } elseif ( $action === 'delist' ) {
            wp_update_post( array( 'ID' => $post_id, 'post_status' => 'trash' ) );
            $notice = 'delisted';
        } else {
            return;
        }

        wp_redirect( admin_url( 'admin.php?page=mdn-review&notice=' . $notice ) );
        exit;
    }

    // ── Email submitter on approval ───────────────────────────────────────────

    private static function notify_org_approved( $post_id ) {
        $submitter_email = get_post_meta( $post_id, '_mdn_submitter_email', true );
        $org_email       = get_post_meta( $post_id, '_mdn_email', true );
        $email           = $submitter_email ?: $org_email;

        if ( ! $email ) return;

        $site_name   = get_bloginfo( 'name' );
        $org_name    = get_the_title( $post_id );
        $profile_url = get_permalink( $post_id );

        $subject  = "Your listing on {$site_name} has been approved!";
        $message  = "Hi,\n\n";
        $message .= "Great news — {$org_name} has been approved and is now listed in the {$site_name} directory.\n\n";
        $message .= "View your profile:\n{$profile_url}\n\n";
        $message .= "Thank you for being part of our directory.\n\n";
        $message .= "— The {$site_name} team";

        wp_mail( $email, $subject, $message );
    }

    // ── Review page HTML ──────────────────────────────────────────────────────

    public static function page_html() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $notice = $_GET['notice'] ?? '';

        $pending = get_posts( array(
            'post_type'      => 'partner',
            'post_status'    => 'pending',
            'posts_per_page' => 50,
            'orderby'        => 'date',
            'order'          => 'ASC',
        ) );

        $recent = get_posts( array(
            'post_type'      => 'partner',
            'post_status'    => array( 'publish', 'trash' ),
            'posts_per_page' => 5,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ) );
        ?>
        <div class="wrap" style="max-width:900px;">
            <h1 style="display:flex;align-items:center;gap:10px;">
                Review Submissions
                <?php if ( count( $pending ) > 0 ) : ?>
                    <span style="background:#d63638;color:#fff;font-size:13px;font-weight:600;
                        border-radius:20px;padding:2px 10px;">
                        <?php echo count( $pending ); ?> pending
                    </span>
                <?php endif; ?>
            </h1>

            <?php if ( $notice === 'approved' ) : ?>
                <div class="notice notice-success is-dismissible"><p>Submission approved and published.</p></div>
            <?php elseif ( $notice === 'delisted' ) : ?>
                <div class="notice notice-warning is-dismissible"><p>Submission rejected and moved to trash.</p></div>
            <?php endif; ?>

            <?php if ( empty( $pending ) ) : ?>
                <div style="background:#fff;border:1px solid #e0e0e0;border-radius:8px;
                    padding:40px;text-align:center;margin-top:20px;">
                    <p style="font-size:16px;color:#666;margin:0;">No pending submissions. All caught up!</p>
                </div>
            <?php else : ?>

            <p style="color:#666;margin-bottom:16px;">
                Approving a submission publishes the profile immediately and notifies the organisation by email.
            </p>

            <?php foreach ( $pending as $post ) :
                $id              = $post->ID;
                $location        = get_post_meta( $id, '_mdn_location', true );
                $website         = get_post_meta( $id, '_mdn_website',  true );
                $email           = get_post_meta( $id, '_mdn_email',    true );
                $submitter_email = get_post_meta( $id, '_mdn_submitter_email', true );
                $phone           = get_post_meta( $id, '_mdn_phone',    true );
                $terms           = get_the_terms( $id, 'partner_category' );
                $category        = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0]->name : '—';
                $submitted       = get_the_date( 'j M Y, g:i a', $post );

                $approve_url = wp_nonce_url(
                    admin_url( 'admin.php?page=mdn-review&mdn_action=approve&mdn_post=' . $id ),
                    'mdn_review_' . $id
                );
                $delist_url = wp_nonce_url(
                    admin_url( 'admin.php?page=mdn-review&mdn_action=delist&mdn_post=' . $id ),
                    'mdn_review_' . $id
                );
                $edit_url = get_edit_post_link( $id );
            ?>
            <div style="background:#fff;border:1px solid #e0e0e0;border-radius:8px;
                margin-bottom:16px;overflow:hidden;">

                <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:16px 20px;border-bottom:1px solid #f0f0f0;gap:12px;flex-wrap:wrap;">
                    <div>
                        <strong style="font-size:16px;"><?php echo esc_html( $post->post_title ); ?></strong>
                        <span style="margin-left:10px;font-size:12px;color:#999;">
                            Submitted <?php echo esc_html( $submitted ); ?>
                        </span>
                    </div>
                    <div style="display:flex;gap:8px;flex-shrink:0;">
                        <a href="<?php echo esc_url( $approve_url ); ?>"
                            class="button button-primary"
                            onclick="return confirm('Approve and publish <?php echo esc_js( $post->post_title ); ?>?')">
                            Approve
                        </a>
                        <a href="<?php echo esc_url( $edit_url ); ?>" class="button">Edit</a>
                        <a href="<?php echo esc_url( $delist_url ); ?>"
                            class="button"
                            style="color:#d63638;"
                            onclick="return confirm('Reject and trash this submission?')">
                            Reject
                        </a>
                    </div>
                </div>

                <div style="padding:16px 20px;display:grid;
                    grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px 20px;">
                    <?php
                    $meta_items = array(
                        'Category'        => $category,
                        'Location'        => $location ?: '—',
                        'Org email'       => $email ?: '—',
                        'Submitter email' => $submitter_email ?: '—',
                        'Phone'           => $phone ?: '—',
                        'Website'         => $website ? '<a href="' . esc_url( $website ) . '" target="_blank">' . esc_html( $website ) . '</a>' : '—',
                    );
                    foreach ( $meta_items as $label => $value ) : ?>
                        <div>
                            <div style="font-size:10px;font-weight:600;text-transform:uppercase;
                                letter-spacing:.05em;color:#999;margin-bottom:3px;">
                                <?php echo esc_html( $label ); ?>
                            </div>
                            <div style="font-size:13px;color:#333;">
                                <?php echo wp_kses( $value, array( 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ( $post->post_content ) : ?>
                <div style="padding:0 20px 16px;border-top:1px solid #f0f0f0;margin-top:4px;">
                    <div style="font-size:10px;font-weight:600;text-transform:uppercase;
                        letter-spacing:.05em;color:#999;margin:12px 0 6px;">Description</div>
                    <p style="font-size:13px;color:#555;margin:0;line-height:1.6;">
                        <?php echo esc_html( wp_trim_words( $post->post_content, 40 ) ); ?>
                    </p>
                </div>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if ( ! empty( $recent ) ) : ?>
            <h2 style="margin-top:32px;font-size:15px;color:#666;">Recent activity</h2>
            <table class="widefat striped" style="border-radius:8px;overflow:hidden;">
                <thead>
                    <tr>
                        <th>Organisation</th>
                        <th>Status</th>
                        <th>Last updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $recent as $post ) :
                        $status_label = $post->post_status === 'publish' ? 'Published' : 'Trashed';
                        $status_color = $post->post_status === 'publish' ? '#2d6a35' : '#999';
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>">
                                <?php echo esc_html( $post->post_title ); ?>
                            </a>
                        </td>
                        <td style="color:<?php echo esc_attr( $status_color ); ?>;font-weight:500;">
                            <?php echo esc_html( $status_label ); ?>
                        </td>
                        <td style="color:#999;font-size:13px;">
                            <?php echo esc_html( get_the_modified_date( 'j M Y', $post ) ); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

        </div>
        <?php
    }
}
