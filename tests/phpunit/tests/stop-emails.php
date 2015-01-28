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
}
