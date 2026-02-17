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
        $query = $wpdb->prepare("SELECT id, title, slug FROM $table WHERE content_type_id=%d", $type_id);
        
        if($search) {
            $like_search = '%' . $wpdb->esc_like($search) . '%';
            $query .= $wpdb->prepare(" AND title LIKE %s", $like_search);
        }
        
        $query .= " ORDER BY title ASC LIMIT 50";
        
        $entries = $wpdb->get_results($query, ARRAY_A);
        
        return new WP_REST_Response($entries, 200);
    }

    public static function search_entries(WP_REST_Request $req) {
        global $wpdb;
        $search = sanitize_text_field($req->get_param('q'));
        $type_id = (int)$req->get_param('type_id');
        
        if(!$search) return new WP_REST_Response([], 200);
        
        $table = $wpdb->prefix.'atlaspress_entries';
        
        if($type_id) {
            $query = $wpdb->prepare(
                "SELECT id, title, content_type_id FROM $table WHERE content_type_id=%d AND MATCH(title, data) AGAINST(%s IN NATURAL LANGUAGE MODE) LIMIT 20",
                $type_id,
                $search
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT id, title, content_type_id FROM $table WHERE MATCH(title, data) AGAINST(%s IN NATURAL LANGUAGE MODE) LIMIT 20",
                $search
            );
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        return new WP_REST_Response($results, 200);
    }
}