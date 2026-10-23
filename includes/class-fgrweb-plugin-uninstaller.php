<?php
/**
 * Fired during plugin uninstall
 *
 * @link       https://fgrweb.es
 * @since      1.0.0
 *
 * @package    Fgrweb_Plugin
 * @subpackage Fgrweb_Plugin/includes
 */

/**
 * Fired during plugin uninstall.
 *
 * This class defines all code necessary to run during the plugin's uninstall.
 *
 * @since      1.0.0
 * @package    Fgrweb_Plugin
 * @subpackage Fgrweb_Plugin/includes
 * @author     Fernando Garcia Rebolledo <info@fgrweb.es>
 */
class Fgrweb_Plugin_Uninstaller {

	/**
	 * Clean up plugin data on uninstall.
	 *
	 * Remove all plugin options from the database when the plugin is uninstalled.
	 * This includes configuration options and security settings.
	 *
	 * @since    1.0.0
	 */
	public static function uninstall() {

		// Delete plugin configuration options.
		delete_option( 'fgrweb_plugin_config' );

		// Delete security options.
		delete_option( 'fgrweb_security_options' );

		// Delete updater options.
		delete_option( 'external_updates-fgrweb-plugin' );

		// For multisite installations, delete options for all sites.
		if ( is_multisite() ) {
			// 'number' => 0 removes the default limit of 100 sites.
			$blog_ids = get_sites(
				array(
					'fields' => 'ids',
					'number' => 0,
				)
			);

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				// Delete options for each site.
				delete_option( 'fgrweb_plugin_config' );
				delete_option( 'fgrweb_security_options' );
				delete_option( 'external_updates-fgrweb-plugin' );

				restore_current_blog();
			}
		}
	}
}
