<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://fgrweb.es
 * @since      1.0.0
 *
 * @package    Fgrweb_Plugin
 * @subpackage Fgrweb_Plugin/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Fgrweb_Plugin
 * @subpackage Fgrweb_Plugin/includes
 * @author     Fernando Garcia Rebolledo <info@fgrweb.es>
 */
class Fgrweb_Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Fgrweb_Plugin_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'FGRWEB_PLUGIN_VERSION' ) ) {
			$this->version = FGRWEB_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'fgrweb-plugin';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Fgrweb_Plugin_Loader. Orchestrates the hooks of the plugin.
	 * - Fgrweb_Plugin_i18n. Defines internationalization functionality.
	 * - Fgrweb_Plugin_Admin. Defines all hooks for the admin area.
	 * - Fgrweb_Plugin_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-fgrweb-plugin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-fgrweb-plugin-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-fgrweb-plugin-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-fgrweb-plugin-public.php';

		$this->loader = new Fgrweb_Plugin_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Fgrweb_Plugin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Fgrweb_Plugin_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Fgrweb_Plugin_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		// Add config menu in options page.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'fgrweb_add_config_menu' );
		// Register settings.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'fgrweb_register_settings' );

		// Apply security settings.
		$this->apply_security_settings( $plugin_admin );
	}

	/**
	 * Apply security settings based on user configuration.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    Fgrweb_Plugin_Admin $plugin_admin Instance of the admin class.
	 * @return   void
	 */
	private function apply_security_settings( $plugin_admin ) {
		$security_options = get_option( 'fgrweb_plugin_security_options', array() );

		// Security Headers.
		if ( ! empty( $security_options['x_frame_options'] ) || ! empty( $security_options['x_content_type_options'] ) ||
			! empty( $security_options['x_xss_protection'] ) || ! empty( $security_options['referrer_policy'] ) ||
			! empty( $security_options['permissions_policy'] ) || ! empty( $security_options['hsts'] ) ) {
			$this->loader->add_action( 'send_headers', $plugin_admin, 'fgrweb_apply_security_headers' );
		}

		// Disable emojis.
		if ( ! empty( $security_options['disable_emojis'] ) ) {
			$this->loader->add_action( 'init', $plugin_admin, 'fgrweb_disable_emojis' );
		}

		// Remove WP version.
		if ( ! empty( $security_options['remove_wp_version'] ) ) {
			$this->loader->add_action( 'init', $plugin_admin, 'fgrweb_remove_wp_version' );
		}

		// Remove WLW Manifest.
		if ( ! empty( $security_options['remove_wlw_manifest'] ) ) {
			$this->loader->add_action( 'init', $plugin_admin, 'fgrweb_remove_wlw_manifest' );
		}

		// Remove RSD Link.
		if ( ! empty( $security_options['remove_rsd_link'] ) ) {
			$this->loader->add_action( 'init', $plugin_admin, 'fgrweb_remove_rsd_link' );
		}

		// Remove Shortlink.
		if ( ! empty( $security_options['remove_shortlink'] ) ) {
			$this->loader->add_action( 'init', $plugin_admin, 'fgrweb_remove_shortlink' );
		}

		// Remove REST API links.
		if ( ! empty( $security_options['remove_rest_api_links'] ) ) {
			$this->loader->add_action( 'init', $plugin_admin, 'fgrweb_remove_rest_api_links' );
		}

		// Remove oEmbed links.
		if ( ! empty( $security_options['remove_oembed_links'] ) ) {
			$this->loader->add_action( 'init', $plugin_admin, 'fgrweb_remove_oembed_links' );
		}

		// Disable file edit.
		if ( ! empty( $security_options['disable_file_edit'] ) ) {
			$plugin_admin->fgrweb_disable_file_edit();
		}

		// Disable XML-RPC.
		if ( ! empty( $security_options['disable_xmlrpc'] ) ) {
			$this->loader->add_filter( 'xmlrpc_enabled', $plugin_admin, 'fgrweb_disable_xmlrpc' );
		}

		// Hide login errors.
		if ( ! empty( $security_options['hide_login_errors'] ) ) {
			$this->loader->add_filter( 'login_errors', $plugin_admin, 'fgrweb_hide_login_errors' );
		}

		// Disable REST API for non-authenticated users.
		if ( ! empty( $security_options['disable_rest_api'] ) ) {
			$this->loader->add_filter( 'rest_authentication_errors', $plugin_admin, 'fgrweb_disable_rest_api' );
		}

		// Disable REST API users endpoint.
		if ( ! empty( $security_options['disable_rest_users'] ) ) {
			$this->loader->add_filter( 'rest_endpoints', $plugin_admin, 'fgrweb_disable_rest_users' );
		}

		// Remove version from scripts and styles.
		if ( ! empty( $security_options['remove_version_scripts'] ) ) {
			$this->loader->add_filter( 'style_loader_src', $plugin_admin, 'fgrweb_remove_version_scripts', 9999 );
			$this->loader->add_filter( 'script_loader_src', $plugin_admin, 'fgrweb_remove_version_scripts', 9999 );
		}

		// Disable trackbacks.
		if ( ! empty( $security_options['disable_trackbacks'] ) ) {
			$this->loader->add_filter( 'pings_open', $plugin_admin, 'fgrweb_disable_trackbacks', 10, 1 );
		}

		// Disable application passwords.
		if ( ! empty( $security_options['disable_application_passwords'] ) ) {
			$this->loader->add_filter( 'wp_is_application_passwords_available', $plugin_admin, 'fgrweb_disable_application_passwords' );
		}

		// Disable feeds.
		if ( ! empty( $security_options['disable_feeds'] ) ) {
			$this->loader->add_action( 'do_feed', $plugin_admin, 'fgrweb_disable_feeds', 1 );
			$this->loader->add_action( 'do_feed_rdf', $plugin_admin, 'fgrweb_disable_feeds', 1 );
			$this->loader->add_action( 'do_feed_rss', $plugin_admin, 'fgrweb_disable_feeds', 1 );
			$this->loader->add_action( 'do_feed_rss2', $plugin_admin, 'fgrweb_disable_feeds', 1 );
			$this->loader->add_action( 'do_feed_atom', $plugin_admin, 'fgrweb_disable_feeds', 1 );
			$this->loader->add_action( 'do_feed_rss2_comments', $plugin_admin, 'fgrweb_disable_feeds', 1 );
			$this->loader->add_action( 'do_feed_atom_comments', $plugin_admin, 'fgrweb_disable_feeds', 1 );
		}

		// Limit post revisions.
		if ( ! empty( $security_options['limit_post_revisions'] ) ) {
			$plugin_admin->fgrweb_limit_post_revisions();
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Fgrweb_Plugin_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Fgrweb_Plugin_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
