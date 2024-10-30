=== HumanCaptcha by Outerbridge ===
Contributors: outerbridge
Author URI: https://outerbridge.co.uk/
Tags: captcha, text-based, human, logic, questions, answers
Requires at least: 4.7
Tested up to: 5.8
Stable tag: trunk

HumanCaptcha is a Captcha that uses questions that require human logic to answer them to the WordPress login form, comments form and registration form. Machines cannot easily answer these types of questions, so using HumanCaptcha is a great way to reduce spam.


== Description ==

Most captchas are based on the requirement to reproduce a number of randomly-generated characters (which are sometimes blurred, jiggled and/or on a fuzzy background). HumanCaptcha generates a simple question which the user must answer using logical thought. HumanCaptcha is much more accessible than standard captchas (which many people find difficult to read or understand). Visually impaired people are much more likely to be able to use HumanCaptcha than a character-based one.

** Captchas **

Most captchas are based on the requirement to reproduce a number of randomly-generated characters (which are sometimes blurred, jiggled and/or on a fuzzy background).  HumanCaptcha generates a simple question which the user must answer using logical thought.  HumanCaptcha is much more accessible than standard captchas, which many people find difficult to read or understand.  Visually impaired people are more likely to be able to use HumanCaptcha than a character-based one.


CAPTCHAs are useful for improving security in a number of situations, for example:

1.	Reducing Comment Spam in Blogs
	Most bloggers will have come across programs that submit spam comments, often with the aim of improving the search engine ranking of a website.  By using a CAPTCHA, only humans can enter comments on your blog, and people do not need to sign up before they enter a comment.

2.	Protecting Email Addresses From Scrapers
	Spammers crawl the web looking for e-mail addresses rendered in text. CAPTCHAs can hide your e-mail address from web scrapers, by requiring users to solve a CAPTCHA before revealing your e-mail. 

3.	Deterring Viruses, Worms and Spam 
	CAPTCHAs may reduce the likelihood of e-mailed viruses, worms and spam, by only accepting an e-mail if it has been established that there is a human behind the sending computer.

= Credits =

Many thanks to:
TH90 (https://wordpress.org/support/profile/th90) of MPW D&D for the Persian translation files (fa_IR). Language: Persian, Country: Iran.
Dayl (http://dayl.ru) in Санкт-Петербург for the Russian translation files.  Language: Russian, Country: Russia.


== Installation ==

1. Install automatically through the 'Plugins', 'Add New' menu in WordPress, or upload the 'outerbridge-humancaptcha' folder to the '/wp-content/plugins/' directory. 

2. Activate the plugin through the 'Plugins' menu in WordPress. Look for the link under the Plugins menu to amend the questions and andswers. 

3. Test the plugin in by logging out and posting a comment.

4. Updates are automatic. Click on "Upgrade Automatically" if prompted from the admin menu. If you ever have to manually upgrade, simply deactivate, uninstall, and repeat the installation steps with the new version.


== Frequently Asked Questions ==

= Where do I get help with this plugin? =

Leave a comment in the support forum and we'll do our best to support.


== Screenshots ==

1. screenshot-1.png shows how the question slots seamlessly into the comments section of the WordPress site.

2. screenshot-2.png shows the administration section of the Outerbridge HumanCaptcha plugin.


== Changelog ==

= 4.X =
* v4.0.0 (11 Jun 21) Fixed session issue, thanks to @tmuk.  Working with WP5.7

= 3.X =
* v3.1 (16 Jan 20) Improved foreign character handling
* v3.0 (05 Jan 18) Improved accessibility (thanks to Ondrej), moved admin menu to Settings, tidied admin page, added Settings link to Plugins page

= 2.X =
* v2.1 (28 Jan 15) General code tidy plus remove references to HCAC
* v2.0 (30 Sep 14) Added Russian translation files

= 1.X =
* v1.9 (29 Aug 14) Tested and stable up to WP4.0
* v1.8 (06 Aug 14) Updated collation and charset options
* v1.7 (05 Aug 14) Updated registration form processing to use the registration_errors filter as suggested by bml13
* 1.6 (30 Apr 14) Removed mysql_real_escape_string() as recommended for WP3.9
* v1.5.4 (12 Dec 13) Tested and stable up to WP3.8 and updated author name
* v1.5.3 (07 Oct 13) Added cross-reference to Human Contact and Captcha
* v1.5.2 (16 Aug 13) Corrected one missed translation point
* v1.5.1 (16 Aug 13) Added TH90 of MPW D&D's Persian translation file
* v1.5 (16 Aug 13) Made the plugin translation ready and tidied the code a bit
* v1.4 (24 Jul 13) Fixed the "add new" option which disappeared if the user deleted all questions
* v1.3 (23 Jul 13) Fixed UTF8 issue
* v1.2.1 (05 Jan 2012) No changes. v1.2 didn't commit properly.
* v1.2 (05 Jan 2012) Updated obr_admin_menu function to check against 'manage_options' rather than 'edit_plugins'.
* v1.1 (03 Jan 2012) Tested and stable up to WP3.3
* v1.0(30 Sep 2011) HumanCaptcha now added to registration and login forms as well as comments form.  Toggles added to admin menu to allow users to decide where HumanCaptcha is applied.

= 0.X =
* 0.2 (30 Aug 2011) Fixed session_start issue
* 0.1 (25 Aug 2011) Initial Release


== Upgrade Notice ==

= v4.0.0 =
* Fixed session issue, thanks to @tmuk.  Working with WP5.7

= v3.1 =
* Improved foreign character handling

= v3.0 =
* Improved accessibility (thanks to Ondrej), moved admin menu to Settings, tidied admin page, added Settings link to Plugins page

= v2.1 =
* General code tidy plus remove references to HCAC

= v2.0 =
* Added Russian translation files

= v1.9 =
* Tested and stable up to WP4.0

= v1.8 =
* Updated collation and charset options

= v1.7 =
* Updated registration form processing to use the registration_errors filter as suggested by bml13

= v1.6 =
* Removed mysql_real_escape_string() as recommended for WP3.9 

= v1.5.4 =
* Tested and stable up to WP3.8 and updated author name

= v1.5.3 =
* Added cross-reference to Human Contact and Captcha

= v1.5.2 =
* Corrected one missed translation point

= v1.5.1 =
* Added TH90 of MPW D&D's Persian translation file

= v1.5 =
* Made the plugin translation ready and tidied the code a bit

= v1.4 =
* Fixed the "add new" option which disappeared if the user deleted all questions

= v1.3 =
* Fixed UTF8 issue

= 1.2.1 =
* No changes. v1.2 didn't commit properly.

= 1.2 =
* Included safer admin menu.

= 1.1 =
* Tested and stable up to WP3.3

= 1.0 =
* New functionality added.

= 0.2 =
* Updated to improve plugin compatability.

= 0.1 =
* This is the initial release.
