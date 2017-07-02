<?php
/**
 * Invisible reCAPTCHA integration for the WordPress login.
 *
 * @package recaptcha-for-wp
 */

/**
 * Plugin Name: reCAPTCHA for WordPress
 * Plugin URI: https://github.com/ssnepenthe/recaptcha-for-wp
 * Description: Invisible reCAPTCHA integration for the WordPress login.
 * Version: 0.1.0
 * Author: Ryan McLaughlin
 * Author URI: https://github.com/ssnepenthe
 * License: GPL-2.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Verify plugin requirements are met and bootstrap accordingly.
 *
 * @return void
 */
function _rfw_bootstrap() {
	$checker = new WP_Requirements\Plugin_Checker( 'reCAPTCHA for WordPress', __FILE__ );

	// Short array syntax.
	$checker->php_at_least( '5.4' );

	// Array of args as second param to "register_setting()".
	$checker->wp_at_least( '4.7' );

	if ( ! $checker->requirements_met() ) {
		$checker->deactivate_and_notify();
		return;
	}

	$dir = plugin_dir_path( __FILE__ );

	require_once $dir . 'inc/debug.php';
	require_once $dir . 'inc/options.php';
	require_once $dir . 'inc/plugin.php';
	require_once $dir . 'inc/recaptcha.php';
	require_once $dir . 'inc/template-tags.php';

	add_action( 'plugins_loaded', 'Recaptcha_For_Wp\\initialize' );
}

/**
 * Require a file (once) if it exists.
 *
 * @param  string $file Filepath.
 *
 * @return void
 */
function _rfw_require_if_exists( $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

_rfw_require_if_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' );
_rfw_bootstrap();
