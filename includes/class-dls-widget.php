<?php

/**
 * Define the widget function.
 *
 * @since      2.0.0
 * @package    DisplayLotroServer
 * @subpackage DisplayLotroServer/includes
 */
class DisplayLotroServer_Widget {

	/**
	 * Register the DisplayLotroServer widget.
	 *
	 * @since    2.0.0
	 */
	public function lotroserver_register_widgets() {

		register_widget( 'LotroServerWidget' );

	}

}