=== Display Git Status ===
Contributors: wpgitupdater
Tags: git
Requires at least: 5.0
Tested up to: 5.6
Stable tag: 1.0.1
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple WordPress plugin to display your current git branch and status in the admin area.

== Description ==

Display Git Status is a pretty simple plugin, all it needs is access to the `shell_exec` function and to be pointed at a git repository.

The plugin will not perform any state altering operations, it accesses the repository using read only methods to fetch information like branch name, last commit and status.

When the git repository is located (defaulting to `wp-content`, or overwritten via a setting) the plugin will add a new admin bar item with the git icon and the current branch name.

When your site has untracked changes the menu items background will turn red.

This is ideal as it gives you a small marker directly in the admin area that some changes have been made which you probably need to look into.

Additionally when accessing the plugins admin page you can see the result of the `git status` command, and the last commit applied to your site.

== Frequently Asked Questions ==

= What repositories can it track? =

The plugin can read from any git repository, it performs on basic git commands.
All you need to do is ensure your web user can access the git repository, and that the `shell_exec` function is enabled.

Additionally it doesnt have to the root or wp-content folder. The plugin can read and display information for any repository it has access to on the filesystem.

= Does it commit changes? =

No this plugin only reads your git repositories status. There are no plans to make this anything more.

= What permissions are required? =

Anyone with the `manage_options` permissions can see/edit the git status admin page.

== Screenshots ==

1. Display Git Status settings page, here you can set the repository location, additionally the git status and last commit information.
2. When your site and repository and are in sync you can see the branch name in the admin bar.
3. And when your site and repository are divergent the admin bar items background changes to red, alerting you of a problem.

== Changelog ==

= 1.0.1 =
* Fix Command and XXS inject, props to @xrzhev

= 1.0.0 =
* Initial Release

== Upgrade Notice ==

= 1.0 =
NA, Initial Release
