<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://fgrweb.es
 * @since      1.0.0
 *
 * @package    Fgrweb_Plugin
 * @subpackage Fgrweb_Plugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fgrweb_Plugin
 * @subpackage Fgrweb_Plugin/admin
 * @author     Fernando Garcia Rebolledo <info@fgrweb.es>
 */
class Fgrweb_Plugin_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fgrweb-plugin-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fgrweb-plugin-admin.js', array( 'jquery' ), $this->version, false );
	}
	/**
	 * Display the plugin settings section.
	 *
	 * @return void
	 */
	public function fgrweb_register_settings() {
		register_setting( 'fgrweb-plugin-config', 'socios_aeca_config' );
		add_settings_section(
			'fgrweb-plugin-config-section-token',
			__( 'Token de autenticación', 'fgrweb-plugin' ),
			array( $this, 'fgrweb_config_section_token' ),
			'fgrweb-plugin-config'
		);
		add_settings_field(
			'github_token',
			__( 'GitHub Token', 'fgrweb-plugin' ),
			array( $this, 'fgrweb_config_field_token' ),
			'fgrweb-plugin-config',
			'fgrweb-plugin-config-section-token'
		);
	}

	/**
	 * Display the plugin settings section.
	 *
	 * @return void
	 */
	public function fgrweb_config_section_token() {
		echo wp_kses( __( '<p>Introduce el token de autenticación de GitHub para comprobar actualizaciones.</p>', 'fgrweb-plugin' ), array( 'p' => array() ) );
	}

	/**
	 * Display the plugin settings field.
	 *
	 * @return void
	 */
	public function fgrweb_config_field_token() {
		$options = get_option( 'fgrweb_plugin_config' );
		?>
		<input type="password" name="fgrweb_plugin_config[github_token]" value="<?php echo isset( $options['github_token'] ) ? esc_attr( '*********' ) : ''; ?>" />
		<?php
	}

	/**
	 * Add the plugin settings page to the admin menu.
	 *
	 * @return void
	 */
	public function fgrweb_add_config_menu() {
		add_options_page(
			'Configuración Fgrweb Plugin',
			'Fgrweb Plugin',
			'manage_options',
			'fgrweb-plugin-config',
			array( $this, 'fgrweb_config_page' )
		);
	}

	/**
	 * Display the plugin settings page.
	 *
	 * @return void
	 */
	public function fgrweb_config_page() {
		// Create tabs.
		$tabs        = array(
			'updates' => 'Actualizaciones',
		);
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'updates';
		?>
		<div class="wrap">
			<h2>Configuración Fgrweb Plugin</h2>
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab => $name ) {
					$class = ( $tab == $current_tab ) ? ' nav-tab-active' : '';
					echo '<a href="?page=fgrweb-plugin-config&tab=' . $tab . '" class="nav-tab' . $class . '">' . $name . '</a>';
				}
				?>
			</h2>
			<?php
			// Display the settings fields in update tab.
			if ( 'updates' === $current_tab ) {
				// Save options.
				if ( isset( $_POST['submit'] ) ) {
					$options = get_option( 'fgrweb_plugin_config' );
					// GitHub token.
					$options['github_token'] = sanitize_text_field( $_POST['fgrweb_plugin_config']['github_token'] );
					// Save options.
					update_option( 'fgrweb_plugin_config', $options );
				}
				?>
				<form method="post" action="options-general.php?page=fgrweb-plugin-config"> 
					<?php
					settings_fields( 'fgrweb-plugin-config-token' );
					do_settings_sections( 'fgrweb-plugin-config' );
					submit_button();
					?>
				</form>
				<?php
			}
			?>
		</div>
		<?php
	}
}
