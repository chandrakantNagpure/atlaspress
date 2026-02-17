<?php
namespace AtlasPress\Rest;

use AtlasPress\Core\Permissions;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ImportExportController {

    public static function register() {
        register_rest_route('atlaspress/v1','/export/content-types',[
            ['methods'=>'GET','callback'=>[self::class,'export_content_types'],'permission_callback'=>[Permissions::class,'can_manage_types']]
        ]);

        register_rest_route('atlaspress/v1','/export/entries/(?P<type_id>\d+)',[
            ['methods'=>'GET','callback'=>[self::class,'export_entries'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);
        
        register_rest_route('atlaspress/v1','/export/(?P<format>csv|json|xml)/(?P<type_id>\d+)',[
            ['methods'=>'GET','callback'=>[self::class,'export_by_format'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);

        register_rest_route('atlaspress/v1','/import/content-types',[
            ['methods'=>'POST','callback'=>[self::class,'import_content_types'],'permission_callback'=>[Permissions::class,'can_manage_types']]
        ]);
        
        register_rest_route('atlaspress/v1','/import/entries/(?P<type_id>\d+)',[
            ['methods'=>'POST','callback'=>[self::class,'import_entries'],'permission_callback'=>[Permissions::class,'can_edit_entries']]
        ]);
    }

    public static function export_content_types() {
        global $wpdb;
        $table = $wpdb->prefix.'atlaspress_content_types';
        $types = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
        
        foreach($types as &$type) {
            $type['settings'] = json_decode($type['settings'], true);
        }

        return new WP_REST_Response([
            'version' => ATLASPRESS_VERSION,
            'export_date' => current_time('mysql'),
            'content_types' => $types
        ], 200);
    }

    public static function export_entries(WP_REST_Request $req) {
        global $wpdb;
        $type_id = (int)$req['type_id'];
        $entries_table = $wpdb->prefix.'atlaspress_entries';
        $types_table = $wpdb->prefix.'atlaspress_content_types';
        
        $type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $types_table WHERE id=%d", $type_id), ARRAY_A);
        if(!$type) return new WP_Error('not_found','Content type not found',['status'=>404]);
        
        $entries = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $entries_table WHERE content_type_id=%d", 
            $type_id
        ), ARRAY_A);

        foreach($entries as &$entry) {
            $entry['data'] = json_decode($entry['data'], true);
        }

        return new WP_REST_Response([
            'version' => ATLASPRESS_VERSION,
            'export_date' => current_time('mysql'),
            'content_type' => $type,
            'entries' => $entries
        ], 200);
    }

    public static function import_content_types(WP_REST_Request $req) {
        global $wpdb;
        $data = $req->get_json_params();
        
        if(!isset($data['content_types']) || !is_array($data['content_types'])) {
            return new WP_Error('invalid_data','Invalid import data',['status'=>422]);
        }

        $table = $wpdb->prefix.'atlaspress_content_types';
        $imported = 0;
        $skipped = 0;

        foreach($data['content_types'] as $type) {
            // Check if slug already exists
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE slug=%s", $type['slug']));
            
            if($exists) {
                $skipped++;
                continue;
            }

            $wpdb->insert($table, [
                'name' => sanitize_text_field($type['name']),
                'slug' => sanitize_title($type['slug']),
                'description' => sanitize_textarea_field($type['description'] ?? ''),
                'settings' => wp_json_encode($type['settings'] ?? []),
                'status' => sanitize_text_field($type['status'] ?? 'active')
            ]);
            
            $imported++;
        }

        return new WP_REST_Response([
            'message' => "Import completed: {$imported} imported, {$skipped} skipped",
            'imported' => $imported,
            'skipped' => $skipped
        ], 200);
    }
    
    public static function export_by_format(WP_REST_Request $req) {
        global $wpdb;
        $type_id = (int)$req['type_id'];
        $format = $req['format'];
        
        $entries_table = $wpdb->prefix.'atlaspress_entries';
        $types_table = $wpdb->prefix.'atlaspress_content_types';
        
        $type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $types_table WHERE id=%d", $type_id), ARRAY_A);
        if(!$type) return new WP_Error('not_found','Content type not found',['status'=>404]);
        
        $entries = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $entries_table WHERE content_type_id=%d", 
            $type_id
        ), ARRAY_A);

        foreach($entries as &$entry) {
            $entry['data'] = json_decode($entry['data'], true);
        }
        
        switch($format) {
            case 'json':
                return self::export_json($type, $entries);
            case 'xml':
                return self::export_xml($type, $entries);
            case 'csv':
                return self::export_csv($type, $entries);
            default:
                return new WP_Error('invalid_format','Invalid export format',['status'=>400]);
        }
    }
    
    private static function export_json($type, $entries) {
        $data = [
            'content_type' => $type['name'],
            'exported_at' => current_time('mysql'),
            'total_entries' => count($entries),
            'entries' => $entries
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($type['slug']) . '-' . gmdate('Y-m-d') . '.json"');
        echo wp_json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
    
    private static function export_xml($type, $entries) {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><export></export>');
        $xml->addChild('content_type', htmlspecialchars($type['name']));
        $xml->addChild('exported_at', current_time('mysql'));
        $xml->addChild('total_entries', count($entries));
        
        $entriesNode = $xml->addChild('entries');
        foreach($entries as $entry) {
            $entryNode = $entriesNode->addChild('entry');
            $entryNode->addChild('id', $entry['id']);
            $entryNode->addChild('title', htmlspecialchars($entry['title']));
            $entryNode->addChild('created_at', $entry['created_at']);
            
            $dataNode = $entryNode->addChild('data');
            foreach($entry['data'] as $key => $value) {
                $dataNode->addChild($key, htmlspecialchars(is_array($value) ? json_encode($value) : $value));
            }
        }
        
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($type['slug']) . '-' . gmdate('Y-m-d') . '.xml"');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $xml->asXML();
        exit;
    }
    
    private static function export_csv($type, $entries) {
        if(empty($entries)) {
            return new WP_Error('no_data','No entries to export',['status'=>404]);
        }
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($type['slug']) . '-' . gmdate('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Get all unique field names
        $fields = ['ID', 'Title', 'Created At'];
        foreach($entries as $entry) {
            if(is_array($entry['data'])) {
                $fields = array_merge($fields, array_keys($entry['data']));
            }
        }
        $fields = array_unique($fields);
        
        // Write header
        fputcsv($output, $fields);
        
        // Write data
        foreach($entries as $entry) {
            $row = [
                $entry['id'],
                $entry['title'],
                $entry['created_at']
            ];
            
            foreach(array_slice($fields, 3) as $field) {
                $value = $entry['data'][$field] ?? '';
                $row[] = is_array($value) ? json_encode($value) : $value;
            }
            
            fputcsv($output, $row);
        }
        
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        fclose($output);
        exit;
    }
    
    public static function import_entries(WP_REST_Request $req) {
        global $wpdb;
        $type_id = (int)$req['type_id'];
        
        $types_table = $wpdb->prefix.'atlaspress_content_types';
        $type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $types_table WHERE id=%d", $type_id));
        if(!$type) return new WP_Error('not_found','Content type not found',['status'=>404]);
        
        $files = $req->get_file_params();
        if(empty($files['file'])) {
            return new WP_Error('no_file','No file uploaded',['status'=>400]);
        }
        
        $file = $files['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if(!in_array($ext, ['csv', 'json'])) {
            return new WP_Error('invalid_format','Only CSV and JSON files are supported',['status'=>400]);
        }
        
        $content = file_get_contents($file['tmp_name']);
        
        if($ext === 'json') {
            return self::import_json($type_id, $content);
        } else {
            return self::import_csv($type_id, $content);
        }
    }
    
    private static function import_json($type_id, $content) {
        global $wpdb;
        $data = json_decode($content, true);
        
        if(!$data || !isset($data['entries'])) {
            return new WP_Error('invalid_json','Invalid JSON format',['status'=>400]);
        }
        
        $table = $wpdb->prefix.'atlaspress_entries';
        $imported = 0;
        
        foreach($data['entries'] as $entry) {
            $wpdb->insert($table, [
                'content_type_id' => $type_id,
                'title' => sanitize_text_field($entry['title'] ?? 'Imported Entry'),
                'slug' => sanitize_title($entry['slug'] ?? $entry['title'] ?? 'imported-' . time()),
                'data' => wp_json_encode($entry['data'] ?? []),
                'status' => sanitize_text_field($entry['status'] ?? 'published'),
                'created_at' => current_time('mysql')
            ]);
            $imported++;
        }
        
        return new WP_REST_Response(['message'=>"Imported {$imported} entries",'imported'=>$imported], 200);
    }
    
    private static function import_csv($type_id, $content) {
        global $wpdb;
        $lines = str_getcsv($content, "\n");
        
        if(empty($lines)) {
            return new WP_Error('empty_file','CSV file is empty',['status'=>400]);
        }
        
        $headers = str_getcsv(array_shift($lines));
        $table = $wpdb->prefix.'atlaspress_entries';
        $imported = 0;
        
        foreach($lines as $line) {
            $row = str_getcsv($line);
            if(count($row) !== count($headers)) continue;
            
            $data = [];
            foreach($headers as $i => $header) {
                if(!in_array($header, ['ID', 'Title', 'Created At'])) {
                    $data[$header] = $row[$i];
                }
            }
            
            $title_index = array_search('Title', $headers);
            $title = $title_index !== false ? $row[$title_index] : 'Imported Entry';
            
            $wpdb->insert($table, [
                'content_type_id' => $type_id,
                'title' => sanitize_text_field($title),
                'slug' => sanitize_title($title . '-' . time()),
                'data' => wp_json_encode($data),
                'status' => 'published',
                'created_at' => current_time('mysql')
            ]);
            $imported++;
        }
        
        return new WP_REST_Response(['message'=>"Imported {$imported} entries",'imported'=>$imported], 200);
    }
}