<?php
/**
 * Plugin options functionality.
 *
 * @package recaptcha-for-wp
 */

namespace Recaptcha_For_Wp;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Get an option and convert it to boolean.
 *
 * @param  string $option Option key.
 *
 * @return boolean
 */
function get_boolean_option( $option ) {
	// Should be stored as "1" or "0".
	return filter_var( get_option( $option ), FILTER_VALIDATE_BOOLEAN );
}

/**
 * Check if the user has enabled reCAPTCHA for the login form.
 *
 * @return boolean
 */
function is_enabled_for_login() {
	return get_boolean_option( 'rfw_login' );
}

/**
 * Check if the user has enabled reCAPTCHA for the lostpassword form.
 *
 * @return boolean
 */
function is_enabled_for_lostpassword() {
	return get_boolean_option( 'rfw_lostpassword' );
}

/**
 * Check if the user has enabled reCAPTCHA for the registration form.
 *
 * @return boolean
 */
function is_enabled_for_registration() {
	return get_boolean_option( 'rfw_registration' );
}

/**
 * Check whether the user has set the secret and site keys.
 *
 * @return boolean
 */
function keys_set() {
	return (bool) secret_key() && (bool) site_key();
}

/**
 * Print an admin notice to remind the user to configure required settings.
 *
 * @return void
 */
function notify_when_options_not_set() {
	if ( ! current_user_can( 'manage_options' ) ) {
		debug( 'The current user does not have a high enough role to see the "keys" notice' );
		return;
	}

	if ( keys_set() ) {
		debug( 'All API keys are set so the notice is not being displayed' );
		return;
	}

	?><div class="notice notice-error">
		<p>
			reCAPTCHA for WordPress: Please visit the <a href="<?php echo esc_url( menu_page_url( 'recaptcha-for-wp', false ) ) ?>">settings page</a> and configure your secret and site keys.
		</p>
	</div><?php
}

/**
 * Register the plugin options page.
 *
 * @return void
 */
function register_menu() {
	add_options_page(
		'reCAPTCHA Settings',
		'reCAPTCHA',
		'manage_options',
		'recaptcha-for-wp',
		function() {
			?><div class="wrap">
				<h1><?php echo esc_html( get_admin_page_title() ) ?></h1>
				<form action="options.php" method="POST">
					<?php settings_fields( 'rfw_recaptcha_group' ) ?>
					<?php do_settings_sections( 'recaptcha-for-wp' ) ?>
					<?php submit_button() ?>
				</form>
			</div><?php
		}
	);
}

/**
 * Register the sections and fields for the plugin options page.
 *
 * @return void
 */
function register_menu_content() {
	add_settings_section(
		'rfw_main',
		'reCAPTCHA Configuration',
		function() {
			?><p>
				Please <a href="https://www.google.com/recaptcha/intro/android.html" target="_blank">sign up for reCAPTCHA</a> and add your API keys below.
			</p>
			<p>
				<strong>Make sure to verify your keys before signing out - if they are entered incorrectly, YOU WILL BE LOCKED OUT OF YOUR SITE</strong>.
			</p><?php
		},
		'recaptcha-for-wp'
	);

	add_settings_field(
		'rfw_secret_key',
		'Secret Key',
		function() {
			$function = sprintf(
				'%s\\%s',
				__NAMESPACE__,
				defined( 'RFW_SECRET_KEY' ) ? 'text_input_disabled' : 'text_input'
			);

			$function( 'rfw_secret_key', 'reCAPTCHA Secret Key', secret_key() );
		},
		'recaptcha-for-wp',
		'rfw_main'
	);

	add_settings_field(
		'rfw_site_key',
		'Site Key',
		function() {
			$function = sprintf(
				'%s\\%s',
				__NAMESPACE__,
				defined( 'RFW_SITE_KEY' ) ? 'text_input_disabled' : 'text_input'
			);

			$function( 'rfw_site_key', 'reCAPTCHA Site Key', site_key() );
		},
		'recaptcha-for-wp',
		'rfw_main'
	);

	add_settings_field(
		'rfw_enable_for',
		'Enable For:',
		function() {
			?><fieldset>
				<legend class="screen-reader-text">
					<span>Select which pages you would like to enable reCAPTCHA for.</span>
				</legend><?php

				checkbox( 'rfw_login', 'Login', '1', is_enabled_for_login() );
				checkbox( 'rfw_lostpassword', 'Lost Password', '1', is_enabled_for_lostpassword() );
				checkbox( 'rfw_registration', 'Registration', '1', is_enabled_for_registration() );

				?><p class="description">
					Select the forms for which you would like to enable reCAPTCHA.
				</p>
			</fieldset><?php
		},
		'recaptcha-for-wp',
		'rfw_main'
	);
}

/**
 * Register the individual plugin settings.
 *
 * @return void
 */
function register_settings() {
	register_setting( 'rfw_recaptcha_group', 'rfw_login',  [
		'default' => '1',
		'sanitize_callback' => __NAMESPACE__ . '\\sanitize_boolean',
	] );

	register_setting( 'rfw_recaptcha_group', 'rfw_lostpassword', [
		'default' => '1',
		'sanitize_callback' => __NAMESPACE__ . '\\sanitize_boolean',
	] );

	register_setting( 'rfw_recaptcha_group', 'rfw_registration', [
		'default' => '1',
		'sanitize_callback' => __NAMESPACE__ . '\\sanitize_boolean',
	] );

	register_setting( 'rfw_recaptcha_group', 'rfw_secret_key', [
		'default' => '',
		'sanitize_callback' => __NAMESPACE__ . '\\sanitize_string',
	] );

	register_setting( 'rfw_recaptcha_group', 'rfw_site_key', [
		'default' => '',
		'sanitize_callback' => __NAMESPACE__ . '\\sanitize_string',
	] );
}

/**
 * Sanitize a boolean setting by converting to string in preparation for saving to the database.
 *
 * @param  mixed $value Value to sanitize.
 *
 * @return string
 */
function sanitize_boolean( $value ) {
	return filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ? '1' : '0';
}

/**
 * Sanitize a string value in preparation for saving to the database.
 *
 * @param  string $value Value to sanitize.
 *
 * @return string
 */
function sanitize_string( $value ) {
	return trim( (string) $value );
}

/**
 * Get the reCAPTCHA secret key set by the user.
 *
 * @return string
 */
function secret_key() {
	return (string) get_option( 'rfw_secret_key' );
}

/**
 * Get the reCAPTCHA site key set by the user.
 *
 * @return string
 */
function site_key() {
	return (string) get_option( 'rfw_site_key' );
}

/**
 * Allow the user to short-circuit plugin options by defining corresponding constant values.
 *
 * @param  false|mixed $pre_option Value to use in place of actual option value.
 * @param  string      $option     Option name.
 *
 * @return false|mixed             False to use actual option, any other value to short-circuit.
 */
function use_constants_when_defined( $pre_option, $option ) {
	if ( false !== $pre_option ) {
		// Something else has already laid claim to this option...
		return $pre_option;
	}

	$constant = strtoupper( $option );

	if ( ! defined( $constant ) ) {
		return $pre_option;
	}

	return constant( $constant );
}
