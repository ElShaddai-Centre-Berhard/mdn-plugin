<?php
/**
 * Plugin Name: Malaysia Diaspora Network
 * Description: Gutenberg-native member portal and partner directory for MDN.
 * Version:     1.0.0
 * Author:      ElShaddai Centre Berhad
 * Plugin URI:  https://github.com/ElShaddai-Centre-Berhad/mdn-plugin
 * License:     GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MDN_VERSION', '1.0.0' );
define( 'MDN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MDN_URL',  plugin_dir_url( __FILE__ ) );

require_once MDN_PATH . 'includes/admin/admin.php';
require_once MDN_PATH . 'includes/directory/directory.php';
require_once MDN_PATH . 'includes/member-management/member-management.php';
require_once MDN_PATH . 'includes/member-portal/member-portal.php';
require_once MDN_PATH . 'includes/blog/blog.php';
require_once MDN_PATH . 'includes/library/library.php';
require_once MDN_PATH . 'includes/resources/resources.php';

register_activation_hook( __FILE__, 'mdn_activate' );
function mdn_activate() {
    require_once MDN_PATH . 'includes/directory/class-mdn-partner-post-type.php';
    MDN_Partner_Post_Type::register();
    flush_rewrite_rules();
}

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'mdn-directory', MDN_URL . 'assets/css/directory.css', array(), MDN_VERSION );
} );
