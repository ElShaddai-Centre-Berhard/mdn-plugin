<?php
/**
 * Plugin Name: Malaysia Diaspora Network
 * Description: Gutenberg-native member portal and partner directory for MDN.
 * Version:     1.1.0
 * Author:      ElShaddai Centre Berhad
 * Plugin URI:  https://github.com/ElShaddai-Centre-Berhad/mdn-plugin
 * License:     GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'MDN_VERSION', '1.1.0' );

// Update checker — pulls releases from GitHub
require_once plugin_dir_path( __FILE__ ) . 'plugin-update-checker/load-v5p6.php';
$mdn_update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
    'https://github.com/ElShaddai-Centre-Berhard/mdn-plugin',
    __FILE__,
    'mdn-plugin'
);
$mdn_update_checker->setBranch( 'main' );
define( 'MDN_PATH', plugin_dir_path( __FILE__ ) );
define( 'MDN_URL',  plugin_dir_url( __FILE__ ) );

require_once MDN_PATH . 'includes/admin/admin.php';
MDN_Admin::init();
require_once MDN_PATH . 'includes/directory/directory.php';
MDN_Directory::init();
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
