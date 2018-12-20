=== Give - PayUmoney Gateway ===
Contributors: givewp
Tags: donations, donation, ecommerce, e-commerce, fundraising, fundraiser, payumoney, gateway
Requires at least: 4.5
Tested up to: 5.0
Stable tag: 1.0.4
License: GPLv3
License URI: https://opensource.org/licenses/GPL-3.0

PayUmoney Gateway Add-on for Give.

== Description ==

This plugin requires the Give plugin activated to function properly. When activated, it adds a payment gateway for payumoney.com.

== Installation ==

= Minimum Requirements =

* WordPress 4.8 or greater
* PHP version 5.3 or greater
* MySQL version 5.0 or greater
* Some payment gateways require fsockopen support (for IPN access)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of Give, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Give" and click Search Plugins. Once you have found the plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading our donation plugin and uploading it to your server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Changelog ==

= 1.0.4: December 18th, 2018 =
* New: Added support for PayUbiz accounts.

= 1.0.3: November 14th, 2018 =
* Tweak: Removed deprecated implementation of payment method label under gateway settings. Use the standard label configuration under Settings > Payment Gateways. Your previously used label will be brought over if customized.
* Fix: Resolved several typos in notices and plugin settings.
* Fix: Resolved 404 documentation link.

= 1.0.2: September 7th, 2018 =
* Fix: Hardening the successful transaction check to prevent unauthorized tampering of donation amount total confirmation from the merchant.
* Fix: The plugin will not deactivate when Give is not active but rather display a message prompting core plugin activation.

= 1.0.1 =
* Fix: The gateway rejects with "Invalid amount" for donations more than 3-digits in the donation amount.

= 1.0 =
* Initial plugin release. Yippee!
