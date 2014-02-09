=== Stop Emails ===
Contributors: salcode
Tags: email, development
Requires at least: 3.6
Tested up to: 3.8.1
Stable tag: 0.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Stops outgoing emails that use wp_mail()

== Description ==

Stops outgoing emails sent using wp_mail() function.
Any calls to wp_mail() will fail silently (i.e. WordPress
will operate as if the email were sent successfully
but no email will actually be sent).
NOTE: If using the PHP mail() function directly, this
plugin will NOT stop the emails.

== Installation ==

1. Install plugin from WordPress plugin repository http://wordpress.org/plugins/
2. Activate Stop Emails through the 'Plugins' menu in WordPress.

= Manual Installation =

1. Upload the entire `stop-emails` directory to the `/wp-content/plugins/` directory.
2. Activate Stop Emails through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Where do the emails go? =

The emails are lost forever.

= Why are some of my emails still being sent? =

Most likely, this is due to a plugin you have running.
There are two different things the plugin could be
doing to cause emails to still be sent.
1. The plugin is overriding our overriding of the sending mechanism.
2. The plugin is calling the PHP function mail() directly.
Unfortunately in either of these cases, this plugin will not help you.

= I'm a developer and I want to log the emails that are stopped =
You can log stopped emails in
php_error.log using the filter 'fe_stop_emails_log_email'

add_filter('fe_stop_emails_log_email', '__return_true');

== Screenshots ==

1. When the plugin is running, there will be a warning on the backend of the website.
1. Lies! The email wasn't really sent, we're running Stop Emails

== Changelog ==

= 0.4.0 =
* Add Spanish translation (es_ES), thanks to Andrew Kurtis from webhostinghub.com

= 0.3.0 =
* Added support for localization

= 0.2.0 =
* Added filter `fe_stop_emails_log_email` for $log_email value, which allows a programmer to add code to
log the blocked emails in the php_error.log
* Renamed functions and classes to follow WordPress standards

= 0.1.0 =
* First release

== Upgrade Notice ==

= 0.4.0 =
Add Spanish translation (es_ES)

= 0.3.0 =
No significant change in functionality.
Added support for localization but no additional languages added.

= 0.2.0 =
No significant change in functionality.
Primary motivation for update was to change "Tested Up To:" value to 3.8

= 0.1.0 =
First Release
