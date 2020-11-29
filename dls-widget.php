<?php

/**
* Adds the LotroServer widget.
*
* @since 0.5
*/
class LotroServerWidget extends WP_Widget {

	/**
	 * Register the widget with WordPress.
	 */
	public function __construct() {
		$widget_ops = array('classname' => 'LotroServerWidget', 'description' => __('Shows a configured list of Lotro servers', 'DLSlanguage') );
		parent::__construct('LotroServerWidget', __('Status of the Lotro Server', 'DLSlanguage'), $widget_ops);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $DLS;
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$leu = isset( $instance['loc_eu'] ) ? $instance['loc_eu'] : false;
		$lus = isset( $instance['loc_us'] ) ? $instance['loc_us'] : false;
		$attr_loc = (empty($leu) && empty($lus) || !empty($leu) && !empty($lus)) ? 'all'
					: (!empty($instance['loc_eu']) ? 'eu'
					: (empty($instance['loc_us']) ?: 'us'));

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		# output of the serverlist
		echo $DLS->show_serverlist($attr_loc);

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
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['loc_eu'] = $new_instance['loc_eu'] ? 1 : 0;
		$instance['loc_us'] = $new_instance['loc_us'] ? 1 : 0;

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		require_once('inc/dls-widget-display.php');
	}

} ?>
