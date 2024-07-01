<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @since      2.0.0
 * @package    DisplayLotroServer
 * @subpackage DisplayLotroServer/public
 */
class DisplayLotroServer_Public {

  /**
	 * An instance of the Datacenter class.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private $datacenter;

  /**
   * An array containing plugin options.
   * 
   * @since   2.0.0
   * @access  private
   */
  private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $datacenter, $options ) {

    $this->datacenter = $datacenter;
    $this->options = $options;

	}

  /**
	 * Function to call and show the serverlist.
	 *
	 * @return returns html, a list with the given servers and there status or an error message
	 * @since 0.9
	 */
	public function show_serverlist($location='all') {

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
						if(in_array($value, $this->options['US'])) unset( $optionsarray[$key] );
					}
				break;
				case 'us':
					foreach($optionsarray as $key => $value) {
						if(in_array($value, $this->options['EU'])) unset( $optionsarray[$key] );
					}
				break;
			}
		}

    // get the serverlist from the datacenter based on the selected servers (saved as optionsarray)
		$servers = $this->datacenter->get_serverlist($optionsarray);

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
					if(in_array($server, $this->options['DE']))
						$listoutput .= '[DE] ';
					if(in_array($server, $this->options['EN']))
						$listoutput .= '[EN] ';
					if(in_array($server, $this->options['FR']))
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
	 * The shortcode to show server list anywhere.
   * 
   * Use the "loc" attribute to define which servers should be shown.
	 *
	 * @param string $atts possible attributes
	 * @return gives back the serverlist
	 * @since 0.9
	 */
	public function lotroserver_shortcode($atts) {
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