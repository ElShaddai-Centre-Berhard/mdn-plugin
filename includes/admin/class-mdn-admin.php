<?php
defined( 'ABSPATH' ) || exit;

class MDN_Admin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menus' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function register_menus(): void {
		add_menu_page(
			__( 'MDN Portal', 'mdn-plugin' ),
			__( 'MDN Portal', 'mdn-plugin' ),
			'manage_options',
			'mdn-portal',
			[ $this, 'render_dashboard' ],
			'dashicons-groups',
			3
		);

		add_submenu_page(
			'mdn-portal',
			__( 'Dashboard', 'mdn-plugin' ),
			__( 'Dashboard', 'mdn-plugin' ),
			'manage_options',
			'mdn-portal',
			[ $this, 'render_dashboard' ]
		);

		add_submenu_page(
			'mdn-portal',
			__( 'Member Management', 'mdn-plugin' ),
			__( 'Member Management', 'mdn-plugin' ),
			'manage_options',
			'mdn-member-management',
			[ $this, 'render_member_management' ]
		);

		add_submenu_page(
			'mdn-portal',
			__( 'Member Portal', 'mdn-plugin' ),
			__( 'Member Portal', 'mdn-plugin' ),
			'manage_options',
			'mdn-member-portal',
			[ $this, 'render_coming_soon' ]
		);

		add_submenu_page(
			'mdn-portal',
			__( 'Partner Directory', 'mdn-plugin' ),
			__( 'Partner Directory', 'mdn-plugin' ),
			'manage_options',
			'mdn-directory',
			[ $this, 'render_coming_soon' ]
		);

		add_submenu_page(
			'mdn-portal',
			__( 'Library', 'mdn-plugin' ),
			__( 'Library', 'mdn-plugin' ),
			'manage_options',
			'mdn-library',
			[ $this, 'render_coming_soon' ]
		);

		add_submenu_page(
			'mdn-portal',
			__( 'Resources', 'mdn-plugin' ),
			__( 'Resources', 'mdn-plugin' ),
			'manage_options',
			'mdn-resources',
			[ $this, 'render_coming_soon' ]
		);
	}

	public function register_settings(): void {
		register_setting( 'mdn_member_management_settings', 'mdn_registration_enabled', [
			'type'              => 'boolean',
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		] );

		register_setting( 'mdn_member_management_settings', 'mdn_email_verification', [
			'type'              => 'boolean',
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		] );

		register_setting( 'mdn_member_management_settings', 'mdn_admin_approval', [
			'type'              => 'boolean',
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		] );

		register_setting( 'mdn_member_management_settings', 'mdn_default_role', [
			'type'              => 'string',
			'default'           => 'subscriber',
			'sanitize_callback' => [ $this, 'sanitize_role' ],
		] );

		register_setting( 'mdn_member_management_settings', 'mdn_registration_page_id', [
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'absint',
		] );

		register_setting( 'mdn_member_management_settings', 'mdn_login_page_id', [
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'absint',
		] );

		register_setting( 'mdn_member_management_settings', 'mdn_profile_page_id', [
			'type'              => 'integer',
			'default'           => 0,
			'sanitize_callback' => 'absint',
		] );

		add_settings_section(
			'mdn_member_management_general',
			__( 'Registration', 'mdn-plugin' ),
			'__return_null',
			'mdn-member-management'
		);

		add_settings_section(
			'mdn_member_management_pages',
			__( 'Page Assignments', 'mdn-plugin' ),
			[ $this, 'render_pages_section_description' ],
			'mdn-member-management'
		);

		add_settings_field(
			'mdn_registration_enabled',
			__( 'Enable Registration', 'mdn-plugin' ),
			[ $this, 'render_checkbox_field' ],
			'mdn-member-management',
			'mdn_member_management_general',
			[
				'label_for'   => 'mdn_registration_enabled',
				'option_name' => 'mdn_registration_enabled',
				'description' => __( 'Allow new users to register on the front end.', 'mdn-plugin' ),
			]
		);

		add_settings_field(
			'mdn_email_verification',
			__( 'Email Verification', 'mdn-plugin' ),
			[ $this, 'render_checkbox_field' ],
			'mdn-member-management',
			'mdn_member_management_general',
			[
				'label_for'   => 'mdn_email_verification',
				'option_name' => 'mdn_email_verification',
				'description' => __( 'Require members to verify their email before accessing the portal.', 'mdn-plugin' ),
			]
		);

		add_settings_field(
			'mdn_admin_approval',
			__( 'Admin Approval', 'mdn-plugin' ),
			[ $this, 'render_checkbox_field' ],
			'mdn-member-management',
			'mdn_member_management_general',
			[
				'label_for'   => 'mdn_admin_approval',
				'option_name' => 'mdn_admin_approval',
				'description' => __( 'Require admin approval before a new account is activated.', 'mdn-plugin' ),
			]
		);

		add_settings_field(
			'mdn_default_role',
			__( 'Default Role', 'mdn-plugin' ),
			[ $this, 'render_role_select_field' ],
			'mdn-member-management',
			'mdn_member_management_general',
			[
				'label_for'   => 'mdn_default_role',
				'option_name' => 'mdn_default_role',
			]
		);

		add_settings_field(
			'mdn_registration_page_id',
			__( 'Registration Page', 'mdn-plugin' ),
			[ $this, 'render_page_select_field' ],
			'mdn-member-management',
			'mdn_member_management_pages',
			[
				'label_for'   => 'mdn_registration_page_id',
				'option_name' => 'mdn_registration_page_id',
			]
		);

		add_settings_field(
			'mdn_login_page_id',
			__( 'Login Page', 'mdn-plugin' ),
			[ $this, 'render_page_select_field' ],
			'mdn-member-management',
			'mdn_member_management_pages',
			[
				'label_for'   => 'mdn_login_page_id',
				'option_name' => 'mdn_login_page_id',
			]
		);

		add_settings_field(
			'mdn_profile_page_id',
			__( 'Profile Page', 'mdn-plugin' ),
			[ $this, 'render_page_select_field' ],
			'mdn-member-management',
			'mdn_member_management_pages',
			[
				'label_for'   => 'mdn_profile_page_id',
				'option_name' => 'mdn_profile_page_id',
			]
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'mdn-' ) === false ) {
			return;
		}
		wp_enqueue_style(
			'mdn-admin',
			MDN_PLUGIN_URL . 'assets/css/admin.css',
			[],
			MDN_VERSION
		);
	}

	// --- Page renderers ---

	public function render_dashboard(): void {
		require MDN_PLUGIN_DIR . 'includes/admin/partials/dashboard.php';
	}

	public function render_member_management(): void {
		require MDN_PLUGIN_DIR . 'includes/admin/partials/member-management.php';
	}

	public function render_coming_soon(): void {
		require MDN_PLUGIN_DIR . 'includes/admin/partials/coming-soon.php';
	}

	// --- Field renderers ---

	public function render_pages_section_description(): void {
		echo '<p>' . esc_html__( 'Assign pages to portal functions. Each page should contain the corresponding MDN block.', 'mdn-plugin' ) . '</p>';
	}

	public function render_checkbox_field( array $args ): void {
		$value = get_option( $args['option_name'] );
		printf(
			'<input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s />',
			esc_attr( $args['option_name'] ),
			checked( 1, $value, false )
		);
		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	public function render_role_select_field( array $args ): void {
		$current = get_option( $args['option_name'], 'subscriber' );
		$roles   = wp_roles()->get_names();
		printf( '<select id="%1$s" name="%1$s">', esc_attr( $args['option_name'] ) );
		foreach ( $roles as $role_key => $role_name ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $role_key ),
				selected( $current, $role_key, false ),
				esc_html( $role_name )
			);
		}
		echo '</select>';
	}

	public function render_page_select_field( array $args ): void {
		$current = (int) get_option( $args['option_name'], 0 );
		wp_dropdown_pages( [
			'name'              => $args['option_name'],
			'id'                => $args['option_name'],
			'selected'          => $current,
			'show_option_none'  => __( '— Select a page —', 'mdn-plugin' ),
			'option_none_value' => 0,
		] );
	}

	// --- Sanitisation ---

	public function sanitize_role( string $role ): string {
		return array_key_exists( $role, wp_roles()->get_names() ) ? $role : 'subscriber';
	}
}
