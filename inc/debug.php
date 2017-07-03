<?php
/**
 * Plugin debugging functionality.
 *
 * @package recaptcha-for-wp
 */

namespace Recaptcha_For_Wp;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Check whether debugging is enabled for logging purposes.
 *
 * @return boolean
 */
function debug_enabled() {
	return defined( 'RFW_DEBUG' ) && RFW_DEBUG
		&& defined( 'WP_DEBUG' ) && WP_DEBUG
		&& defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
}

/**
 * Print a message in debug.log when debugging is enabled.
 *
 * @param  string $message Message to print.
 *
 * @return void
 */
function debug( $message ) {
	if ( ! debug_enabled() ) {
		return;
	}

	error_log( sprintf( '[reCAPTCHA for WordPress] %s', $message ) );
}
