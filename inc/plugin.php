<?php
/**
 * Core plugin functionality.
 *
 * @package recaptcha-for-wp
 */

namespace Recaptcha_For_Wp;

/**
 * Attempt to verify the reCAPTCHA response provided by the current request and add errors to a
 * WP_Error object if there are any problems in doing so.
 *
 * @param  \WP_Error $errors Error bag.
 *
 * @return \WP_Error
 */
function add_verification_errors( $errors ) {
	list( $token, $remote_ip ) = request_values();

	if ( ! $token ) {
		$errors->add(
			'missing_recaptcha',
			'<strong>ERROR</strong>: Missing reCAPTCHA response. Are you a bot?'
		);
	} elseif ( ! verify_response( $token, $remote_ip ) ) {
		$errors->add(
			'invalid_recaptcha',
			'<strong>ERROR</strong>: Invalid reCAPTCHA response. Are you a bot?'
		);
	}

	return $errors;
}

/**
 * Hook the plugin functionality in to WordPress.
 *
 * @return void
 */
function initialize() {
	// @todo Earlier priority for captcha verifications?
	// Also register settings on login page so we have access to defaults.
	add_action( 'login_init', __NAMESPACE__ . '\\register_settings' );
	add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );

	add_action( 'admin_menu', __NAMESPACE__ . '\\register_menu' );
	add_action( 'admin_init', __NAMESPACE__ . '\\register_menu_content' );

	add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\register_scripts' );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\register_scripts' );
	add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts' );
	add_filter( 'script_loader_tag', __NAMESPACE__ . '\\async_and_defer', 10, 2 );

	add_action( 'lostpassword_post', __NAMESPACE__ . '\\lostpassword_handler' );
	add_filter( 'registration_errors', __NAMESPACE__ . '\\registration_handler' );
	add_filter( 'wp_authenticate_user', __NAMESPACE__ . '\\login_handler', 10, 2 );

	add_action( 'admin_notices', __NAMESPACE__ . '\\notify_when_options_not_set' );

	foreach ( [ 'login', 'lostpassword', 'registration', 'secret_key', 'site_key' ] as $option ) {
		add_filter(
			"pre_option_rfw_{$option}",
			__NAMESPACE__ . '\\use_constants_when_defined',
			10,
			2
		);
	}
}

/**
 * Ensure that a valid reCAPTCHA response is present for login attempts.
 *
 * @param  \WP_Error|\WP_User $user     Error object for failed login, user object otherwise.
 * @param  string             $password Password to check against user.
 *
 * @return \WP_Error|\WP_User
 */
function login_handler( $user, $password ) {
	if ( ! keys_set() ) {
		debug( 'Login reCAPTCHA disabled because one of secret or site keys is not set' );
		return $user;
	}

	if ( ! is_enabled_for_login() ) {
		debug( 'Login reCAPTCHA disabled by user settings' );
		return $user;
	}

	$errors = add_verification_errors( new \WP_Error );

	if ( $errors->get_error_code() ) {
		return $errors;
	}

	return $user;
}

/**
 * Ensure that a valid reCAPTCHA response is present for lostpassword attempts.
 *
 * @param  \WP_Error $errors Error bag.
 *
 * @return void
 */
function lostpassword_handler( $errors ) {
	if ( ! keys_set() ) {
		debug( 'Lostpassword reCAPTCHA disabled because one of secret or site keys is not set' );
		return;
	}

	if ( ! is_enabled_for_lostpassword() ) {
		debug( 'Lostpassword reCAPTCHA disabled by user settings' );
		return;
	}

	add_verification_errors( $errors );
}

/**
 * Ensure that a valid reCAPTCHA response is present for registration attempts.
 *
 * @param  \WP_Error $errors Error bag.
 *
 * @return \WP_Error
 */
function registration_handler( $errors ) {
	if ( ! keys_set() ) {
		debug( 'Registration reCAPTCHA disabled because one of secret or site keys is not set' );
		return;
	}

	if ( ! is_enabled_for_registration() ) {
		debug( 'Registration reCAPTCHA disabled by user settings' );
		return $errors;
	}

	return add_verification_errors( $errors );
}
