<?php
namespace AtlasPress\Integrations;

use AtlasPress\Core\ApiSecurity;
use WP_Error;
use WP_REST_Request;

class HubSpot {
    
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    public static function register_routes() {
        register_rest_route('atlaspress/v1', '/hubspot/webhook', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'handle_webhook'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    public static function handle_webhook(WP_REST_Request $request) {
        global $wpdb;

        $auth = self::authorize_request($request);
        if (is_wp_error($auth)) {
            return $auth;
        }
        
        $data = $request->get_json_params();
        
        if (empty($data)) {
            return new WP_Error('invalid_data', 'No data received', ['status' => 400]);
        }
        
        $formData = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, ['submittedAt', 'pageUrl', 'pageName'])) {
                $formData[$key] = $value;
            }
        }
        
        $contentTypeId = get_option('atlaspress_hubspot_content_type');
        
        if (!$contentTypeId) {
            $wpdb->insert($wpdb->prefix . 'atlaspress_content_types', [
                'name' => 'HubSpot Forms',
                'slug' => 'hubspot-forms',
                'settings' => json_encode(['fields' => []]),
                'created_at' => current_time('mysql')
            ]);
            $contentTypeId = $wpdb->insert_id;
            update_option('atlaspress_hubspot_content_type', $contentTypeId);
        }
        
        $wpdb->insert($wpdb->prefix . 'atlaspress_entries', [
            'content_type_id' => $contentTypeId,
            'title' => 'HubSpot Form - ' . date('Y-m-d H:i:s'),
            'slug' => 'hubspot-' . time(),
            'data' => json_encode($formData),
            'status' => 'published',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);
        
        return ['success' => true, 'entry_id' => $wpdb->insert_id];
    }

    private static function authorize_request(WP_REST_Request $request) {
        $policy = ApiSecurity::get_signature_policy();
        $require_signed_default = !empty($policy['require_signed_hubspot_webhook']);
        $allow_legacy_default = !empty($policy['allow_legacy_signed_ingest']);

        $require_signed = (bool) apply_filters('atlaspress_require_signed_hubspot_webhook', $require_signed_default, $request);
        $allow_legacy = (bool) apply_filters('atlaspress_allow_legacy_signed_ingest', $allow_legacy_default, $request);

        $secret = (string) get_option('atlaspress_hubspot_secret', '');
        if (trim($secret) === '') {
            $secret = (string) get_option('atlaspress_webhook_secret', '');
        }

        if (trim($secret) === '') {
            if ($require_signed) {
                return new WP_Error('secret_not_configured', 'HubSpot webhook secret is not configured for signed request verification', ['status' => 503]);
            }
            return true;
        }

        if ($require_signed || ApiSecurity::has_signature_headers($request)) {
            $verification = ApiSecurity::verify_signed_request($request, $secret, [
                'require_nonce' => true,
                'allow_legacy_signatures' => $allow_legacy,
                'replay_context' => 'hubspot_webhook',
            ]);

            if (is_wp_error($verification)) {
                return $verification;
            }

            return true;
        }

        $provided = $request->get_header('X-AtlasPress-Secret');
        if (!is_string($provided) || $provided === '') {
            $provided = $request->get_param('secret');
        }

        if (!is_string($provided) || !hash_equals($secret, $provided)) {
            return new WP_Error('forbidden', 'Invalid HubSpot webhook secret', ['status' => 403]);
        }

        return true;
    }
}
