## Display LotRO server

Wordpress-Plugin for showing the server status of LotRO servers (as widget or shortcode).

Requires at least (wordpress version): 3.4.2  
Tested up to: 3.6

License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html

### Description

(At the moment) This plugin uses the external status-script from http://status.warriorsofnargathrond.com

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
