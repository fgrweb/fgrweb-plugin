<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://fgrweb.es
 * @since      1.0.0
 *
 * @package    Fgrweb_Plugin
 * @subpackage Fgrweb_Plugin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Fgrweb_Plugin
 * @subpackage Fgrweb_Plugin/includes
 * @author     Fernando Garcia Rebolledo <info@fgrweb.es>
 */
class Fgrweb_Plugin_Deactivator {

	/**
	 * Plugin deactivation handler.
	 *
	 * This method is called when the plugin is deactivated. Note that plugin
	 * options are NOT deleted here - they are preserved in case the plugin
	 * is reactivated. Options are only deleted during uninstall.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Clear any cached data if needed.
		// Note: Options are not deleted here, only during uninstall.
	}

}
