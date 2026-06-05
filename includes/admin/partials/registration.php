<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1>Registration Settings</h1>
    <?php settings_errors( 'mdn_registration' ); ?>
    <form method="post" action="options.php">
        <?php
        settings_fields( 'mdn_registration' );
        do_settings_sections( 'mdn-registration' );
        submit_button();
        ?>
    </form>
</div>
