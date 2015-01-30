<?php
/**
 * Admin Settings Page for Stop Emails Plugin
 * Value set is fe_stop_emails_options a
 * serialized array
 */

// Add a menu for our option page
add_action('admin_menu', 'fe_stop_emails_add_page');
function fe_stop_emails_add_page() {
	add_options_page(
		__( 'Stop Emails', 'stop-emails' ),
		__( 'Stop Emails', 'stop-emails' ),
		'manage_options',
		'fe_stop_emails',
		'fe_stop_emails_option_page'
	);
}

// Draw the option page
function fe_stop_emails_option_page() {
?>
	<div class="wrap">
		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e('Stop Emails:', 'stop-emails'); ?></h2>
		<form action="options.php" method="post">
<?php
	settings_fields('fe_stop_emails_options');
	do_settings_sections('fe_stop_emails');
	submit_button();
?>
		</form>
	</div>
<?php
}

// Register and define the settings
add_action('admin_init', 'fe_stop_emails_admin_init');
function fe_stop_emails_admin_init(){
	register_setting(
		'fe_stop_emails_options',
		'fe_stop_emails_options',
		'fe_stop_emails_validate_options'
	);
	add_settings_section(
		'fe_stop_emails_main',
		__( 'Email Logging', 'stop-emails' ),
		'fe_stop_emails_section_text',
		'fe_stop_emails'
	);
	add_settings_field(
		'log-email',
		__( 'Log Emails', 'stop-emails' ),
		'fe_stop_emails_setting_radio_btn',
		'fe_stop_emails',
		'fe_stop_emails_main'
	);
}

// Draw the section header
function fe_stop_emails_section_text() {
	echo '<p>';
	_e( 'The Stop Emails plugin has the option to log stopped emails to the PHP error log.', 'stop-emails');
	echo '</p>';
}

// Display and fill the form field
function fe_stop_emails_setting_radio_btn() {
	$options = get_option( 'fe_stop_emails_options' );

	if( isset( $options['log-email'] ) ) {
		$log_emails = ( $options['log-email'] ? 1 : 0 );
	} else {
		$log_emails = 0;
	}

	$html = '';
	$html .= '<fieldset>';

	$html .= '<p>';
	$html .= '<label for="fe_stop_emails_log_email_false">';
	$html .=  '<input type="radio" id="fe_stop_emails_log_email_false" name="fe_stop_emails_options[log-email]" value="0"' . checked( 0, $log_emails, false ) . '/>';
	$html .= __( 'Disable Logging', 'stop-emails' );
	$html .= '</label>';
	$html .= '</p>';

	$html .= '<p>';
	$html .= '<label for="fe_stop_emails_log_email_true">';
	$html .= '<input type="radio" id="fe_stop_emails_log_email_true" name="fe_stop_emails_options[log-email]" value="1"' . checked( 1, $log_emails, false ) . '/>';
	$html .= __( 'Log stopped emails to the PHP Error Log', 'stop-emails' );
	$html .= '</label>';
	$html .= '</p>';

	$html .= '</fieldset>';
	echo $html;
}

// Validate user input (we want text only)
function fe_stop_emails_validate_options( $input ) {
	// Define the array for the updated options
	$output = array();

	// Loop through each of the options sanitizing the data
	foreach( $input as $key => $val ) {

		if( isset ( $input[$key] ) ) {
			$output[$key] = ( $input[$key] ? 1 : 0 );
		} // end if

	} // end foreach

	return $output;
}
