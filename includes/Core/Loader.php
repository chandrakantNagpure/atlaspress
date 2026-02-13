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
use AtlasPress\Integrations\HubSpot;

class Loader {

    public static function init() {
        ApiSecurity::init();
        Version::init();
        Permissions::init();
        Webhooks::init();
        Network::init();
        RealTime::init();
        Security::init();
        CLI::init();
        
        add_action( 'admin_menu', [ self::class, 'admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_assets' ] );
        add_action( 'rest_api_init', [ ContentTypesController::class, 'register' ] );
        add_action( 'rest_api_init', [ DashboardController::class, 'register' ] );
        add_action( 'rest_api_init', [ EntriesController::class, 'register' ] );
        add_action( 'rest_api_init', [ ImportExportController::class, 'register' ] );
        add_action( 'rest_api_init', [ FileUploadController::class, 'register' ] );
        add_action( 'rest_api_init', [ GraphQLController::class, 'register' ] );
        add_action( 'rest_api_init', [ RelationshipController::class, 'register' ] );
        add_action( 'rest_api_init', [ FormGeneratorController::class, 'register' ] );
        add_action( 'wp_ajax_atlaspress_setup', [ SetupWizard::class, 'handle_setup' ] );
        add_action( 'wp_ajax_atlaspress_reset_setup', [ SetupWizard::class, 'handle_reset' ] );
        add_action( 'wp_ajax_save_security_settings', [ self::class, 'handle_security_settings' ] );
        add_action( 'wp_ajax_atlaspress_save_webhooks', [ self::class, 'handle_save_webhooks' ] );
        
        Integration::init();
        HubSpot::init();
    }

    public static function admin_menu() {
        Menu::register();
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'atlaspress' ) === false ) return;

        $api_keys = get_option('atlaspress_api_keys', []);
        if (!is_array($api_keys)) {
            $api_keys = [];
        }

        wp_enqueue_style('atlaspress-admin', ATLASPRESS_URL.'assets/css/admin.css', [], ATLASPRESS_VERSION);
        
        // Enqueue entries-specific CSS
        if ( strpos( $hook, 'atlaspress-entries' ) !== false ) {
            wp_enqueue_style('atlaspress-entries', ATLASPRESS_URL.'assets/css/entries.css', ['atlaspress-admin'], ATLASPRESS_VERSION);
        }
        
        wp_enqueue_script('atlaspress-admin', ATLASPRESS_URL.'assets/js/admin.js', ['wp-element','wp-api-fetch'], ATLASPRESS_VERSION, true);
        
        // Add ajaxurl for setup wizard
        wp_localize_script('atlaspress-admin', 'atlaspress_ajax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('atlaspress_setup'),
            'security_nonce' => wp_create_nonce('atlaspress_security_settings'),
            'license_nonce' => wp_create_nonce('atlaspress_license_action'),
            'network_nonce' => wp_create_nonce('atlaspress_network_sync'),
            'admin_url' => admin_url()
        ]);
        
        // Add security settings
        wp_localize_script('atlaspress-admin', 'atlaspress_settings', [
            'allowed_origins' => get_option('atlaspress_allowed_origins', []),
            'api_keys' => array_keys($api_keys),
            'api_key_details' => \AtlasPress\Core\ApiSecurity::get_api_key_details(),
            'api_key_scopes' => \AtlasPress\Core\ApiSecurity::get_available_api_key_scopes(),
            'signature_policy' => \AtlasPress\Core\ApiSecurity::get_signature_policy(),
            'rate_limit_rules' => \AtlasPress\Core\Security::get_effective_rate_limit_rules(),
            'is_pro_active' => \AtlasPress\Core\ProVersion::is_pro_active()
        ]);
    }

    public static function handle_security_settings() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        check_ajax_referer('atlaspress_security_settings', 'nonce');

        if (isset($_POST['generate_api_key'])) {
            $key_name = sanitize_text_field(wp_unslash($_POST['api_key_name'] ?? 'Generated Key'));
            $scopes = \AtlasPress\Core\ApiSecurity::sanitize_api_key_scopes(wp_unslash($_POST['api_key_scopes'] ?? []));
            $expires_at = \AtlasPress\Core\ApiSecurity::sanitize_api_key_expiration(wp_unslash($_POST['api_key_expires_at'] ?? ''));
            $allowed_ips = \AtlasPress\Core\ApiSecurity::sanitize_api_key_allowed_ips(wp_unslash($_POST['api_key_allowed_ips'] ?? []));
            $api_key = \AtlasPress\Core\ApiSecurity::generate_api_key($key_name, $scopes, [
                'expires_at' => $expires_at,
                'allowed_ips' => $allowed_ips,
            ]);
            wp_send_json_success([
                'api_key' => $api_key,
                'api_key_details' => \AtlasPress\Core\ApiSecurity::get_api_key_details(),
            ]);
            return;
        }

        if (isset($_POST['api_key_action'])) {
            $api_key_action = sanitize_text_field(wp_unslash($_POST['api_key_action']));
            $key_name = sanitize_text_field(wp_unslash($_POST['api_key_name'] ?? ''));

            if ($key_name === '') {
                wp_send_json_error('Missing API key name');
                return;
            }

            if ($api_key_action === 'rotate') {
                $rotated = \AtlasPress\Core\ApiSecurity::rotate_api_key($key_name);
                if (!$rotated) {
                    wp_send_json_error('Unable to rotate API key');
                    return;
                }

                wp_send_json_success([
                    'message' => 'API key rotated successfully',
                    'api_key' => $rotated['api_key'],
                    'key_name' => $rotated['name'],
                    'api_key_details' => \AtlasPress\Core\ApiSecurity::get_api_key_details(),
                ]);
                return;
            }

            if ($api_key_action === 'disable' || $api_key_action === 'enable') {
                $status = $api_key_action === 'disable' ? 'disabled' : 'active';
                $updated = \AtlasPress\Core\ApiSecurity::set_api_key_status($key_name, $status);
                if (!$updated) {
                    wp_send_json_error('Unable to update API key status');
                    return;
                }

                wp_send_json_success([
                    'message' => $status === 'disabled' ? 'API key disabled' : 'API key enabled',
                    'api_key_details' => \AtlasPress\Core\ApiSecurity::get_api_key_details(),
                ]);
                return;
            }

            if ($api_key_action === 'update_constraints') {
                $expires_at = \AtlasPress\Core\ApiSecurity::sanitize_api_key_expiration(wp_unslash($_POST['api_key_expires_at'] ?? ''));
                $allowed_ips = \AtlasPress\Core\ApiSecurity::sanitize_api_key_allowed_ips(wp_unslash($_POST['api_key_allowed_ips'] ?? []));
                $updated = \AtlasPress\Core\ApiSecurity::update_api_key_constraints($key_name, [
                    'expires_at' => $expires_at,
                    'allowed_ips' => $allowed_ips,
                ]);
                if (!$updated) {
                    wp_send_json_error('Unable to update API key constraints');
                    return;
                }

                wp_send_json_success([
                    'message' => 'API key restrictions updated',
                    'api_key_details' => \AtlasPress\Core\ApiSecurity::get_api_key_details(),
                ]);
                return;
            }

            if ($api_key_action === 'delete') {
                $deleted = \AtlasPress\Core\ApiSecurity::delete_api_key($key_name);
                if (!$deleted) {
                    wp_send_json_error('Unable to delete API key');
                    return;
                }

                wp_send_json_success([
                    'message' => 'API key deleted',
                    'api_key_details' => \AtlasPress\Core\ApiSecurity::get_api_key_details(),
                ]);
                return;
            }

            wp_send_json_error('Invalid API key action');
            return;
        }

        if (
            isset($_POST['allowed_origins'])
            || isset($_POST['require_signed_form_capture'])
            || isset($_POST['require_signed_hubspot_webhook'])
            || isset($_POST['allow_legacy_signed_ingest'])
            || isset($_POST['rate_limit_rules'])
        ) {
            if (isset($_POST['allowed_origins'])) {
                $origins = array_filter(array_map('trim', explode("\n", sanitize_textarea_field($_POST['allowed_origins']))));
                \AtlasPress\Core\ApiSecurity::set_allowed_origins($origins);
            }

            $policy = [];
            if (isset($_POST['require_signed_form_capture'])) {
                $policy['require_signed_form_capture'] = sanitize_text_field(wp_unslash($_POST['require_signed_form_capture']));
            }

            if (isset($_POST['require_signed_hubspot_webhook'])) {
                $policy['require_signed_hubspot_webhook'] = sanitize_text_field(wp_unslash($_POST['require_signed_hubspot_webhook']));
            }

            if (isset($_POST['allow_legacy_signed_ingest'])) {
                $policy['allow_legacy_signed_ingest'] = sanitize_text_field(wp_unslash($_POST['allow_legacy_signed_ingest']));
            }

            $updated_policy = \AtlasPress\Core\ApiSecurity::set_signature_policy($policy);

            if (isset($_POST['rate_limit_rules'])) {
                $raw_rules = wp_unslash($_POST['rate_limit_rules']);
                $updated_rules = \AtlasPress\Core\Security::set_rate_limit_rules($raw_rules);
                if ($updated_rules === false) {
                    wp_send_json_error('Custom rate limit rules require an active Pro license');
                    return;
                }
            }

            wp_send_json_success([
                'message' => 'Settings saved successfully',
                'settings' => [
                    'allowed_origins' => get_option('atlaspress_allowed_origins', []),
                    'signature_policy' => $updated_policy,
                    'rate_limit_rules' => \AtlasPress\Core\Security::get_effective_rate_limit_rules(),
                    'is_pro_active' => \AtlasPress\Core\ProVersion::is_pro_active()
                ]
            ]);
            return;
        }

        wp_send_json_error('Invalid request');
    }
    
    public static function handle_save_webhooks() {
        check_ajax_referer('atlaspress_webhooks', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        $webhooks = json_decode(stripslashes($_POST['webhooks']), true);
        update_option('atlaspress_webhooks', $webhooks);
        wp_send_json_success(['message' => 'Webhooks saved']);
    }
}
