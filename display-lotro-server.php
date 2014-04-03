<?php
/**
 * Plugin Name: Display Lotro Server
 * Plugin URI: http://hdroblog.anna-fischer.info/wordpress-plugin-display-lotro-server/
 * Description: Shows a server list of the choosen servers (see the settings). Can be placed as a widget or a shortcode in every article or page.
 *
 * Version: 1.1
 *
 * Author: Anna Fischer
 * Author URI: http://hdroblog.anna-fischer.info/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'DLS_VERSION', '1.0' );

if ( !defined( 'DLS_PATH' ) )
	define( 'DLS_PATH', plugin_dir_path( __FILE__ ) );
if ( !defined( 'DLS_BASENAME' ) )
	define( 'DLS_BASENAME', plugin_basename( __FILE__ ) );
if ( !defined( 'DLS_IMAGES_URL' ) )
	define( 'DLS_IMAGES_URL', trailingslashit( plugin_dir_url( __FILE__ ) . 'img' ) );

/**
 * @package Main
 */
class DisplayLotroServer {

	public
		$options,
		$defaults,
		$optiontag = 'lotroserver_options',
		$optionsection = 'serversection',
		$serverslistEU = array( 'Anduin','Belegaer','Eldar','Estel','Evernight','Gilrain','Gwaihir','Laurelin','Maiar','Morthond','Sirannon','Vanyar','Withywindle' ),
		$serverslistUS = array( 'Arkenstone', 'Brandywine', 'Crickhollow', 'Dwarrowdelf', 'Elendilmir', 'Firefoot', 'Gladden', 'Imladris', 'Landroval', 'Meneldor', 'Nimrodel', 'Riddermark', 'Silverlode', 'Vilya', 'Windfola', 'Bullroarer'),
		$serverDE = array( 'Anduin', 'Belegaer', 'Gwaihir', 'Maiar', 'Morthond', 'Vanyar' ),
		$serverEN = array( 'Eldar', 'Evernight', 'Gilrain', 'Laurelin', 'Withywindle' ),
		$serverFR = array( 'Sirannon', 'Estel' ),
		$status = array('OFFLINE', 'ONLINE'),
		$dataServerArray;

	/**
	 * Constructor
	 */
	function __construct() {
		// Activation hook
    	register_activation_hook( __FILE__, array( $this, 'activate' ) );
    	// register_deactivation_hook( __FILE__, array( 'DisplayLotroServer', 'deactivate' ) );

    	// Load language file
		load_plugin_textdomain( 'DLSlanguage', false, dirname( DLS_BASENAME ) . '/languages/' );

		$euNull = array_fill(0, 13, '0');
		$usNull = array_fill(0, 16, '0');
		$this->defaults = array(
			'EU' => array_combine($this->serverslistEU, $euNull),
			'US' => array_combine($this->serverslistUS, $usNull),
			'shortcode' => true,
			'version' => DLS_VERSION
		);
		$this->dataServerArray = $this->get_server_info();

		$this->check_options();
		$this->options = get_option( $this->optiontag );

		add_action( 'plugins_loaded', array( $this, 'init' ), 1 );

	}

	/**
	 * Initialisation of the plugin
	 */
	function init() {
		// plugin upgrade
		if ($this->options && version_compare($this->options['version'], DLS_VERSION, '<')) {
			return 'You have to upgrade the plugin.';
		}

		if(is_admin()) {
			require_once('dls-admin.php');
			if(class_exists('LotroServerGUI')) {
				$DLSadmin = new LotroServerGUI();
				add_action( 'wp_ajax_nopriv_dlsajax-submit', array( $DLSadmin, 'reset_settings_ajax' ) );
        		add_action( 'wp_ajax_dlsajax-submit', array( $DLSadmin, 'reset_settings_ajax' ) );
			}
		}
	}

	/**
	 * Activation of the plugin
	 */
	function activate() {
		global $wp_version;
		if (version_compare(PHP_VERSION, '5.3.0', '<') && version_compare($wp_version, '3.5', '<')) {
			deactivate_plugins(DLS_BASENAME); // Deactivate ourself
			wp_die(__('Sorry, but you can\'t run this plugin, it requires PHP 5.3 or higher and Wordpress version 3.5 or higher.'));
			return;
		}
	}

	/**
    * Checks the optiontag and possibly set the default options
    *
    * @since 1.0
    */
    function check_options() {
    	// check to see if option already present
		if( get_option( $this->optiontag ) === false ) {
			// Adds an option for saving the settings
			add_option( $this->optiontag, $this->defaults, '', 'no' );
		} else {
			// option is already in the database
			// get the stored value, merge it with default and update
			$old_op = get_option( $this->optiontag );
			$new_op = wp_parse_args( $old_op, $this->defaults );
			update_option( $this->optiontag, $new_op );
		}
    }

    /**
	* helperfunction
	*
	* @see http://www.selfphp.de/code_snippets/code_snippet.php?id=11
	* @return true/false if domain is available or not
	* @since 0.9.5
	**/
	function domainAvailable ( $strDomain ) {
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
	* Getting the status of the server - with help of the given IP.
	*
	* @return gives back 'ONLINE' or 'OFFLINE'
	* @since 1.0
	**/
	function getServerStatus($site) {
		$fp = stream_socket_client('udp://'.$site, $errno, $errstr, 0.1);
		if (!$fp) {
			// echo "ERROR: $errno - $errstr<br />\n";
		    return $this->status[0];
		} else {
		    fwrite($fp, "\n");
		    stream_set_timeout($fp, 0.1);
		    fread($fp, 26);
		    fclose($fp);
		    return $this->status[1];
		}
	}

	/**
	* Get the server information array - given through the data center urls.
	*
	* @return $array contains all the needed server information
	* @since 1.0
	**/
	function get_server_info() {
		$options = get_option( $this->optiontag );

		$dataArray = array();

		$datacenterUrl = 'http://gls.lotro.com/GLS.DataCenterServer/Service.asmx?WSDL';
		$bullroarerUrl = 'http://gls-bullroarer.lotro.com/GLS.DataCenterServer/Service.asmx?WSDL';

		if( $this->domainAvailable($datacenterUrl) || $this->domainAvailable($bullroarerUrl) ) {
			$client = new SoapClient($datacenterUrl);
			$result = $client->GetDatacenters( array( 'game' => 'LOTRO' ) );
			$dataArray = $result->GetDatacentersResult->Datacenter->Worlds->World;

			if(isset($options['US']['Bullroarer']) && $options['US']['Bullroarer'] === '1') {
				$clientB = new SoapClient($bullroarerUrl);
				$resultB = $clientB->GetDatacenters( array( 'game' => 'LOTRO' ) );
				$dataArray[] = $resultB->GetDatacentersResult->Datacenter->Worlds->World;
			}

			return $dataArray;
		} else {
			return $dataArray;
		}
	}

	/**
	* Function to call the servers, IPs and the status.
	*
	* @return gives back an array of server/ip/status or a string when Lotro DataCenter isn't available
	* @since 1.0
	**/
	function get_serverlist($sa) {

		$serverlist = array();

		if( !empty( $this->dataServerArray ) ) {
			for($i=0;$i<sizeof($this->dataServerArray);$i++) {
				foreach ($sa as $value) {
					if(strpos($this->dataServerArray[$i]->Name, $value, 0) !== false) {
						$xmlfile = $this->dataServerArray[$i]->StatusServerUrl;
						$xml = @simplexml_load_file($xmlfile);
						if(!$xml) {
							$server = 'OFFLINE';
							return $server;
						} else {
							$loginserver = explode(';', $xml->loginservers);
							$status1 = $this->getServerStatus($loginserver[0]);
							$status2 = $this->getServerStatus($loginserver[1]);
							if($status1 === 'ONLINE' && $status2 === 'ONLINE') {
								$serverlist[] = array( 'Name' => (string) $xml->name, 'IP' => array_filter($loginserver), 'Status' => 'online');
							} else {
								$serverlist[] = array( 'Name' => (string) $xml->name, 'IP' => array_filter($loginserver), 'Status' => 'offline');
							}							
						}
					} else {
						continue;
					}
				}
			}

			return $serverlist;
		} else {
			__('The DataCenter is not available. Any Request to get the server status is not possible at the moment.', 'DLSlanguage');
		}
		
	}

	/**
	* Function to call and show the serverlist.
	*
	* @return gives back the status and the name of the server
	* @since 0.9
	**/
	function show_serverlist($location='all') {

		$options = get_option( $this->optiontag );

		if( isset( $options[0] ) && empty( $options[0] ) ) {
			unset( $options[0] );
		}

		foreach( $options as $server ) {
			if( is_array($server) ) {
				foreach( $server as $name => $wert ) {
					if( $wert === '1' ) {
						$serverarray[] = $name;
					}
				}
			}
		}

		if($location !== 'all') {
			switch($location) {
				case 'eu':
					foreach($serverarray as $key => $value) {
						if(in_array($value, $this->$serverslistUS)) unset( $serverarray[$key] );
					}
				break;
				case 'us':
					foreach($serverarray as $key => $value) {
						if(in_array($value, $this->serverslistEU)) unset( $serverarray[$key] );
					}
				break;
			}
		}

		$servers = $this->get_serverlist($serverarray);

		if( !empty($servers) && is_array($servers) ) {
			$listoutput = '<ul>';
			foreach( $servers as $server ) {
				if( $server['Status'] === 'online' ) {
					$listoutput .= '<li>'.$server['Name'].' (<img src="'.DLS_IMAGES_URL.'up.png" alt="online" />)</li>';
				} else {
					$listoutput .= '<li>'.$server['Name'].' (<img src="'.DLS_IMAGES_URL.'down.png" alt="offline" />)</li>';
				}
			}
			$listoutput .= '</ul>';

			return $listoutput;
		} else {
			if($servers === 'OFFLINE') {
				$listoutput = '<ul>';
				foreach( $serverarray as $server ) {
					$listoutput .= '<li>';
					if(in_array($server, $this->serverDE))
						$listoutput .= '[DE] ';
					if(in_array($server, $this->serverEN))
						$listoutput .= '[EN] ';
					if(in_array($server, $this->serverFR))
						$listoutput .= '[FR] ';

					$listoutput .= $server.' (<img src="'.DLS_IMAGES_URL.'down.png" alt="offline" />)';
					$listoutput .= '</li>';										
				}
				$listoutput .= '</ul>';

				return $listoutput;
			} else {
				return __('There are currently no server information. Please try again later.', 'DLSlanguage');
			}
		}
	}

	/**
	* the shortcode (maybe in later releases with additonal attributes)
	*
	* @param string $atts possible attributes
	* @return gives back the serverlist
	* @since 0.9
	**/
	function lotroserver_shortcode($atts) {

		/* 
		 * extract the attributes into variables
		 * loc = can be 'eu' or 'us' to show the specified serves
		 */
		extract(shortcode_atts(array(
			'loc' => 'all'
		), $atts));

	   return $this->show_serverlist($loc);

	}

}

if(class_exists('DisplayLotroServer')) {
    // instantiate the plugin class
    $DLS = new DisplayLotroServer();
}

require_once('dls-widget.php');

/**
* Function to register the Widget
*
* @since 0.9.8
*/

function lotroserver_register_widgets() {
	register_widget( 'LotroServerWidget' );
}
add_action( 'widgets_init', 'lotroserver_register_widgets' );

unset( $options );
