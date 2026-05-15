<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap mdn-admin-wrap">
	<h1><?php esc_html_e( 'Member Management', 'mdn-plugin' ); ?></h1>

	<?php if ( isset( $_GET['mdn_pages_created'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Pages created successfully.', 'mdn-plugin' ); ?></p>
		</div>
	<?php endif; ?>

	<?php settings_errors( 'mdn_member_management_settings' ); ?>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'mdn_member_management_settings' );
		do_settings_sections( 'mdn-member-management' );
		submit_button();
		?>
	</form>
</div>
