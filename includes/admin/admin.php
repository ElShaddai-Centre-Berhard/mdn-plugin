<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MDN_Admin {

    public static function init() {
        add_action( 'admin_menu',        array( __CLASS__, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin' ) );
    }

    public static function register_menus() {
        add_menu_page(
            'MDN Portal',
            'MDN Portal',
            'manage_options',
            'mdn-dashboard',
            array( __CLASS__, 'page_dashboard' ),
            'dashicons-networking',
            30
        );

        add_submenu_page(
            'mdn-dashboard',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'mdn-dashboard',
            array( __CLASS__, 'page_dashboard' )
        );

        add_submenu_page(
            'mdn-dashboard',
            'Registration Settings',
            'Registration',
            'manage_options',
            'mdn-registration',
            array( __CLASS__, 'page_registration' )
        );

        add_submenu_page(
            'mdn-dashboard',
            'Member Portal',
            'Member Portal',
            'manage_options',
            'mdn-member-portal',
            array( __CLASS__, 'page_coming_soon' )
        );
    }

    public static function page_dashboard() {
        require MDN_PATH . 'includes/admin/partials/dashboard.php';
    }

    public static function page_registration() {
        require MDN_PATH . 'includes/admin/partials/registration.php';
    }

    public static function page_coming_soon() {
        require MDN_PATH . 'includes/admin/partials/coming-soon.php';
    }

    public static function enqueue_admin( $hook ) {
        if ( strpos( $hook, 'mdn-' ) === false ) return;
        wp_enqueue_style( 'mdn-admin', MDN_URL . 'assets/css/admin.css', array(), MDN_VERSION );
    }
}
