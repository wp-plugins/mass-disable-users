=== Mass-Disable Users plugin for WordPress ===
Contributors: channeleaton
Tags: users, multisite
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 0.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows network admins to disable users via CSV while retaining all author information.

== Description ==

For large multisite installations of WordPress, it's easy to let user management go by the wayside. Many times we forget to disable or remove old user accounts, leaving our sites open to users who are no longer part of our organization.

Mass-Disable Users (MDU) aims to fix that.

MDU allows super-administrators to disable old users while retaining all of the author information. This is especially important for news sites.

How does it work?

MDU takes a list of email addresses (in CSV format) and compares it to the existing WordPress users database then any users who do not exist in the CSV file are demoted to Subscriber in every blog!

NOTE: This plugin only works on multisite!

== Installation ==

1. Upload the `mass-disable-users` directory to `/wp-content/plugins/`
2. Network activate the plugin through Network Admin->Plugins

== Changelog ==

= 0.1 =

* Initial release
