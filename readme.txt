=== Optimizations ACE MC ===
Contributors: PDowney
Tags: optimization, performance, wp-optimizer, speed, seo
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A lightweight WordPress optimization plugin with pre-configured performance enhancements.

== Description ==

Optimizations ACE MC is a simple, lightweight WordPress optimization plugin that runs pre-configured performance enhancements to improve your website's speed and efficiency.

= Features =

* Remove WordPress version from head
* Remove unnecessary meta tags
* Remove emoji scripts and styles
* Disable REST API for non-logged-in users
* Remove query strings from static resources

= Requirements =

* WordPress 6.5 or higher
* PHP 7.4 or higher

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/optimizations-ace-mc` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The optimization features will be automatically enabled upon activation.

== Frequently Asked Questions ==

= Do I need to configure anything? =

No, all optimizations are automatically enabled when the plugin is activated. This plugin is designed to be simple and work out of the box.

= Will this plugin slow down my site? =

No, this plugin is designed to improve performance by removing unnecessary code and optimizations that can slow down your site.

= Is this plugin compatible with other optimization plugins? =

Yes, this plugin focuses on basic optimizations and should work alongside other optimization plugins.

== Changelog ==

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
