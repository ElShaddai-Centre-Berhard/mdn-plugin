<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap mdn-admin-wrap">
	<h1><?php esc_html_e( 'Member Management', 'mdn-plugin' ); ?></h1>

	<?php settings_errors( 'mdn_member_management_settings' ); ?>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'mdn_member_management_settings' );
		do_settings_sections( 'mdn-member-management' );
		submit_button();
		?>
	</form>
</div>
