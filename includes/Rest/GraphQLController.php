<?php
namespace AtlasPress\Rest;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class GraphQLController {

    public static function register() {
        register_rest_route('atlaspress/v1','/graphql',[
            ['methods'=>['GET','POST'],'callback'=>[self::class,'query'],'permission_callback'=>'__return_true']
        ]);
    }

    public static function query(WP_REST_Request $req) {
        $query = $req->get_param('query');
        $variables = $req->get_param('variables') ?: [];
        
        if(!$query) {
            return new WP_Error('no_query','GraphQL query required',['status'=>400]);
        }

        try {
            $result = self::parseAndExecute($query, $variables);
            return new WP_REST_Response(['data' => $result], 200);
        } catch (\Exception $e) {
            return new WP_Error('query_error', $e->getMessage(), ['status'=>400]);
        }
    }

    private static function parseAndExecute($query, $variables) {
        $result = [];
        
        // Parse contentTypes query
        if(preg_match('/contentTypes\s*(?:\(([^)]*)\))?\s*{([^}]+)}/s', $query, $matches)) {
            $args = self::parseArgs($matches[1] ?? '');
            $fields = self::parseFields($matches[2]);
            $result['contentTypes'] = self::getContentTypes($args, $fields);
        }
        
        // Parse entries query
        if(preg_match('/entries\s*\(([^)]*)\)\s*{([^}]+)}/s', $query, $matches)) {
            $args = self::parseArgs($matches[1]);
            $fields = self::parseFields($matches[2]);
            $result['entries'] = self::getEntries($args, $fields);
        }
        
        // Parse single entry query
        if(preg_match('/entry\s*\(([^)]*)\)\s*{([^}]+)}/s', $query, $matches)) {
            $args = self::parseArgs($matches[1]);
            $fields = self::parseFields($matches[2]);
            $result['entry'] = self::getEntry($args, $fields);
        }

        return $result;
    }

    private static function parseArgs($argsString) {
        $args = [];
        if(preg_match_all('/(\w+):\s*([\d"\w]+)/', $argsString, $matches, PREG_SET_ORDER)) {
            foreach($matches as $match) {
                $value = trim($match[2], '"');
                $args[$match[1]] = is_numeric($value) ? (int)$value : $value;
            }
        }
        return $args;
    }

    private static function parseFields($fieldsString) {
        return array_map('trim', explode(',', str_replace(['{', '}', '\n'], '', $fieldsString)));
    }

    private static function getContentTypes($args, $fields) {
        global $wpdb;
        $table = $wpdb->prefix.'atlaspress_content_types';
        
        $query = "SELECT * FROM $table";
        $params = [];
        
        if(isset($args['id'])) {
            $query .= " WHERE id=%d";
            $params[] = $args['id'];
        }
        
        $query .= " ORDER BY id DESC";
        
        if(isset($args['limit'])) {
            $query .= " LIMIT %d";
            $params[] = $args['limit'];
        }
        
        $types = $params 
            ? $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A)
            : $wpdb->get_results($query, ARRAY_A);
        
        foreach($types as &$type) {
            $type['settings'] = json_decode($type['settings'], true) ?: [];
        }

        return $types;
    }

    private static function getEntries($args, $fields) {
        global $wpdb;
        $table = $wpdb->prefix.'atlaspress_entries';
        
        if(!isset($args['contentTypeId'])) {
            throw new \Exception('contentTypeId is required');
        }
        
        $query = "SELECT * FROM $table WHERE content_type_id=%d";
        $params = [$args['contentTypeId']];
        
        if(isset($args['status'])) {
            $query .= " AND status=%s";
            $params[] = $args['status'];
        }
        
        $query .= " ORDER BY id DESC";
        
        if(isset($args['limit'])) {
            $query .= " LIMIT %d";
            $params[] = $args['limit'];
        } else {
            $query .= " LIMIT 50";
            $params[] = 50;
        }
        
        $entries = $wpdb->get_results($wpdb->prepare($query, ...$params), ARRAY_A);

        foreach($entries as &$entry) {
            $entry['data'] = json_decode($entry['data'], true) ?: [];
        }

        return $entries;
    }

    private static function getEntry($args, $fields) {
        global $wpdb;
        $table = $wpdb->prefix.'atlaspress_entries';
        
        if(!isset($args['id'])) {
            throw new \Exception('id is required');
        }
        
        $entry = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id=%d",
            $args['id']
        ), ARRAY_A);
        
        if($entry) {
            $entry['data'] = json_decode($entry['data'], true) ?: [];
        }

        return $entry;
    }
}