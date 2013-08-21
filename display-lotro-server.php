<?php
/**
 * Plugin Name: Display Lotro Server
 * Plugin URI: http://hdroblog.anna-fischer.info/wordpress-plugin-display-lotro-server/
 * Description: Displays a server list of the configured servers (see the settings). Can be placed as a widget or a shortcode in every article or page. This plugin uses the status-script from http://status.warriorsofnargathrond.com
 *
 * Version: 0.9.8
 *
 * Author: Anna Fischer
 * Author URI: http://hdroblog.anna-fischer.info/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

require_once('dls-widget.php');
require_once('dls-admin.php');

class DisplayLotroServer {

	public static
		$optiontag = 'lotroserver_options',
		$serverslistEU = array( 'Anduin','Belegaer','Eldar','Estel','Evernight','Gilrain','Gwaihir','Laurelin','Maiar','Morthond','Sirannon','Vanyar','Withywindle' ),
		$serverslistUS = array( 'Arkenstone', 'Brandywine', 'Crickhollow', 'Dwarrowdelf', 'Elendilmir', 'Firefoot', 'Gladden', 'Imladris', 'Landroval', 'Meneldor', 'Nimrodel', 'Riddermark', 'Silverlode', 'Vilya', 'Windfola', 'Bullroarer'),
		$serverDE = array( 'Anduin', 'Belegaer', 'Gwaihir', 'Maiar', 'Morthond', 'Vanyar' ),
		$serverEN = array( 'Eldar', 'Evernight', 'Gilrain', 'Laurelin', 'Withywindle' ),
		$serverFR = array( 'Sirannon', 'Estel' );

	private static
		$arySettings = array(
			'shortcode' => true
		);

	/**
	* Constructor
	*
	* @since 0.1
	* @version 0.9.8
	**/
	function __construct() {
		self::constants();

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );

		// Add different admin settings and admin menu
		add_action( 'admin_init', array( 'LotroServerGUI', 'lotroserver_admin_init') );
		add_action( 'admin_menu', array( $this, 'buildAdminMenu' ) );

		// Settings changed?
		if (isset($_POST['action']) && $_POST['action'] == 'save_serveroptions')
			LotroServerGUI::saveSettings();

		// Add meta links to plugin details
		add_filter( 'plugin_action_links', array( $this, 'set_plugin_meta' ), 10, 2 );

		// Enable shortcodes if enabled
		if (self::$arySettings['shortcode'])
			add_shortcode( 'lotroserver', array( $this, 'lotroserver_shortcode') );

		// Load language file
		load_plugin_textdomain( 'DLSlanguage', false, DLS_LANG_URL );
	}

	/**
    * Defines constants used by the plugin
    *
    * @since 0.9.8
    */
    function constants() {
        define( 'DLS_VERSION', '0.9.7' );
        define( 'DLS_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
        define( 'DLS_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
        define( 'DLS_PLUGIN_NAME', plugin_basename( __FILE__ ) );
        define( 'DLS_PLUGIN_DIR', dirname( plugin_basename( __FILE__ ) ) );
        define( 'DLS_IMAGES_URL', trailingslashit( DLS_PLUGIN_URL . 'img' ) );
        define( 'DLS_LANG_URL', trailingslashit( DLS_PLUGIN_DIR . '/languages' ) );
    }

	/**
	* Activation of the plugin
	*
	* @since 0.1
	* @version 0.9.8
	**/
	function activate() {
		global $wp_version;
		if (version_compare(PHP_VERSION, '5.3.0', '>=') && version_compare($wp_version, '3.4.2', '>=')) {
			deactivate_plugins(DLS_PLUGIN_NAME); // Deactivate ourself
			wp_die(__('Sorry, but you can\'t run this plugin, it requires PHP 5.3 or higher and Wordpress version 3.4.2 or higher.'));
			return;
		}

		#Adds an option for saving the choosen servers
		add_option( self::$optiontag, array(), '', 'no' );
	}

	/**
	* Uninstallation
	*
	* @global wpdb get access to the wordpress-database and to clean it after uninstallation
	* @since 0.1
	**/
	function uninstall() {
		global $wpdb;
		# delete the options
		delete_option(self::$optiontag);
		#clean up the database
		$wpdb->query("OPTIMIZE TABLE `" .$wpdb->options. "`");
	}

	/**
	* Send a message after installing/updating
	*
	* @since 0.1
	* @version 0.9.8
	*/
	function updateMessage() {
		# success message after installation
		$strText = 'DisplayLotroServer '.DLS_VERSION.' '.__('installed','DLSlanguage').'.';

		$strSettings = __('Please update your configuration and choose the servers','DLSlanguage');
		$strLink = sprintf('<a href="options-general.php?page=%s">%s</a>', DLS_PLUGIN_DIR, __('Settings', 'DLSlanguage'));
		
		# display information message for setting up the servers
		echo '<div class="updated"><p>'.$strText.' <strong>'.__('Important', 'DLSlanguage').':</strong> '.$strSettings.': '.$strLink.'.</p></div>';
	}

	/**
	* Add plugin meta links to plugin details
	*
	* @see http://wpengineer.com/1295/meta-links-for-wordpress-plugins/
	* @since 0.5
	* @version 0.9.8
	*/
	function set_plugin_meta($links, $file) {
	
		/* create link */
		if ( $file == DLS_PLUGIN_NAME ) {
			array_unshift(
				$links,
				sprintf( '<a href="options-general.php?page=%s">%s</a>', DLS_PLUGIN_DIR, __('Settings', 'DLSlanguage') )
			);
		}
		
		return $links;
	}

	/**
	* Adds an option page for configuration
	*
	* @since 0.1
	* @version 0.9.8
	**/
	function buildAdminMenu() {
		$intOptionsPage = add_options_page( __('Settings: Display Lotro Server', 'DLSlanguage'), __('Display Lotro Server', 'DLSlanguage'), 'manage_options', 'display-lotro-server', array( 'LotroServerGUI', 'showAdminPage' ) );
	}

	/**
	* Helferfunktion zur Serverliste
	*
	* @see http://www.selfphp.de/code_snippets/code_snippet.php?id=11
	* @return true/false if domain is available or not
	* @since 0.9.5
	* @version 0.9.6
	**/
	static function domainAvailable ( $strDomain ) {
		$rCurlHandle = curl_init ( $strDomain );

		curl_setopt ( $rCurlHandle, CURLOPT_CONNECTTIMEOUT, 10 );
		curl_setopt ( $rCurlHandle, CURLOPT_HEADER, TRUE );
		curl_setopt ( $rCurlHandle, CURLOPT_NOBODY, TRUE );
		curl_setopt ( $rCurlHandle, CURLOPT_RETURNTRANSFER, TRUE );

		$strResponse = curl_exec ( $rCurlHandle );

		curl_close ( $rCurlHandle );

		if ( !$strResponse )
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	* Function to call and show the serverlist.
	*
	* @return gives back the status and the name of the server
	* @since 0.9
	* @version 0.9.8
	**/
	static function show_serverlist($location='all') {

		$options = get_option( self::$optiontag );

		if( isset( $options[0] ) && empty( $options[0] ) ) {
			unset( $options[0] );
		}

		foreach( $options as $name => $wert ) {
			if( $wert === '1' ) {
				$serverarray[] = $name;
			}
		}

		if($location !== 'all') {
			switch($location) {
				case 'eu':
					foreach($serverarray as $key => $value) {
						if(in_array($value, self::$serverslistUS)) unset( $serverarray[$key] );
					}
				break;
				case 'us':
					foreach($serverarray as $key => $value) {
						if(in_array($value, self::$serverslistEU)) unset( $serverarray[$key] );
					}
				break;
			}
		}

		$servers = implode( ",", $serverarray );
		# read xmlfile, check if the domain is available and load the xml data to the array $xml
		$xmlfile = 'http://status.warriorsofnargathrond.com/activity/external.php?status='.$servers;
		if( self::domainAvailable($xmlfile) ) {
			$xml = simplexml_load_file($xmlfile);

			$listoutput = '<ul>';
			foreach( $xml as $server ) {
				# the number 2 stands for the status online
				if( $server->status == 2 ) {
					$listoutput .= '<li>'.$server->name.' (<img src="'.DLS_IMAGES_URL.'up.png" alt="online" />)</li>';
				} else {
					$listoutput .= '<li>'.$server->name.' (<img src="'.DLS_IMAGES_URL.'down.png" alt="offline" />)</li>';
				}
			}
			$listoutput .= '</ul>';

			return $listoutput;
		} else {
			return __('There were no current server information found. Please try again later.', 'DLSlanguage');
		}
	}

	/**
	* the shortcode (maybe in later releases with additonal attributes)
	*
	* @param string $atts possible attributes
	* @return gives back the serverlist
	* @since 0.9
	* @version 0.9.7
	**/
	function lotroserver_shortcode($atts) {

		/* 
		 * extract the attributes into variables
		 * loc = can be 'eu' or 'us' to show the specified serves
		 */
		extract(shortcode_atts(array(
			'loc' => 'all'
		), $atts));

	   return self::show_serverlist($loc);

	}

}

/**
* Function to register the Widget
*
* @since 0.9.8
*/
function lotroserver_register_widgets() {
	register_widget( 'LotroServerWidget' );
}
add_action( 'widgets_init', 'lotroserver_register_widgets' );

/* start the plugin */
new DisplayLotroServer;
 ?>