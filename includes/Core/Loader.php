<?php
namespace AtlasPress\Core;

use AtlasPress\Admin\Menu;
use AtlasPress\Rest\ContentTypesController;
use AtlasPress\Rest\DashboardController;
use AtlasPress\Rest\EntriesController;
use AtlasPress\Rest\ImportExportController;
use AtlasPress\Rest\FileUploadController;
use AtlasPress\Rest\GraphQLController;
use AtlasPress\Rest\RelationshipController;
use AtlasPress\Rest\FormGeneratorController;
use AtlasPress\Admin\Pages\SetupWizard;
use AtlasPress\Admin\Pages\Integration;

class Loader
{

    public static function init()
    {
        ApiSecurity::init();
        Version::init();
        Permissions::init();
        Webhooks::init();
        Network::init();
        RealTime::init();
        Security::init();
        CLI::init();

        add_action('admin_menu', [self::class, 'admin_menu']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_action('rest_api_init', [ContentTypesController::class, 'register']);
        add_action('rest_api_init', [DashboardController::class, 'register']);
        add_action('rest_api_init', [EntriesController::class, 'register']);
        add_action('rest_api_init', [ImportExportController::class, 'register']);
        add_action('rest_api_init', [FileUploadController::class, 'register']);
        add_action('rest_api_init', [GraphQLController::class, 'register']);
        add_action('rest_api_init', [RelationshipController::class, 'register']);
        add_action('rest_api_init', [FormGeneratorController::class, 'register']);
        add_action('wp_ajax_atlaspress_setup', [SetupWizard::class, 'handle_setup']);
        add_action('wp_ajax_atlaspress_reset_setup', [SetupWizard::class, 'handle_reset']);
        add_action('wp_ajax_save_security_settings', [self::class, 'handle_security_settings']);
        add_action('wp_ajax_atlaspress_save_webhooks', [self::class, 'handle_save_webhooks']);
        add_action('wp_ajax_atlaspress_save_cors', [self::class, 'handle_save_cors']);

        Integration::init();
    }

    public static function admin_menu()
    {
        Menu::register();
    }

    public static function enqueue_assets($hook)
    {
        if (strpos($hook, 'atlaspress') === false)
            return;

        wp_enqueue_style('atlaspress-admin', ATLASPRESS_URL . 'assets/css/admin.css', [], ATLASPRESS_VERSION);

        // Enqueue entries-specific CSS
        if (strpos($hook, 'atlaspress-entries') !== false) {
            wp_enqueue_style('atlaspress-entries', ATLASPRESS_URL . 'assets/css/entries.css', ['atlaspress-admin'], ATLASPRESS_VERSION);
        }

        wp_enqueue_script('atlaspress-admin', ATLASPRESS_URL . 'assets/js/admin.js', ['wp-element', 'wp-api-fetch'], ATLASPRESS_VERSION, true);

        // Add ajaxurl for setup wizard
        wp_localize_script('atlaspress-admin', 'atlaspress_ajax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('atlaspress_setup'),
            'admin_url' => admin_url(),
            'plugin_url' => ATLASPRESS_URL
        ]);

        // Add security settings
        wp_localize_script('atlaspress-admin', 'atlaspress_settings', [
            'allowed_origins' => get_option('atlaspress_allowed_origins', []),
            'api_keys' => array_keys(get_option('atlaspress_api_keys', []))
        ]);
    }

    public static function handle_security_settings()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        if (isset($_POST['generate_api_key'])) {
            $key_name = sanitize_text_field($_POST['api_key_name'] ?? 'Generated Key');
            $api_key = \AtlasPress\Core\ApiSecurity::generate_api_key($key_name);
            wp_send_json_success(['api_key' => $api_key]);
            return;
        }

        if (isset($_POST['allowed_origins'])) {
            $origins = array_filter(array_map('trim', explode("\n", sanitize_textarea_field($_POST['allowed_origins']))));
            \AtlasPress\Core\ApiSecurity::set_allowed_origins($origins);
            wp_send_json_success(['message' => 'Settings saved successfully']);
            return;
        }

        wp_send_json_error('Invalid request');
    }

    public static function handle_save_webhooks()
    {
        check_ajax_referer('atlaspress_webhooks', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $webhooks = json_decode(stripslashes($_POST['webhooks']), true);
        update_option('atlaspress_webhooks', $webhooks);
        wp_send_json_success(['message' => 'Webhooks saved']);
    }

    public static function handle_save_cors()
    {
        check_ajax_referer('atlaspress_cors', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $origins = array_filter(array_map('trim', explode("\n", sanitize_textarea_field($_POST['allowed_origins']))));
        \AtlasPress\Core\ApiSecurity::set_allowed_origins($origins);
        wp_send_json_success(['message' => 'CORS settings saved']);
    }
}
