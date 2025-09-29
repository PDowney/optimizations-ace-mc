<?php
/**
 * Main plugin class.
 *
 * @package OptimizationsAceMc
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main plugin class.
 */
class Optimizations_Ace_Mc {

	/**
	 * The single instance of the class.
	 *
	 * @var Optimizations_Ace_Mc|null
	 */
	protected static $instance = null;

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Default settings.
	 *
	 * @var array
	 */
	private $default_settings = array(
		'woocommerce_show_empty_categories'   => false,
		'woocommerce_hide_category_count'     => false,
		'woocommerce_user_order_count_column' => false,
		'wpsl_show_store_categories'          => false,
		'wpsl_disable_rest_api'               => false,
		'admin_user_registration_date_column' => false,
	);

	/**
	 * Main instance.
	 *
	 * @return Optimizations_Ace_Mc
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Load settings.
		$this->load_settings();

		// Initialize admin interface.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );

		// Initialize optimizations.
		$this->init_optimizations();
	}

	/**
	 * Load plugin settings.
	 */
	private function load_settings() {
		$saved_settings = get_option( 'optimizations_ace_mc_settings', array() );
		$this->settings = wp_parse_args( $saved_settings, $this->default_settings );
	}

	/**
	 * Get setting value.
	 *
	 * @param string $key Setting key.
	 * @return mixed Setting value.
	 */
	private function get_setting( $key ) {
		return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : false;
	}

	/**
	 * Initialize optimization functions.
	 */
	private function init_optimizations() {
		// WooCommerce optimizations.
		$this->init_woocommerce_optimizations();

		// WP Store Locator optimizations.
		$this->init_wpsl_optimizations();

		// WordPress admin optimizations.
		$this->init_admin_optimizations();
	}

	/**
	 * Initialize WooCommerce-specific optimizations.
	 */
	private function init_woocommerce_optimizations() {
		// Show empty categories in WooCommerce.
		if ( $this->get_setting( 'woocommerce_show_empty_categories' ) ) {
			add_filter( 'woocommerce_product_subcategories_hide_empty', '__return_false' );
		}

		// Hide category product count in product archives.
		if ( $this->get_setting( 'woocommerce_hide_category_count' ) ) {
			add_filter( 'woocommerce_subcategory_count_html', '__return_false' );
		}

		// Add order count column to users admin.
		if ( $this->get_setting( 'woocommerce_user_order_count_column' ) && is_admin() && current_user_can( 'list_users' ) ) {
			add_filter( 'manage_users_columns', array( $this, 'add_user_order_count_column' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'display_user_order_count_column' ), 10, 3 );
			add_filter( 'manage_users_sortable_columns', array( $this, 'make_user_order_count_sortable' ) );
		}
	}

	/**
	 * Initialize WP Store Locator optimizations.
	 */
	private function init_wpsl_optimizations() {
		// Show store categories in store locator.
		if ( $this->get_setting( 'wpsl_show_store_categories' ) ) {
			add_filter( 'wpsl_store_meta', array( $this, 'add_store_categories_to_meta' ), 10, 2 );
			add_filter( 'wpsl_info_window_template', array( $this, 'customize_info_window_template' ) );
		}

		// Disable REST API for store locator post type.
		if ( $this->get_setting( 'wpsl_disable_rest_api' ) ) {
			add_filter( 'wpsl_post_type_args', array( $this, 'custom_post_type_args' ) );
		}
	}

	/**
	 * Initialize WordPress admin optimizations.
	 */
	private function init_admin_optimizations() {
		if ( ! is_admin() || ! current_user_can( 'list_users' ) ) {
			return;
		}

		// Add registration date column to users admin.
		if ( $this->get_setting( 'admin_user_registration_date_column' ) ) {
			add_filter( 'manage_users_columns', array( $this, 'add_user_registration_date_column' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'display_user_registration_date_column' ), 10, 3 );
			add_filter( 'manage_users_sortable_columns', array( $this, 'make_user_registration_date_sortable' ) );
		}
	}

	/**
	 * Add order count column to users table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_user_order_count_column( $columns ) {
		$columns['user_order_count'] = __( 'Order Count', 'optimizations-ace-mc' );
		return $columns;
	}

	/**
	 * Display order count in users table.
	 *
	 * @param string $output Custom column output.
	 * @param string $column_name Name of the column.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public function display_user_order_count_column( $output, $column_name, $user_id ) {
		// Security check - only for admins with list_users capability.
		if ( ! is_admin() || ! current_user_can( 'list_users' ) ) {
			return $output;
		}

		if ( 'user_order_count' === $column_name ) {
			$order_count = wc_get_customer_order_count( absint( $user_id ) );
			return esc_html( number_format_i18n( $order_count ) );
		}
		return $output;
	}

	/**
	 * Make order count column sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function make_user_order_count_sortable( $columns ) {
		$columns['user_order_count'] = 'user_order_count';
		return $columns;
	}

	/**
	 * Add store categories to store meta.
	 *
	 * @param array $store_meta Existing store meta.
	 * @param int   $store_id Store ID.
	 * @return array
	 */
	public function add_store_categories_to_meta( $store_meta, $store_id ) {
		$terms               = get_the_terms( absint( $store_id ), 'wpsl_store_category' );
		$store_meta['terms'] = '';

		if ( $terms && ! is_wp_error( $terms ) ) {
			if ( count( $terms ) > 1 ) {
				$location_terms = array();
				foreach ( $terms as $term ) {
					if ( ! empty( $term->name ) ) {
						$location_terms[] = sanitize_text_field( $term->name );
					}
				}
				$store_meta['terms'] = implode( ', ', $location_terms );
			} elseif ( ! empty( $terms[0]->name ) ) {
				$store_meta['terms'] = sanitize_text_field( $terms[0]->name );
			}
		}

		return $store_meta;
	}

	/**
	 * Customize info window template to include categories.
	 *
	 * @return string
	 */
	public function customize_info_window_template() {
		$info_window_template  = '<div data-store-id="<%= id %>" class="wpsl-info-window">' . "\n";
		$info_window_template .= '        <p>' . "\n";
		$info_window_template .= '            ' . wpsl_store_header_template() . "\n";
		$info_window_template .= '            <span><%= address %></span>' . "\n";
		$info_window_template .= '            <% if ( address2 ) { %>' . "\n";
		$info_window_template .= '            <span><%= address2 %></span>' . "\n";
		$info_window_template .= '            <% } %>' . "\n";
		$info_window_template .= '            <span>' . wpsl_address_format_placeholders() . '</span>' . "\n";
		$info_window_template .= '        </p>' . "\n";

		// Include the category names.
		$info_window_template .= '        <% if ( terms ) { %>' . "\n";
		$info_window_template .= '        <p>' . esc_html__( 'Certifications:', 'optimizations-ace-mc' ) . ' <%= terms %></p>' . "\n";
		$info_window_template .= '        <% } %>' . "\n";

		$info_window_template .= '        <%= createInfoWindowActions( id ) %>' . "\n";
		$info_window_template .= '    </div>' . "\n";

		return $info_window_template;
	}

	/**
	 * Disable REST API for WP Store Locator post type.
	 *
	 * @param array $args Post type arguments.
	 * @return array
	 */
	public function custom_post_type_args( $args ) {
		$args['show_in_rest'] = false;
		return $args;
	}

	/**
	 * Add registration date column to users table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_user_registration_date_column( $columns ) {
		$columns['registration_date'] = __( 'Registration Date', 'optimizations-ace-mc' );
		return $columns;
	}

	/**
	 * Display registration date in users table.
	 *
	 * @param string $output Custom column output.
	 * @param string $column_name Name of the column.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public function display_user_registration_date_column( $output, $column_name, $user_id ) {
		// Security check - only for admins with list_users capability.
		if ( ! is_admin() || ! current_user_can( 'list_users' ) ) {
			return $output;
		}

		if ( 'registration_date' === $column_name ) {
			$registration_date = get_the_author_meta( 'registered', absint( $user_id ) );
			if ( $registration_date ) {
				$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
				return esc_html( wp_date( $date_format, strtotime( $registration_date ) ) );
			}
			return esc_html__( 'Unknown', 'optimizations-ace-mc' );
		}
		return $output;
	}

	/**
	 * Make registration date column sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function make_user_registration_date_sortable( $columns ) {
		$columns['registration_date'] = 'registered';
		return $columns;
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'ACE MC Optimizations', 'optimizations-ace-mc' ),
			__( 'ACE MC Optimizations', 'optimizations-ace-mc' ),
			'manage_options',
			'optimizations-ace-mc',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Initialize settings.
	 */
	public function init_settings() {
		register_setting(
			'optimizations_ace_mc_group',
			'optimizations_ace_mc_settings',
			array( $this, 'sanitize_settings' )
		);

		// Initialize settings sections.
		$this->init_woocommerce_settings();
		$this->init_wpsl_settings();
		$this->init_admin_settings();
	}

	/**
	 * Initialize WooCommerce settings section.
	 */
	private function init_woocommerce_settings() {
		add_settings_section(
			'woocommerce_section',
			__( 'WooCommerce Optimizations', 'optimizations-ace-mc' ),
			array( $this, 'woocommerce_section_callback' ),
			'optimizations-ace-mc'
		);

		add_settings_field(
			'woocommerce_show_empty_categories',
			__( 'Show Empty Categories', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'woocommerce_section',
			array(
				'name'        => 'woocommerce_show_empty_categories',
				'description' => __( 'Show empty product categories in WooCommerce category listings.', 'optimizations-ace-mc' ),
			)
		);

		add_settings_field(
			'woocommerce_hide_category_count',
			__( 'Hide Category Product Count', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'woocommerce_section',
			array(
				'name'        => 'woocommerce_hide_category_count',
				'description' => __( 'Hide the product count numbers in category listings.', 'optimizations-ace-mc' ),
			)
		);

		add_settings_field(
			'woocommerce_user_order_count_column',
			__( 'User Order Count Column', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'woocommerce_section',
			array(
				'name'        => 'woocommerce_user_order_count_column',
				'description' => __( 'Add an order count column to the WordPress Users admin table.', 'optimizations-ace-mc' ),
			)
		);
	}

	/**
	 * Initialize WP Store Locator settings section.
	 */
	private function init_wpsl_settings() {
		add_settings_section(
			'wpsl_section',
			__( 'WP Store Locator Optimizations', 'optimizations-ace-mc' ),
			array( $this, 'wpsl_section_callback' ),
			'optimizations-ace-mc'
		);

		add_settings_field(
			'wpsl_show_store_categories',
			__( 'Show Store Categories', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'wpsl_section',
			array(
				'name'        => 'wpsl_show_store_categories',
				'description' => __( 'Display store categories in the store locator info windows as "Certifications".', 'optimizations-ace-mc' ),
			)
		);

		add_settings_field(
			'wpsl_disable_rest_api',
			__( 'Disable REST API', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'wpsl_section',
			array(
				'name'        => 'wpsl_disable_rest_api',
				'description' => __( 'Disable REST API endpoint for WP Store Locator post type for security.', 'optimizations-ace-mc' ),
			)
		);
	}

	/**
	 * Initialize WordPress admin settings section.
	 */
	private function init_admin_settings() {
		add_settings_section(
			'admin_section',
			__( 'WordPress Admin Optimizations', 'optimizations-ace-mc' ),
			array( $this, 'admin_section_callback' ),
			'optimizations-ace-mc'
		);

		add_settings_field(
			'admin_user_registration_date_column',
			__( 'User Registration Date Column', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'admin_section',
			array(
				'name'        => 'admin_user_registration_date_column',
				'description' => __( 'Add a registration date column to the WordPress Users admin table.', 'optimizations-ace-mc' ),
			)
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Sanitize each setting based on its type.
		foreach ( array_keys( $this->default_settings ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$sanitized[ $key ] = (bool) $input[ $key ];
			} else {
				$sanitized[ $key ] = false;
			}
		}

		return $sanitized;
	}

	/**
	 * WooCommerce section callback.
	 */
	public function woocommerce_section_callback() {
		echo '<p>' . esc_html__( 'Configure WooCommerce-specific optimizations. These features enhance the WooCommerce user experience and admin functionality.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * WP Store Locator section callback.
	 */
	public function wpsl_section_callback() {
		echo '<p>' . esc_html__( 'Configure WP Store Locator optimizations. These features enhance store location functionality and security.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * WordPress Admin section callback.
	 */
	public function admin_section_callback() {
		echo '<p>' . esc_html__( 'Configure WordPress admin interface optimizations. These features enhance the admin user experience.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * Checkbox field callback.
	 *
	 * @param array $args Field arguments.
	 */
	public function checkbox_field_callback( $args ) {
		$name        = $args['name'];
		$description = isset( $args['description'] ) ? $args['description'] : '';
		$checked     = $this->get_setting( $name );

		printf(
			'<label for="%1$s">
				<input type="checkbox" id="%1$s" name="optimizations_ace_mc_settings[%1$s]" value="1" %2$s />
				%3$s
			</label>',
			esc_attr( $name ),
			checked( $checked, true, false ),
			esc_html( $description )
		);
	}

	/**
	 * Settings page.
	 */
	public function settings_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'optimizations-ace-mc' ) );
		}

		// Display success message if settings were updated.
		$this->display_admin_notices();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<?php $this->display_plugin_info(); ?>
			
			<form method="post" action="options.php">
				<?php
				settings_fields( 'optimizations_ace_mc_group' );
				do_settings_sections( 'optimizations-ace-mc' );
				submit_button();
				?>
			</form>

			<?php
			$this->display_dependencies_info();
			$this->display_support_info();
			$this->output_admin_styles();
			?>
		</div>
		<?php
	}

	/**
	 * Display admin notices.
	 */
	private function display_admin_notices() {
		// Note: Form submission is handled automatically by WordPress Settings API.
		// The settings_fields() function handles CSRF protection via nonces.
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully!', 'optimizations-ace-mc' ) . '</p></div>';
		}
	}

	/**
	 * Display plugin information.
	 */
	private function display_plugin_info() {
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Plugin Information:', 'optimizations-ace-mc' ); ?></strong>
				<?php esc_html_e( 'This plugin provides pre-configured optimizations for WooCommerce, WP Store Locator, and WordPress admin interfaces.', 'optimizations-ace-mc' ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Version:', 'optimizations-ace-mc' ); ?></strong> <?php echo esc_html( OPTIMIZATIONS_ACE_MC_VERSION ); ?> |
				<strong><?php esc_html_e( 'WordPress:', 'optimizations-ace-mc' ); ?></strong> <?php esc_html_e( '6.5+ required', 'optimizations-ace-mc' ); ?> |
				<strong><?php esc_html_e( 'PHP:', 'optimizations-ace-mc' ); ?></strong> <?php esc_html_e( '7.4+ required', 'optimizations-ace-mc' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display plugin dependencies information.
	 */
	private function display_dependencies_info() {
		?>
		<div class="card">
			<h2><?php esc_html_e( 'Plugin Dependencies', 'optimizations-ace-mc' ); ?></h2>
			<p><?php esc_html_e( 'This plugin is designed to work with the following plugins:', 'optimizations-ace-mc' ); ?></p>
			<ul>
				<li>
					<strong>WooCommerce:</strong> 
					<?php
					if ( class_exists( 'WooCommerce' ) ) {
						echo '<span style="color: green;">✓ ' . esc_html__( 'Active', 'optimizations-ace-mc' ) . '</span>';
					} else {
						echo '<span style="color: orange;">! ' . esc_html__( 'Not detected', 'optimizations-ace-mc' ) . '</span>';
					}
					?>
				</li>
				<li>
					<strong>WP Store Locator:</strong> 
					<?php
					if ( class_exists( 'WP_Store_locator' ) || function_exists( 'wpsl_store_header_template' ) ) {
						echo '<span style="color: green;">✓ ' . esc_html__( 'Active', 'optimizations-ace-mc' ) . '</span>';
					} else {
						echo '<span style="color: orange;">! ' . esc_html__( 'Not detected', 'optimizations-ace-mc' ) . '</span>';
					}
					?>
				</li>
			</ul>
			<p><em><?php esc_html_e( 'Note: Plugin features will only be active when their corresponding dependencies are installed and activated.', 'optimizations-ace-mc' ); ?></em></p>
		</div>
		<?php
	}

	/**
	 * Display support information.
	 */
	private function display_support_info() {
		?>
		<div class="card">
			<h2><?php esc_html_e( 'Support & Documentation', 'optimizations-ace-mc' ); ?></h2>
			<p>
				<?php esc_html_e( 'For support, bug reports, or feature requests:', 'optimizations-ace-mc' ); ?>
				<a href="https://github.com/PDowney/optimizations-ace-mc" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Visit the GitHub repository', 'optimizations-ace-mc' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Output admin page styles.
	 */
	private function output_admin_styles() {
		?>
		<style>
			.card {
				background: #fff;
				border: 1px solid #ccd0d4;
				border-radius: 4px;
				padding: 15px;
				margin: 20px 0;
				max-width: 100%;
			}
			.card h2 {
				margin-top: 0;
			}
			.form-table th {
				width: 200px;
			}
			.form-table td label {
				display: flex;
				align-items: flex-start;
				gap: 10px;
			}
			.form-table td label input[type="checkbox"] {
				margin-top: 2px;
				flex-shrink: 0;
			}
		</style>
		<?php
	}
}
