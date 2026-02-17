<?php
namespace AtlasPress\Rest;

use AtlasPress\Core\Permissions;
use WP_REST_Request;
use WP_REST_Response;

class RelationshipController {

    public static function register() {
        register_rest_route('atlaspress/v1','/relationships/(?P<type_id>\d+)',[
            ['methods'=>'GET','callback'=>[self::class,'get_related'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);

        register_rest_route('atlaspress/v1','/search/entries',[
            ['methods'=>'GET','callback'=>[self::class,'search_entries'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);
    }

    public static function get_related(WP_REST_Request $req) {
        global $wpdb;
        $type_id = (int)$req['type_id'];
        $field_name = sanitize_text_field($req->get_param('field'));
        $search = sanitize_text_field($req->get_param('search'));
        
        $table = $wpdb->prefix.'atlaspress_entries';
        $query = "SELECT id, title, slug FROM $table WHERE content_type_id=%d";
        $params = [$type_id];
        
        if($search) {
            $query .= " AND title LIKE %s";
            $params[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        $query .= " ORDER BY title ASC LIMIT 50";
        
        $entries = $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A);
        
        return new WP_REST_Response($entries, 200);
    }

    public static function search_entries(WP_REST_Request $req) {
        global $wpdb;
        $search = sanitize_text_field($req->get_param('q'));
        $type_id = (int)$req->get_param('type_id');
        
        if(!$search) return new WP_REST_Response([], 200);
        
        $table = $wpdb->prefix.'atlaspress_entries';
        $query = "SELECT id, title, content_type_id FROM $table WHERE ";
        $params = [];
        
        if($type_id) {
            $query .= "content_type_id=%d AND ";
            $params[] = $type_id;
        }
        
        $query .= "MATCH(title, data) AGAINST(%s IN NATURAL LANGUAGE MODE) LIMIT 20";
        $params[] = $search;
        
        $results = $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A);
        
        return new WP_REST_Response($results, 200);
    }
}