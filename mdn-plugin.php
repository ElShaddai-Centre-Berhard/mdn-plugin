<?php
/*
 * Plugin Name:       Malaysia Diaspora Network
 * Plugin URI:        https://github.com/ElShaddai-Centre-Berhard/mdn-plugin
 * Description:       A plugin built with the idea of making a simple management tool for Malaysia Diaspora Network Directory
 * Version:           1.0.0
 * Author:            James Wobil
 * Requires at least: 6.4
 * Author URI:        https://github.com/wobiljames
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$mdn_update_checker = PucFactory::buildUpdateChecker(
	'https://github.com/ElShaddai-Centre-Berhard/mdn-plugin/',
	__FILE__,
	'mdn-plugin'
);

$mdn_update_checker->setBranch( 'main' );
$mdn_update_checker->getVcsApi()->enableReleaseAssets();
