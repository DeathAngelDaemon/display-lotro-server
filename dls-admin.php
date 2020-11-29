<?php

/**
 * @package Admin
 */

/* security request */
if ( ! class_exists('DisplayLotroServer') ) {
	die();
}

/**
* GUI for the DLS Adminpage
*
* @since 0.9.8
*/
class LotroServerGUI extends DisplayLotroServer {

	/**
	 * Class constructor, which basically only hooks the init function on the init hook
	 */
	public function __construct() {
		// Add admin settings and admin menu
    add_action( 'admin_init', array( $this, 'lotroserver_admin_init' ) );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'config_page_styles' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . DLS_BASENAME , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	private function add_settings_link( $links ) {
		$settings_link = '<a href="' . menu_page_url( 'display-lotro-server', false ) . '">' . __( 'Settings', 'DLSlanguage' ) . '</a>';
    array_push( $links, $settings_link );
    return $links;
	}

	/**
	* Register the settings and validation method to save the options.
	**/
	private function lotroserver_admin_init() {
		register_setting( $this->optionsection, $this->optiontag, array( $this, 'dls_options_validate' ) );
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	private function add_settings_page() {
		add_options_page( __('Settings: Display Lotro Server', 'DLSlanguage'), __('Display Lotro Server', 'DLSlanguage'), 'manage_options', 'display-lotro-server',  array( $this, 'build_options_page_html' ) );
	}

	/**
	 * Loads the required styles for the config page.
	 */
	private function config_page_styles() {
		global $pagenow;
		if ( $pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] === 'display-lotro-server' ) {
			wp_register_style( 'dls-admin-css', plugins_url( 'css/admin-style.css', __FILE__ ), array(), DLS_VERSION );
			wp_register_style( 'dls-font-css', plugins_url( 'css/font-awesome.min.css', __FILE__ ), array(), DLS_VERSION );
			wp_enqueue_style( 'dls-admin-css' );
			wp_enqueue_style( 'dls-font-css' );

			wp_enqueue_script( 'dls-ajax-request', plugins_url( 'js/admin-ajax.js', __FILE__ ), array( 'jquery' ) );
			wp_localize_script( 'dls-ajax-request', 'DLSAjax', array(
			    'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			    'resetSettingsNonce' => wp_create_nonce( 'dlsajax-reset-settings-nonce' ),
			    )
			);
		}
	}

 	/**
	 * Under construction!
	 * Function for ajax usage
	 */
	public function reset_settings_ajax() {
 		global $DLS;
    $nonce = $_POST['resetSettingsNonce'];

		// verify the wordpress nonce to avoid security risks
    if ( ! wp_verify_nonce( $nonce, 'dlsajax-reset-settings-nonce' ) )
      die( 'Cheating!');

    // ignore the request if the current user doesn't have
    // sufficient permissions
    if ( current_user_can( 'manage_options' ) ) {
    	delete_option($this->optiontag);
	 		$new = array_replace($DLS->options, $DLS->defaults);
	 		if( is_array($new) ) {
	 			$success = __('Your settings are now on default.', 'DLSlanguage');
	 		} else {
	 			$success = __('Something went wrong! Please contact the plugin developer.', 'DLSlanguage');
	 		}

      // generate the response
      $response = json_encode( $success );

      // response output
      header( "Content-Type: application/json" );
      echo $response;
    }

    exit;
	}

	/**
	 * Under construction!
	 * Validate function to sanitize text fields or textareas
	 *
	 * @return array/string $input the option which will be saved here
	 */
	private function dls_options_validate($input) {
		if ( ! check_admin_referer( 'save_serveroptions', '_wpnonce-lotroserver' ) ) {
      die( 'Cheating!');
    }

    if( isset($input['shortcode']) ) {
      $input['shortcode'] = intval($input['shortcode']);      
    }

		return $input;
	}

	/**
	* Load the HTML for the admin page
	**/
	private function build_options_page_html() {
		require_once('inc/dls-admin-page-display.php');
	}

}
