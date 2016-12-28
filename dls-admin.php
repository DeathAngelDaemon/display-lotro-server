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
	function __construct() {
		// Add admin settings and admin menu
		add_action( 'admin_menu', array( $this, 'buildAdminMenu' ) );
        add_action( 'admin_init', array( $this, 'lotroserver_admin_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'config_page_styles' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . DLS_BASENAME , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	function add_settings_link( $links ) {
		$settings_link = '<a href="' . menu_page_url( 'display-lotro-server', false ) . '">' . __( 'Settings', 'DLSlanguage' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	* Initialize certain admin functions and sets the checkboxes for the configuration page.
	**/
	function lotroserver_admin_init() {
		// add_settings_section('lotroserver_shortcode_options', __('Shortcode', 'DLSlanguage'),  array( 'LotroServerGUI', 'shortcode_section_text' ), parent::$optionsection);
			// add_settings_field(	'choice_shortcode', __( 'Do you want to use the shortcode for posts and pages?', 'DLSlanguage' ), array( 'LotroServerGUI', 'check_shortcode_callback' ), parent::$optionsection, 'lotroserver_shortcode_options', array( 'label_for' => 'choice_shortcode' ) );
		register_setting( $this->optionsection, $this->optiontag, array( $this, 'dls_options_validate' ) );
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	function buildAdminMenu() {
		add_options_page( __('Settings: Display Lotro Server', 'DLSlanguage'), __('Display Lotro Server', 'DLSlanguage'), 'manage_options', 'display-lotro-server',  array( $this, 'showAdminPage' ) );
	}

	/**
	 * Loads the required styles for the config page.
	 */
	function config_page_styles() {
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
	function reset_settings_ajax() {
 		global $DLS;
	    $nonce = $_POST['resetSettingsNonce'];

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
	function dls_options_validate($input) {
		if ( ! check_admin_referer( 'save_serveroptions', '_wpnonce-lotroserver' ) )
	        die( 'Cheating!');
		return $input;
	}

	/**
	* Load the HTML for the admin page
	**/
	function showAdminPage() {
		global $DLS;
?>
<div class="wrap">
	<h2><?php _e( 'Display Lotro Server: Settings', 'DLSlanguage' ); ?></h2>
	<?php _e( 'Set up the plugin here. <em>Important: Choose at least one server, otherwiese nothing will be displayed.</em>', 'DLSlanguage' ); ?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<form method="post" action="options.php" class="dls-form">
		<?php wp_nonce_field( 'save_serveroptions', '_wpnonce-lotroserver' ); ?>
		<?php settings_fields($this->optionsection); ?>
	<div id="post-body-content">
		<div class="leftside">
		<h3><?php _e('EU server choice', 'DLSlanguage'); ?>:</h3>
		<div class="desc">
			<?php _e('Choose the EU servers you want to show at the frontend.', 'DLSlanguage'); ?>
		</div>
		<i class="fa fa-square-o fa-lg" id="eu_all" title="<?php _e('Select all EU servers', 'DLSlanguage'); ?>"></i> <?php _e('Select all EU servers', 'DLSlanguage'); ?><br>
		<i class="fa fa-chevron-right" id="de_all" title="<?php _e('Select only DE servers', 'DLSlanguage'); ?>"> DE &nbsp;</i>
		<i class="fa fa-chevron-right" id="en_all" title="<?php _e('Select only EN servers', 'DLSlanguage'); ?>"> EN &nbsp;</i>
		<i class="fa fa-chevron-right" id="fr_all" title="<?php _e('Select only FR servers', 'DLSlanguage'); ?>"> FR &nbsp;</i>
	    <ul id="eu-server">
	    <?php
	    foreach($this->serverslistEU as $servername) {
	    	$rpg = ($servername === 'Belegaer' || $servername === 'Laurelin' || $servername === 'Estel') ? ' - RP' : '';
	    	?>
	    	<li>
				<label for="choice_<?php echo strtolower($servername); ?>">
					<?php
						if(in_array($servername, $this->serverDE)) { echo '[DE'.$rpg.'] '; }
						if(in_array($servername, $this->serverEN)) { echo '[EN'.$rpg.'] '; }
						if(in_array($servername, $this->serverFR)) { echo '[FR'.$rpg.'] '; }
						echo $servername;
					?>
				</label>
				<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[EU]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options['EU'][$servername])) ?: checked($DLS->options['EU'][$servername], 1); ?> />
				<input type="hidden" name="checkserver" class="checkserver" value="<?php echo $servername; ?>">
			</li>
		<?php
		}
		?>
		</ul>
		</div>
		<div class="rightside">
		<h3><?php _e('US server choice', 'DLSlanguage'); ?>:</h3>
		<div class="desc">
			<?php _e('Choose the US servers you want to show at the frontend.', 'DLSlanguage'); ?>
		</div>
		<i class="fa fa-square-o fa-lg" id="us_all" title="<?php _e('Select all US servers', 'DLSlanguage'); ?>"></i> <?php _e('Select all US servers', 'DLSlanguage'); ?><br>
		<ul id="us-server">
		<?php
		foreach($this->serverslistUS as $servername) {
			?>
			<li>
				<label for="choice_<?php echo strtolower($servername); ?>">
					<?php
						if($servername === 'Bullroarer') { echo '[BETA] '; }
						echo $servername;
					?>
				</label>
				<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[US]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options['US'][$servername])) ?: checked($DLS->options['US'][$servername], 1); ?> />
				<input type="hidden" name="checkserver" class="checkserver" value="<?php echo $servername; ?>">
			</li>
		<?php
		}
		?>
	    </ul>
		</div>
		<div class="clear"></div>
	</div>
	<div id="postbox-container-1" class="postbox-container">
		<div class="meta-box">
			<div class="postbox">
				<h3><span><?php _e('About this plugin', 'DLSlanguage'); ?></span></h3>
				<div class="inside">
				<p><strong><?php _e('Version', 'DLSlanguage'); ?>:</strong> <?php echo DLS_VERSION; ?></p>
				<p><strong><?php _e('Description', 'DLSlanguage'); ?>:</strong><br>
					<?php _e('Choose the servers from the lists, which you want to show up on the frontend. You can select all EU or all US servers, but you can also choose only DE, EN or FR servers - in combination with the US servers. Everything is possible!', 'DLSlanguage'); ?>
				</p><p>
				<strong><?php _e('Widget', 'DLSlanguage'); ?>:</strong><br>
					<?php _e('You can use the <em>Display Lotro Server Status Widget</em> to show up your chosen servers in a sidebar. The widget also allows you to choose if you want to show only the EU or the US servers you have checked here (e.g. if you want to insert two widgets for each region).', 'DLSlanguage'); ?>
				</p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e('Need help?', 'DLSlanguage'); ?></span></h3>
				<div class="inside">
					<p><?php _e('You need help and don\'t know where to find it? No problem. At first, please try to find the solution at GitHub. If that doesn\'t help, create a new issue at GitHub or contact me via mail. I\'ll prefer GitHub, because your problem or better the solution of you problem can help other users as well.', 'DLSlanguage'); ?></p>
					<p>
						<strong>GitHub:</strong> <a href="https://github.com/DeathAngelDaemon/display-lotro-server" title="<?php _e('The DLS project at GitHub', 'DLSlanguage'); ?>">Display Lotro Server @ GitHub</a><br />
						<strong>E-Mail:</strong> deathangeldaemon@gmail.com
					</p>
				</div>
			</div>
		</div> <!-- .meta-box-sortables -->
	</div>
	<br class="clear">
	    <?php submit_button(NULL,'primary','submit-serveroptions'); ?>
	    <i class="fa fa-trash-o" id="reset" title="<?php _e('Reset to default settings', 'DLSlanguage'); ?>"> <?php _e('Reset to default settings', 'DLSlanguage'); ?></i>
    </form>
    <div id="loading" class="load-info">
    	<i class="fa fa-spinner fa-spin"></i>
    </div>
    <div id="remember" class="remember-info">
    	<i class="fa fa-exclamation-triangle"></i>
    	<?php _e('Please don\'t forget to save your changed settings.', 'DLSlanguage'); ?>
    </div>
</div>
</div>
</div>
<?php
	}

}
