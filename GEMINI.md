# Project-specific instructions for Gemini AI
# This file provides context about the Optimizations ACE MC WordPress plugin
# to help Gemini understand the codebase and provide better analysis

# Optimizations ACE MC - WordPress Plugin

## Project Overview
This is a lightweight WordPress optimization plugin designed specifically for a single-site deployment with guaranteed WooCommerce and WP Store Locator plugin availability.

## Plugin Details
- **Name:** Optimizations ACE MC
- **Version:** Latest
- **WordPress Compatibility:** 6.5+
- **PHP Compatibility:** 7.4+
- **License:** GPL-3.0-or-later
- **Text Domain:** optimizations-ace-mc

## Architecture & Design Patterns

### Singleton Pattern
The main plugin class uses the singleton pattern to ensure only one instance exists:
```php
protected static $instance = null;
public static function instance() {
    if ( null === self::$instance ) {
        self::$instance = new self();
    }
    return self::$instance;
}
```

### File Structure
- `optimizations-ace-mc.php` - Main plugin bootstrap file
- `class-optimizations-ace-mc.php` - Core plugin functionality
- `languages/` - Translation files
- `.github/workflows/` - CI/CD automation

## WordPress Coding Standards

### Naming Conventions
- **Functions:** `snake_case` (WordPress standard)
- **Classes:** `PascalCase_With_Underscores` (WordPress standard)
- **Variables:** `$snake_case`
- **Constants:** `UPPER_SNAKE_CASE`
- **Files:** `lowercase-with-hyphens.php`

### Security Requirements
- Always use `esc_html()`, `esc_attr()`, `esc_url()` for output
- Sanitize input with `sanitize_text_field()`, `absint()`, etc.
- Use `current_user_can()` for capability checks
- Implement proper nonce verification for forms
- Use WordPress database functions, never raw SQL

### WordPress Integration
- **Hooks:** Prefer actions and filters over direct function calls
- **Database:** Use WordPress functions (`get_option`, `get_post_meta`, etc.)
- **Internationalization:** All strings must use `__()` or `esc_html__()`
- **Text Domain:** Always use `'optimizations-ace-mc'`

## Plugin-Specific Context

### Single-Site Deployment
This plugin is designed for a dedicated environment where:
- WooCommerce is guaranteed to be active (no activation checks needed)
- WP Store Locator is guaranteed to be active (no activation checks needed)
- Performance optimizations can be more aggressive
- Error handling can assume plugin dependencies exist

### Core Functionality

#### WooCommerce Optimizations
- Show empty product categories
- Hide category product counts
- Add order count column to admin users list
- Admin-only functionality with proper capability checks

#### WP Store Locator Optimizations  
- Add store categories to location metadata
- Customize info window templates
- Disable REST API for store post types
- Category display in map popups

#### WordPress Admin Enhancements
- Registration date column in users list
- Sortable custom columns
- Admin-only features with security checks

### Performance Considerations
- Database queries should be cached where possible
- WooCommerce order count queries run per user (consider caching)
- Store category queries run per location (consider caching)
- All admin features properly scoped to admin area only

### Security Focus Areas
- Input sanitization for all user data
- Output escaping for all dynamic content
- Proper capability checks for admin functionality
- WordPress nonce usage for state-changing operations

## Development Standards

### Error Handling
- Graceful degradation when dependencies missing
- Proper validation of function parameters
- No PHP errors or warnings in production
- Meaningful error messages for debugging

### Documentation
- Complete PHPDoc blocks for all methods
- Inline comments for complex logic
- README files for repository information
- Changelog maintenance with semantic versioning

### Testing & Quality Assurance
- PHPStan static analysis (Level 5)
- PHPCS WordPress coding standards
- PHPMD mess detection
- Multi-PHP version compatibility (7.4, 8.0, 8.3, 8.4)
- WordPress Plugin Check compliance

## When Reviewing Code

### Critical Issues to Flag
1. **Security vulnerabilities** (SQL injection, XSS, CSRF)
2. **WordPress standard violations**
3. **Performance bottlenecks** (uncached database queries)
4. **Missing input validation or output escaping**
5. **Capability check bypasses**

### Positive Patterns to Recognize
1. Proper use of WordPress APIs
2. Consistent error handling
3. Performance optimizations
4. Security best practices
5. Clear documentation

### Suggestions to Provide
1. WordPress-specific solutions over generic PHP
2. Performance improvements through caching
3. Security enhancements
4. Code readability improvements
5. Documentation updates

Remember: This plugin prioritizes security, performance, and WordPress ecosystem compatibility.
