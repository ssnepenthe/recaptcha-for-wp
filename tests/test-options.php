<?php

class Options_Test extends WP_UnitTestCase {
	/** @test */
	function enabled_functions_return_boolean() {
		$this->assertFalse( get_option( 'rfw_login' ) );
		$this->assertFalse( get_option( 'rfw_lostpassword' ) );
		$this->assertFalse( get_option( 'rfw_registration' ) );

		update_option( 'rfw_login', '1' );
		update_option( 'rfw_lostpassword', '1' );
		update_option( 'rfw_registration', '1' );

		$this->assertTrue( Recaptcha_For_Wp\is_enabled_for_login() );
		$this->assertTrue( Recaptcha_For_Wp\is_enabled_for_lostpassword() );
		$this->assertTrue( Recaptcha_For_Wp\is_enabled_for_registration() );
	}

	/** @test */
	function it_knows_when_keys_are_set() {
		$this->assertFalse( Recaptcha_For_Wp\keys_set() );

		update_option( 'rfw_secret_key', 'test' );
		update_option( 'rfw_site_key', 'test' );

		$this->assertTrue( Recaptcha_For_Wp\keys_set() );
	}

	/** @test */
	function it_sanitizes_boolean_options_as_one_or_zero() {
		$this->assertSame( '1', Recaptcha_For_Wp\sanitize_boolean( true ) );
		$this->assertSame( '0', Recaptcha_For_Wp\sanitize_boolean( false ) );
	}

	/** @test */
	function it_sanitizes_string_options_by_converting_and_trimming() {
		$this->assertSame( '', Recaptcha_For_Wp\sanitize_string( null ) );
		$this->assertEquals( 'test', Recaptcha_For_Wp\sanitize_string( ' test ') );
		$this->assertSame( '1', Recaptcha_For_Wp\sanitize_string( 1 ) );
	}
}
