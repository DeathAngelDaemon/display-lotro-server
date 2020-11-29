<?php

/**
 * Provide an admin area view for the plugin
 *
 * @package    Widget
 * @subpackage Widget/Display
 */

$title = (isset($instance['title'])) ? $instance['title'] : __('Lotro server list', 'DLSlanguage');
$locEU = (isset($instance['loc_eu'])) ? $instance['loc_eu'] : 0;
$locUS = (isset($instance['loc_us'])) ? $instance['loc_us'] : 0;

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'DLSlanguage' ); ?></label>
  <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
  <?php _e( 'Only show servers from specific location', 'DLSlanguage' ) ?><br />
  <input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('loc_eu'); ?>" name="<?php echo $this->get_field_name('loc_eu'); ?>" <?php checked( $locEU, 1 ); ?> />
  <label for="<?php echo $this->get_field_id('loc_eu'); ?>"><?php _e( 'EU server', 'DLSlanguage' ) ?></label>
  <br />
  <input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('loc_us'); ?>" name="<?php echo $this->get_field_name('loc_us'); ?>" <?php checked( $locUS, 1 ); ?> />
  <label for="<?php echo $this->get_field_id('loc_us'); ?>"><?php _e( 'US server', 'DLSlanguage' ) ?></label>
</p>