<?php
/**
 * Plugin Name: AtlasPress
 * Plugin URI: https://github.com/chandrakantNagpure/atlaspress
 * Description: AtlasPress turns WordPress into a structured data backend.
 * Define schemas, create custom content types and fields, manage data in admin, expose APIs, capture form submissions, and integrate with modern stacks using REST, GraphQL, and webhooks.
 * Version: 1.0.0
 * Author: Chandrakant
 * Author URI: https://github.com/chandrakantNagpure
 * Text Domain: atlaspress
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ATLASPRESS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ATLASPRESS_URL', plugin_dir_url( __FILE__ ) );
define( 'ATLASPRESS_VERSION', '1.0.0' );
define( 'ATLASPRESS_DB_VERSION', '1.0.0' );

add_action(
    'plugins_loaded',
    static function () {
        load_plugin_textdomain(
            'atlaspress',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }
);

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
AtlasPress\FormProxy::init();
