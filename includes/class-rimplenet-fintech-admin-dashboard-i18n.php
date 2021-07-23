<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       rimplenet.com
 * @since      1.0.0
 *
 * @package    Rimplenet_Fintech_Admin_Dashboard
 * @subpackage Rimplenet_Fintech_Admin_Dashboard/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Rimplenet_Fintech_Admin_Dashboard
 * @subpackage Rimplenet_Fintech_Admin_Dashboard/includes
 * @author     Rimplenet <developers@rimplenet.com>
 */
class Rimplenet_Fintech_Admin_Dashboard_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rimplenet-fintech-admin-dashboard',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
