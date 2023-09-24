=== BP Attachments ===
Contributors: buddypress
Donate link: https://wordpressfoundation.org
Tags: BuddyPress, attachments, media, add-on
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.1
Requires PHP: 5.6
Tested up to: 6.3
Stable tag: 1.2.0

BP Attachments is a BuddyPress Add-on to manage your community members media.

== Description ==

The BP Attachments Add-on is being developed and maintained by the official BuddyPress development team. Thanks to it your community members can finally share media the BuddyPress way!

= Current features =

* Front-end and back-end Media library for all your members.
* Administrators can moderate Members media from the back-end Media library.
* Members can upload public or private media into their personal Media Library as well as organize them creating file directories, photo albums, movie or music playlists.
* User media blocks for all your site's content contributors.
* Members can upload and attach public media to their activity updates (the BP Activity component needs to be active).
+ Members can upload and attach private media to their private messages (the BP Messages component needs to be active).
+ All members public media can be browsed from the Community Media directory.

= Experimental features =

* A new Member's profile image Upload UI

= Future features =

* A new cover image Upload UI.
* Share media with friends.
* Share media between group members.

= Join the BuddyPress community =

If you're interested in contributing to BuddyPress, we'd love to have you. Head over to the [BuddyPress Documentation](https://codex.buddypress.org/participate-and-contribute/) site to find out how you can pitch in.

Growing the BuddyPress community means better software for everyone!

== Installation ==

= Requirements =

* WordPress 6.1.
* BuddyPress 11.0.0.

= Automatic installation =

Using the automatic installation let WordPress handles everything itself. To do an automatic install of BP Attachments, log in to your WordPress dashboard, navigate to the Plugins menu. Click on the Add New link, then activate the "BuddyPress Add-ons" tab to quickly find the BP Attachments plugin's card.
Once you've found BP Attachments, you can view details about the latest release, such as community reviews, ratings, and description. Install the BP Attachments Add-on by simply pressing "Install Now".

== Frequently Asked Questions ==

= Where can I get support? =

Our community provides free support at [https://buddypress.org/support/](https://buddypress.org/support/).

= Where can I report a bug? =

Report bugs or suggest ideas at [https://github.com/buddypress/bp-attachments/issues](https://github.com/buddypress/bp-attachments/issues), participate to this plugin development at [https://github.com/buddypress/bp-attachments/pulls](https://github.com/buddypress/bp-attachments/pulls).

= Who builds the BP Attachments Add-on? =

BP Attachments is a BuddyPress Add-on and is free software, built by an international community of volunteers. Some contributors to BuddyPress are employed by companies that use BuddyPress, while others are consultants who offer BuddyPress-related services for hire. No one is paid by the BuddyPress project for his or her contributions.

If you would like to provide monetary support to the BP Attachments Add-on or the BuddyPress plugin, please consider a donation to the [WordPress Foundation](https://wordpressfoundation.org), or ask your favorite contributor how they prefer to have their efforts rewarded.

== Screenshots ==

1. **The Member's media library**
2. **The "Attach Media" Activity Post Form button**
3. **The Community Video and Community Image blocks in the WP Post Editor**

== Upgrade Notice ==

= 1.2.0 =

No specific upgrade tasks to perform.

= 1.1.0 =

No specific upgrade tasks to perform.

= 1.0.0 =

Initial version of the plugin, no upgrade needed.

== Changelog ==

= 1.2.0 =

- Make sure to only override the WP queried object if it is an Attachment one.
- Only list the BP Attachments Add-on in optional components into the BP Components Administration screen.

= 1.1.0 =

- Make the Add-on BuddyPress 12.0 ready.
- Disable the experimental avatar UI by default.
- Add a filter to prepend the Medium block to the Activity text block.
- Add a delete avatar link into the change avatar screen.

= 1.0.0 =

Initial version of the plugin.
