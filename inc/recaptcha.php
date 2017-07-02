<?php
/**
 * The reCAPTCHA-specific plugin functionality.
 *
 * @package recaptcha-for-wp
 */

namespace Recaptcha_For_Wp;

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

	$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';

	$conditions = [
		'login' => is_enabled_for_login(),
		'lostpassword' => is_enabled_for_lostpassword(),
		'register' => is_enabled_for_registration(),
	];

	if ( ! array_key_exists( $action, $conditions ) || ! $conditions[ $action ] ) {
		debug( 'Scripts not enqueued because the login request is for an unsupported action or has been disabled by the user' );
		return;
	}

	wp_enqueue_script( 'recaptcha' );
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
 * Register the reCAPTCHA script and required (inline) callbacks.
 *
 * @return void
 */
function register_scripts() {
	// @todo async, defer
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

	if ( isset( $json['success'] ) && $json['success'] ) {
		debug( 'Verification succeeded' );
		return true;
	}

	debug( 'Verification failed because API reported a non-successful attempt' );
	return false;
}
