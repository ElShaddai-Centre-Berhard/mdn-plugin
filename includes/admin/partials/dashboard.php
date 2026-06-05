<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1>MDN Portal</h1>
    <p class="description">Malaysia Diaspora Network — manage your portal modules below.</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-top:24px;">

        <?php
        $modules = array(
            array(
                'title'  => 'Partner Directory',
                'desc'   => 'Manage partner profiles, approve submissions, and feature partners.',
                'url'    => admin_url( 'edit.php?post_type=partner' ),
                'status' => 'active',
            ),
            array(
                'title'  => 'Member Portal',
                'desc'   => 'Gated area for members — login, profile, and exclusive content.',
                'url'    => admin_url( 'admin.php?page=mdn-member-portal' ),
                'status' => 'coming-soon',
            ),
            array(
                'title'  => 'Registration',
                'desc'   => 'Configure front-end registration settings and required fields.',
                'url'    => admin_url( 'admin.php?page=mdn-registration' ),
                'status' => 'active',
            ),
        );
        foreach ( $modules as $m ) :
            $badge = $m['status'] === 'active'
                ? '<span style="font-size:11px;background:#d4edda;color:#155724;padding:2px 8px;border-radius:20px;">Active</span>'
                : '<span style="font-size:11px;background:#f8d7da;color:#721c24;padding:2px 8px;border-radius:20px;">Coming soon</span>';
        ?>
        <div style="background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                <strong style="font-size:15px;"><?php echo esc_html( $m['title'] ); ?></strong>
                <?php echo $badge; ?>
            </div>
            <p style="font-size:13px;color:#666;margin:0 0 14px;"><?php echo esc_html( $m['desc'] ); ?></p>
            <a href="<?php echo esc_url( $m['url'] ); ?>" class="button button-secondary">Manage</a>
        </div>
        <?php endforeach; ?>

    </div>
</div>
