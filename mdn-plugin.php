<?php
/*
 * Plugin Name:       Malaysia Diaspora Network
 * Plugin URI:        https://github.com/ElShaddai-Centre-Berhard/mdn-plugin
 * Description:       A plugin built with the idea of making a simple management tool for Malaysia Diaspora Network Directory
 * Version:           1.0.3
 * Author:            James Wobil
 * Requires at least: 6.4
 * Author URI:        https://github.com/wobiljames
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MDN_VERSION', '1.0.3' );
define( 'MDN_PLUGIN_FILE', __FILE__ );
define( 'MDN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MDN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$mdn_update_checker = PucFactory::buildUpdateChecker(
	'https://github.com/ElShaddai-Centre-Berhard/mdn-plugin/',
	__FILE__,
	'mdn-plugin'
);

$mdn_update_checker->setBranch( 'main' );
$mdn_update_checker->getVcsApi()->enableReleaseAssets();

if ( defined( 'MDN_GITHUB_TOKEN' ) && MDN_GITHUB_TOKEN ) {
	$mdn_update_checker->setAuthentication( MDN_GITHUB_TOKEN );
}

if ( is_admin() ) {
	require_once MDN_PLUGIN_DIR . 'includes/admin/class-mdn-admin.php';
	new MDN_Admin();
}