<?php
namespace AtlasPress\Rest;

use AtlasPress\Core\FieldTypes;
use AtlasPress\Core\Permissions;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EntriesController {

    public static function register() {
        register_rest_route('atlaspress/v1','/entries/bulk-delete',[
            ['methods'=>'POST','callback'=>[self::class,'bulk_delete'],'permission_callback'=>[Permissions::class,'can_delete_entries']]
        ]);
        
        register_rest_route('atlaspress/v1','/entries/bulk-update',[
            ['methods'=>'POST','callback'=>[self::class,'bulk_update'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);

        register_rest_route('atlaspress/v1','/content-types/(?P<type_id>\d+)/entries',[
            ['methods'=>'GET','callback'=>[self::class,'index'],'permission_callback'=>[Permissions::class,'can_edit_entries']],
            ['methods'=>'POST','callback'=>[self::class,'store'],'permission_callback'=>[Permissions::class,'can_submit_entries']],
            ['methods'=>'DELETE','callback'=>[self::class,'bulk_delete_by_type'],'permission_callback'=>[Permissions::class,'can_delete_entries']]
        ]);

        register_rest_route('atlaspress/v1','/entries/(?P<id>\d+)',[
            ['methods'=>'GET','callback'=>[self::class,'show'],'permission_callback'=>[Permissions::class,'can_edit_entries']],
            ['methods'=>'PUT','callback'=>[self::class,'update'],'permission_callback'=>[Permissions::class,'can_edit_entries']],
            ['methods'=>'DELETE','callback'=>[self::class,'delete'],'permission_callback'=>[Permissions::class,'can_delete_entries']]
        ]);
        
        register_rest_route('atlaspress/v1','/entries/(?P<id>\d+)/duplicate',[
            ['methods'=>'POST','callback'=>[self::class,'duplicate'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);
    }

    public static function index(WP_REST_Request $req) {
        global $wpdb;
        $type_id = (int)$req['type_id'];
        $page = max(1, (int)$req->get_param('page') ?: 1);
        $per_page = min(100, max(1, (int)$req->get_param('per_page') ?: 20));
        $offset = ($page - 1) * $per_page;
        $search = sanitize_text_field($req->get_param('search'));
        $status = sanitize_text_field($req->get_param('status'));
        
        $table = $wpdb->prefix.'atlaspress_entries';
        
        // Build query
        $where = $wpdb->prepare("content_type_id=%d", $type_id);
        
        if($search) {
            $where .= $wpdb->prepare(" AND (title LIKE %s OR data LIKE %s)", 
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        if($status) {
            $where .= $wpdb->prepare(" AND status=%s", $status);
        }
        
        // Get total count
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where");
        
        // Get paginated results
        $entries = $wpdb->get_results(
            "SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT $per_page OFFSET $offset",
            ARRAY_A
        );

        foreach($entries as &$entry) {
            $entry['data'] = json_decode($entry['data'], true) ?: [];
        }

        return new WP_REST_Response([
            'data' => $entries,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => (int)$total,
                'total_pages' => ceil($total / $per_page)
            ]
        ], 200);
    }

    public static function show(WP_REST_Request $req) {
        global $wpdb;
        $id = (int)$req['id'];
        $table = $wpdb->prefix.'atlaspress_entries';
        
        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id), ARRAY_A);
        if(!$entry) return new WP_Error('not_found','Entry not found',['status'=>404]);

        $entry['data'] = json_decode($entry['data'], true) ?: [];
        return new WP_REST_Response($entry, 200);
    }

    public static function store(WP_REST_Request $req) {
        global $wpdb;
        $type_id = (int)$req['type_id'];
        $title = sanitize_text_field($req['title']);
        $data = $req['data'] ?: [];

        if(!$title) return new WP_Error('title_required','Title required',['status'=>422]);

        // Auto-detect and create schema if content type has no fields
        self::autoDetectSchema($type_id, $data);

        // Validate against content type schema
        $validation = self::validateEntry($type_id, $data);
        if(is_wp_error($validation)) return $validation;

        $slug = sanitize_title($title);
        $wpdb->insert($wpdb->prefix.'atlaspress_entries', [
            'content_type_id' => $type_id,
            'title' => $title,
            'slug' => $slug,
            'data' => wp_json_encode($data),
            'author_id' => get_current_user_id()
        ]);
        
        $entry_id = $wpdb->insert_id;
        
        // Trigger webhook
        do_action('atlaspress_entry_created', ['id'=>$entry_id,'title'=>$title,'content_type_id'=>$type_id]);

        return new WP_REST_Response(['message'=>'Created','id'=>$entry_id], 201);
    }

    public static function update(WP_REST_Request $req) {
        global $wpdb;
        $id = (int)$req['id'];
        $title = sanitize_text_field($req['title']);
        $data = $req['data'] ?: [];

        $table = $wpdb->prefix.'atlaspress_entries';
        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id), ARRAY_A);
        if(!$entry) return new WP_Error('not_found','Entry not found',['status'=>404]);

        // Validate against content type schema
        $validation = self::validateEntry($entry['content_type_id'], $data);
        if(is_wp_error($validation)) return $validation;

        $update_data = ['data' => wp_json_encode($data)];
        if($title) {
            $update_data['title'] = $title;
            $update_data['slug'] = sanitize_title($title);
        }

        $wpdb->update($table, $update_data, ['id'=>$id]);

        // Trigger webhook / realtime
        do_action('atlaspress_entry_updated', [
            'id' => $id,
            'title' => $update_data['title'] ?? $entry['title'],
            'content_type_id' => $entry['content_type_id']
        ]);

        return new WP_REST_Response(['message'=>'Updated'], 200);
    }

    public static function delete(WP_REST_Request $req) {
        global $wpdb;
        $id = (int)$req['id'];
        $table = $wpdb->prefix.'atlaspress_entries';
        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id), ARRAY_A);
        $deleted = $wpdb->delete($table, ['id'=>$id]);
        
        if(!$deleted) return new WP_Error('not_found','Entry not found',['status'=>404]);

        // Trigger webhook / realtime
        if($entry) {
            do_action('atlaspress_entry_deleted', [
                'id' => $id,
                'title' => $entry['title'],
                'content_type_id' => $entry['content_type_id']
            ]);
        }
        return new WP_REST_Response(['message'=>'Deleted'], 200);
    }

    public static function bulk_delete(WP_REST_Request $req) {
        global $wpdb;
        $ids = $req['ids'] ?? [];
        
        if(!is_array($ids) || empty($ids)) {
            return new WP_Error('invalid_ids','No IDs provided',['status'=>422]);
        }
        
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}atlaspress_entries WHERE id IN ($placeholders)",
            ...$ids
        ));
        
        return new WP_REST_Response(['message'=>"Deleted $deleted entries"], 200);
    }

    public static function bulk_delete_by_type(WP_REST_Request $req) {
        global $wpdb;
        $type_id = (int)$req['type_id'];
        
        $deleted = $wpdb->delete($wpdb->prefix.'atlaspress_entries', ['content_type_id'=>$type_id]);
        return new WP_REST_Response(['message'=>"Deleted $deleted entries"], 200);
    }
    
    public static function bulk_update(WP_REST_Request $req) {
        global $wpdb;
        $ids = $req['ids'] ?? [];
        $status = sanitize_text_field($req['status'] ?? '');
        
        if(!is_array($ids) || empty($ids)) {
            return new WP_Error('invalid_ids','No IDs provided',['status'=>422]);
        }
        
        if(!in_array($status, ['published', 'draft', 'pending'])) {
            return new WP_Error('invalid_status','Invalid status',['status'=>422]);
        }
        
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        
        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}atlaspress_entries SET status=%s WHERE id IN ($placeholders)",
            $status,
            ...$ids
        ));
        
        return new WP_REST_Response(['message'=>"Updated $updated entries to $status"], 200);
    }
    
    public static function duplicate(WP_REST_Request $req) {
        global $wpdb;
        $id = (int)$req['id'];
        $table = $wpdb->prefix.'atlaspress_entries';
        
        $entry = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id), ARRAY_A);
        if(!$entry) return new WP_Error('not_found','Entry not found',['status'=>404]);
        
        $wpdb->insert($table, [
            'content_type_id' => $entry['content_type_id'],
            'title' => $entry['title'] . ' (Copy)',
            'slug' => $entry['slug'] . '-copy-' . time(),
            'data' => $entry['data'],
            'status' => 'draft',
            'author_id' => get_current_user_id()
        ]);
        
        return new WP_REST_Response(['message'=>'Entry duplicated','id'=>$wpdb->insert_id], 201);
    }

    private static function validateEntry($type_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix.'atlaspress_content_types';
        $type = $wpdb->get_row($wpdb->prepare("SELECT settings FROM $table WHERE id=%d", $type_id), ARRAY_A);
        
        if(!$type) return new WP_Error('invalid_type','Content type not found',['status'=>404]);
        
        $settings = json_decode($type['settings'], true);
        $fields = $settings['fields'] ?? [];

        foreach($fields as $field) {
            $value = $data[$field['name']] ?? null;
            if(!FieldTypes::validateField($field, $value)) {
                return new WP_Error('validation_failed',"Validation failed for field: {$field['name']}",['status'=>422]);
            }
        }

        return true;
    }
    
    private static function autoDetectSchema($type_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix.'atlaspress_content_types';
        $type = $wpdb->get_row($wpdb->prepare("SELECT settings FROM $table WHERE id=%d", $type_id), ARRAY_A);
        
        if(!$type) return;
        
        $settings = json_decode($type['settings'], true) ?: [];
        $existing_fields = $settings['fields'] ?? [];
        
        // Only auto-detect if no fields exist
        if(!empty($existing_fields)) return;
        
        $detected_fields = [];
        
        foreach($data as $key => $value) {
            $field_type = self::detectFieldType($value);
            
            $detected_fields[] = [
                'name' => sanitize_key($key),
                'label' => ucwords(str_replace(['_', '-'], ' ', $key)),
                'type' => $field_type,
                'required' => false,
                'id' => time() . wp_rand(1000, 9999)
            ];
        }
        
        if(!empty($detected_fields)) {
            $settings['fields'] = $detected_fields;
            $settings['auto_detected'] = true;
            $settings['detected_at'] = current_time('mysql');
            
            $wpdb->update(
                $table,
                ['settings' => wp_json_encode($settings)],
                ['id' => $type_id]
            );
        }
    }
    
    private static function detectFieldType($value) {
        if(is_array($value)) return 'json';
        if(filter_var($value, FILTER_VALIDATE_EMAIL)) return 'email';
        if(filter_var($value, FILTER_VALIDATE_URL)) return 'url';
        if(is_numeric($value)) return 'number';
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return 'date';
        if(strlen($value) > 100) return 'textarea';
        return 'text';
    }
}
