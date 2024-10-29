=== Amuga Ajax Log ===
Contributors: asheboro
Donate link: paypal.me/davepinson
Tags: troubleshoot, utilities, ajax, admin-ajax
Requires at least: 4.8
Tested up to: 5.5.1
Requires PHP: 5.6
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Amuga Ajax Log is a tool built for troubleshooters who need to know what is hitting admin-ajax.

== Description ==

It's time to find out what is really hitting your admin-ajax.php file so much.

Contrary to popular belief, hits to admin-ajax.php do not only come from the Heartbeat API. Many plugins make use of WordPress' built-in ajax functionality, sometimes negatively impacting site performance and resource usage. Unfortunately, it's not always easy to tell what is causing these hits.

Many blogs and web hosts will tell you to install a plugin to limit hits to the Heartbeat API. While this is great for hits coming from the Heartbeat API, it does nothing to affect plugins that use admin-ajax. So how do you stop what you can't see?

That's where Amuga Ajax Log comes in.

Amuga Ajax Log tracks and logs actions that hit admin-ajax. It gives you a look at essential data, such as:

* Requested action name
* Possible function or method name
* Suspected location
* Page that triggered the hit

The plugin does not track information about the user.

Amuga Ajax Log was built to make it easier to see what is really increasing your admin-ajax usage. The plugin logs the data to a flat file or a custom database table. It also provides an easy to read Leaderboard that shows you which actions are hitting the most.

"But won't a busy site cause a lot of data to be tracked?" asks curious local man.

Yes, that is true. That is why we don't recommend leaving the plugin activated for extended periods. As a troubleshooter, your goal with this plugin is to obtain enough information to solve your issue. With the Leaderboard and Recent Hits list, you will have a higher chance of tracking down what is hammering your admin-ajax.php file.

Another nice feature of Amuga Ajax Log is that it can clean up after itself. We've provided two easy options for cleaning up data.

**Purge Current Database Log**

From the Settings page, check the box that says Purge Current Database Log, hit Save, and Amuga Ajax Log will clear all admin-ajax hit records it has stored in the database.

**Remove All Data on Deactivation**

We are a big fan of plugins that provide an option to remove their data after deactivation. Amuga Ajax Log does the same. From the Settings page, check the box that says Remove All Data on Deactivation, hit Save, and when you deactivate Amuga Ajax Log from the Plugins page, the plugin does the following:

* Removes our flag log file if it exists
* Removes our custom database table
* Removes our data from the options table

**A Few Notes:**

Keep in mind that this plugin does not stop admin-ajax hits; it only records them and provides information about the hit. It is up to you or your developer to determine what to do next.

Because you can have Classes that share method names (ex. ILike->Tacos, ILove->Tacos, GiveMe->Tacos), it is possible that there will be multiple Locations listed.

Sometimes, we can't figure out where something is. In those cases, we recommend using the action name or the function name and running a Grep search using SSH, or doing a text search with a tool such as Notepad++.

== Installation ==

1. Install Amuga Ajax Log by uploading the amuga-ajax-log.zip ZIP file.
2. Activate it through the 'Plugins' menu in WordPress.
3. It is now ready to work. Default settings may be altered in the Amuga Ajax Log settings page.

== Frequently Asked Questions ==

= Does my server need anything super special to run this? =

This was developed using PHP 7.4 and should work on 7.0+. Anything lower has not been tested.

= Will this plugin stop a plugin from using admin-ajax? =

No, this plugin only watches what fires through admin-ajax and logs it. If we added the option to unhook an action running through admin-ajax, your site would probably break and you would be sad.

= How does this plugin find the file sending the action to admin-ajax? =

We search for the action by using function names and searching for class methods. If we are unable to determine the location, we recommend using a grep search via SSH to find what is using the action name we've logged.

= Is it true that this plugin will make me more glamorous? =

Yes. Absolutely. Probably. No, not really.

== Changelog ==

= 1.0 =
August 21, 2020

* Rewrote large chunks
* Battle tested
* Ate a sandwich
* Released plugin to the world, as it is possible other troubleshooters may find this useful. This changelog will make sense later, but this is the first release.

-----

= 0.1 =
November 21, 2017

* Built the plugin for personal use
