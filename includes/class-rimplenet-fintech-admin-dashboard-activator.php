<?php

/**
 * Fired during plugin activation
 *
 * @link       rimplenet.com
 * @since      1.0.0
 *
 * @package    Rimplenet_Fintech_Admin_Dashboard
 * @subpackage Rimplenet_Fintech_Admin_Dashboard/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Rimplenet_Fintech_Admin_Dashboard
 * @subpackage Rimplenet_Fintech_Admin_Dashboard/includes
 * @author     Rimplenet <developers@rimplenet.com>
 */
class Rimplenet_Fintech_Admin_Dashboard_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{

		//check if rimplenet is installed



	}

	public function is_rimplenet_active()
	{
		$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

		foreach ($active_plugins as $plugin) {
			if (strpos($plugin, 'rimplenet/rimplenet.php')) {
				return true;
			}
		}

		return false;
	}
}
