<?php

class Tests_Stop_Emails extends WP_UnitTestCase {

	function test_global_phpmailer_exists() {
		global $phpmailer;
		$this->assertTrue( isset( $phpmailer ), 'global $phpmailer is not set' );
	}

	function test_phpmailer_class_exists() {
		$this->assertTrue( class_exists( 'PHPMailer' ), 'class PHPMailer does not exist' );
	}

	function test_phpmailer_replacement_class_exists() {
		$this->assertTrue( class_exists( 'Fe_Stop_Emails_Fake_PHPMailer' ),
			'class Fe_Stop_Emails_Fake_PHPMailer does not exist'
		);
	}

	function test_method_replace_w_fake_phpmailer() {
		$stop_emails = new Fe_Stop_Emails;
		$obj = new StdClass;
		$phpmailer = $stop_emails->replace_w_fake_phpmailer( $obj );
		$this->assertInstanceOf( 'Fe_Stop_Emails_Fake_PHPMailer', $phpmailer,
			'method replace_w_fake_phpmailer did not return instace of Fe_Stop_Emails_Fake_PHPMailer'
		);
	}

	function test_phpmailer_replaced() {
		global $phpmailer;

		$this->assertInstanceOf( 'Fe_Stop_Emails_Fake_PHPMailer', $phpmailer,
			'global $phpmailer is not an instance of Fe_Stop_Emails_Fake_PHPMailer'
		);
	}

	function test_log_to_error_log_default_to_zero() {
		$stop_emails = new Fe_Stop_Emails;
		$this->assertFalse( $stop_emails->should_emails_be_logged_to_php_error_log(),
			'should_emails_be_logged_to_php_error_log did not default to false'
		);
	}

	function test_log_to_error_log_filter() {
		add_filter( 'fe_stop_emails_log_email', '__return_true' );
		$stop_emails = new Fe_Stop_Emails;
		$this->assertTrue( $stop_emails->should_emails_be_logged_to_php_error_log(),
			'should_emails_be_logged_to_php_error_log did not return true when a __return_true filter was applied'
		);
	}

	function test_log_to_error_log_read_option_true() {
		$option = array(
			'log-email' => 1,
		);
		add_option( 'fe_stop_emails_options', $option );
		$stop_emails = new Fe_Stop_Emails;
		$this->assertTrue( $stop_emails->should_emails_be_logged_to_php_error_log(),
			'should_emails_be_logged_to_php_error_log did not return true when the option was set'
		);
	}

	function test_log_to_error_log_read_option_false() {
		$option = array(
			'log-email' => 0,
		);
		add_option( 'fe_stop_emails_options', $option );
		$stop_emails = new Fe_Stop_Emails;
		$this->assertFalse( $stop_emails->should_emails_be_logged_to_php_error_log(),
			'should_emails_be_logged_to_php_error_log did not return false when the option was set'
		);
	}

}
