<?php
namespace AtlasPress\Rest;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AtlasPress\Core\FieldTypes;
use AtlasPress\Core\Permissions;
use AtlasPress\Core\Cache;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ContentTypesController {

    public static function register() {
        // Bypass rate limiting for admin users
        add_filter('rest_request_before_callbacks', function($response, $handler, $request) {
            if (strpos($request->get_route(), '/atlaspress/v1/') === 0) {
                remove_filter('rest_request_before_callbacks', 'rest_cookie_check_errors', 100);
            }
            return $response;
        }, 10, 3);
        
        register_rest_route('atlaspress/v1','/content-types/bulk-delete',[
            ['methods'=>'POST','callback'=>[self::class,'bulk_delete'],'permission_callback'=>[Permissions::class,'can_manage_types']]
        ]);

        register_rest_route('atlaspress/v1','/content-types',[
            ['methods'=>'GET','callback'=>[self::class,'index'],'permission_callback'=>[Permissions::class,'can_manage_types']],
            ['methods'=>'POST','callback'=>[self::class,'store'],'permission_callback'=>[Permissions::class,'can_manage_types']]
        ]);

        register_rest_route('atlaspress/v1','/content-types/(?P<id>\d+)',[
            ['methods'=>'GET','callback'=>[self::class,'show'],'permission_callback'=>[Permissions::class,'can_manage_types']],
            ['methods'=>'DELETE','callback'=>[self::class,'delete'],'permission_callback'=>[Permissions::class,'can_manage_types']]
        ]);

        register_rest_route('atlaspress/v1','/content-types/(?P<id>\d+)/schema',[
            ['methods'=>'PUT','callback'=>[self::class,'update_schema'],'permission_callback'=>[Permissions::class,'can_manage_types']]
        ]);

        register_rest_route('atlaspress/v1','/field-types',[
            ['methods'=>'GET','callback'=>[self::class,'field_types'],'permission_callback'=>[Permissions::class,'can_manage_types']]
        ]);
    }

    public static function index() {
        global $wpdb;
        $table = $wpdb->prefix.'atlaspress_content_types';
        
        // Check if table exists
        if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return new WP_Error('table_missing','Database tables not found. Please run setup wizard.',['status'=>500]);
        }
        
        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC",ARRAY_A);
        
        if($wpdb->last_error) {
            return new WP_Error('db_error','Database error: '.$wpdb->last_error,['status'=>500]);
        }
        
        // Parse settings JSON
        foreach($results as &$result) {
            $result['settings'] = json_decode($result['settings'], true) ?: [];
        }
        
        return new WP_REST_Response(['data'=>$results],200);
    }

    public static function show(WP_REST_Request $req) {
        global $wpdb;
        $id = (int)$req['id'];
        $table = $wpdb->prefix.'atlaspress_content_types';
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id),ARRAY_A);
        
        if(!$result) return new WP_Error('not_found','Content type not found',['status'=>404]);
        
        $result['settings'] = json_decode($result['settings'], true) ?: [];
        return new WP_REST_Response($result,200);
    }

    public static function store(WP_REST_Request $req) {
        global $wpdb;
        $name = sanitize_text_field($req['name']);
        if(!$name) return new WP_Error('name_required','Name required',['status'=>422]);
        $slug = sanitize_title($name);
        $table = $wpdb->prefix.'atlaspress_content_types';
        if($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE slug=%s",$slug)))
            return new WP_Error('slug_exists','Slug exists',['status'=>409]);

        $wpdb->insert($table,['name'=>$name,'slug'=>$slug,'settings'=>'{}','status'=>'active']);
        
        // Clear cache
        Cache::flush('dashboard_stats');
        Cache::flush('content_types');
        
        // Trigger webhook
        do_action('atlaspress_content_type_created', ['id'=>$wpdb->insert_id,'name'=>$name,'slug'=>$slug]);
        
        return new WP_REST_Response(['message'=>'Created','id'=>$wpdb->insert_id],201);
    }

    public static function delete(WP_REST_Request $req) {
        global $wpdb;
        $id = (int)$req['id'];
        $table = $wpdb->prefix.'atlaspress_content_types';
        $entries_table = $wpdb->prefix.'atlaspress_entries';
        
        // First delete associated entries
        $wpdb->delete($entries_table, ['content_type_id' => $id]);
        
        // Then delete the content type
        $deleted = $wpdb->delete($table, ['id' => $id]);
        
        if(!$deleted) return new WP_Error('not_found','Content type not found',['status'=>404]);
        
        // Clear cache
        Cache::flush('dashboard_stats');
        Cache::flush('content_types');
        
        return new WP_REST_Response(['message'=>'Deleted'],200);
    }

    public static function bulk_delete(WP_REST_Request $req) {
        global $wpdb;
        $ids = $req['ids'] ?? [];
        
        if(!is_array($ids) || empty($ids)) {
            return new WP_Error('invalid_ids','No IDs provided',['status'=>422]);
        }
        
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        
        // First delete all associated entries
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}atlaspress_entries WHERE content_type_id IN ($placeholders)",
            ...$ids
        ));
        
        // Then delete the content types
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}atlaspress_content_types WHERE id IN ($placeholders)",
            ...$ids
        ));
        
        // Clear cache
        Cache::flush('dashboard_stats');
        Cache::flush('content_types');
        
        return new WP_REST_Response(['message'=>"Deleted $deleted content types"], 200);
    }

    public static function update_schema(WP_REST_Request $req) {
        global $wpdb;
        $id = (int)$req['id'];
        $schema = $req['schema'];
        if(!is_array($schema)) return new WP_Error('invalid_schema','Invalid schema',['status'=>422]);

        // Validate field types
        $fieldTypes = FieldTypes::getTypes();
        foreach($schema as $field) {
            if(!isset($fieldTypes[$field['type']])) {
                return new WP_Error('invalid_field_type','Invalid field type: '.$field['type'],['status'=>422]);
            }
        }

        $wpdb->update($wpdb->prefix.'atlaspress_content_types',
            ['settings'=>wp_json_encode(['fields'=>$schema])],
            ['id'=>$id]
        );

        return rest_ensure_response(['message'=>'Schema saved']);
    }

    public static function field_types() {
        return new WP_REST_Response(FieldTypes::getTypes(),200);
    }
}
