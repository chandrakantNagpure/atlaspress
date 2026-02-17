<?php
namespace AtlasPress\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CLI {
    
    public static function init() {
        if(!defined('WP_CLI') || !WP_CLI) return;
        
        \WP_CLI::add_command('atlaspress', [self::class, 'main']);
    }
    
    public static function main($args, $assoc_args) {
        $command = $args[0] ?? 'help';
        
        switch($command) {
            case 'stats':
                self::show_stats();
                break;
            case 'cache':
                self::manage_cache($args[1] ?? 'status');
                break;
            case 'export':
                self::export_data($assoc_args);
                break;
            case 'import':
                self::import_data($assoc_args);
                break;
            default:
                self::show_help();
        }
    }
    
    private static function show_stats() {
        global $wpdb;
        
        $types_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}atlaspress_content_types");
        $entries_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}atlaspress_entries");
        
        \WP_CLI::line("AtlasPress Statistics:");
        \WP_CLI::line("Content Types: $types_count");
        \WP_CLI::line("Entries: $entries_count");
        
        if(is_multisite()) {
            $network_stats = Network::get_network_stats();
            \WP_CLI::line("\nNetwork Sites: " . count($network_stats));
        }
    }
    
    private static function manage_cache($action) {
        switch($action) {
            case 'clear':
                Cache::flush();
                \WP_CLI::success('Cache cleared');
                break;
            case 'status':
                $stats = Cache::get('dashboard_stats');
                \WP_CLI::line($stats ? 'Cache active' : 'Cache empty');
                break;
        }
    }
    
    private static function export_data($args) {
        $file = $args['file'] ?? 'atlaspress-export.json';
        
        global $wpdb;
        $types = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}atlaspress_content_types", ARRAY_A);
        $entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}atlaspress_entries", ARRAY_A);
        
        $data = [
            'version' => ATLASPRESS_VERSION,
            'export_date' => current_time('mysql'),
            'content_types' => $types,
            'entries' => $entries
        ];
        
        file_put_contents($file, wp_json_encode($data, JSON_PRETTY_PRINT));
        \WP_CLI::success("Exported to $file");
    }
    
    private static function import_data($args) {
        $file = $args['file'] ?? 'atlaspress-export.json';
        
        if(!file_exists($file)) {
            \WP_CLI::error("File not found: $file");
            return;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if(!$data) {
            \WP_CLI::error("Invalid JSON file");
            return;
        }
        
        global $wpdb;
        $imported = 0;
        
        foreach($data['content_types'] as $type) {
            $wpdb->replace("{$wpdb->prefix}atlaspress_content_types", $type);
            $imported++;
        }
        
        foreach($data['entries'] as $entry) {
            $wpdb->replace("{$wpdb->prefix}atlaspress_entries", $entry);
            $imported++;
        }
        
        \WP_CLI::success("Imported $imported items");
    }
    
    private static function show_help() {
        \WP_CLI::line("AtlasPress CLI Commands:");
        \WP_CLI::line("  stats          Show statistics");
        \WP_CLI::line("  cache clear    Clear cache");
        \WP_CLI::line("  cache status   Show cache status");
        \WP_CLI::line("  export --file=<file>  Export data");
        \WP_CLI::line("  import --file=<file>  Import data");
    }
}