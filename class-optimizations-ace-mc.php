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
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Initialize optimizations.
        $this->init_optimizations();
    }

    /**
     * Load plugin textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'optimizations-ace-mc',
            false,
            dirname( OPTIMIZATIONS_ACE_MC_PLUGIN_BASENAME ) . '/languages/'
        );
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
        add_filter( 'woocommerce_product_subcategories_hide_empty', '__return_false' );

        // Hide category product count in product archives.
        add_filter( 'woocommerce_subcategory_count_html', '__return_false' );

        // Add order count column to users admin.
        if ( is_admin() && current_user_can( 'list_users' ) ) {
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
        add_filter( 'wpsl_store_meta', array( $this, 'add_store_categories_to_meta' ), 10, 2 );
        add_filter( 'wpsl_info_window_template', array( $this, 'customize_info_window_template' ) );

        // Disable REST API for store locator post type.
        add_filter( 'wpsl_post_type_args', array( $this, 'custom_post_type_args' ) );
    }

    /**
     * Initialize WordPress admin optimizations.
     */
    private function init_admin_optimizations() {
        if ( ! is_admin() || ! current_user_can( 'list_users' ) ) {
            return;
        }

        // Add registration date column to users admin.
        add_filter( 'manage_users_columns', array( $this, 'add_user_registration_date_column' ) );
        add_filter( 'manage_users_custom_column', array( $this, 'display_user_registration_date_column' ), 10, 3 );
        add_filter( 'manage_users_sortable_columns', array( $this, 'make_user_registration_date_sortable' ) );
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
}
