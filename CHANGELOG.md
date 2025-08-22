# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Settings Page:**
  - Comprehensive admin settings page for managing all plugin optimizations
  - User-configurable options for WooCommerce, WP Store Locator, and WordPress admin features
  - Individual enable/disable controls for each optimization feature
  - Plugin dependency status indicators with visual feedback
  - Responsive admin interface with proper WordPress styling
  - Full internationalization support for all settings text

### Security
- **Settings Security:**
  - Fixed improper nonce verification in settings page form submission
  - Implemented proper WordPress Settings API nonce handling
  - Enhanced CSRF protection by removing manual nonce checks in favor of WordPress core handling
  - Maintained proper capability checks (manage_options) for admin access
  - All form inputs sanitized using WordPress Settings API sanitization callbacks

### Changed
- **Plugin Architecture:**
  - All optimization features are now optional and user-configurable
  - Features load conditionally based on user settings instead of being always active
  - Improved plugin structure with centralized settings management
  - Enhanced user experience with clear feature descriptions and dependency information

## [1.0.5] - 2025-08-11

### Security
- **GitHub Actions Security:**
  - Fixed critical code injection vulnerabilities in all AI-powered workflows
  - Implemented secure environment variable usage pattern to prevent script injection attacks
  - Applied two-stage security pattern for handling untrusted step outputs
  - Secured workflows: ai-pr-comment.yml, gemini-security-scan.yml, wordpress-standards-check.yml

### Added
- **AI-Powered Workflows:**
  - Comprehensive GitHub Actions workflow suite with Gemini AI integration
  - Automated code review and security scanning with AI assistance
  - Pull request analysis and automated commenting system
  - WordPress coding standards validation with AI recommendations
  - Gemini assistant for interactive code help and issue management

### Fixed
- **Workflow Integrity:**
  - Resolved YAML corruption issues in GitHub Actions workflows
  - Fixed duplicate environment variable definitions
  - Corrected workflow syntax and structure for proper execution

## [1.0.4] - 2025-08-02

### Removed
- **Plugin Checks:**
  - Removed WooCommerce activation check (`is_woocommerce_active()` method)
  - Removed WP Store Locator activation check (`is_wpsl_active()` method)
  - Removed `function_exists('wc_get_customer_order_count')` fallback check

### Changed
- **Performance:**
  - Optimized for single-site deployment where WooCommerce and WP Store Locator are guaranteed to be active
  - Simplified code structure by removing unnecessary plugin availability validations
  - Direct function calls without existence checks for better performance

## [1.0.3] - 2025-07-25

### Fixed
- **Code Standards:**
  - Fixed string concatenation issues by removing unnecessary concatenation operators
  - Corrected variable alignment to follow WordPress coding standards
  - Fixed line ending characters from CRLF to LF for consistency
  - Resolved PHPStan errors by updating function return types and class references
  - Added proper bootstrap configuration for PHPStan analysis

### Changed
- **File Structure:**
  - Moved main class to `class-optimizations-ace-mc.php` following WordPress naming conventions
  - Updated main plugin file to include class file properly
  - Improved code organization and maintainability

### Added
- **Development Tools:**
  - Added `.distignore` file for proper WordPress.org deployment
  - Enhanced PHPStan configuration with proper bootstrap files
  - Improved workflow compatibility testing

## [1.0.2] - 2025-07-16

### Fixed
- **Coding Standards:**
  - Corrected various PHPCS coding standards violations, including alignment and quote usage.
  - Fixed an XML syntax error in the `phpcs.xml` ruleset file.

## [1.0.1] - 2025-07-10

### Fixed
- **PHPStan compatibility:**
  - Added function_exists() checks for WP Store Locator functions to prevent errors when plugin is not installed
  - Fixed WP_Term property access using !empty() instead of isset() to satisfy PHPStan analysis
  - Added PHPStan ignore rules for WP Store Locator functions in configuration
- **PHPMD configuration:**
  - Updated PHPMD ruleset to properly exclude WordPress naming conventions (snake_case)
  - Fixed camelCase naming rule conflicts with WordPress coding standards
- **Code quality improvements:**
  - Improved error handling for missing plugin dependencies
  - Enhanced static analysis compliance

### Changed
- Updated singleton pattern implementation to avoid PHPStan static access warnings
- Improved text domain consistency throughout the codebase

### Added
- Initial plugin structure
- Basic WordPress optimization framework
- Support for WordPress 6.5+
- Support for PHP 7.4+
- Internationalization support
- Security checks and validation
- **WooCommerce optimizations:**
  - Show empty product categories in archives
  - Hide category product count in product archives
  - User order count column in admin users table (sortable)
- **WP Store Locator optimizations:**
  - Display store categories in store info windows
  - Disable REST API for store locator post type
  - Custom info window template with certifications
- **WordPress admin optimizations:**
  - User registration date column in admin users table (sortable)

### Changed
- Updated plugin to use WordPress 6.8 compatibility
- Fixed text domain to match plugin slug format
- Improved singleton pattern implementation
- Updated PHPMD configuration for WordPress coding standards
- Standardized text domain to 'optimizations-ace-mc' (lowercase, hyphenated) throughout codebase

### Fixed
- Text domain mismatch (now uses 'optimizations-ace-mc' consistently)
- PHPStan type checking issues with singleton pattern
- Removed invalid 'Network' header from plugin file
- WordPress compatibility testing up to version 6.8
- PHPMD warnings for WordPress naming conventions
- **Security improvements:**
  - Added proper capability checks for admin modifications
  - Added WooCommerce and WP Store Locator dependency checks
  - Proper data sanitization and escaping for all output
  - Fixed function name collision in user column sorting
  - Added input validation with `absint()` for user IDs

### Security
- All user inputs are properly sanitized and validated
- Capability checks ensure only authorized users can access admin features
- Plugin dependencies are verified before executing related functionality
- All output is properly escaped to prevent XSS attacks
