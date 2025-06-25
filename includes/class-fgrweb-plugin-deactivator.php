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
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Delete options - updater.
		delete_option( 'fgrweb_plugin_config' );
		delete_option( 'external_updates-fgrweb-plugin' );
	}

}
