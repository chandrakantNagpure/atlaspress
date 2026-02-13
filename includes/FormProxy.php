<?php
namespace AtlasPress;

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
    
    public static function capture_submission($request) {
        global $wpdb;
        
        $data = $request->get_json_params();
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
            
            $schema[] = ['name' => $key, 'type' => $type, 'validation' => []];
        }
        return $schema;
    }
}
