<?php
/**
 * Plugin Name: Optimizations ACE MC
 * Plugin URI: https://github.com/PDowney/optimizations-ace-mc
 * Description: A lightweight WordPress optimization plugin with pre-configured performance enhancements for WooCommerce, WP Store Locator, and WordPress admin.
 * Version: 1.0.8
 * Author: PDowney
 * Author URI: https://github.com/PDowney
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: optimizations-ace-mc
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 6.9
 * Requires PHP: 7.4
 *
 * @package OptimizationsAceMc
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants.
define( 'OPTIMIZATIONS_ACE_MC_VERSION', '1.0.8' );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_FILE', __FILE__ );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OPTIMIZATIONS_ACE_MC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include the main class.
require_once OPTIMIZATIONS_ACE_MC_PLUGIN_DIR . 'class-optimizations-ace-mc.php';

/**
 * Initialize the plugin.
 *
 * @return Optimizations_Ace_Mc
 */
function optimizations_ace_mc() {
    return Optimizations_Ace_Mc::instance();
}

// Start the plugin once all other plugins are loaded.
add_action( 'plugins_loaded', 'optimizations_ace_mc' );
