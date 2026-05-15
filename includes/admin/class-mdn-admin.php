<?php
defined( 'ABSPATH' ) || exit;

class MDN_Admin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menus' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_post_mdn_create_pages', [ $this, 'handle_create_pages' ] );
	}

	// -------------------------------------------------------------------------
	// Menus
	// -------------------------------------------------------------------------

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

	// -------------------------------------------------------------------------
	// Settings
	// -------------------------------------------------------------------------

	public function register_settings(): void {

		// --- Registration options ---
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

		// --- Page setup options ---
		register_setting( 'mdn_member_management_settings', 'mdn_page_mode', [
			'type'              => 'string',
			'default'           => 'auto',
			'sanitize_callback' => [ $this, 'sanitize_page_mode' ],
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

		// --- Sections ---
		add_settings_section(
			'mdn_member_management_general',
			__( 'Registration', 'mdn-plugin' ),
			'__return_null',
			'mdn-member-management'
		);

		add_settings_section(
			'mdn_member_management_pages',
			__( 'Page Setup', 'mdn-plugin' ),
			[ $this, 'render_page_setup_description' ],
			'mdn-member-management'
		);

		// --- Registration fields ---
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

		// --- Page setup field (single field renders the whole UI) ---
		add_settings_field(
			'mdn_page_mode',
			__( 'Page Mode', 'mdn-plugin' ),
			[ $this, 'render_page_mode_field' ],
			'mdn-member-management',
			'mdn_member_management_pages'
		);
	}

	// -------------------------------------------------------------------------
	// Assets
	// -------------------------------------------------------------------------

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
		wp_enqueue_script(
			'mdn-admin',
			MDN_PLUGIN_URL . 'assets/js/admin.js',
			[],
			MDN_VERSION,
			true
		);
	}

	// -------------------------------------------------------------------------
	// Page renderers
	// -------------------------------------------------------------------------

	public function render_dashboard(): void {
		require MDN_PLUGIN_DIR . 'includes/admin/partials/dashboard.php';
	}

	public function render_member_management(): void {
		require MDN_PLUGIN_DIR . 'includes/admin/partials/member-management.php';
	}

	public function render_coming_soon(): void {
		require MDN_PLUGIN_DIR . 'includes/admin/partials/coming-soon.php';
	}

	// -------------------------------------------------------------------------
	// Field renderers
	// -------------------------------------------------------------------------

	public function render_page_setup_description(): void {
		echo '<p>' . esc_html__( 'Choose how the Registration, Login, and Profile pages are set up.', 'mdn-plugin' ) . '</p>';
	}

	public function render_page_mode_field(): void {
		$mode = get_option( 'mdn_page_mode', 'auto' );
		$pages = [
			'mdn_registration_page_id' => __( 'Registration Page', 'mdn-plugin' ),
			'mdn_login_page_id'        => __( 'Login Page', 'mdn-plugin' ),
			'mdn_profile_page_id'      => __( 'Profile Page', 'mdn-plugin' ),
		];
		?>
		<fieldset>
			<label>
				<input type="radio" name="mdn_page_mode" value="auto" <?php checked( $mode, 'auto' ); ?> />
				<?php esc_html_e( 'Auto-create pages', 'mdn-plugin' ); ?>
				<span class="description"> — <?php esc_html_e( 'plugin creates the pages for you', 'mdn-plugin' ); ?></span>
			</label>
			<br />
			<label>
				<input type="radio" name="mdn_page_mode" value="manual" <?php checked( $mode, 'manual' ); ?> />
				<?php esc_html_e( 'Assign existing pages', 'mdn-plugin' ); ?>
				<span class="description"> — <?php esc_html_e( 'choose pages you have already created', 'mdn-plugin' ); ?></span>
			</label>
		</fieldset>

		<?php /* Auto mode: status + create button */ ?>
		<div id="mdn-page-mode-auto" class="mdn-page-mode-section" style="<?php echo $mode !== 'auto' ? 'display:none' : ''; ?>">
			<table class="mdn-page-status-table">
				<?php foreach ( $pages as $option => $label ) :
					$page_id = (int) get_option( $option, 0 );
					$exists  = $page_id && get_post( $page_id ) && get_post_status( $page_id ) === 'publish';
				?>
				<tr>
					<td><?php echo esc_html( $label ); ?></td>
					<td>
						<?php if ( $exists ) : ?>
							<span class="mdn-status mdn-status--ok">&#10003; <?php esc_html_e( 'Created', 'mdn-plugin' ); ?></span>
							<a href="<?php echo esc_url( get_permalink( $page_id ) ); ?>" target="_blank" class="mdn-link">
								<?php esc_html_e( 'View', 'mdn-plugin' ); ?>
							</a>
							<a href="<?php echo esc_url( get_edit_post_link( $page_id ) ); ?>" class="mdn-link">
								<?php esc_html_e( 'Edit', 'mdn-plugin' ); ?>
							</a>
						<?php else : ?>
							<span class="mdn-status mdn-status--missing">&#9679; <?php esc_html_e( 'Not created', 'mdn-plugin' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>

			<?php
			$all_exist = true;
			foreach ( array_keys( $pages ) as $option ) {
				$id = (int) get_option( $option, 0 );
				if ( ! $id || ! get_post( $id ) || get_post_status( $id ) !== 'publish' ) {
					$all_exist = false;
					break;
				}
			}
			?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:12px;">
				<?php wp_nonce_field( 'mdn_create_pages', 'mdn_create_pages_nonce' ); ?>
				<input type="hidden" name="action" value="mdn_create_pages" />
				<button type="submit" class="button <?php echo $all_exist ? 'button-secondary' : 'button-primary'; ?>">
					<?php echo $all_exist ? esc_html__( 'Recreate Pages', 'mdn-plugin' ) : esc_html__( 'Create Pages', 'mdn-plugin' ); ?>
				</button>
				<?php if ( $all_exist ) : ?>
					<span class="description" style="margin-left:8px;"><?php esc_html_e( 'All pages already exist.', 'mdn-plugin' ); ?></span>
				<?php endif; ?>
			</form>
		</div>

		<?php /* Manual mode: page dropdowns */ ?>
		<div id="mdn-page-mode-manual" class="mdn-page-mode-section" style="<?php echo $mode !== 'manual' ? 'display:none' : ''; ?>">
			<table class="form-table mdn-inner-table">
				<?php foreach ( $pages as $option => $label ) :
					$current = (int) get_option( $option, 0 );
				?>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td>
						<?php wp_dropdown_pages( [
							'name'              => $option,
							'id'                => $option,
							'selected'          => $current,
							'show_option_none'  => __( '— Select a page —', 'mdn-plugin' ),
							'option_none_value' => 0,
						] ); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
		<?php
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

	// -------------------------------------------------------------------------
	// Auto page creation
	// -------------------------------------------------------------------------

	public function handle_create_pages(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'mdn-plugin' ) );
		}

		check_admin_referer( 'mdn_create_pages', 'mdn_create_pages_nonce' );

		$pages = [
			'mdn_registration_page_id' => [
				'title'   => __( 'Member Registration', 'mdn-plugin' ),
				'content' => '<!-- wp:paragraph --><p>' . __( 'Register for an account.', 'mdn-plugin' ) . '</p><!-- /wp:paragraph -->',
			],
			'mdn_login_page_id'        => [
				'title'   => __( 'Member Login', 'mdn-plugin' ),
				'content' => '<!-- wp:paragraph --><p>' . __( 'Log in to your account.', 'mdn-plugin' ) . '</p><!-- /wp:paragraph -->',
			],
			'mdn_profile_page_id'      => [
				'title'   => __( 'My Profile', 'mdn-plugin' ),
				'content' => '<!-- wp:paragraph --><p>' . __( 'View and edit your profile.', 'mdn-plugin' ) . '</p><!-- /wp:paragraph -->',
			],
		];

		foreach ( $pages as $option => $data ) {
			$existing_id = (int) get_option( $option, 0 );

			if ( $existing_id && get_post( $existing_id ) ) {
				continue;
			}

			$page_id = wp_insert_post( [
				'post_title'   => $data['title'],
				'post_content' => $data['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			] );

			if ( ! is_wp_error( $page_id ) ) {
				update_option( $option, $page_id );
			}
		}

		wp_safe_redirect( add_query_arg(
			[ 'page' => 'mdn-member-management', 'mdn_pages_created' => '1' ],
			admin_url( 'admin.php' )
		) );
		exit;
	}

	// -------------------------------------------------------------------------
	// Sanitisation
	// -------------------------------------------------------------------------

	public function sanitize_role( string $role ): string {
		return array_key_exists( $role, wp_roles()->get_names() ) ? $role : 'subscriber';
	}

	public function sanitize_page_mode( string $mode ): string {
		return in_array( $mode, [ 'auto', 'manual' ], true ) ? $mode : 'auto';
	}
}
