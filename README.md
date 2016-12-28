## Display LotRO server

Wordpress-Plugin for showing the server status of LotRO servers (as widget or shortcode).

Requires at least (wordpress version): 4.3  
Tested up to: 4.7

License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

### Description

With "Display Lotro Server" you can configure which servers should be displayed. After configuring you can use the included Widget or the Shortcode [lotroserver] to display your list of servers.
The servers will be shown with their names and their localization (e.g. [DE] for German servers) and in brackets behind the name, a small arrow will be shown:
* a green arrow (pointing to the top) for "online"
* or a red arrow (pointing to the bottom) for "offline"

You can put the Widget in every sidebar you want, and the shortcode in every article or page.

### Installation

You need an existing Wordpress installation to use this plugin!  
Please follow these instructions to install the plugin correctly.

1. Download the plugin (zip-file) and extract it on your PC.
2. Upload the folder "display-lotro-server" to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the servers you want to show under the 'Settings' menu in WordPress -> "Display Lotro Server"

### Frequently Asked Questions

#### How to use the shortcode?
You can use the shortcode `lotroserver` without any attributes to show all the servers you have checked in the configuration in one list.
Since release 0.9.7 you can add the attribute 'loc' to the shortcode. The attribute stands for 'location' and can have two different values: 'eu' or 'us'. If you don't insert any value, it will be handled like there were no attribute 'loc'.

Example: `[lotroserver loc="us"]`
This will show only the US-servers you have checked in the configuration.

### Changelog

### 1.3
* Enables two (or more) lotro server widgets at the same time
* Includes the settings and the functionality of the shortcode
* Fixed some bugs and php warnings
* Tested up to the new WP version 4.7
* Did a lot of code review

### 1.2
* Updated the server list, after the big world closure in late 2015
* Fixed a few notices and warnings in the current wordpress version
* did some code cleanup

### 1.1
* Fixed a bug with the serverlist output (when the datacenter urls are empty)
* Updated the admin interface for better usability (e.g. added ajax support)
* Added the possibility to reset the settings to default
* Combined the settings with the sanitize callback (in preparation of coming up settings)
* Updated the translation
* some code cleanup to reduce the filesizes

### 1.0
* Re-structured a lot of code
* (in addition to remove the addiction to the status script of warriorsofnargathrond.com)
* revamped the admin interface (added a style and changed the saving options structure)
* updated the translation
* fixed the widget to be compatible with the code changes

#### 0.9.8
* Re-structured the code
* Fixed wrong version comparison
* Added choice of server location to the widget
* Added security fixes
* Updated translation
* code cleanup

#### 0.9.7
* Added 'loc' attribute to the shortcode
* Added first FAQ to the Readme
* Bugfixes
* Updated (german and missing) translation
* Added/updated/translated some comments

#### 0.9.6
* Fixed some strict PHP Errors/Warnings
* Tested compatibility for WP 3.6
* code cleanup

#### 0.9.5
* Bugfixes
* Tested compatibility for WP 3.5
* Added translation possibility
* Added german translation
* some code cleanup

#### 0.9
* Bugfixes
* Added more servers
* Added the functionality of the shortcode

#### 0.5
* Added more servers and made the Widget functional

#### 0.1
* First Alpha-Status with a functional backend
