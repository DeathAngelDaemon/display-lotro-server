<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      2.0.0
 * @package    DisplayLotroServer
 * @subpackage DisplayLotroServer/includes
 */
class DisplayLotroServer {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

  /**
   * The instance of the public class.
   * 
   * @since   2.0.0
   * @access  protected
   */
  protected $plugin_public;

  /**
   * 
   */
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
		$dataServerArray;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

    // prepare default option values and set every server option to 0 (unchecked)
    $euNull = array_fill(0, 5, '0');
		$usNull = array_fill(0, 5, '0');
		$this->defaults = array(
			'EU' => array_combine($this->serverslistEU, $euNull),
			'US' => array_combine($this->serverslistUS, $usNull),
      'DE' => $this->serverDE,
      'EN' => $this->serverEN,
      'FR' => $this->serverFR,
			'shortcode' => 0,
			'version' => DLS_VERSION
		);

		$this->load_dependencies();
		$this->set_locale();

    // define and get options
    $this->define_options();

    // define shortcode & widget
    $this->define_shortcode();
    $this->define_widget();

    // set admin & public hooks
		$this->define_admin_hooks();
		$this->define_public_hooks();

    // start the plugin
    $this->loader->add_action( 'plugins_loaded', $this, 'init' );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - DisplayLotroServer_Loader. Orchestrates the hooks of the plugin.
	 * - DisplayLotroServer_i18n. Defines internationalization functionality.
	 * - DisplayLotroServer_Admin. Defines all hooks for the admin area.
	 * - DisplayLotroServer_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dls-loader.php';

    /**
		 * The class responsible for logic regarding the lotro datacenters.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dls-datacenter.php';

    /**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dls-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-dls-admin.php';

    /**
		 * The classes responsible for registering and defining the Widget.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/_dls-widget-integration.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-dls-widget.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-dls-public.php';
    $plugin_datacenter = new DisplayLotroServer_Datacenter( $this->options );
		$this->plugin_public = new DisplayLotroServer_Public( $plugin_datacenter, $this->options );

		$this->loader = new DisplayLotroServer_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new DisplayLotroServer_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

  /**
   * Checks the optiontag and possibly set the default options
   *
   * @since 1.0.0
   */
  private function define_options() {
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

    $this->options = get_option( $this->optiontag );

    // avoid empty entry in options array
		if( isset( $this->options[0] ) && empty( $this->options[0] ) ) {
			unset( $this->options[0] );
		}
  }

  /**
   * Introduce the shortcode if it was checked in the options.
   * 
   * @since 2.0.0
   */
  private function define_shortcode() {
    if( isset( $this->options['shortcode'] ) && $this->options['shortcode'] === 1) {
			add_shortcode( 'lotroserver', array( $this->plugin_public, 'lotroserver_shortcode' ) );
		} else {
			remove_shortcode( 'lotroserver' );
		}
  }

  /**
   * 
   */
  private function define_widget() {
    $plugin_widget = new DisplayLotroServer_Widget();

    $this->loader->add_action( 'widgets_init', $plugin_widget, 'lotroserver_register_widgets' );
  }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new DisplayLotroServer_Admin( $this->options );

    $this->loader->add_action( 'admin_init', $plugin_admin, 'lotroserver_admin_init' );
    $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );

    // Add settings link to plugins page
		$this->loader->add_filter( 'plugin_action_links_' . DLS_BASENAME , $plugin_admin, 'add_settings_link' );

    // register styles & scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'config_page_styles' );

    // register ajax functionality
    $this->loader->add_action( 'wp_ajax_nopriv_dlsajax-submit', $plugin_admin, 'reset_settings_ajax' );
    $this->loader->add_action( 'wp_ajax_dlsajax-submit', $plugin_admin, 'reset_settings_ajax' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		//$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles' );
		//$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts' );

	}

  /**
	 * Initialisation of the plugin
	 */
	public function init() {
		// plugin upgrade
		if ($this->options && version_compare($this->options['version'], DLS_VERSION, '<')) {
			return 'You have to upgrade the plugin.';
		}

    // get cached datacenter array
    //$this->dataServerArray = $this->get_cached_datacenter();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

}