<?php
/*
 * Deactivation code for Stop Emails Plugin
 * Remove option
 * @fe_stop_emails_options
 */

 function fe_stop_emails_deactivate() {     
     delete_option( 'fe_stop_emails_options' );
 }
