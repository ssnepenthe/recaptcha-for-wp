<?php
/**
 * The plugin uninstall script.
 *
 * @package recaptcha-for-wp
 */

/**
 * The plugin uninstaller.
 *
 * @return void
 */
function _rfw_uninstall() {
	$options = [
		'rfw_login',
		'rfw_lostpassword',
		'rfw_registration',
		'rfw_secret_key',
		'rfw_site_key',
	];

	foreach ( $options as $option ) {
		delete_option( $option );
	}
}

_rfw_uninstall();
