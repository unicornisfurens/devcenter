=== WPSiteSync for Content ===
Contributors: serverpress, spectromtech, davejesch, Steveorevo
Donate link: http://wpsitesync.com
Tags: content, synchronization, database, staging, development, live, server, meta, attachments, taxonomies, moving data, data migration, push data, push content, import, export, spectromtech, serverpress, desktopserver, acf, advanced custom fields
Requires at least: 3.5
Tested up to: 4.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides features for synchronizing content between two WordPress sites.

== Description ==

WPSiteSync for Content is a tool that allows you to Synchronize content between WordPress installs. This allows you to have a development or staging site where you can create content and have it approved, then synchronize that content to another live install. When Syncing content, all meta data, attachments, taxonomy and other information for the the Content is moved.

[youtube https://www.youtube.com/watch?v=KpeiTMbdj_Y]

><strong>Support Details:</strong> We are happy to provide support and help troubleshoot issues. Visit our Contact page at <a href="http://serverpress.com/contact/" target="_blank">http://serverpress.com/contact/</a>. Users should know however, that we check the WordPress.org support forums once a week on Fridays from 10am to 12pm PST (UTC -8).

The WPSiteSync for Content plugin was specifically designed to ease your workflow when creating content between development, staging and live servers. The tool removes the need to migrate an entire database, potentially overwriting new content on the live site, just to update a few pages or posts. Now you can easily move your content from one install to another with the click of a button, reducing errors and saving you time.

While WPSiteSync for Content is optimized to work with local development tools such as DesktopServer, it is designed to be fully functional in any WordPress environment.


The WPSiteSync for Content Features:

* Synchronize Content (posts and pages) between sites.
* Automatically updates taxonomy information.
* Synchronizes meta-data (including meta-data created with Advanced Custom Fields).
* Synchronizes attachments, moving image files between sites.

<strong>Important Note:</strong> The WPSiteSync for Content plugin is <em><strong>currently a Release Candidate.</strong></em> While we have done a lot of testing, you may still experience some errors. Please use with caution. We welcome your feedback and suggestions on how we can make this better.

<strong>ServerPress, LLC is not responsible for any loss of data that may occur as a result of WPSiteSync for Content's use.</strong> However, should you experience such an issue, we want to know about it right away.

We're also hard at work on additional features and are nearing completion for add-ons that will allow for syncing Custom Post Types, Author attribution, pulling content from live to test/staging server, syncing comments, and more.

== Installation ==

Installation instructions: To install, do the following:

1. From the dashboard of your site, navigate to Plugins --> Add New.
2. Select the "Upload Plugin" button.
3. Click on the "Choose File" button to upload your file.
3. When the Open dialog appears select the wpsitesynccontent.zip file from your desktop.
4. Follow the on-screen instructions and wait until the upload is complete.
5. When finished, activate the plugin via the prompt. A confirmation message will be displayed.

or, you can upload the files directly to your server.

1. Upload all of the files in `wpsitesynccontent.zip` to your  `/wp-content/plugins/wpsitesynccontent` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

You will need to Install and Activate the WPSiteSync for Content plugin on your development website (the Source) as well as the Target web site (where the Content is being moved to).

Once activated, you can use the Configuration page found at Settings -> WPSiteSync for Content, on the Source website with the URL of the Target and the login credentials to use when sending data. This will allow the WPSiteSync for Content plugin to communicate with the Target website, authenticate, and then move the data between the websites. You do not need to Configure WPSiteSync for Content on the Target website as this will only be receiving Synchronization requests from the Source site.

== Frequently Asked Questions ==

= Do I need to Install WPSiteSync for Content on both sites? =

Yes! The WPSiteSync for Content needs to be installed on the local or Staging server (the website you're moving the data from - the Source), as well as the Live server (the website you're moving the data to - the Target).

= Does this plugin Synchronize all of my content at once? =

No. WPSiteSync for Content will only synchronize the Page or Post content that you are editing. And it will only Synchronize the content when you tell it to. This allows you to control exactly what content is moved between sites and when it will be moved.

= Will this overwrite data while I am editing? =

No. WPSiteSync checks to see if the Content is being edited by someone else on the Target web site. If it is, it will not update the Content, allowing you to coordinate with the users on the Target web site.

In addition, each time Content is updated or Synchronized on the Target web site, a Post Revision is created (using the Post Revision settings). This allows you to recover Content to a previous version.

= Does WPSiteSync only update Page and Posts Content? =

Yes. Support for Custom Post Types is coming very soon. Additional plugins for User Attribution, Synchronizing Comments and Pulling content are in the testing stage and will be released soon as well.

More complex data, such as Woo Commerce products, Forms (like Gravity Forms or Ninja Forms), and other plugins that use custom database tables will be supported by additional plugins that work with those products.

== Screenshots ==

1. Configuration page.
2. WPSiteSync for Content metabox.

== Changelog ==

= 0.9.6 - May 20, 2016 =
* Release Candidate 1
* Add authentication token rather than storing passwords.
* Fix issue with not removing Favorite Image on Target when image removed on Source.

= 0.9.5 - May 2, 2016 =
* Fix CSS conflict with wp-product-feed-manager plugin; load CSS/JS only on needed pages and make CSS rules more specific.

= 0.9.4 - Apr 29, 2016 =
* Fix media upload for images referenced within the Content and for featured images.

= 0.9.3 - Apr 26, 2016 =
* Fix taxonomy sync issue when taxonomy does not exist in some conditions on Target.

= 0.9.2 - Apr 22, 2016 =
* Fix runtime error when embeded images are in content.

= 0.9.1 - Apr 20, 2016 =
* Work around for missing apache_request_headers() on SiteGround; fix misnamed header constant.

= 0.9 - Apr 18, 2016 =
* First release - BETA

== Upgrade Notice ==

= 0.9 =
First release.
