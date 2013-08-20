<?php
/*
 * Plugin Name: Stop Emails
 * Plugin URI: http://salferrarello.com/stop-emails-wordpress-plugin/
 * Description: Stops outgoing emails. Any outgoing emails will fail
 * silently (i.e. WordPress will operate as if the email were sent
 * successfully but no email will actually be sent).
 * Version: 0.1.0
 * Author: Sal Ferrarello
 * Author URI: http://salferrarello.com/
 */

function FeStopEmails( $phpmailer ) {
    // as a developer, you can enable logging all your emails
    // to the PHP error log when they are prevented from sending
    // in the future, this will be a setting for the plugin
    $logEmail = false;

    if ( !class_exists('FeStopEmailsFakePHPMailer') ) {

        // create class to extend PHPMailer to prevent sending
        // why not move this class definition outside of the surrounding function?
        // PHPMailer is not defined when the raw code in this file is processed,
        // therefore, we need to wait until late enough in the loading
        // that the class is loaded
        class FeStopEmailsFakePHPMailer extends PHPMailer {
            // remove all functionality from Send method (other than
            // return a true value)
            function Send() {
                return true;
            } // Send()

            // static function for logging the email in
            // an instance of $phpmailer
            public static function LogEmail( $phpmailer ) {
                $logEntry = "\n";
                $logEntry .= 'To: ';
                foreach ($phpmailer->to as $toArray) {
                    foreach ($toArray as $to) {
                        if ( is_string($to) && trim($to) ) {
                            $logEntry .= $to . ', ';
                        }
                    }
                } // foreach
                $logEntry .= "\n";
                $logEntry .= 'From: ' . $phpmailer->From . "\n";
                $logEntry .= 'Subject: ' . $phpmailer->Subject . "\n";
                $logEntry .= $phpmailer->Body . "\n";

                error_log($logEntry);
            } // LogEmail()
        } // class FeStopEmailsFakePHPMailer
    } // if class FeStopEmailsFakePHPMailer does not already exist

    if ($logEmail) { FeStopEmailsFakePHPMailer::LogEmail( $phpmailer ); }

    // stop emails
    $phpmailer = new FeStopEmailsFakePHPMailer();

} // FeStopEmails()

function FeStopEmailsWarning() {
    echo "\n<div class='error'><p>";
    _e('<strong>Emails Disabled:</strong> The Stop Emails plugin is currently active, which will prevent any emails from being sent. To enable emails, disable the plugin.', 'stop-emails');
    echo "</p></div>";

} // FeStopEmailsWarning()

// stop emails
add_action('phpmailer_init', 'FeStopEmails');
// display a warning that emails are being stopped
add_action('admin_notices', 'FeStopEmailsWarning');
