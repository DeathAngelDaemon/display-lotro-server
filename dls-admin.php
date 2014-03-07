<?php

/* Sicherheitsabfrage */
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
	* Saves all changes on the configuration
	*/
	static function saveSettings() {

		if ( empty($_POST) && !check_admin_referer( 'save_serveroptions', '_wpnonce-lotroserver' ) ) {
			wp_die('No form successful transmitted.');
		} else {
			$options = get_option(parent::$optiontag);
			if (!empty($_POST['lotroserver_useDefaults'])) {
				delete_option('lotroserver_options');
				$_POST['notice'] = __( 'The settings are set back to default.', 'DLSlanguage' );
			} else {
				foreach(parent::$serverslistEU as $servername) {
					if(isset($_POST['lotroserver_choice_'.strtolower($servername)]))
						$options[''.$servername.''] = $_POST['lotroserver_choice_'.strtolower($servername)];
				}
				foreach(parent::$serverslistUS as $servername) {
					if(isset($_POST['lotroserver_choice_'.strtolower($servername)]))
						$options[''.$servername.''] = $_POST['lotroserver_choice_'.strtolower($servername)];
				}
				update_option( parent::$optiontag, $options);
				$_POST['notice'] = __( 'Settings saved.', 'DLSlanguage' );
			}
		}

	}

	/**
	* Initialize certain admin functions and sets the checkboxes for the configuration page.
	**/
	static function lotroserver_admin_init() {
		add_settings_section('lotroserver_shortcode_options', __('Shortcode', 'DLSlanguage'),  array( 'LotroServerGUI', 'shortcode_section_text' ), 'serversection');
			add_settings_field(	'choice_shortcode', __( 'Do you want to use the shortcode for posts and pages?', 'DLSlanguage' ), array( 'LotroServerGUI', 'check_shortcode_callback' ), 'serversection', 'lotroserver_shortcode_options', array( 'label_for' => 'choice_shortcode' ) );

		add_settings_section('lotroserver_eu_options', __('EU Server Settings', 'DLSlanguage'),  array( 'LotroServerGUI', 'eu_server_section_text' ), 'serversection');
		foreach(parent::$serverslistEU as $servername) {
			if( in_array($servername, parent::$serverDE) ) {
				if( $servername === 'Belegaer') {
					add_settings_field(	'choice_'.strtolower($servername), '[DE - RP] '.$servername, array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback' ), 'serversection', 'lotroserver_eu_options', array( 'label_for' => 'choice_'.strtolower($servername) ) );
				} else {
					add_settings_field(
						'choice_'.strtolower($servername),
						'[DE] '.$servername,
						array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback'),
						'serversection',
						'lotroserver_eu_options',
						array( 'label_for' => 'choice_'.strtolower($servername) )
					);
				}
			} elseif( in_array($servername, parent::$serverEN) ) {
				if( $servername === 'Laurelin') {
					add_settings_field(	'choice_'.strtolower($servername), '[EN - RP] '.$servername, array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback'), 'serversection', 'lotroserver_eu_options', array( 'label_for' => 'choice_'.strtolower($servername) ) );
				} else {
					add_settings_field(
						'choice_'.strtolower($servername),
						'[EN] '.$servername,
						array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback'),
						'serversection',
						'lotroserver_eu_options',
						array( 'label_for' => 'choice_'.strtolower($servername) )
					);
				}
			} elseif( in_array($servername, parent::$serverFR) ) {
				if( $servername === 'Estel') {
					add_settings_field(	'choice_'.strtolower($servername), '[FR - RP] '.$servername, array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback'), 'serversection', 'lotroserver_eu_options', array( 'label_for' => 'choice_'.strtolower($servername) ) );
				} else {
					add_settings_field(
						'choice_'.strtolower($servername),
						'[FR] '.$servername,
						array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback'),
						'serversection',
						'lotroserver_eu_options',
						array( 'label_for' => 'choice_'.strtolower($servername) )
					);
				}
			} else {
				add_settings_field(
					'choice_'.strtolower($servername),
					$servername,
					array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback'),
					'serversection',
					'lotroserver_eu_options',
					array( 'label_for' => 'choice_'.strtolower($servername) )
				);
			}
		}
		add_settings_section('lotroserver_us_options', __('US Server Settings', 'DLSlanguage'),  array( 'LotroServerGUI', 'us_server_section_text'), 'serversection');
		foreach(parent::$serverslistUS as $servername) {
			if( $servername === 'Bullroarer') {
				add_settings_field(	'choice_'.strtolower($servername), '[Beta] '.$servername, array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback'), 'serversection', 'lotroserver_us_options', array( 'label_for' => 'choice_'.strtolower($servername) ) );
			} else {
				add_settings_field(
					'choice_'.strtolower($servername),
					$servername,
					array( 'LotroServerGUI', 'server_check_'.strtolower($servername).'_callback'),
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
	   	*/
	   	static function shortcode_section_text() {
			echo __('Decide to use the shortcode or not.', 'DLSlanguage');
		}
		static function check_shortcode_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_shortcode" name="lotroserver_choice_shortcode" value="1" <?php if(isset($options['Shortcode'])) checked($options['Shortcode'], 1); ?> />
			<?php
		}
		static function eu_server_section_text() {
			echo __('Choose from the given EU servers below your favourite ones. These are displayed in the frontend.', 'DLSlanguage');
		}
		static function server_check_anduin_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_anduin" name="lotroserver_choice_anduin" value="1" <?php if(isset($options['Anduin'])) checked($options['Anduin'], 1); ?> />
			<?php
		}
		static function server_check_belegaer_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_belegaer" name="lotroserver_choice_belegaer" value="1" <?php if(isset($options['Belegaer'])) checked($options['Belegaer'], 1); ?> />
			<?php
		}
		static function server_check_gwaihir_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_gwaihir" name="lotroserver_choice_gwaihir" value="1" <?php if(isset($options['Gwaihir'])) checked($options['Gwaihir'], 1); ?> />
			<?php
		}
		static function server_check_maiar_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_maiar" name="lotroserver_choice_maiar" value="1" <?php if(isset($options['Maiar'])) checked($options['Maiar'], 1); ?> />
			<?php
		}
		static function server_check_morthond_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_morthond" name="lotroserver_choice_morthond" value="1" <?php if(isset($options['Morthond'])) checked($options['Morthond'], 1); ?> />
			<?php
		}
		static function server_check_vanyar_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_vanyar" name="lotroserver_choice_vanyar" value="1" <?php if(isset($options['Vanyar'])) checked($options['Vanyar'], 1); ?> />
			<?php
		}
		static function server_check_eldar_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_eldar" name="lotroserver_choice_eldar" value="1" <?php if(isset($options['Eldar'])) checked($options['Eldar'], 1); ?> />
			<?php
		}
		static function server_check_evernight_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_evernight" name="lotroserver_choice_evernight" value="1" <?php if(isset($options['Evernight'])) checked($options['Evernight'], 1); ?> />
			<?php
		}
		static function server_check_gilrain_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_gilrain" name="lotroserver_choice_gilrain" value="1" <?php if(isset($options['Gilrain'])) checked($options['Gilrain'], 1); ?> />
			<?php
		}
		static function server_check_laurelin_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_laurelin" name="lotroserver_choice_laurelin" value="1" <?php if(isset($options['Laurelin'])) checked($options['Laurelin'], 1); ?> />
			<?php
		}
		static function server_check_withywindle_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_withywindle" name="lotroserver_choice_withywindle" value="1" <?php if(isset($options['Withywindle'])) checked($options['Withywindle'], 1); ?> />
			<?php
		}
		static function server_check_sirannon_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_sirannon" name="lotroserver_choice_sirannon" value="1" <?php if(isset($options['Sirannon'])) checked($options['Sirannon'], 1); ?> />
			<?php
		}
		static function server_check_estel_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_estel" name="lotroserver_choice_estel" value="1" <?php if(isset($options['Estel'])) checked($options['Estel'], 1); ?> />
			<?php
		}

		static function us_server_section_text() {
			echo __('Choose from the given US servers below your favourite ones. These are displayed in the frontend.', 'DLSlanguage');
		}
		static function server_check_arkenstone_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_arkenstone" name="lotroserver_choice_arkenstone" value="1" <?php if(isset($options['Arkenstone'])) checked($options['Arkenstone'], 1); ?> />
			<?php
		}
		static function server_check_brandywine_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_brandywine" name="lotroserver_choice_brandywine" value="1" <?php if(isset($options['Brandywine'])) checked($options['Brandywine'], 1); ?> />
			<?php
		}
		static function server_check_crickhollow_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_crickhollow" name="lotroserver_choice_crickhollow" value="1" <?php if(isset($options['Crickhollow'])) checked($options['Crickhollow'], 1); ?> />
			<?php
		}
		static function server_check_dwarrowdelf_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_dwarrowdelf" name="lotroserver_choice_dwarrowdelf" value="1" <?php if(isset($options['Dwarrowdelf'])) checked($options['Dwarrowdelf'], 1); ?> />
			<?php
		}
		static function server_check_elendilmir_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_elendilmir" name="lotroserver_choice_elendilmir" value="1" <?php if(isset($options['Elendilmir'])) checked($options['Elendilmir'], 1); ?> />
			<?php
		}
		static function server_check_firefoot_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_firefoot" name="lotroserver_choice_firefoot" value="1" <?php if(isset($options['Firefoot'])) checked($options['Firefoot'], 1); ?> />
			<?php
		}
		static function server_check_gladden_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_gladden" name="lotroserver_choice_gladden" value="1" <?php if(isset($options['Gladden'])) checked($options['Gladden'], 1); ?> />
			<?php
		}
		static function server_check_imladris_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_imladris" name="lotroserver_choice_imladris" value="1" <?php if(isset($options['Imladris'])) checked($options['Imladris'], 1); ?> />
			<?php
		}
		static function server_check_landroval_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_landroval" name="lotroserver_choice_landroval" value="1" <?php if(isset($options['Landroval'])) checked($options['Landroval'], 1); ?> />
			<?php
		}
		static function server_check_meneldor_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_meneldor" name="lotroserver_choice_meneldor" value="1" <?php if(isset($options['Meneldor'])) checked($options['Meneldor'], 1); ?> />
			<?php
		}
		static function server_check_nimrodel_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_nimrodel" name="lotroserver_choice_nimrodel" value="1" <?php if(isset($options['Nimrodel'])) checked($options['Nimrodel'], 1); ?> />
			<?php
		}
		static function server_check_riddermark_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_riddermark" name="lotroserver_choice_riddermark" value="1" <?php if(isset($options['Riddermark'])) checked($options['Riddermark'], 1); ?> />
			<?php
		}
		static function server_check_silverlode_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_silverlode" name="lotroserver_choice_silverlode" value="1" <?php if(isset($options['Silverlode'])) checked($options['Silverlode'], 1); ?> />
			<?php
		}
		static function server_check_vilya_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_vilya" name="lotroserver_choice_vilya" value="1" <?php if(isset($options['Vilya'])) checked($options['Vilya'], 1); ?> />
			<?php
		}
		static function server_check_windfola_callback() {
			$options = get_option( parent::$optiontag );
			?>
				<input type="checkbox" id="choice_windfola" name="lotroserver_choice_windfola" value="1" <?php if(isset($options['Windfola'])) checked($options['Windfola'], 1); ?> />
			<?php
		}
		static function server_check_bullroarer_callback() {
			$options = get_option( parent::$optiontag );
			?>
			<input type="checkbox" id="choice_bullroarer" name="lotroserver_choice_bullroarer" value="1" <?php if(isset($options['Bullroarer'])) checked($options['Bullroarer'], 1); ?> />
			<?php
		}

	/**
	* Load the HTML for the admin page
	**/
	static function showAdminPage() {
?>
<div class="wrap">
		<?php
		if( isset($_POST['notice']) )
			echo '<div id="message" class="updated"><p><strong>' . $_POST['notice'] . '</strong></p></div>';
		?>
	<h2><?php echo __( 'Display Lotro Server: Settings', 'DLSlanguage' ); ?></h2>
	<?php settings_errors(); ?>
	<?php echo __( 'On this page you can do change the settings for displaying your desired servers.', 'DLSlanguage' ); ?>
	<form method="post" action="">
		<?php wp_nonce_field( 'save_serveroptions', '_wpnonce-lotroserver' ); ?>
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

}
