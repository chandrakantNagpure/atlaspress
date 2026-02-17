<?php
namespace AtlasPress\Rest;

use AtlasPress\Core\Permissions;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FileUploadController {

    public static function register() {
        register_rest_route('atlaspress/v1','/upload',[
            ['methods'=>'POST','callback'=>[self::class,'upload'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);

        register_rest_route('atlaspress/v1','/files/(?P<id>\d+)',[
            ['methods'=>'DELETE','callback'=>[self::class,'delete'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);
    }

    public static function upload(WP_REST_Request $req) {
        if(!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $files = $req->get_file_params();
        if(empty($files['file'])) {
            return new WP_Error('no_file','No file provided',['status'=>400]);
        }

        $file = $files['file'];
        $field_type = sanitize_text_field($req->get_param('field_type'));
        $allowed_types = $req->get_param('allowed_types');
        $max_size = $req->get_param('max_size');

        // Validate file type
        if($allowed_types && is_array($allowed_types)) {
            $file_type = wp_check_filetype($file['name']);
            if(!in_array($file_type['ext'], $allowed_types)) {
                return new WP_Error('invalid_type','File type not allowed',['status'=>400]);
            }
        }

        // Validate file size
        $limit = wp_max_upload_size();
        if(is_numeric($max_size) && $max_size > 0) {
            $limit = min($limit, (int)$max_size);
        }
        if(isset($file['size']) && $file['size'] > $limit) {
            return new WP_Error('file_too_large','File exceeds allowed size',['status'=>400]);
        }

        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($file, $upload_overrides);

        if($movefile && !isset($movefile['error'])) {
            // Create attachment
            $attachment = [
                'guid' => $movefile['url'],
                'post_mime_type' => $movefile['type'],
                'post_title' => sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME)),
                'post_content' => '',
                'post_status' => 'inherit'
            ];

            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
            
            if(!function_exists('wp_generate_attachment_metadata')) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
            }
            
            $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);

            return new WP_REST_Response([
                'id' => $attach_id,
                'url' => $movefile['url'],
                'filename' => basename($movefile['file']),
                'type' => $movefile['type'],
                'size' => filesize($movefile['file'])
            ], 200);
        }

        return new WP_Error('upload_failed', $movefile['error'] ?? 'Upload failed', ['status'=>500]);
    }

    public static function delete(WP_REST_Request $req) {
        $id = (int)$req['id'];
        $deleted = wp_delete_attachment($id, true);
        
        if(!$deleted) {
            return new WP_Error('delete_failed','Failed to delete file',['status'=>500]);
        }

        return new WP_REST_Response(['message'=>'File deleted'], 200);
    }
}
