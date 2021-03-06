<?php
/**
 * Plugin Name: Display Lotro Server
 * Plugin URI: https://hdro.blog/wordpress-plugin-display-lotro-server/
 * Description: Shows a server list of the choosen servers (see the settings). Can be placed as a widget or a shortcode in every article or page.
 *
 * Version: 1.4
 *
 * Author: Anna Fischer
 * Author URI: https://hdro.blog/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

define( 'DLS_VERSION', '1.4' );

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
		$serverslistEU = array( 'Belegaer', 'Evernight', 'Gwaihir', 'Laurelin', 'Sirannon' ),
		$serverslistUS = array( 'Arkenstone', 'Brandywine', 'Crickhollow', 'Gladden', 'Landroval'),
		$serverDE = array( 'Belegaer', 'Gwaihir' ),
		$serverEN = array( 'Evernight', 'Laurelin' ),
		$serverFR = array( 'Sirannon' ),
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

		$euNull = array_fill(0, 5, '0');
		$usNull = array_fill(0, 5, '0');
		$this->defaults = array(
			'EU' => array_combine($this->serverslistEU, $euNull),
			'US' => array_combine($this->serverslistUS, $usNull),
			'shortcode' => 0,
			'version' => DLS_VERSION
		);
		$this->dataServerArray = $this->get_cached_datacenter();

		$this->check_options();
		$this->options = get_option( $this->optiontag );

    // avoid empty entry in options array
		if( isset( $this->options[0] ) && empty( $this->options[0] ) ) {
			unset( $this->options[0] );
		}

		if( isset( $this->options['shortcode'] ) && $this->options['shortcode'] === 1) {
			add_shortcode( 'lotroserver', array( $this, 'lotroserver_shortcode' ) );
		} else {
			remove_shortcode( 'lotroserver' );
		}

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
		if (version_compare(PHP_VERSION, '5.6.0', '<') && version_compare($wp_version, '4.3', '<')) {
			deactivate_plugins(DLS_BASENAME); // Deactivate ourself
			wp_die(__('Sorry, but you can\'t run this plugin, it requires PHP 5.6 or higher and Wordpress version 4.3 or higher.'));
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
	function is_domain_available ( $strDomain ) {
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
	function get_server_status($site) {
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

	function get_cached_datacenter() {
		if ( false === ( $dls_datacenter_result = get_transient( 'dls_datacenter_result' ) ) ) {
			// It wasn't there, so regenerate the data and save the transient
			$dls_datacenter_result = $this->get_datacenter_result();
			set_transient( 'dls_datacenter_result', $dls_datacenter_result, 24 * HOUR_IN_SECONDS );
		}

		return $dls_datacenter_result;
	}

	/**
	* Get the server information array - given through the data center urls.
	*
	* @return $array contains all the needed server information
	* @since 1.0
	**/
	function get_datacenter_result() {

		$dataArray = array();

		$datacenterUrl = 'http://gls.lotro.com/GLS.DataCenterServer/Service.asmx?WSDL';
		$bullroarerUrl = 'http://gls-bullroarer.lotro.com/GLS.DataCenterServer/Service.asmx?WSDL';

		if( $this->is_domain_available($datacenterUrl) || $this->is_domain_available($bullroarerUrl) ) {
			try {
				$client = new SoapClient($datacenterUrl);
				$result = $client->GetDatacenters( array( 'game' => 'LOTRO' ) );
				$dataArray = $result->GetDatacentersResult->Datacenter->Worlds->World;

				if(isset($this->options['US']['Bullroarer']) && $this->options['US']['Bullroarer'] === '1') {
					$clientB = new SoapClient($bullroarerUrl);
					$resultB = $clientB->GetDatacenters( array( 'game' => 'LOTRO' ) );
					$dataArray[] = $resultB->GetDatacentersResult->Datacenter->Worlds->World;
				}
			} catch(Exception $e) {
				$logdir = DLS_PATH.'logs/';
				if(!is_dir($logdir)) mkdir($logdir);
				$file = 'log_'.date('Y-m-d').'.txt';
				$content = "[".date('Y-m-d')."] Error when trying to get lotro server information. Following message occured: ".$e->getMessage();
				file_put_contents($logdir.$file, $content, FILE_APPEND | LOCK_EX);
				return $dataArray;
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
							$status1 = $this->get_server_status($loginserver[0]);
							$status2 = $this->get_server_status($loginserver[1]);
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
			return __('The DataCenter is not available. Any Request to get the server status is not possible at the moment.', 'DLSlanguage');
		}

	}

	/**
	* Function to call and show the serverlist.
	*
	* @return returns html, a list with the given servers and there status or an error message
	* @since 0.9
	**/
	function show_serverlist($location='all') {

    // loop through the options and check which server was selected
		foreach( $this->options as $server ) {
			if( is_array($server) ) {
				foreach( $server as $name => $value ) {
					if( $value === '1' ) {
						$optionsarray[] = $name;
					}
				}
			}
		}

    // check if the options array exists and is not empty (otherwise no options were set)
		if(empty($optionsarray) || !isset($optionsarray)) {
			return __('There are no servers to show. Please check your settings and choose at least one server.', 'DLSlanguage');
		}

    // if location parameter is set (by widget or shortcode) loop through the array and delete the entries which are not eligable
    // e.g. if 'eu' is selected, remove the us-server from the array and vice versa
		if($location !== 'all') {
			switch($location) {
				case 'eu':
					foreach($optionsarray as $key => $value) {
						if(in_array($value, $this->serverslistUS)) unset( $optionsarray[$key] );
					}
				break;
				case 'us':
					foreach($optionsarray as $key => $value) {
						if(in_array($value, $this->serverslistEU)) unset( $optionsarray[$key] );
					}
				break;
			}
		}

    // get the serverlist from the datacenter based on the selected servers (saved as optionsarray)
		$servers = $this->get_serverlist($optionsarray);

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
				foreach( $optionsarray as $server ) {
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
