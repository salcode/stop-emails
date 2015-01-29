<?php
/*
 * Plugin Name: Stop Emails
 * Plugin URI: http://salferrarello.com/stop-emails-wordpress-plugin/
 * Description: Stops outgoing emails sent using wp_mail() function
 * Any calls to wp_mail() will fail silently (i.e. WordPress
 * will operate as if the email were sent successfully
 * but no email will actually be sent).
 * NOTE: If using the PHP mail() function directly, this
 * plugin will NOT stop the emails.
 * Version: 0.8.0
 * Author: Sal Ferrarello
 * Author URI: http://salferrarello.com/
 * Text Domain: stop-emails
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

register_deactivation_hook( __FILE__, array( 'Fe_Stop_Emails', 'on_deactivation' ) );

// load PHPMailer class, so we can subclass it
require_once ABSPATH . WPINC . '/class-phpmailer.php';

/**
 * Subclass of PHPMailer to prevent Sending.
 *
 * This subclass of PHPMailer replaces the send() method
 * with a method that does not send.
 * This subclass is based on the WP Core MockPHPMailer
 * subclass found in phpunit/includes/mock-mailer.php
 *
 * @since 0.8.0
 * @see PHPMailer
 */
class Fe_Stop_Emails_Fake_PHPMailer extends PHPMailer {
	var $mock_sent = array();

	/**
	 * Replacement send() method that does not send.
	 *
	 * Unlike the PHPMailer send method,
	 * this method never calls the method postSend(),
	 * which is where the email is actually sent
	 *
	 * @since 0.8.0
	 * @return bool
	 */
	function send() {
		try {
			if ( ! $this->preSend() ) {
				return false;
			}

			$mock_email = array(
				'to'     => $this->to,
				'cc'     => $this->cc,
				'bcc'    => $this->bcc,
				'header' => $this->MIMEHeader,
				'body'   => $this->MIMEBody,
			);

			$this->mock_sent[] = $mock_email;

			// hook to allow logging
			do_action( 'fe_stop_emails_log', $mock_email );

			return true;
		} catch ( phpmailerException $e ) {
			return false;
		}
	}
}

/**
 * Stop Emails Plugin Class.
 *
 * Prevents emails from being sent and provides basic logging.
 * Replaces PHPMailer global instance $phpmailer with an instance
 * of the subclass Fe_Stop_Emails_Fake_PHPMailer
 *
 * @since 0.8.0
 */
class Fe_Stop_Emails {
	/**
	 * Constuctor to setup plugin.
	 *
	 * @since 0.8.0
	 */
	public function __construct() {
		$this->add_hooks();

		$this->settings_page();

	}

	/**
	 * Add hooks.
	 *
	 * @since 0.8.0
	 */
	public function add_hooks() {
		add_action( 'plugins_loaded', array( $this, 'replace_phpmailer' ) );
		add_action( 'fe_stop_emails_log', array( $this, 'log_to_php_error_log' ) );
		add_action( 'admin_notices', array( $this, 'warning' ) );
		add_action('init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Replace the global $phpmailer with fake phpmailer.
	 *
	 * @since 0.8.0
	 *
	 * @return Fe_Stop_Emails_Fake_PHPMailer instance, the object that replaced
	 *                                                 the global $phpmailer
	 */
	public function replace_phpmailer() {
		global $phpmailer;
		return $this->replace_w_fake_phpmailer( $phpmailer );
	}

	/**
	 * Replace the parameter object with an instance of
	 * Fe_Stop_Emails_Fake_PHPMailer.
	 *
	 * @since 0.8.0
	 *
	 * @param PHPMailer $obj
	 * @return Fe_Stop_Emails_Fake_PHPMailer $obj
	 */
	public function replace_w_fake_phpmailer( &$obj = null ) {
		$obj = new Fe_Stop_Emails_Fake_PHPMailer;

		return $obj;
	}

	/**
	 * Should emails be logged to the PHP error log.
	 *
	 * @since 0.8.0
	 *
	 * @return bool
	 */
	public function should_emails_be_logged_to_php_error_log() {
		$options = get_option( 'fe_stop_emails_options' );

		if ( $options && isset( $options['log-email'] ) ) {
			// use value from options
			$log_to_error_log = $options['log-email'];
		} else {
			// default value
			$log_to_error_log = 0;
		}

		$log_to_error_log = apply_filters( 'fe_stop_emails_log_email', $log_to_error_log );

		return (bool) $log_to_error_log;
	}

	/**
	 * Hooked function for email logging.
	 *
	 * Checks if email should be logged and logs it if necessary
	 *
	 * @since 0.8.0
	 */
	public function log_to_php_error_log( $mock_email ) {
		if ( $this->should_emails_be_logged_to_php_error_log() ) {
			$text = $this->mock_email_to_text( $mock_email );
			error_log( $text );
		}
	}

	/**
	 * Convert email to text.
	 *
	 * @since 0.8.0
	 *
	 * @param Fe_Stop_Emails_Fake_PHPMailer $fake_phpmailer
	 * @return string, text version of email
	 */
	public function mock_email_to_text( $mock_email ) {
		return print_r( $mock_email, true );
	}

	/**
	 * Display Warning the emails are being stopped.
	 *
	 * Display admin notice warning that emails are being
	 * stopped, additionally if emails are being logged
	 * in the PHP error_log, it is noted that emails are
	 * being logged.
	 *
	 * @since 0.8.0
	 */
	public function warning() {
		echo "\n<div class='error'><p>";
		echo "<strong>";
		if ( $this->should_emails_be_logged_to_php_error_log() ) {
			_e('Logging Disabled Emails', 'stop-emails');
		} else {
			_e('Emails Disabled', 'stop-emails');
		}
		echo ': ';
		echo "</strong>";

		_e( 'The Stop Emails plugin is currently active, which will prevent any emails from being sent.  ', 'stop-emails' );
		_e( 'To send emails, disable the plugin.', 'stop-emails');
		echo "</p></div>";
	}

	/**
	 * Create Settings Page.
	 *
	 * The settings page is created in lib/admin-settings.php.
	 * We include a check that this file exists, so we can
	 * run this plugin with only this primary file; this
	 * allows use as a "mu-plugins" plugin.
	 *
	 * @since 0.8.0
	 */
	public function settings_page() {
		$plugin_dir_path = plugin_dir_path(__FILE__);
		$plugin_basename = plugin_basename(__FILE__);

		if ( file_exists( "{$plugin_dir_path}lib/admin-settings.php" ) ) {
			// create admin settings screen
			require_once("{$plugin_dir_path}lib/admin-settings.php");

			// Add Settings link on Plugin Page
			add_filter("plugin_action_links_$plugin_basename", array( $this, 'settings_link_on_plugin_page' ) );
		}
	}

	/**
	 * Add a settings link to links for this plugin on the plugin page.
	 *
	 * Add to the $links array, an element that contains the html markup
	 * for the settings page for this link.
	 *
	 * @since 0.8.0
	 * @param array of strings, each of which is the markup for a link
	 * @return array of strings, each of which is the markup for a link
	 *                           with additional link
	 */
	public function settings_link_on_plugin_page( $links ) {
		$links[] = '<a href="' .
			admin_url( 'options-general.php?page=fe_stop_emails' ) .
			'">' . __('Settings') . '</a>';
		return $links;
	}

	/**
	 * Load textdomain for translations.
	 *
	 * @since 0.8.0
	 */
	public function load_textdomain() {
		$domain = 'stop-emails';
		$plugin_rel_path = dirname(plugin_basename(__FILE__)) . '/languages';

		load_plugin_textdomain( $domain, false, $plugin_rel_path );
	}

	/**
	 * On plugin deactivation clean up.
	 *
	 * Remove the plugin option, where settings are stored
	 *
	 * @since 0.8.0
	 */
	public static function on_deactivation() {
		delete_option( 'fe_stop_emails_options' );
	}
}

new Fe_Stop_Emails;
