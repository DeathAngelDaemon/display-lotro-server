<?php

/**
 * Define the logic for the datacenter information.
 *
 * Get the datacenter information about the servers and return cached values.
 *
 * @since      2.0.0
 * @package    DisplayLotroServer
 * @subpackage DisplayLotroServer/includes
 */
class DisplayLotroServer_Datacenter {

  /**
	 * The options of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array    $options    The current options of this plugin.
	 */
	private $options;

  /**
	 * An array containing information about the datacenters.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array    $dataServerArray    An array with datacenters.
	 */
	private $dataServerArray;

  /**
   * An array defining the two status messages that can be returned by datacenter status.
   * 
   * @since   2.0.0
   * @access  private
   */
  private $status = array('OFFLINE', 'ONLINE');

  /**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param    array    $options    Options of the plugin.
	 */
	public function __construct( $options ) {

		$this->options = $options;
    $this->dataServerArray = $this->get_cached_datacenter();

	}

  /**
	 * Function to call the servers, IPs and the status.
	 *
	 * @return gives back an array of server/ip/status or a string when Lotro DataCenter isn't available
	 * @since 1.0
	 */
	public function get_serverlist($sa) {

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
	 * Get the datacenter result from cache if exists. Otherwise add it to cache/transient.
	 *
	 * @since    1.0.0
	 */
	private function get_cached_datacenter() {
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
	 */
	private function get_datacenter_result() {

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
	 * helperfunction
	 *
	 * @see http://www.selfphp.de/code_snippets/code_snippet.php?id=11
	 * @return true/false if domain is available or not
	 * @since 0.9.5
	 */
	private function is_domain_available ( $strDomain ) {
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
	 */
	private function get_server_status($site) {
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

}