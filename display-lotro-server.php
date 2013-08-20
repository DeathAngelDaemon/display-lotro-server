<?php
/**
 * Plugin Name: Display Lotro Server
 * Plugin URI: http://hdroblog.anna-fischer.info/wordpress-plugin-display-lotro-server/
 * Description: Displays a server list of the configured servers (see the settings). Can be placed as a widget or a shortcode in every article or page. This plugin uses the status-script from http://status.warriorsofnargathrond.com
 *
 * Version: 0.9.7
 *
 * Author: Anna Fischer
 * Author URI: http://hdroblog.anna-fischer.info/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

class DisplayLotroServer {

	private static
		$strVersion = '0.9.7',
		$optiontag = 'lotroserver_options',
		$strPluginBasename = NULL,
		$serverslistEU = array( 'Anduin','Belegaer','Eldar','Estel','Evernight','Gilrain','Gwaihir','Laurelin','Maiar','Morthond','Sirannon','Vanyar','Withywindle' ),
		$serverslistUS = array( 'Arkenstone', 'Brandywine', 'Crickhollow', 'Dwarrowdelf', 'Elendilmir', 'Firefoot', 'Gladden', 'Imladris', 'Landroval', 'Meneldor', 'Nimrodel', 'Riddermark', 'Silverlode', 'Vilya', 'Windfola', 'Bullroarer'),
		$serverDE = array( 'Anduin', 'Belegaer', 'Gwaihir', 'Maiar', 'Morthond', 'Vanyar' ),
		$serverEN = array( 'Eldar', 'Evernight', 'Gilrain', 'Laurelin', 'Withywindle' ),
		$serverFR = array( 'Sirannon', 'Estel' ),
		$arySettings = array(
			'shortcode' => true
		);

	/**
	* Constructor
	*
	* @since 0.1
	* @version 0.9.6
	**/
	function __construct(){

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );

		// Add different admin settings and admin menu
		add_action('admin_init', array( $this, 'lotroserver_admin_init'));
		add_action( 'admin_menu', array( $this, 'buildAdminMenu' ) );

		// Store plugin basename
		self::$strPluginBasename = plugin_basename(__FILE__);

		// Settings changed?
		if (isset($_POST['action']) && $_POST['action'] == 'save_serveroptions')
			$this->saveSettings();

		// Add meta links to plugin details
		add_filter( 'plugin_row_meta', array( $this, 'setPluginMeta' ), 10, 2 );

		// Enable shortcodes if enabled
		if (self::$arySettings['shortcode'])
			add_shortcode( 'lotroserver', array( $this, 'lotroserver_shortcode') );

		// Load language file
		load_plugin_textdomain( 'DLSlanguage', false, dirname(self::$strPluginBasename)."/languages/" );
	}

	/**
	* Activation of the plugin
	*
	* @since 0.1
	* @version 0.9.7
	**/
	function activate(){
		global $wp_version;
		if (version_compare(PHP_VERSION, '5.3.0', '<') && version_compare($wp_version, '3.4.2', '>')) {
			deactivate_plugins($this->plugin_name); // Deactivate ourself
			wp_die(__('Sorry, but you can\'t run this plugin, it requires PHP 5.2 or higher and Wordpress version 3.4.2 or higher.'));
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
	function uninstall(){
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
	*/
	function updateMessage() {
		# success message after installation
		$strText = 'DisplayLotroServer '.self::$strVersion.' '.__('installed','DLSlanguage').'.';

		$strSettings = __('Please update your configuration and choose the servers','DLSlanguage');
		$strLink = sprintf('<a href="options-general.php?page=%s">%s</a>', dirname(self::$strPluginBasename), __('Settings', 'DLSlanguage'));
		
		# display information message for setting up the servers
		echo '<div class="updated"><p>'.$strText.' <strong>'.__('Important', 'DLSlanguage').':</strong> '.$strSettings.': '.$strLink.'.</p></div>';
	}

	/**
	* Get this plugin's URL
	*
	* @since 0.1
	*/
	function getPluginURL() {
		# Return plugins URL + /display-lotro-server/
		return trailingslashit(plugins_url().'/display-lotro-server/');
	}

	/**
	* Add plugin meta links to plugin details
	*
	* @see http://wpengineer.com/1295/meta-links-for-wordpress-plugins/
	* @since 0.5
	* @version 0.9.6
	*/
	function setPluginMeta($strLinks, $strFile) {
		// Add link just to this plugin's details
		if ($strFile == self::$strPluginBasename)
			return array_merge(
			$strLinks,
			array(
				sprintf('<a href="options-general.php?page=%s">%s</a>', dirname(self::$strPluginBasename), __('Settings', 'DLSlanguage'))
			)
			);
		// Don't affect other plugins details
		return $strLinks;
	}

	/**
	* Adds an option page for configuration
	*
	* @since 0.1
	* @version 0.9.6
	**/
	function buildAdminMenu() {
		$intOptionsPage = add_options_page( __('Settings: Display Lotro Server', 'DLSlanguage'), __('Display Lotro Server', 'DLSlanguage'), 'manage_options', 'display-lotro-server', array( &$this, 'showAdminPage' ) );
	}

	/**
	* Load the HTML for the admin page
	*
	* @since 0.1
	* @version 0.9.6
	**/
	function showAdminPage() {
?>
<div class="wrap">
		<?php
		if( $_POST['notice'] )
			echo '<div id="message" class="updated"><p><strong>' . $_POST['notice'] . '.</strong></p></div>';
		?>
	<h2><?php echo __( 'Display Lotro Server: Settings', 'DLSlanguage' ); ?></h2>
	<?php settings_errors(); ?>
	<?php echo __( 'On this page you can do change the settings for displaying your desired servers.', 'DLSlanguage' ); ?>
	<form method="post" action="">
		<?php settings_fields('serversection'); ?>
	    <?php do_settings_sections('serversection'); ?>
	    <br />
		<p>
	      <label>
	        <input type="checkbox" name="lotroserver_useDefaults" />
	        <?php echo __( 'Set settings to default', 'DLSlanguage' ); ?>
	      </label>
	    </p>
	    <?php submit_button(NULL,'primary','submit-serveroptions'); ?>
	    <input name="action" value="save_serveroptions" type="hidden" />
    </form>
</div>
<?php
	}

	/**
	* Saves all changes on the configuration
	*
	* @since 0.1
	* @version 0.9.7
	*/
	function saveSettings(){
		# Update Settings on Save
		if( $_POST['action'] == 'save_serveroptions' ) {
			$options = get_option(self::$optiontag);
			if (!empty($_POST['lotroserver_useDefaults'])) {
				delete_option('lotroserver_options');
				$_POST['notice'] = __( 'The settings are set back to default.', 'DLSlanguage' );
			} else {
				foreach(self::$serverslistEU as $servername) {
					$options[''.$servername.''] = $_POST['lotroserver_choice_'.strtolower($servername)];
				}
				foreach(self::$serverslistUS as $servername) {
					$options[''.$servername.''] = $_POST['lotroserver_choice_'.strtolower($servername)];
				}
				update_option( self::$optiontag, $options);
				$_POST['notice'] = __( 'Settings saved.', 'DLSlanguage' );
			}
		}
	}

	/**
	* Initialize certain admin functions and sets the checkboxes for the configuration page.
	*
	* @since 0.1
	* @version 0.9.6
	**/
	function lotroserver_admin_init() {
		add_settings_section('lotroserver_eu_options', __('EU Server Settings', 'DLSlanguage'),  array( $this, 'eu_server_section_text'), 'serversection');
		foreach(self::$serverslistEU as $servername) {
			if( in_array($servername, self::$serverDE) ) {
				if( $servername === 'Belegaer') {
					add_settings_field(	'choice_'.strtolower($servername), '[DE - RP] '.$servername, array( $this, 'server_check_'.strtolower($servername).'_callback'), 'serversection', 'lotroserver_eu_options', array( 'label_for' => 'choice_'.strtolower($servername) ) );
				} else {
					add_settings_field(
						'choice_'.strtolower($servername),
						'[DE] '.$servername,
						array( $this, 'server_check_'.strtolower($servername).'_callback'),
						'serversection',
						'lotroserver_eu_options',
						array( 'label_for' => 'choice_'.strtolower($servername) )
					);
				}
			} elseif( in_array($servername, self::$serverEN) ) {
				if( $servername === 'Laurelin') {
					add_settings_field(	'choice_'.strtolower($servername), '[EN - RP] '.$servername, array( $this, 'server_check_'.strtolower($servername).'_callback'), 'serversection', 'lotroserver_eu_options', array( 'label_for' => 'choice_'.strtolower($servername) ) );
				} else {
					add_settings_field(
						'choice_'.strtolower($servername),
						'[EN] '.$servername,
						array( $this, 'server_check_'.strtolower($servername).'_callback'),
						'serversection',
						'lotroserver_eu_options',
						array( 'label_for' => 'choice_'.strtolower($servername) )
					);
				}
			} elseif( in_array($servername, self::$serverFR) ) {
				if( $servername === 'Estel') {
					add_settings_field(	'choice_'.strtolower($servername), '[FR - RP] '.$servername, array( $this, 'server_check_'.strtolower($servername).'_callback'), 'serversection', 'lotroserver_eu_options', array( 'label_for' => 'choice_'.strtolower($servername) ) );
				} else {
					add_settings_field(
						'choice_'.strtolower($servername),
						'[FR] '.$servername,
						array( $this, 'server_check_'.strtolower($servername).'_callback'),
						'serversection',
						'lotroserver_eu_options',
						array( 'label_for' => 'choice_'.strtolower($servername) )
					);
				}
			} else {
				add_settings_field(
					'choice_'.strtolower($servername),
					$servername,
					array( $this, 'server_check_'.strtolower($servername).'_callback'),
					'serversection',
					'lotroserver_eu_options',
					array( 'label_for' => 'choice_'.strtolower($servername) )
				);
			}
		}
		add_settings_section('lotroserver_us_options', __('US Server Settings', 'DLSlanguage'),  array( $this, 'us_server_section_text'), 'serversection');
		foreach(self::$serverslistUS as $servername) {
			if( $servername === 'Bullroarer') {
				add_settings_field(	'choice_'.strtolower($servername), '[Beta] '.$servername, array( $this, 'server_check_'.strtolower($servername).'_callback'), 'serversection', 'lotroserver_us_options', array( 'label_for' => 'choice_'.strtolower($servername) ) );
			} else {
				add_settings_field(
					'choice_'.strtolower($servername),
					$servername,
					array( $this, 'server_check_'.strtolower($servername).'_callback'),
					'serversection',
					'lotroserver_us_options',
					array( 'label_for' => 'choice_'.strtolower($servername) )
				);
			}
		}
		register_setting( 'serversection', 'lotroserver_options');
	}

		/**
	   	* The Callbacks for the checkboxes and text.
	   	*
		* @since 0.1
		* @version 0.9.7
	   	*/
		function eu_server_section_text(){
			echo __('Choose from the given EU servers below your favourite ones. These are displayed in the frontend.', 'DLSlanguage');
		}
		function server_check_anduin_callback() {
			$options = get_option( self::$optiontag );
			?>
					<input type="checkbox" id="choice_anduin" name="lotroserver_choice_anduin" value="1" <?php checked($options['Anduin'], 1); ?> />
					<?php
		}
		function server_check_belegaer_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_belegaer" name="lotroserver_choice_belegaer" value="1" <?php checked($options['Belegaer'], 1); ?> />
			<?php
		}
		function server_check_gwaihir_callback() {
			$options = get_option( self::$optiontag );
			?>
						<input type="checkbox" id="choice_gwaihir" name="lotroserver_choice_gwaihir" value="1" <?php checked($options['Gwaihir'], 1); ?> />
						<?php
		}
		function server_check_maiar_callback() {
			$options = get_option( self::$optiontag );
			?>
					<input type="checkbox" id="choice_maiar" name="lotroserver_choice_maiar" value="1" <?php checked($options['Maiar'], 1); ?> />
					<?php
		}
		function server_check_morthond_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_morthond" name="lotroserver_choice_morthond" value="1" <?php checked($options['Morthond'], 1); ?> />
			<?php
		}
		function server_check_vanyar_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_vanyar" name="lotroserver_choice_vanyar" value="1" <?php checked($options['Vanyar'], 1); ?> />
			<?php
		}
		function server_check_eldar_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_eldar" name="lotroserver_choice_eldar" value="1" <?php checked($options['Eldar'], 1); ?> />
			<?php
		}
		function server_check_evernight_callback() {
			$options = get_option( self::$optiontag );
			?>
					<input type="checkbox" id="choice_evernight" name="lotroserver_choice_evernight" value="1" <?php checked($options['Evernight'], 1); ?> />
				<?php
		}
		function server_check_gilrain_callback() {
			$options = get_option( self::$optiontag );
			?>
					<input type="checkbox" id="choice_gilrain" name="lotroserver_choice_gilrain" value="1" <?php checked($options['Gilrain'], 1); ?> />
				<?php
		}
		function server_check_laurelin_callback() {
			$options = get_option( self::$optiontag );
			?>
					<input type="checkbox" id="choice_laurelin" name="lotroserver_choice_laurelin" value="1" <?php checked($options['Laurelin'], 1); ?> />
				<?php
		}
		function server_check_withywindle_callback() {
			$options = get_option( self::$optiontag );
			?>
					<input type="checkbox" id="choice_withywindle" name="lotroserver_choice_withywindle" value="1" <?php checked($options['Withywindle'], 1); ?> />
				<?php
		}
		function server_check_sirannon_callback() {
			$options = get_option( self::$optiontag );
			?>
					<input type="checkbox" id="choice_sirannon" name="lotroserver_choice_sirannon" value="1" <?php checked($options['Sirannon'], 1); ?> />
				<?php
		}
		function server_check_estel_callback() {
			$options = get_option( self::$optiontag );
			?>
					<input type="checkbox" id="choice_estel" name="lotroserver_choice_estel" value="1" <?php checked($options['Estel'], 1); ?> />
				<?php
		}

		function us_server_section_text() {
			echo __('Choose from the given US servers below your favourite ones. These are displayed in the frontend.', 'DLSlanguage');
		}
		function server_check_arkenstone_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_arkenstone" name="lotroserver_choice_arkenstone" value="1" <?php checked($options['Arkenstone'], 1); ?> />
				<?php
		}
		function server_check_brandywine_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_brandywine" name="lotroserver_choice_brandywine" value="1" <?php checked($options['Brandywine'], 1); ?> />
				<?php
		}
		function server_check_crickhollow_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_crickhollow" name="lotroserver_choice_crickhollow" value="1" <?php checked($options['Crickhollow'], 1); ?> />
				<?php
		}
		function server_check_dwarrowdelf_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_dwarrowdelf" name="lotroserver_choice_dwarrowdelf" value="1" <?php checked($options['Dwarrowdelf'], 1); ?> />
				<?php
		}
		function server_check_elendilmir_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_elendilmir" name="lotroserver_choice_elendilmir" value="1" <?php checked($options['Elendilmir'], 1); ?> />
				<?php
		}
		function server_check_firefoot_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_firefoot" name="lotroserver_choice_firefoot" value="1" <?php checked($options['Firefoot'], 1); ?> />
				<?php
		}
		function server_check_gladden_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_gladden" name="lotroserver_choice_gladden" value="1" <?php checked($options['Gladden'], 1); ?> />
				<?php
		}
		function server_check_imladris_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_imladris" name="lotroserver_choice_imladris" value="1" <?php checked($options['Imladris'], 1); ?> />
				<?php
		}
		function server_check_landroval_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_landroval" name="lotroserver_choice_landroval" value="1" <?php checked($options['Landroval'], 1); ?> />
				<?php
		}
		function server_check_meneldor_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_meneldor" name="lotroserver_choice_meneldor" value="1" <?php checked($options['Meneldor'], 1); ?> />
				<?php
		}
		function server_check_nimrodel_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_nimrodel" name="lotroserver_choice_nimrodel" value="1" <?php checked($options['Nimrodel'], 1); ?> />
				<?php
		}
		function server_check_riddermark_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_riddermark" name="lotroserver_choice_riddermark" value="1" <?php checked($options['Riddermark'], 1); ?> />
				<?php
		}
		function server_check_silverlode_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_silverlode" name="lotroserver_choice_silverlode" value="1" <?php checked($options['Silverlode'], 1); ?> />
				<?php
		}
		function server_check_vilya_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_vilya" name="lotroserver_choice_vilya" value="1" <?php checked($options['Vilya'], 1); ?> />
				<?php
		}
		function server_check_windfola_callback() {
			$options = get_option( self::$optiontag );
			?>
				<input type="checkbox" id="choice_windfola" name="lotroserver_choice_windfola" value="1" <?php checked($options['Windfola'], 1); ?> />
				<?php
		}
		function server_check_bullroarer_callback() {
			$options = get_option( self::$optiontag );
			?>
			<input type="checkbox" id="choice_bullroarer" name="lotroserver_choice_bullroarer" value="1" <?php checked($options['Bullroarer'], 1); ?> />
			<?php
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
	* @version 0.9.7
	**/
	static function show_serverlist($location='all') {

		$options = get_option( self::$optiontag );

		if( isset( $options[0] ) && empty( $options[0] ) ) {
			unset( $options[0] );
		}

		foreach( $options as $name => $wert ) {
			if( $wert === "1" ) {
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
					$listoutput .= '<li>'.$server->name.' (<img src="'.self::getPluginURL().'img/up.png" alt="online" />)</li>';
				} else {
					$listoutput .= '<li>'.$server->name.' (<img src="'.self::getPluginURL().'img/down.png" alt="offline" />)</li>';
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
* Adds the LotroServer widget.
*
* @since 0.5
* @version 0.9.6
*/
class LotroServerWidget extends WP_Widget {

	/**
	 * Register the widget with WordPress.
	 */
	public function __construct() {
		$widget_ops = array('classname' => 'LotroServerWidget', 'description' => __('Shows a configured list of Lotro servers', 'DLSlanguage') );
		$this->WP_Widget('LotroServerWidget', 'Display Lotro Server Status', $widget_ops);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $options;
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		# output of the serverlist
		echo DisplayLotroServer::show_serverlist();

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __('Lotro server list', 'DLSlanguage');
		}
		?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</p>
			<?php
	}

}
# Register the Widget
add_action( 'widgets_init', create_function('', 'return register_widget("LotroServerWidget");') );

/* start the plugin */
new DisplayLotroServer;

 ?>