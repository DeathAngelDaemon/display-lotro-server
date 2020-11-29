<?php

/**
 * Provide an admin area view for the plugin
 *
 * @package    Admin
 * @subpackage Admin/PageDisplay
 */

global $DLS;

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
	<h2><?php _e( 'Display Lotro Server: Settings', 'DLSlanguage' ); ?></h2>
	<?php _e( 'Set up the plugin here. <em>Important: Choose at least one server, otherwiese nothing will be displayed.</em>', 'DLSlanguage' ); ?>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<form method="post" action="options.php" class="dls-form">
		<?php wp_nonce_field( 'save_serveroptions', '_wpnonce-lotroserver' ); ?>
		<?php settings_fields($this->optionsection); ?>
	<div id="post-body-content">
    <h3><?php _e('General settings', 'DLSlanguage'); ?>:</h3>
    <ul>
      <li>
        <label for="choice_shortcode"><?php _e('Do you want to use the shortcode?', 'DLSlanguage'); ?></label>
        <input type="checkbox" id="choice_shortcode" name="<?php echo $this->optiontag.'[shortcode]' ?>" value="1" <?php (!isset($DLS->options['shortcode'])) ?: checked($DLS->options['shortcode'], 1); ?> />
      </li>
    </ul>
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
	    	$rpg = ($servername === 'Belegaer' || $servername === 'Laurelin') ? ' - RP' : '';
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
  		<?php	}	?>
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
					<?php _e('Choose the servers from the lists, which you want to show up on your site. You can select all EU or all US servers, but you can also choose only DE, EN or FR servers - in combination with the US servers. Everything is possible!', 'DLSlanguage'); ?>
				</p><p>
				<strong><?php _e('Widget', 'DLSlanguage'); ?>:</strong><br>
					<?php _e('You can use the widget <em>Status of the Lotro Server</em> to show up your chosen servers in a sidebar. The widget also allows you to choose if you want to show only the EU or the US servers you have checked here (e.g. if you want to insert two widgets for each region).', 'DLSlanguage'); ?>
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