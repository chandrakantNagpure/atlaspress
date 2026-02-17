<?php
namespace AtlasPress\Rest;

use AtlasPress\Core\Permissions;
use AtlasPress\Core\Cache;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DashboardController {

    public static function register() {
        register_rest_route(
            'atlaspress/v1',
            '/dashboard',
            [
                'methods'  => 'GET',
                'callback' => [ self::class, 'stats' ],
                'permission_callback' => [Permissions::class, 'can_view_dashboard']
            ]
        );
    }

    public static function stats() {
        return Cache::remember('dashboard_stats', function() {
            global $wpdb;

            $types_table = $wpdb->prefix . 'atlaspress_content_types';
            $entries_table = $wpdb->prefix . 'atlaspress_entries';
            
            // Check if tables exist
            if($wpdb->get_var("SHOW TABLES LIKE '$types_table'") != $types_table) {
                return new WP_REST_Response([
                    'contentTypes' => 0,
                    'fields' => 0,
                    'entries' => 0,
                    'apiStatus' => 'Setup Required',
                    'recentEntries' => [],
                    'topContentTypes' => [],
                    'weeklyStats' => []
                ], 200);
            }
            
            $types = $wpdb->get_results( "SELECT * FROM $types_table ORDER BY created_at DESC", ARRAY_A );
            $entriesCount = $wpdb->get_var( "SELECT COUNT(*) FROM $entries_table" );
            
            // Recent entries
            $recentEntries = $wpdb->get_results(
                "SELECT e.*, ct.name as content_type_name 
                 FROM $entries_table e 
                 LEFT JOIN $types_table ct ON e.content_type_id = ct.id 
                 ORDER BY e.created_at DESC LIMIT 5", 
                ARRAY_A
            );
            
            // Top content types by entry count
            $topTypes = $wpdb->get_results(
                "SELECT ct.name, COUNT(e.id) as entry_count 
                 FROM $types_table ct 
                 LEFT JOIN $entries_table e ON ct.id = e.content_type_id 
                 GROUP BY ct.id 
                 ORDER BY entry_count DESC LIMIT 5", 
                ARRAY_A
            );
            
            // Weekly stats
            $weeklyStats = $wpdb->get_results(
                "SELECT DATE(created_at) as date, COUNT(*) as count 
                 FROM $entries_table 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
                 GROUP BY DATE(created_at) 
                 ORDER BY date DESC", 
                ARRAY_A
            );

            $contentTypesCount = count( $types );
            $fieldsCount = 0;

            foreach ( $types as $type ) {
                if ( empty( $type['settings'] ) ) continue;

                $settings = json_decode( $type['settings'], true );
                if ( isset( $settings['fields'] ) && is_array( $settings['fields'] ) ) {
                    $fieldsCount += count( $settings['fields'] );
                }
            }

            return new WP_REST_Response([
                'contentTypes' => $contentTypesCount,
                'fields' => $fieldsCount,
                'entries' => (int)$entriesCount,
                'apiStatus' => 'Healthy',
                'recentEntries' => $recentEntries,
                'topContentTypes' => $topTypes,
                'weeklyStats' => $weeklyStats
            ], 200);
        }, 300); // Cache for 5 minutes
    }
}
