<?php
/**
 * Main plugin class.
 *
 * @package OptimizationsAceMc
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Main plugin class.
 *
 * @since 1.0.0
 */
class Optimizations_Ace_Mc {

	/**
	 * The single instance of the class.
	 *
	 * @since 1.0.0
	 * @var self|null
	 */
	protected static ?self $instance = null;

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.6
	 * @var array<string, bool>
	 */
	private array $settings = [];

	/**
	 * Cached date format string.
	 *
	 * @since 1.0.9
	 * @var string
	 */
	private string $date_format = '';

	/**
	 * Default settings.
	 *
	 * @since 1.0.6
	 * @var array<string, bool>
	 */
	private readonly array $default_settings;

	/**
	 * Settings page hook suffix for conditional asset loading.
	 *
	 * @since 1.0.9
	 * @var string
	 */
	private string $settings_page_hook = '';

	/**
	 * Main instance.
	 *
	 * @since 1.0.0
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Prevent cloning.
	 *
	 * @since 1.0.7
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 *
	 * @since 1.0.7
	 */
	public function __wakeup(): void {
		_doing_it_wrong( __METHOD__, esc_html__( 'Unserializing is not allowed.', 'optimizations-ace-mc' ), '1.0.7' );
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->default_settings = [
			'woocommerce_show_empty_categories'   => false,
			'woocommerce_hide_category_count'     => false,
			'woocommerce_user_order_count_column' => false,
			'wpsl_show_store_categories'          => false,
			'wpsl_disable_rest_api'               => false,
			'admin_user_registration_date_column' => false,
		];

		// Load settings.
		$this->load_settings();

		// Initialize admin interface.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );

		// Initialize optimizations.
		$this->init_optimizations();
	}

	/**
	 * Load plugin settings.
	 *
	 * @since 1.0.6
	 */
	private function load_settings(): void {
		$saved_settings = get_option( 'optimizations_ace_mc_settings', [] );
		$this->settings = wp_parse_args( $saved_settings, $this->default_settings );
	}

	/**
	 * Get setting value.
	 *
	 * @since 1.0.6
	 * @param string $key Setting key.
	 * @return mixed Setting value or false if not found.
	 */
	private function get_setting( string $key ): mixed {
		return $this->settings[ $key ] ?? false;
	}

	/**
	 * Initialize optimization functions.
	 *
	 * @since 1.0.0
	 */
	private function init_optimizations(): void {
		$this->init_woocommerce_optimizations();
		$this->init_wpsl_optimizations();
		$this->init_admin_optimizations();
	}

	/**
	 * Initialize WooCommerce-specific optimizations.
	 *
	 * @since 1.0.0
	 */
	private function init_woocommerce_optimizations(): void {
		if ( $this->get_setting( 'woocommerce_show_empty_categories' ) ) {
			add_filter( 'woocommerce_product_subcategories_hide_empty', '__return_false' );
		}

		if ( $this->get_setting( 'woocommerce_hide_category_count' ) ) {
			add_filter( 'woocommerce_subcategory_count_html', '__return_false' );
		}

		if ( $this->get_setting( 'woocommerce_user_order_count_column' ) && is_admin() && current_user_can( 'list_users' ) ) {
			add_filter( 'manage_users_columns', array( $this, 'add_user_order_count_column' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'display_user_order_count_column' ), 10, 3 );
		}
	}

	/**
	 * Initialize WP Store Locator optimizations.
	 *
	 * @since 1.0.0
	 */
	private function init_wpsl_optimizations(): void {
		if ( $this->get_setting( 'wpsl_show_store_categories' ) ) {
			add_filter( 'wpsl_store_meta', array( $this, 'add_store_categories_to_meta' ), 10, 2 );
			add_filter( 'wpsl_info_window_template', array( $this, 'customize_info_window_template' ) );
		}

		if ( $this->get_setting( 'wpsl_disable_rest_api' ) ) {
			add_filter( 'wpsl_post_type_args', array( $this, 'custom_post_type_args' ) );
		}
	}

	/**
	 * Initialize WordPress admin optimizations.
	 *
	 * @since 1.0.6
	 */
	private function init_admin_optimizations(): void {
		if ( ! is_admin() || ! current_user_can( 'list_users' ) ) {
			return;
		}

		if ( $this->get_setting( 'admin_user_registration_date_column' ) ) {
			add_filter( 'manage_users_columns', array( $this, 'add_user_registration_date_column' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'display_user_registration_date_column' ), 10, 3 );
			add_filter( 'manage_users_sortable_columns', array( $this, 'make_user_registration_date_sortable' ) );
		}
	}

	/**
	 * Add order count column to users table.
	 *
	 * @since 1.0.0
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_user_order_count_column( array $columns ): array {
		$columns['user_order_count'] = __( 'Order Count', 'optimizations-ace-mc' );
		return $columns;
	}

	/**
	 * Display order count in users table.
	 *
	 * @since 1.0.0
	 * @param string $output Custom column output.
	 * @param string $column_name Name of the column.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public function display_user_order_count_column( string $output, string $column_name, int $user_id ): string {
		if ( 'user_order_count' === $column_name ) {
			$order_count = wc_get_customer_order_count( absint( $user_id ) );
			return esc_html( number_format_i18n( $order_count ) );
		}
		return $output;
	}

	/**
	 * Add store categories to store meta.
	 *
	 * @since 1.0.0
	 * @param array $store_meta Existing store meta.
	 * @param int   $store_id Store ID.
	 * @return array
	 */
	public function add_store_categories_to_meta( array $store_meta, int $store_id ): array {
		$terms               = get_the_terms( absint( $store_id ), 'wpsl_store_category' );
		$store_meta['terms'] = '';

		if ( $terms && ! is_wp_error( $terms ) ) {
			$location_terms = [];
			foreach ( $terms as $term ) {
				if ( ! empty( $term->name ) ) {
					$location_terms[] = esc_html( $term->name );
				}
			}
			$store_meta['terms'] = implode( ', ', $location_terms );
		}

		return $store_meta;
	}

	/**
	 * Customize info window template to include categories.
	 *
	 * The category label defaults to "Certifications:" and can be changed via
	 * the 'optimizations_ace_mc_store_category_label' filter.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function customize_info_window_template(): string {
		/**
		 * Filters the label shown before store categories in the WPSL info window.
		 *
		 * @since 1.0.9
		 * @param string $label The category label. Default 'Certifications:'.
		 */
		$category_label = apply_filters( 'optimizations_ace_mc_store_category_label', __( 'Certifications:', 'optimizations-ace-mc' ) );

		$info_window_template  = '<div data-store-id="<%= id %>" class="wpsl-info-window">' . "\n";
		$info_window_template .= '        <p>' . "\n";
		$info_window_template .= '            ' . wpsl_store_header_template() . "\n";
		$info_window_template .= '            <span><%= address %></span>' . "\n";
		$info_window_template .= '            <% if ( address2 ) { %>' . "\n";
		$info_window_template .= '            <span><%= address2 %></span>' . "\n";
		$info_window_template .= '            <% } %>' . "\n";
		$info_window_template .= '            <span>' . wpsl_address_format_placeholders() . '</span>' . "\n";
		$info_window_template .= '        </p>' . "\n";

		$info_window_template .= '        <% if ( terms ) { %>' . "\n";
		$info_window_template .= '        <p>' . esc_html( $category_label ) . ' <%= terms %></p>' . "\n";
		$info_window_template .= '        <% } %>' . "\n";

		$info_window_template .= '        <%= createInfoWindowActions( id ) %>' . "\n";
		$info_window_template .= '    </div>' . "\n";

		return $info_window_template;
	}

	/**
	 * Disable REST API for WP Store Locator post type.
	 *
	 * @since 1.0.0
	 * @param array $args Post type arguments.
	 * @return array
	 */
	public function custom_post_type_args( array $args ): array {
		$args['show_in_rest'] = false;
		return $args;
	}

	/**
	 * Add registration date column to users table.
	 *
	 * @since 1.0.6
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_user_registration_date_column( array $columns ): array {
		$columns['registration_date'] = __( 'Registration Date', 'optimizations-ace-mc' );
		return $columns;
	}

	/**
	 * Display registration date in users table.
	 *
	 * @since 1.0.6
	 * @param string $output Custom column output.
	 * @param string $column_name Name of the column.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public function display_user_registration_date_column( string $output, string $column_name, int $user_id ): string {
		if ( 'registration_date' === $column_name ) {
			$registration_date = get_the_author_meta( 'registered', absint( $user_id ) );
			if ( $registration_date ) {
				if ( '' === $this->date_format ) {
					$this->date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
				}
				return esc_html( wp_date( $this->date_format, strtotime( $registration_date ) ) );
			}
			return esc_html__( 'Unknown', 'optimizations-ace-mc' );
		}
		return $output;
	}

	/**
	 * Make registration date column sortable.
	 *
	 * @since 1.0.6
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public function make_user_registration_date_sortable( array $columns ): array {
		$columns['registration_date'] = 'registered';
		return $columns;
	}

	/**
	 * Add admin menu.
	 *
	 * @since 1.0.6
	 */
	public function add_admin_menu(): void {
		$this->settings_page_hook = (string) add_options_page(
			__( 'ACE MC Optimizations', 'optimizations-ace-mc' ),
			__( 'ACE MC Optimizations', 'optimizations-ace-mc' ),
			'manage_options',
			'optimizations-ace-mc',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Enqueue admin styles on the plugin settings page only.
	 *
	 * @since 1.0.9
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_admin_styles( string $hook_suffix ): void {
		if ( $hook_suffix !== $this->settings_page_hook ) {
			return;
		}

		wp_enqueue_style(
			'optimizations-ace-mc-admin',
			OPTIMIZATIONS_ACE_MC_PLUGIN_URL . 'assets/css/admin.css',
			[],
			OPTIMIZATIONS_ACE_MC_VERSION
		);
	}

	/**
	 * Initialize settings.
	 *
	 * @since 1.0.6
	 */
	public function init_settings(): void {
		register_setting(
			'optimizations_ace_mc_group',
			'optimizations_ace_mc_settings',
			array( $this, 'sanitize_settings' )
		);

		$this->init_woocommerce_settings();
		$this->init_wpsl_settings();
		$this->init_admin_settings();
	}

	/**
	 * Initialize WooCommerce settings section.
	 *
	 * @since 1.0.6
	 */
	private function init_woocommerce_settings(): void {
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
			[
				'name'        => 'woocommerce_show_empty_categories',
				'description' => __( 'Show empty product categories in WooCommerce category listings.', 'optimizations-ace-mc' ),
			]
		);

		add_settings_field(
			'woocommerce_hide_category_count',
			__( 'Hide Category Product Count', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'woocommerce_section',
			[
				'name'        => 'woocommerce_hide_category_count',
				'description' => __( 'Hide the product count numbers in category listings.', 'optimizations-ace-mc' ),
			]
		);

		add_settings_field(
			'woocommerce_user_order_count_column',
			__( 'User Order Count Column', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'woocommerce_section',
			[
				'name'        => 'woocommerce_user_order_count_column',
				'description' => __( 'Add an order count column to the WordPress Users admin table.', 'optimizations-ace-mc' ),
			]
		);
	}

	/**
	 * Initialize WP Store Locator settings section.
	 *
	 * @since 1.0.6
	 */
	private function init_wpsl_settings(): void {
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
			[
				'name'        => 'wpsl_show_store_categories',
				'description' => __( 'Display store categories in the store locator info windows.', 'optimizations-ace-mc' ),
			]
		);

		add_settings_field(
			'wpsl_disable_rest_api',
			__( 'Disable REST API', 'optimizations-ace-mc' ),
			array( $this, 'checkbox_field_callback' ),
			'optimizations-ace-mc',
			'wpsl_section',
			[
				'name'        => 'wpsl_disable_rest_api',
				'description' => __( 'Disable REST API endpoint for WP Store Locator post type for security.', 'optimizations-ace-mc' ),
			]
		);
	}

	/**
	 * Initialize WordPress admin settings section.
	 *
	 * @since 1.0.6
	 */
	private function init_admin_settings(): void {
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
			[
				'name'        => 'admin_user_registration_date_column',
				'description' => __( 'Add a registration date column to the WordPress Users admin table.', 'optimizations-ace-mc' ),
			]
		);
	}

	/**
	 * Sanitize settings.
	 *
	 * @since 1.0.6
	 * @param array $input Raw input data.
	 * @return array<string, bool> Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = [];

		foreach ( array_keys( $this->default_settings ) as $key ) {
			$sanitized[ $key ] = isset( $input[ $key ] ) && (bool) $input[ $key ];
		}

		return $sanitized;
	}

	/**
	 * WooCommerce section callback.
	 *
	 * @since 1.0.6
	 */
	public function woocommerce_section_callback(): void {
		echo '<p>' . esc_html__( 'Configure WooCommerce-specific optimizations. These features enhance the WooCommerce user experience and admin functionality.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * WP Store Locator section callback.
	 *
	 * @since 1.0.6
	 */
	public function wpsl_section_callback(): void {
		echo '<p>' . esc_html__( 'Configure WP Store Locator optimizations. These features enhance store location functionality and security.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * WordPress Admin section callback.
	 *
	 * @since 1.0.6
	 */
	public function admin_section_callback(): void {
		echo '<p>' . esc_html__( 'Configure WordPress admin interface optimizations. These features enhance the admin user experience.', 'optimizations-ace-mc' ) . '</p>';
	}

	/**
	 * Checkbox field callback.
	 *
	 * @since 1.0.6
	 * @param array $args Field arguments.
	 */
	public function checkbox_field_callback( array $args ): void {
		$name        = $args['name'];
		$description = $args['description'] ?? '';
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
	 *
	 * @since 1.0.6
	 */
	public function settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'optimizations-ace-mc' ) );
		}

		$this->display_admin_notices();
		?>
		<div class="wrap optimizations-ace-mc-wrap">
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
			$this->display_support_info();
			?>
		</div>
		<?php
	}

	/**
	 * Display admin notices.
	 *
	 * WordPress Settings API handles CSRF via settings_fields() nonces.
	 * The 'settings-updated' GET parameter is set by WordPress core after
	 * options.php processes the form — no additional nonce check is needed.
	 *
	 * @since 1.0.6
	 */
	private function display_admin_notices(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress core sets this param after nonce-verified save.
		if ( isset( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully!', 'optimizations-ace-mc' ) . '</p></div>';
		}
	}

	/**
	 * Display plugin information.
	 *
	 * @since 1.0.6
	 */
	private function display_plugin_info(): void {
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Plugin Information:', 'optimizations-ace-mc' ); ?></strong>
				<?php esc_html_e( 'This plugin provides pre-configured optimizations for WooCommerce, WP Store Locator, and WordPress admin interfaces.', 'optimizations-ace-mc' ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Version:', 'optimizations-ace-mc' ); ?></strong> <?php echo esc_html( OPTIMIZATIONS_ACE_MC_VERSION ); ?> |
				<strong><?php esc_html_e( 'WordPress:', 'optimizations-ace-mc' ); ?></strong> <?php esc_html_e( '6.6+ required', 'optimizations-ace-mc' ); ?> |
				<strong><?php esc_html_e( 'PHP:', 'optimizations-ace-mc' ); ?></strong> <?php esc_html_e( '8.1+ required', 'optimizations-ace-mc' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display support information.
	 *
	 * @since 1.0.6
	 */
	private function display_support_info(): void {
		?>
		<div class="optimizations-ace-mc-card">
			<h2><?php esc_html_e( 'Support & Documentation', 'optimizations-ace-mc' ); ?></h2>
			<p>
				<?php esc_html_e( 'For support, bug reports, or feature requests:', 'optimizations-ace-mc' ); ?>
				<a href="https://github.com/EngineScript/optimizations-ace-mc" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Visit the GitHub repository', 'optimizations-ace-mc' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
