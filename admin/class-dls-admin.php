<?php

/* security request */
if ( ! class_exists('DisplayLotroServer') ) {
	die();
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    DisplayLotroServer
 * @subpackage DisplayLotroServer/admin
 */
class DisplayLotroServer_Admin extends DisplayLotroServer {

	/**
	 * The options array
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      array    $options    The options of this plugin.
	 */
	public $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param    string    $options     The options array.
	 */
	public function __construct( $options ) {

		$this->options = $options;

	}

	/**
	 * Register the settings and validation method to save the options.
	 * 
	 * @since 1.0.0
	 */
	public function lotroserver_admin_init() {
		register_setting( 
			$this->optionsection, 
			$this->optiontag, 
			array( $this, 'dls_options_validate' ) 
		);
	}

	/**
	 * Add settings page to admin menu
	 * 
	 * @since 1.0.0
	 */
	public function add_settings_page() {
		add_options_page( 
			__('Settings: Display Lotro Server', 'DLSlanguage'), 
			__('Display Lotro Server', 'DLSlanguage'), 
			'manage_options', 
			'display-lotro-server', 
			array( $this, 'build_options_page_html' ) 
		);
	}

	/**
	 * Loads the required styles for the config page.
	 */
	public function config_page_styles() {
		global $pagenow;
		if ( $pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] === 'display-lotro-server' ) {
			wp_enqueue_script( 'dls-ajax-request', plugins_url( 'js/dls-admin-ajax.js', __FILE__ ), array( 'jquery' ) );
			wp_localize_script( 'dls-ajax-request', 'DLSAjax', array(
			    'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			    'resetSettingsNonce' => wp_create_nonce( 'dlsajax-reset-settings-nonce' ),
			    )
			);
		}
	}

	/**
	 * Under construction!
	 * Validate function to sanitize text fields or textareas
	 *
	 * @return array/string $input the option which will be saved here
	 */
	public function dls_options_validate($input) {
		if ( ! check_admin_referer( 'update' ) ) {
      die( 'Cheating!');
    }

    if( isset($input['shortcode']) ) {
      $input['shortcode'] = intval($input['shortcode']);      
    }

		return $input;
	}

	/**
	 * Load the HTML for the admin page
	 * 
	 * @since 1.0.0
	 */
	public function build_options_page_html() {
		echo '<pre>';
		echo var_dump($this->options);
		echo '</pre>';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/dls-admin-display.php';
	}

	/**
	 * Add settings link to plugin list table
	 * 
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . menu_page_url( 'display-lotro-server', false ) . '">' . __( 'Settings', 'DLSlanguage' ) . '</a>';
  	array_push( $links, $settings_link );
  	return $links;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in DisplayLotroServer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The DisplayLotroServer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( DLS_BASENAME, plugin_dir_url( __FILE__ ) . 'css/dls-admin-style.css', array(), DLS_VERSION, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in DisplayLotroServer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The DisplayLotroServer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( DLS_BASENAME, plugin_dir_url( __FILE__ ) . 'js/dls-admin-ajax.js', array( 'jquery' ), DLS_VERSION, false );

	}

}
