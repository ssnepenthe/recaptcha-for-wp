<?php

class Recaptcha_Test extends WP_UnitTestCase {
	/** @test */
	function it_adds_body_class_for_enabled_actions() {
		$body_classes = [ 'one', 'two', 'three' ];

		$this->assertEquals(
			$body_classes,
			Recaptcha_For_Wp\add_overlay_body_class( $body_classes, 'login' )
		);
		$this->assertEquals(
			$body_classes,
			Recaptcha_For_Wp\add_overlay_body_class( $body_classes, 'lostpassword' )
		);
		$this->assertEquals(
			$body_classes,
			Recaptcha_For_Wp\add_overlay_body_class( $body_classes, 'register' )
		);

		update_option( 'rfw_login', '1' );
		update_option( 'rfw_lostpassword', '1' );
		update_option( 'rfw_registration', '1' );

		$new_classes = array_merge( $body_classes, [ 'rfw-has-overlay' ] );

		$this->assertEquals(
			$new_classes,
			Recaptcha_For_Wp\add_overlay_body_class( $body_classes, 'login' )
		);
		$this->assertEquals(
			$new_classes,
			Recaptcha_For_Wp\add_overlay_body_class( $body_classes, 'lostpassword' )
		);
		$this->assertEquals(
			$new_classes,
			Recaptcha_For_Wp\add_overlay_body_class( $body_classes, 'register' )
		);
	}

	/** @test */
	function it_adds_async_and_defer_atts_to_script_tag() {
		$tag = "<script type='text/javascript'>\n
var rfwOnSubmit = function() { document.forms[0].submit(); }; var rfwOnLoad = function() { grecaptcha.render( \"wp-submit\", { callback: rfwOnSubmit, sitekey: \"6LeQPScUAAAAAPFg5dHgoUMAllWKaknT4AZd8kP_\", } ); };\n
</script>\n
<script type='text/javascript' src='https://www.google.com/recaptcha/api.js?onload=rfwOnLoad&#038;render=explicit'></script>\n";

		$modified = "<script type='text/javascript'>\n
var rfwOnSubmit = function() { document.forms[0].submit(); }; var rfwOnLoad = function() { grecaptcha.render( \"wp-submit\", { callback: rfwOnSubmit, sitekey: \"6LeQPScUAAAAAPFg5dHgoUMAllWKaknT4AZd8kP_\", } ); };\n
</script>\n
<script type='text/javascript' src='https://www.google.com/recaptcha/api.js?onload=rfwOnLoad&#038;render=explicit' async defer></script>\n";

		$this->assertEquals( $tag, Recaptcha_For_Wp\async_and_defer( $tag, 'bad-handle' ) );
		$this->assertEquals( $modified, Recaptcha_For_Wp\async_and_defer( $tag, 'recaptcha' ) );
	}

	/** @test */
	function it_gives_correct_current_login_action() {
		// No action set falls back to "login".
		$this->assertEquals( 'login', Recaptcha_For_Wp\get_current_login_action() );

		$_REQUEST['action'] = 'login';
		$this->assertEquals( 'login', Recaptcha_For_Wp\get_current_login_action() );

		$_REQUEST['action'] = 'lostpassword';
		$this->assertEquals( 'lostpassword', Recaptcha_For_Wp\get_current_login_action() );

		$_REQUEST['action'] = 'register';
		$this->assertEquals( 'register', Recaptcha_For_Wp\get_current_login_action() );

		// Invalid action falls back to login as well.
		$_REQUEST['action'] = 'fake';
		$this->assertEquals( 'login', Recaptcha_For_Wp\get_current_login_action() );

		// Action becomes valid when something is attached to corresponding hook.
		add_action( 'login_form_fake', function() {} );
		$this->assertEquals( 'fake', Recaptcha_For_Wp\get_current_login_action() );
	}

	/** @test */
	function it_knows_if_it_is_enabled_for_a_given_action() {
		$actions = [
			'login' => 'login',
			'lostpassword' => 'lostpassword',
			'register' => 'registration',
		];

		foreach ( $actions as $action => $key ) {
			$this->assertFalse( Recaptcha_For_Wp\is_enabled_for_action( $action ) );

			update_option( "rfw_{$key}", '1' );

			$this->assertTrue( Recaptcha_For_Wp\is_enabled_for_action( $action ) );
		}

		// Always returns false for unrecognized action.
		$this->assertFalse( Recaptcha_For_Wp\is_enabled_for_action( 'fake' ) );
	}

	/** @test */
	function it_knows_if_a_login_action_is_valid() {
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

		foreach ( $valid_actions as $action ) {
			$this->assertTrue( Recaptcha_For_Wp\is_valid_login_action( $action ) );
		}

		$this->assertFalse( Recaptcha_For_Wp\is_valid_login_action( 'fake' ) );

		// Action becomes valid when something is attached to corresponding hook.
		add_action( 'login_form_fake', function() {} );

		$this->assertTrue( Recaptcha_For_Wp\is_valid_login_action( 'fake' ) );
	}
}
