<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      2.0.0
 * @package    DisplayLotroServer
 * @subpackage DisplayLotroServer/includes
 */
class DisplayLotroServer_Activator {

	/**
	 * Checks for PHP and Wordpress version
	 *
	 * @since    2.0.0
	 */
	public static function activate() {
    global $wp_version;

		if (version_compare(PHP_VERSION, '5.6.0', '<') && version_compare($wp_version, '4.3', '<')) {
			deactivate_plugins(DLS_BASENAME); // Deactivate ourself
			wp_die(__('Sorry, but you can\'t run this plugin, it requires PHP 5.6 or higher and Wordpress version 4.3 or higher.'));
			return;
		}
    
	}

}