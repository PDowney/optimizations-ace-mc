=== Optimizations ACE MC ===
Contributors: PDowney
Tags: optimization, performance, wp-optimizer, speed, seo
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.7
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A lightweight WordPress optimization plugin with pre-configured performance enhancements.

== Description ==

Optimizations ACE MC is a comprehensive WordPress optimization plugin that provides user-configurable performance enhancements for WooCommerce, WP Store Locator, and WordPress admin interfaces.

= Features =

**WooCommerce Optimizations:**
* Show empty product categories in archives (configurable)
* Hide category product count in product archives (configurable)
* Add user order count column to admin users table with sorting (configurable)

**WP Store Locator Optimizations:**
* Display store categories in store info windows (configurable)
* Disable REST API for store locator post type for enhanced security (configurable)

**WordPress Admin Optimizations:**
* Add user registration date column to admin users table with sorting (configurable)

**Settings Management:**
* Comprehensive admin settings page for all optimizations
* Individual enable/disable controls for each feature
* Plugin dependency status indicators
* User-friendly interface with clear descriptions

= Requirements =

* WordPress 6.5 or higher
* PHP 7.4 or higher
* WooCommerce (for WooCommerce-specific features)
* WP Store Locator (for store locator features)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/optimizations-ace-mc` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Settings > Optimizations ACE MC** to configure which optimization features you want to enable.
4. Enable or disable individual optimizations based on your site's needs and available plugins.

== Frequently Asked Questions ==

= Do I need to configure anything? =

The plugin comes with default settings enabled, but you can customize which optimizations to use via the **Settings > Optimizations ACE MC** admin page. Each feature can be individually enabled or disabled.

= What happens if I don't have WooCommerce or WP Store Locator installed? =

The plugin will detect which plugins are available and only show relevant optimization options. Features for missing plugins will be clearly marked as inactive and won't affect your site.

= Will this plugin slow down my site? =

No, this plugin is designed to improve performance by adding useful admin enhancements and optimizations. All features are optional and can be disabled if not needed.

= Is this plugin compatible with other optimization plugins? =

Yes, this plugin focuses on specific admin and functionality enhancements rather than general optimization, so it should work alongside other optimization plugins.

== Changelog ==

= 1.0.7 - 2025-09-15 =
* Added: AI-powered code analysis workflow using Google Gemini for automated security scanning
* Added: Comprehensive WordPress coding standards compliance checking in CI/CD
* Added: Performance analysis for database queries and resource optimization
* Added: Pull request and push event analysis with detailed security feedback
* Enhanced: Dynamic workflow handling for both PR and push events with unified output
* Enhanced: Real-time code diff analysis with focus on security implications
* Security: Environment variable protection against command injection vulnerabilities
* Security: Secure API key management through GitHub repository secrets
* Fixed: Resolved 404 errors when posting PR comments on push events
* Fixed: JavaScript syntax errors and YAML parsing issues in workflow scripts
* Fixed: Improved workflow reliability and comprehensive error handling

= 1.0.6 - 2025-08-22 =
* Added: Comprehensive admin settings page for managing all plugin optimizations
* Added: User-configurable options for WooCommerce, WP Store Locator, and WordPress admin features  
* Added: Individual enable/disable controls for each optimization feature
* Added: Plugin dependency status indicators with visual feedback
* Security: Fixed improper nonce verification in settings page form submission
* Security: Enhanced CSRF protection using proper WordPress Settings API handling
* Changed: All optimization features are now optional and user-configurable
* Changed: Features load conditionally based on user settings
* Fixed: Array alignment issues to meet WordPress coding standards
* Fixed: Removed unused variables and refactored long methods for better code quality
* Fixed: Converted entire codebase to WordPress-standard tab indentation
* Fixed: Updated PHPCS configuration to properly allow tabs for indentation per WordPress standards

= 1.0.5 - 2025-08-11 =
* Security: Fixed critical code injection vulnerabilities in GitHub Actions workflows
* Security: Implemented secure environment variable usage pattern for AI workflows
* Added: Comprehensive AI-powered workflow suite with Gemini integration
* Added: Automated code review and security scanning with AI assistance
* Added: Interactive Gemini assistant for code help and issue management
* Fixed: YAML corruption issues and duplicate environment variable definitions

= 1.0.4 - 2025-08-02 =
* Removed: All fallback methods and plugin availability checks for single-site deployment
* Removed: WooCommerce and WP Store Locator activation checks since plugins are guaranteed to be active
* Simplified: Direct function calls without existence validation for better performance
* Optimized: Code structure for dedicated single-site environment

= 1.0.3 - 2025-07-25 =
* Fixed: Code standards compliance - string concatenation issues
* Fixed: Variable alignment to follow WordPress coding standards
* Fixed: Line ending characters from CRLF to LF for consistency
* Fixed: PHPStan errors by updating function return types and class references
* Changed: Moved main class to proper WordPress naming convention file structure
* Added: Enhanced PHPStan configuration and development tools

= 1.0.2 - 2025-07-16 =
* Enhanced WordPress compatibility testing
* Improved automated code quality checks
* Added comprehensive CI/CD workflow
* Updated documentation and security guidelines

= 1.0.1 =
* Fixed PHPStan compatibility issues with WP Store Locator functions
* Improved PHPMD configuration for WordPress coding standards
* Enhanced error handling for missing plugin dependencies
* Updated singleton pattern to avoid static analysis warnings
* Fixed WP_Term property access validation

= 1.0.0 =
* Initial release
* Core optimization functions
* WordPress performance enhancements
* Language support
