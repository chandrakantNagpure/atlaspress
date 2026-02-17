<?php
namespace AtlasPress\Rest;

class FormGeneratorController {
    
    public static function register() {
        $controller = new self();
        $controller->register_routes();
    }
    
    public function register_routes() {
        register_rest_route('atlaspress/v1', '/entries/poll', [
            'methods' => 'GET',
            'callback' => [$this, 'poll_entries'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    public function poll_entries($request) {
        global $wpdb;
        $lastCheck = $request->get_param('last_check') ?: gmdate('Y-m-d H:i:s', strtotime('-1 minute'));
        
        $newEntries = $wpdb->get_results($wpdb->prepare(
            "SELECT e.id, e.title, e.created_at, ct.name as content_type_name, ct.id as content_type_id 
            FROM {$wpdb->prefix}atlaspress_entries e 
            JOIN {$wpdb->prefix}atlaspress_content_types ct ON e.content_type_id = ct.id 
            WHERE e.created_at > %s 
            ORDER BY e.created_at DESC",
            $lastCheck
        ));
        
        return [
            'new_entries' => $newEntries,
            'count' => count($newEntries),
            'last_check' => current_time('mysql')
        ];
    }
}
