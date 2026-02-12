<?php
/**
 * Plugin Name: AtlasPress
 * Description: A schema-driven backend and middleware layer for modern WordPress applications.
 * Version: 1.0.0
 * Author: Chandrakant
 * Text Domain: atlaspress
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: true
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'ATLASPRESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ATLASPRESS_URL', plugin_dir_url( __FILE__ ) );
define( 'ATLASPRESS_VERSION', '1.0.0' );
define( 'ATLASPRESS_DB_VERSION', '1.0.0' );

// Disable REST API rate limiting for AtlasPress
add_filter('rest_request_before_callbacks', function($response, $handler, $request) {
    if (strpos($request->get_route(), '/atlaspress/') !== false) {
        remove_filter('rest_post_dispatch', 'rest_send_allow_header', 10);
    }
    return $response;
}, 10, 3);

add_filter('rest_authentication_errors', function($result) {
    if (!empty($result)) {
        return $result;
    }
    if (is_user_logged_in()) {
        return true;
    }
    return $result;
});

// Load autoloader FIRST
require_once ATLASPRESS_PATH . 'includes/Core/Autoloader.php';
AtlasPress\Core\Autoloader::register();

// Now everything else can load automatically
require_once ATLASPRESS_PATH . 'includes/Core/Installer.php';
require_once ATLASPRESS_PATH . 'includes/Core/Loader.php';
require_once ATLASPRESS_PATH . 'includes/Core/Version.php';

register_activation_hook( __FILE__, function () {
    AtlasPress\Core\Installer::install();
    update_option('atlaspress_install_date', current_time('mysql'));
});

register_deactivation_hook( __FILE__, function () {
    // Clean up temporary data
    AtlasPress\Core\Cache::flush();
});

AtlasPress\Core\Version::init();
AtlasPress\Core\Loader::init();
AtlasPress\Core\ProVersion::init();
