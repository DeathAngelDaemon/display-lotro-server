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
        add_action( 'admin_print_styles', array( $this, 'config_page_styles' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . DLS_BASENAME , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	function add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=display-lotro-server">' . __( 'Settings', 'DLSlanguage' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	* Initialize certain admin functions and sets the checkboxes for the configuration page.
	**/
	function lotroserver_admin_init() {
		// add_settings_section('lotroserver_shortcode_options', __('Shortcode', 'DLSlanguage'),  array( 'LotroServerGUI', 'shortcode_section_text' ), parent::$optionsection);
			// add_settings_field(	'choice_shortcode', __( 'Do you want to use the shortcode for posts and pages?', 'DLSlanguage' ), array( 'LotroServerGUI', 'check_shortcode_callback' ), parent::$optionsection, 'lotroserver_shortcode_options', array( 'label_for' => 'choice_shortcode' ) );
		register_setting( $this->optionsection, $this->optiontag );
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
			wp_enqueue_style( 'dls-admin-css', plugins_url( 'css/admin-style.css', __FILE__ ), array(), DLS_VERSION );
		}
	}

	/**
	 * Under construction!
	 * Validate function to sanitize text fields or textareas
	 *
	 * @return array/string $input the option which will be saved here
	 */
	function dls_options_validate($input) {
		return $input;
	}

	/**
	* Load the HTML for the admin page
	**/
	function showAdminPage() {
		global $DLS;
?>
<div class="wrap">
	<?php settings_errors(); ?>
	<h2><?php _e( 'Display Lotro Server: Settings', 'DLSlanguage' ); ?></h2>
	<?php _e( 'Set up the plugin here. <em>Important: Choose at least one server, otherwiese nothing will be displayed.</em>', 'DLSlanguage' ); ?>
	<form method="post" action="options.php" class="dls-form">
		<?php wp_nonce_field( 'save_serveroptions', '_wpnonce-lotroserver' ); ?>
		<?php settings_fields($this->optionsection); ?>
	<div class="leftside">
		<h3>EU Server Auswahl:</h3>
		<div class="desc">
			<?php _e('Choose the EU servers you want to show at the frontend.', 'DLSlanguage'); ?>
		</div>
	    <ul>
	    <?php
	    foreach($this->serverslistEU as $servername) {
			if( in_array($servername, $this->serverDE) ) {
				if( $servername === 'Belegaer') {
		?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>">[DE - RP] <?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[EU]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["EU"][$servername])) ?: checked($DLS->options["EU"][$servername], 1); ?> />
					</li>
		<?php	} else {	?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>">[DE] <?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[EU]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["EU"][$servername])) ?: checked($DLS->options["EU"][$servername], 1); ?> />
					</li>
		<?php
				}
			} elseif( in_array($servername, $this->serverEN) ) {
				if( $servername === 'Laurelin') {
		?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>">[EN - RP] <?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[EU]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["EU"][$servername])) ?: checked($DLS->options["EU"][$servername], 1); ?> />
					</li>
		<?php	} else {	?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>">[EN] <?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[EU]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["EU"][$servername])) ?: checked($DLS->options["EU"][$servername], 1); ?> />
					</li>
		<?php
				}
			} elseif( in_array($servername, $this->serverFR) ) {
				if( $servername === 'Estel') {
		?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>">[FR - RP] <?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[EU]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["EU"][$servername])) ?: checked($DLS->options["EU"][$servername], 1); ?> />
					</li>
		<?php	} else {	?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>">[FR] <?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[EU]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["EU"][$servername])) ?: checked($DLS->options["EU"][$servername], 1); ?> />
					</li>
		<?php
				}
			} else {
		?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>"><?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[EU]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["EU"][$servername])) ?: checked($DLS->options["EU"][$servername], 1); ?> />
					</li>
		<?php
			}
		}
		?>
		</ul>
	</div>
	<div class="rightside">
		<h3>US Server Auswahl:</h3>
		<div class="desc">
			<?php _e('Choose the US servers you want to show at the frontend.', 'DLSlanguage'); ?>
		</div>
		<ul>
		<?php
		foreach($this->serverslistUS as $servername) {
			if( $servername === 'Bullroarer') {
		?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>">[BETA] <?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[US]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["US"][$servername])) ?: checked($DLS->options["US"][$servername], 1); ?> />
					</li>
		<?php	} else {	?>
					<li>
						<label for="choice_<?php echo strtolower($servername); ?>"><?php echo $servername; ?></label>
						<input type="checkbox" id="choice_<?php echo strtolower($servername); ?>" name="<?php echo $this->optiontag.'[US]['.$servername.']'; ?>" value="1" <?php (!isset($DLS->options["US"][$servername])) ?: checked($DLS->options["US"][$servername], 1); ?> />
					</li>
		<?php
			}
		}
		?>
	    </ul>
	</div>
	<div class="clear"></div>
	    <?php submit_button(NULL,'primary','submit-serveroptions'); ?>	    
    </form>
</div>
<?php
	}

}
