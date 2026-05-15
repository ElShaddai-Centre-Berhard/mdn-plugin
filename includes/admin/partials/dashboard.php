<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap mdn-admin-wrap">
	<h1><?php esc_html_e( 'MDN Portal', 'mdn-plugin' ); ?></h1>
	<p class="mdn-admin-intro">
		<?php esc_html_e( 'Welcome to the Malaysia Diaspora Network plugin. Use the menu on the left to configure each module.', 'mdn-plugin' ); ?>
	</p>

	<div class="mdn-admin-cards">

		<div class="mdn-admin-card">
			<h2><?php esc_html_e( 'Member Management', 'mdn-plugin' ); ?></h2>
			<p><?php esc_html_e( 'Configure registration, login, email verification, admin approval, and roles.', 'mdn-plugin' ); ?></p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mdn-member-management' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Configure', 'mdn-plugin' ); ?>
			</a>
		</div>

		<div class="mdn-admin-card mdn-admin-card--disabled">
			<h2><?php esc_html_e( 'Member Portal', 'mdn-plugin' ); ?></h2>
			<p><?php esc_html_e( 'Blog, library, and resources sections for authenticated members.', 'mdn-plugin' ); ?></p>
			<span class="mdn-badge"><?php esc_html_e( 'Coming soon', 'mdn-plugin' ); ?></span>
		</div>

		<div class="mdn-admin-card mdn-admin-card--disabled">
			<h2><?php esc_html_e( 'Partner Directory', 'mdn-plugin' ); ?></h2>
			<p><?php esc_html_e( 'Searchable directory of partners with profiles and filters.', 'mdn-plugin' ); ?></p>
			<span class="mdn-badge"><?php esc_html_e( 'Coming soon', 'mdn-plugin' ); ?></span>
		</div>

		<div class="mdn-admin-card mdn-admin-card--disabled">
			<h2><?php esc_html_e( 'Library', 'mdn-plugin' ); ?></h2>
			<p><?php esc_html_e( 'Downloadable files organised by category with access control.', 'mdn-plugin' ); ?></p>
			<span class="mdn-badge"><?php esc_html_e( 'Coming soon', 'mdn-plugin' ); ?></span>
		</div>

		<div class="mdn-admin-card mdn-admin-card--disabled">
			<h2><?php esc_html_e( 'Resources', 'mdn-plugin' ); ?></h2>
			<p><?php esc_html_e( 'Curated links, guides, and external references — tagged and searchable.', 'mdn-plugin' ); ?></p>
			<span class="mdn-badge"><?php esc_html_e( 'Coming soon', 'mdn-plugin' ); ?></span>
		</div>

	</div>
</div>
