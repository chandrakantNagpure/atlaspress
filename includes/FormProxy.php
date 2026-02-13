<?php
namespace AtlasPress;

use AtlasPress\Core\ApiSecurity;
use WP_Error;
use WP_REST_Request;

class FormProxy {
    
    public static function init() {
        add_action('wp_enqueue_scripts', [__CLASS__, 'inject_proxy_script']);
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    public static function inject_proxy_script() {
        wp_enqueue_script('atlaspress-form-proxy', 
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/form-proxy.js', 
            [], '1.0', true
        );
        wp_localize_script('atlaspress-form-proxy', 'atlaspressProxy', [
            'apiUrl' => rest_url('atlaspress/v1/form-capture'),
            'enabled' => get_option('atlaspress_proxy_enabled', '1')
        ]);
    }
    
    public static function register_routes() {
        register_rest_route('atlaspress/v1', '/form-capture', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'capture_submission'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    public static function capture_submission(WP_REST_Request $request) {
        global $wpdb;

        $auth = self::authorize_request($request);
        if (is_wp_error($auth)) {
            return $auth;
        }
        
        $data = $request->get_json_params();
        if (!is_array($data)) {
            return new WP_Error('invalid_data', 'Invalid request payload', ['status' => 400]);
        }

        $formType = $data['_formType'] ?? 'unknown';
        $formId = $data['_formId'] ?? 'unknown';
        $url = $data['_pageUrl'] ?? '';
        
        unset($data['_formType'], $data['_formId'], $data['_pageUrl']);
        
        // Find or create content type
        $contentType = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}atlaspress_content_types WHERE slug = %s",
            sanitize_title($formType . '-' . $formId)
        ));
        
        if (!$contentType) {
            $schema = self::generate_schema($data);
            $settings = json_encode(['fields' => $schema]);
            
            $wpdb->insert("{$wpdb->prefix}atlaspress_content_types", [
                'name' => ucfirst($formType) . ' Form',
                'slug' => sanitize_title($formType . '-' . $formId),
                'settings' => $settings,
                'created_at' => current_time('mysql')
            ]);
            
            $contentTypeId = $wpdb->insert_id;
        } else {
            $contentTypeId = $contentType->id;
        }
        
        // Save entry
        $wpdb->insert("{$wpdb->prefix}atlaspress_entries", [
            'content_type_id' => $contentTypeId,
            'title' => 'Form Submission - ' . date('Y-m-d H:i:s'),
            'data' => json_encode($data),
            'status' => 'published',
            'created_at' => current_time('mysql')
        ]);
        
        return ['success' => true, 'entry_id' => $wpdb->insert_id];
    }
    
    private static function generate_schema($data) {
        $schema = [];
        foreach ($data as $key => $value) {
            $type = 'text';
            if (filter_var($value, FILTER_VALIDATE_EMAIL)) $type = 'email';
            elseif (filter_var($value, FILTER_VALIDATE_URL)) $type = 'url';
            elseif (is_numeric($value)) $type = 'number';
            elseif (strlen($value) > 100) $type = 'textarea';
            
            $schema[] = ['name' => sanitize_key($key), 'type' => $type, 'validation' => []];
        }
        return $schema;
    }

    private static function authorize_request(WP_REST_Request $request) {
        $policy = ApiSecurity::get_signature_policy();
        $require_signed_default = !empty($policy['require_signed_form_capture']);
        $allow_legacy_default = !empty($policy['allow_legacy_signed_ingest']);

        $require_signed = (bool) apply_filters('atlaspress_require_signed_form_capture', $require_signed_default, $request);
        $allow_legacy = (bool) apply_filters('atlaspress_allow_legacy_signed_ingest', $allow_legacy_default, $request);

        $secret = (string) get_option('atlaspress_form_capture_secret', '');
        if (trim($secret) === '') {
            $secret = (string) get_option('atlaspress_webhook_secret', '');
        }

        if (trim($secret) === '') {
            if ($require_signed) {
                return new WP_Error('secret_not_configured', 'Form capture secret is not configured for signed request verification', ['status' => 503]);
            }
            return true;
        }

        if ($require_signed || ApiSecurity::has_signature_headers($request)) {
            $verification = ApiSecurity::verify_signed_request($request, $secret, [
                'require_nonce' => true,
                'allow_legacy_signatures' => $allow_legacy,
                'replay_context' => 'form_capture',
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
            return new WP_Error('forbidden', 'Invalid capture secret', ['status' => 403]);
        }

        return true;
    }
}
