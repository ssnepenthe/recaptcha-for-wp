<?php
/**
 * The reCAPTCHA-specific plugin functionality.
 *
 * @package recaptcha-for-wp
 */

namespace Recaptcha_For_Wp;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Add a plugin-specific body class to the login page for actions where reCAPTCHA is enabled.
 *
 * @param array  $classes List of body classes.
 * @param string $action  The current login action.
 *
 * @return array
 */
function add_overlay_body_class( $classes, $action ) {
	if ( ! is_enabled_for_action( $action ) ) {
		return $classes;
	}

	return array_merge( $classes, [ 'rfw-has-overlay' ] );
}

/**
 * Add required inline styles for the <noscript> overlay.
 *
 * @return void
 */
function add_overlay_css() {
	$action = get_current_login_action();

	if ( ! is_enabled_for_action( $action ) ) {
		return;
	}

	// Minified version of assets/overlay.css.
	$css = '.rfw-overlay,.rfw-overlay .rfw-overlay-background{bottom:0;left:0;position:fixed;right:0;top:0}.rfw-overlay .dashicons{font-size:32px;height:32px;width:32px}.rfw-overlay .rfw-overlay-background{background-color:rgba(30,30,30,.5)}.rfw-overlay .rfw-overlay-content{background-color:#fff;border-radius:3px;box-shadow:0 1px 3px rgba(0,0,0,.13);box-sizing:border-box;height:160px;left:50%;margin-left:-160px;margin-top:-80px;padding:30px 20px;position:relative;text-align:center;top:50%;width:320px}.rfw-overlay p{margin-bottom:1em}';

	wp_add_inline_style( 'login', $css );
}

/**
 * Add async and defer to the <script> per the recommendation of the reCAPTCHA docs.
 *
 * @param  string $tag    The full script tag.
 * @param  string $handle The registered handle for the script.
 *
 * @return string
 */
function async_and_defer( $tag, $handle ) {
	if ( 'recaptcha' !== $handle ) {
		return $tag;
	}

	// -11 to insert just before the closing ">".
	return substr_replace( $tag, ' async defer', -11, 0 );
}

/**
 * Enqueue reCAPTCHA scripts as appropriate based on user settings.
 *
 * @return void
 */
function enqueue_scripts() {
	if ( ! keys_set() ) {
		debug( 'Scripts not enqueued because one of the secret or site keys is not set' );
		return;
	}

	$action = get_current_login_action();

	if ( ! is_enabled_for_action( $action ) ) {
		debug( 'Scripts not enqueued because the login request is for an unsupported action or has been disabled by the user' );
		return;
	}

	wp_enqueue_script( 'recaptcha' );
}

/**
 * Get the current login action (assumes you are on the wp-login.php).
 *
 * @return string
 */
function get_current_login_action() {
	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';

	if ( ! is_valid_login_action( $action ) ) {
		$action = 'login';
	}

	return $action;
}

/**
 * Determine is reCAPTCHA has been enabled for a given login action.
 *
 * @param  string $action The login action to check.
 *
 * @return boolean
 */
function is_enabled_for_action( $action ) {
	$conditions = [
		'login' => is_enabled_for_login(),
		'lostpassword' => is_enabled_for_lostpassword(),
		'register' => is_enabled_for_registration(),
	];

	if ( ! array_key_exists( $action, $conditions ) || ! $conditions[ $action ] ) {
		return false;
	}

	return true;
}

/**
 * Check whether a given login action is valid.
 *
 * @param  string $action The login action to check.
 *
 * @return boolean
 */
function is_valid_login_action( $action ) {
	$valid_actions = [
		'login',
		'logout',
		'lostpassword',
		'postpass',
		'register',
		'resetpass',
		'retrievepassword',
		'rp',
	];

	return in_array( $action, $valid_actions, true )
		|| false !== has_filter( "login_form_{$action}" );
}

/**
 * Print a <noscript> overlay if on a login action for which reCAPTCHA is enabled.
 *
 * @return void
 */
function print_overlay() {
	$action = get_current_login_action();

	if ( ! is_enabled_for_action( $action ) ) {
		return;
	}

	noscript_overlay( 'warning', 'JavaScript is required to log in to this site' );
}

/**
 * Register the reCAPTCHA script and required (inline) callbacks.
 *
 * @return void
 */
function register_scripts() {
	wp_register_script(
		'recaptcha',
		'https://www.google.com/recaptcha/api.js?onload=rfwOnLoad&render=explicit',
		[],
		null,
		true
	);

	// @todo Further investigation on the workings of the modal that pop up informing you that you
	// have been logged out in another tab. Seems to work fine, may be some issues.
	// Not sure how I feel about this "templating" method...
	$template = [
		// @todo More robust handling so this can be re-used for contact, comment and other forms.
		'var rfwOnSubmit = function() {',
			'document.forms[0].submit();',
		'};',
		'var rfwOnLoad = function() {',
			'grecaptcha.render( "wp-submit", {',
				'callback: rfwOnSubmit,',
				sprintf( 'sitekey: %s,', wp_json_encode( site_key() ) ),
			'} );',
		'};',
	];

	wp_add_inline_script( 'recaptcha', implode( ' ', $template ), 'before' );
}

/**
 * Get the reCAPTCHA specific values from $_POST and $_SERVER.
 *
 * @return string[]
 */
function request_values() {
	return [
		filter_input( INPUT_POST, 'g-recaptcha-response' ) ?: '',
		isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '',
	];
}

/**
 * Get the base URL for the reCAPTCHA verification API.
 *
 * @return string
 */
function verification_url() {
	return 'https://www.google.com/recaptcha/api/siteverify';
}

/**
 * Verify a reCAPATCHA response.
 *
 * @param  string $token     The g-recaptcha-response token.
 * @param  string $remote_ip IP address of the remote user.
 *
 * @return boolean
 */
function verify_response( $token, $remote_ip = '' ) {
	if ( ! $token ) {
		debug( 'Verification failed because token is empty' );
		return false;
	}

	$params = [
		'secret' => (string) secret_key(),
		'response' => (string) $token,
	];

	if ( $remote_ip ) {
		$params['remoteip'] = (string) $remote_ip;
	}

	$response = wp_safe_remote_post( verification_url(), [
		'body' => $params,
	] );

	if ( is_wp_error( $response ) ) {
		debug( 'Verification failed due to WP HTTP error' );
		return false;
	}

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		debug( 'Verification failed because the API request returned a non-200 response' );
		return false;
	}

	$json = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( null === $json || JSON_ERROR_NONE !== json_last_error() ) {
		debug( 'Failed to json_decode() API response with message: ' . json_last_error_msg() );
		return false;
	}

	if ( isset( $json['success'] ) && $json['success'] ) {
		debug( 'Verification succeeded' );
		return true;
	}

	debug( 'Verification failed because API reported a non-successful attempt' );
	return false;
}
