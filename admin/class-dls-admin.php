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
	 * @access   public
	 * @var      array    $options    The options of this plugin.
	 */
	public $options;

	/**
	 * The defaults array
	 *
	 * @since    2.0.0
	 * @access   public
	 * @var      array    $defaults    The default options of this plugin.
	 */
	public $defaults;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 * @param    string    $options     The options array.
	 */
	public function __construct( $options, $defaults ) {

		$this->options = $options;
		$this->defaults = $defaults;

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
			array(
				'type' => 'array',
				'sanitize_callback' => array( $this, 'dls_options_validate' )
			) 
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
	 * Validate function to sanitize text fields, textareas or checkboxes.
	 * This function is called upon update_option().
	 *
	 * @return array/string $input the option which will be saved here
	 */
	public function dls_options_validate($input) {

		if( isset( $input['EU'] ) ) {
			foreach($input['EU'] as $key => $value) {
				if( isset( $input['EU'][$key] ) ) {
					$input['EU'][$key] = absint($value);
				}
			}
		}

		if( isset( $input['US'] ) ) {
			foreach($input['US'] as $key => $value) {
				if( isset( $input['US'][$key] ) ) {
					$input['US'][$key] = absint($value);
				}
			}
		}

		if( isset( $input['shortcode'] ) ) {
			$input['shortcode'] = absint( $input['shortcode'] );
		}

		return $input;

	}

	/**
	 * Function to handle form fields and update the options.
	 * Show success message at the end.
	 * 
	 * @since		2.0.0
	 */
	public function dls_update_options($form) {

		echo "UPDATE OPTION";

		if( isset( $form['shortcode'] ) && $form['shortcode'] !== $this->options['shortcode'] ) {
      $this->options['shortcode'] = $form['shortcode'];
    }

		// set EU server options
		foreach ($this->options['EU'] as $euServer) {
			echo "EU server: " . $euServer;
			if( isset( $form['EU'] ) ) {
				$this->options['EU'][$euServer] = in_array($euServer, $form['EU']) ? 1 : 0;
			} else {
				$this->options['EU'][$euServer] = 0;
			}
		}

		// set US server options
		foreach ($this->options['US'] as $usServer) {
			echo "US server: " . $usServer;
			if( isset( $form['US'] ) ) {
				$this->options['US'][$usServer] = in_array($usServer, $form['US']) ? 1 : 0;
			} else {
				$this->options['US'][$usServer] = 0;
			}
		}

		// actually trigger the update of options
		update_option(
			$this->optiontag,
			$this->options
		);

		// add success message
		add_settings_error( 'dls_messages', 'dls_message', __( 'Settings Saved', 'DLSLanguage' ), 'success' );
	}

	/**
	 * Load the HTML for the admin page
	 * 
	 * @since		1.0.0
	 * @since		2.0.0		Added handling for form submit and options update.
	 */
	public function build_options_page_html() {
		// check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die( "You do not have permission to view this page." );
    }

		// check if POST Request was done with right nonce
		if ( isset( $_POST['action'] ) && $_POST['action'] === 'dls_post_options' ) {
			if( ! check_admin_referer( 'dls_post_options', '_wpnonce_dls_options_verify' ) )
				return;

			// if the "lotroserver_options" do not exist (which is the case when every option is unchecked),
			// reset to default settings
			if( isset($_POST['lotroserver_options'])) {
				$this->dls_update_options($_POST['lotroserver_options']);
			} else {
				update_option( $this->optiontag, $this->defaults );
			}
		}

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
