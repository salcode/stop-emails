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
 * Version: 0.6.0
 * Author: Sal Ferrarello
 * Author URI: http://salferrarello.com/
 * Text Domain: stop-emails
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// run tasks on deactivate
include('lib/deactivate.php');
register_deactivation_hook( __FILE__, 'fe_stop_emails_deactivate' );

// create admin settings screen
include('lib/admin-settings.php');

// stop emails
add_action('phpmailer_init', 'fe_stop_emails');

// display a warning that emails are being stopped
add_action('admin_notices', 'fe_stop_emails_warning');

// Load plugin text domain
add_action('init', 'fe_stop_emails_load_plugin_textdomain');

function fe_stop_emails( $phpmailer ) {

    $log_email = fe_stop_emails_will_log_emails();

    if ( !class_exists('Fe_Stop_Emails_Fake_PHPMailer') ) {

        // create class to extend PHPMailer to prevent sending
        // why not move this class definition outside of the surrounding function?
        // PHPMailer is not defined when the raw code in this file is processed,
        // therefore, we need to wait until late enough in the loading
        // that the class is loaded
        class Fe_Stop_Emails_Fake_PHPMailer extends PHPMailer {
            // remove all functionality from Send method (other than
            // return a true value)
            function Send() {
                return true;
            } // Send()

            // static function for logging the email in
            // an instance of $phpmailer
            public static function LogEmail( $phpmailer ) {
                $log_entry = "\n";

                $log_entry .= __( 'To: ', 'stop-emails' );
                foreach ($phpmailer->to as $toArray) {
                    foreach ($toArray as $to) {
                        if ( is_string($to) && trim($to) ) {
                            $log_entry .= $to . ', ';
                        }
                    }
                } // foreach
                $log_entry .= "\n";

                $log_entry .= __( 'From: ', 'stop-emails' );
                $log_entry .= $phpmailer->From . "\n";

                $log_entry .= __( 'Subject: ', 'stop-emails' );
                $log_entry .= $phpmailer->Subject . "\n";

                $log_entry .= $phpmailer->Body . "\n";
                error_log( $log_entry );

            } // LogEmail()
        } // class Fe_Stop_Emails_Fake_PHPMailer
    } // if class Fe_Stop_Emails_Fake_PHPMailer does not already exist

    if ( $log_email ) { Fe_Stop_Emails_Fake_PHPMailer::LogEmail( $phpmailer ); }

    // stop emails
    $phpmailer = new Fe_Stop_Emails_Fake_PHPMailer();

}

function fe_stop_emails_will_log_emails() {
    // retrieve set options
    $options = get_option( 'fe_stop_emails_options' );

    // set $log_email based on saved options
    // default to zero
    if ( $options && isset( $options['log-email'] ) ) {
        // use value from options
        $log_email = $options['log-email'];
    } else {
        // default value
        $log_email = 0;
    }

    // apply filter to log_email value
    // as a developer, you can enable logging all your emails
    // to the PHP error log when they are prevented from sending
    $log_email = apply_filters( 'fe_stop_emails_log_email', $log_email );

    return $log_email;
}

function fe_stop_emails_warning() {
    echo "\n<div class='error'><p>";
        echo "<strong>";
        if ( fe_stop_emails_will_log_emails() ) {
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

function fe_stop_emails_load_plugin_textdomain() {
    $domain = 'stop-emails';
    $plugin_rel_path = dirname(plugin_basename(__FILE__)) . '/languages';

    load_plugin_textdomain( $domain, false, $plugin_rel_path );
}
