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
		register_setting( 'fgrweb-plugin-config', 'fgrweb_plugin_config' );
		register_setting( 'fgrweb-plugin-security', 'fgrweb_security_options' );

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
			'updates'  => 'Actualizaciones',
			'security' => 'Seguridad',
		);
		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'updates';
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
				if ( isset( $_POST['submit'] ) && isset( $_POST['fgrweb_config_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fgrweb_config_nonce'] ) ), 'fgrweb_config_action' ) ) {
					$options = get_option( 'fgrweb_plugin_config' );
					// GitHub token.
					if ( isset( $_POST['fgrweb_plugin_config']['github_token'] ) ) {
						$options['github_token'] = sanitize_text_field( wp_unslash( $_POST['fgrweb_plugin_config']['github_token'] ) );
					}
					// Save options.
					update_option( 'fgrweb_plugin_config', $options );
					echo '<div class="notice notice-success"><p>' . esc_html__( 'Configuración guardada correctamente.', 'fgrweb-plugin' ) . '</p></div>';
				}
				?>
				<form method="post" action="options-general.php?page=fgrweb-plugin-config">
					<?php
					wp_nonce_field( 'fgrweb_config_action', 'fgrweb_config_nonce' );
					settings_fields( 'fgrweb-plugin-config-token' );
					do_settings_sections( 'fgrweb-plugin-config' );
					submit_button();
					?>
				</form>
				<?php
			} elseif ( 'security' === $current_tab ) {
				$this->fgrweb_security_page();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Display the security settings page.
	 *
	 * @return void
	 */
	public function fgrweb_security_page() {
		// Save options.
		if ( isset( $_POST['submit'] ) && isset( $_POST['fgrweb_security_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fgrweb_security_nonce'] ) ), 'fgrweb_security_action' ) ) {
			$options = array();

			// Get all POST data.
			if ( isset( $_POST['fgrweb_security'] ) && is_array( $_POST['fgrweb_security'] ) ) {
				foreach ( $_POST['fgrweb_security'] as $key => $value ) {
					$options[ sanitize_key( $key ) ] = '1';
				}
			}

			// Save options.
			update_option( 'fgrweb_security_options', $options );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Configuración de seguridad guardada correctamente.', 'fgrweb-plugin' ) . '</p></div>';
		}

		$security_options = get_option( 'fgrweb_security_options', array() );
		?>
		<div class="fgrweb-security-settings">
			<form method="post" action="">
				<?php wp_nonce_field( 'fgrweb_security_action', 'fgrweb_security_nonce' ); ?>

			<h3><?php esc_html_e( 'Cabeceras de Seguridad HTTP', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'X-Frame-Options', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[x_frame_options]" value="1" <?php checked( isset( $security_options['x_frame_options'] ), true ); ?> />
							<?php esc_html_e( 'Prevenir clickjacking (SAMEORIGIN)', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'X-Content-Type-Options', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[x_content_type_options]" value="1" <?php checked( isset( $security_options['x_content_type_options'] ), true ); ?> />
							<?php esc_html_e( 'Prevenir MIME sniffing', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'X-XSS-Protection', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[x_xss_protection]" value="1" <?php checked( isset( $security_options['x_xss_protection'] ), true ); ?> />
							<?php esc_html_e( 'Protección contra XSS en navegadores antiguos', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Referrer-Policy', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[referrer_policy]" value="1" <?php checked( isset( $security_options['referrer_policy'] ), true ); ?> />
							<?php esc_html_e( 'Controlar información de referrer (strict-origin-when-cross-origin)', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Permissions-Policy', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[permissions_policy]" value="1" <?php checked( isset( $security_options['permissions_policy'] ), true ); ?> />
							<?php esc_html_e( 'Controlar APIs del navegador (geolocation, microphone, camera)', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Strict-Transport-Security (HSTS)', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[hsts]" value="1" <?php checked( isset( $security_options['hsts'] ), true ); ?> />
							<?php esc_html_e( 'Forzar HTTPS (max-age=31536000)', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Limpieza y Optimización', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Emojis de WordPress', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[disable_emojis]" value="1" <?php checked( isset( $security_options['disable_emojis'] ), true ); ?> />
							<?php esc_html_e( 'Deshabilitar emojis de WordPress', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Generator Meta Tag', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[remove_wp_version]" value="1" <?php checked( isset( $security_options['remove_wp_version'] ), true ); ?> />
							<?php esc_html_e( 'Eliminar meta tag generator (ocultar versión de WordPress)', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'WLW Manifest', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[remove_wlw_manifest]" value="1" <?php checked( isset( $security_options['remove_wlw_manifest'] ), true ); ?> />
							<?php esc_html_e( 'Eliminar Windows Live Writer Manifest', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'RSD Link', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[remove_rsd_link]" value="1" <?php checked( isset( $security_options['remove_rsd_link'] ), true ); ?> />
							<?php esc_html_e( 'Eliminar Really Simple Discovery link', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Shortlink', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[remove_shortlink]" value="1" <?php checked( isset( $security_options['remove_shortlink'] ), true ); ?> />
							<?php esc_html_e( 'Eliminar shortlink del header', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'REST API Links', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[remove_rest_api_links]" value="1" <?php checked( isset( $security_options['remove_rest_api_links'] ), true ); ?> />
							<?php esc_html_e( 'Eliminar REST API links del header', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'oEmbed Links', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[remove_oembed_links]" value="1" <?php checked( isset( $security_options['remove_oembed_links'] ), true ); ?> />
							<?php esc_html_e( 'Eliminar oEmbed discovery links', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Protección de Archivos y Directorios', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Edición de Archivos', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[disable_file_edit]" value="1" <?php checked( isset( $security_options['disable_file_edit'] ), true ); ?> />
							<?php esc_html_e( 'Deshabilitar edición de archivos en el panel de administración', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'XML-RPC', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[disable_xmlrpc]" value="1" <?php checked( isset( $security_options['disable_xmlrpc'] ), true ); ?> />
							<?php esc_html_e( 'Deshabilitar XML-RPC completamente', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Login y Autenticación', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Errores de Login', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[hide_login_errors]" value="1" <?php checked( isset( $security_options['hide_login_errors'] ), true ); ?> />
							<?php esc_html_e( 'Ocultar mensajes de error de login detallados', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'REST API y Endpoints', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'REST API para No Autenticados', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[disable_rest_api]" value="1" <?php checked( isset( $security_options['disable_rest_api'] ), true ); ?> />
							<?php esc_html_e( 'Deshabilitar REST API para usuarios no autenticados', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Usuarios en REST API', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[disable_rest_users]" value="1" <?php checked( isset( $security_options['disable_rest_users'] ), true ); ?> />
							<?php esc_html_e( 'Ocultar listado de usuarios en REST API', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Información del Sistema', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Versión en Scripts', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[remove_version_scripts]" value="1" <?php checked( isset( $security_options['remove_version_scripts'] ), true ); ?> />
							<?php esc_html_e( 'Eliminar versión de WordPress en scripts y styles', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Comentarios y Contenido', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Trackbacks y Pingbacks', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[disable_trackbacks]" value="1" <?php checked( isset( $security_options['disable_trackbacks'] ), true ); ?> />
							<?php esc_html_e( 'Deshabilitar trackbacks y pingbacks', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Desactivación de Funcionalidades', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Application Passwords', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[disable_application_passwords]" value="1" <?php checked( isset( $security_options['disable_application_passwords'] ), true ); ?> />
							<?php esc_html_e( 'Deshabilitar Application Passwords', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Feeds RSS', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[disable_feeds]" value="1" <?php checked( isset( $security_options['disable_feeds'] ), true ); ?> />
							<?php esc_html_e( 'Deshabilitar feeds RSS', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Base de Datos', 'fgrweb-plugin' ); ?></h3>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Revisiones de Posts', 'fgrweb-plugin' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="fgrweb_security[limit_post_revisions]" value="1" <?php checked( isset( $security_options['limit_post_revisions'] ), true ); ?> />
							<?php esc_html_e( 'Limitar revisiones de posts a 5', 'fgrweb-plugin' ); ?>
						</label>
					</td>
				</tr>
			</table>

				<?php submit_button( __( 'Guardar Configuración de Seguridad', 'fgrweb-plugin' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Apply security headers.
	 *
	 * @return void
	 */
	public function fgrweb_apply_security_headers() {
		$options = get_option( 'fgrweb_security_options', array() );

		if ( isset( $options['x_frame_options'] ) ) {
			header( 'X-Frame-Options: SAMEORIGIN' );
		}

		if ( isset( $options['x_content_type_options'] ) ) {
			header( 'X-Content-Type-Options: nosniff' );
		}

		if ( isset( $options['x_xss_protection'] ) ) {
			header( 'X-XSS-Protection: 1; mode=block' );
		}

		if ( isset( $options['referrer_policy'] ) ) {
			header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		}

		if ( isset( $options['permissions_policy'] ) ) {
			header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
		}

		if ( isset( $options['hsts'] ) && is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
		}
	}

	/**
	 * Disable emojis.
	 *
	 * @return void
	 */
	public function fgrweb_disable_emojis() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'tiny_mce_plugins', array( $this, 'fgrweb_disable_emojis_tinymce' ) );
		add_filter( 'wp_resource_hints', array( $this, 'fgrweb_disable_emojis_dns_prefetch' ), 10, 2 );
	}

	/**
	 * Filter function used to remove the tinymce emoji plugin.
	 *
	 * @param array $plugins The plugins array.
	 * @return array Plugins array without emoji plugin.
	 */
	public function fgrweb_disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}

	/**
	 * Remove emoji CDN hostname from DNS prefetching hints.
	 *
	 * @param array  $urls URLs to print for resource hints.
	 * @param string $relation_type The relation type the URLs are printed for.
	 * @return array Difference betwen the two arrays.
	 */
	public function fgrweb_disable_emojis_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls          = array_diff( $urls, array( $emoji_svg_url ) );
		}
		return $urls;
	}

	/**
	 * Remove WordPress version from head.
	 *
	 * @return void
	 */
	public function fgrweb_remove_wp_version() {
		remove_action( 'wp_head', 'wp_generator' );
		add_filter( 'the_generator', '__return_empty_string' );
	}

	/**
	 * Remove WLW Manifest link.
	 *
	 * @return void
	 */
	public function fgrweb_remove_wlw_manifest() {
		remove_action( 'wp_head', 'wlwmanifest_link' );
	}

	/**
	 * Remove RSD link.
	 *
	 * @return void
	 */
	public function fgrweb_remove_rsd_link() {
		remove_action( 'wp_head', 'rsd_link' );
	}

	/**
	 * Remove shortlink.
	 *
	 * @return void
	 */
	public function fgrweb_remove_shortlink() {
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	}

	/**
	 * Remove REST API links from header.
	 *
	 * @return void
	 */
	public function fgrweb_remove_rest_api_links() {
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	}

	/**
	 * Remove oEmbed discovery links.
	 *
	 * @return void
	 */
	public function fgrweb_remove_oembed_links() {
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	}

	/**
	 * Disable file editing.
	 *
	 * @return void
	 */
	public function fgrweb_disable_file_edit() {
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}
	}

	/**
	 * Disable XML-RPC.
	 *
	 * @param bool $enabled Whether XML-RPC is enabled.
	 * @return bool
	 */
	public function fgrweb_disable_xmlrpc( $enabled ) {
		return false;
	}

	/**
	 * Hide login errors.
	 *
	 * @return string Generic error message.
	 */
	public function fgrweb_hide_login_errors() {
		return __( 'Información de acceso incorrecta.', 'fgrweb-plugin' );
	}

	/**
	 * Disable REST API for non-authenticated users.
	 *
	 * @param WP_Error|null|bool $result The result to check.
	 * @return WP_Error|null|bool
	 */
	public function fgrweb_disable_rest_api( $result ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_disabled',
				__( 'La REST API está deshabilitada para usuarios no autenticados.', 'fgrweb-plugin' ),
				array( 'status' => 401 )
			);
		}
		return $result;
	}

	/**
	 * Disable users endpoint in REST API.
	 *
	 * @param array $endpoints The REST API endpoints.
	 * @return array
	 */
	public function fgrweb_disable_rest_users( $endpoints ) {
		if ( isset( $endpoints['/wp/v2/users'] ) ) {
			unset( $endpoints['/wp/v2/users'] );
		}
		if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
			unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}

	/**
	 * Remove version from scripts and styles.
	 *
	 * @param string $src The source URL.
	 * @return string
	 */
	public function fgrweb_remove_version_scripts( $src ) {
		if ( strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	}

	/**
	 * Disable trackbacks and pingbacks.
	 *
	 * @param bool $open Whether pingbacks are open.
	 * @return bool
	 */
	public function fgrweb_disable_trackbacks( $open ) {
		return false;
	}

	/**
	 * Disable application passwords.
	 *
	 * @param bool $enabled Whether application passwords are enabled.
	 * @return bool
	 */
	public function fgrweb_disable_application_passwords( $enabled ) {
		return false;
	}

	/**
	 * Disable RSS feeds.
	 *
	 * @return void
	 */
	public function fgrweb_disable_feeds() {
		wp_die( esc_html__( 'No hay feeds disponibles. Por favor, visita la página principal.', 'fgrweb-plugin' ) );
	}

	/**
	 * Limit post revisions.
	 *
	 * @return void
	 */
	public function fgrweb_limit_post_revisions() {
		if ( ! defined( 'WP_POST_REVISIONS' ) ) {
			define( 'WP_POST_REVISIONS', 5 );
		}
	}
}
