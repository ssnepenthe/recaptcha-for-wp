<?php
/**
 * Template tags used for the reCAPTCHA options page.
 *
 * @package recaptcha-for-wp
 */

namespace Recaptcha_For_Wp;

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
 * Print a text input for the options page.
 *
 * @param  string $option   Option key.
 * @param  string $label    Input placeholder text.
 * @param  string $value    Current value for the input.
 * @param  string $constant The constant that provides the option value.
 *
 * @return void
 */
function text_input( $option, $label, $value, $constant ) {
	$disabled = defined( $constant );

	?><input
		class="regular-text"
		<?php disabled( $disabled ) ?>
		id="<?php echo esc_attr( $option ) ?>"
		name="<?php echo esc_attr( $option ) ?>"
		placeholder="<?php echo esc_attr( $label ) ?>"
		type="text"
		value="<?php echo esc_attr( $value ) ?>"
	><?php

	if ( $disabled ) {
		?><p class="description">
			Field disabled because setting has been defined via the <kbd><?php echo esc_html( $constant ) ?></kbd> constant
		</p><?php
	}
}