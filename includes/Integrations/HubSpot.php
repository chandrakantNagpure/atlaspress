<?php
namespace AtlasPress\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
    
    public static function handle_webhook($request) {
        global $wpdb;
        
        $data = $request->get_json_params();
        
        if (empty($data)) {
            return new \WP_Error('invalid_data', 'No data received', ['status' => 400]);
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
}
