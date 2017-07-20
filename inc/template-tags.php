<?php
/**
 * Template tags used for the reCAPTCHA options page.
 *
 * @package recaptcha-for-wp
 */

namespace Recaptcha_For_Wp;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Print a checkbox for the options page.
 *
 * @param  string  $option  Option key.
 * @param  string  $label   Checkbox label.
 * @param  string  $value   Value for a checked box.
 * @param  boolean $checked Whether this checkbox should be checked.
 *
 * @return void
 *
 * @todo Constant so we can disable?
 */
function checkbox( $option, $label, $value, $checked ) {
	?><label>
		<input
			<?php checked( $checked ) ?>
			id="<?php echo esc_attr( $option ) ?>"
			name="<?php echo esc_attr( $option ) ?>"
			type="checkbox"
			value="<?php echo esc_attr( $value ) ?>"
		>
		<?php echo esc_html( $label ) ?>
	</label>

	<br><?php
}

/**
 * Print a <noscript> overlay.
 *
 * @param  string $icon    A Dashicons icon identifier.
 * @param  string $message The message to print in the overlay.
 *
 * @return void
 */
function noscript_overlay( $icon, $message ) {
	?><noscript>
		<div class="rfw-overlay">
			<div class="rfw-overlay-background"></div>
			<div class="rfw-overlay-content">
				<p><span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span></p>
				<p><?php echo esc_html( $message ); ?></p>
			</div>
		</div>
	</noscript><?php
}

/**
 * Print a text input for the options page.
 *
 * @param  string $name     Option key.
 * @param  string $label    Option label - used as input placeholder text.
 * @param  string $value    Current value for the input.
 *
 * @return void
 */
function text_input( $name, $label, $value ) {
	?><input
		class="regular-text"
		id="<?php echo esc_attr( $name ) ?>"
		name="<?php echo esc_attr( $name ) ?>"
		placeholder="<?php echo esc_attr( $label ) ?>"
		type="text"
		value="<?php echo esc_attr( $value ) ?>"
	><?php
}

/**
 * Print a placeholder for a text input setting that is not allowed to be modified.
 *
 * This does not actually print a disabled text input - might want to reconsider the name.
 *
 * @param  string $name  Option key
 * @param  string $label Option label - used to identify for user.
 * @param  string $value Current option value.
 *
 * @return void
 */
function text_input_disabled( $name, $label, $value ) {
	?><?php echo esc_html( $label ); ?> set to <kbd><?php echo esc_html( $value ); ?></kbd> via constant.

	<input
		id="<?php echo esc_attr( $name ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		type="hidden"
		value="<?php echo esc_attr( $value ); ?>"
	><?php
}
