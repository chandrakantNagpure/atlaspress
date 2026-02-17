<?php
namespace AtlasPress\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Network {
    
    public static function init() {
        if(!is_multisite()) return;
        
        add_action('network_admin_menu', [self::class, 'network_menu']);
        add_action('wp_ajax_atlaspress_sync_content', [self::class, 'sync_content']);
    }
    
    public static function network_menu() {
        add_menu_page(
            'AtlasPress Network',
            'AtlasPress Network', 
            'manage_network',
            'atlaspress-network',
            [self::class, 'network_page'],
            'dashicons-database'
        );
    }
    
    public static function network_page() {
        $sites = get_sites(['number' => 100]);
        echo '<div class="wrap">';
        echo '<h1>AtlasPress Network</h1>';
        echo '<div id="atlaspress-network-app" data-sites="' . esc_attr(wp_json_encode($sites)) . '"></div>';
        echo '</div>';
    }
    
    public static function sync_content() {
        if(!current_user_can('manage_network')) wp_die('Unauthorized');

        $source_site = isset( $_POST['source_site'] ) ? (int) $_POST['source_site'] : 0;
        $target_sites = isset( $_POST['target_sites'] ) ? array_map('intval', (array) $_POST['target_sites'] ) : array();
        $content_types = isset( $_POST['content_types'] ) ? array_map('intval', (array) $_POST['content_types'] ) : array();
        
        switch_to_blog($source_site);
        global $wpdb;
        
        $types_table = $wpdb->prefix . 'atlaspress_content_types';
        $entries_table = $wpdb->prefix . 'atlaspress_entries';
        
        $types = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $types_table WHERE id IN (" . implode(',', array_fill(0, count($content_types), '%d')) . ")",
            ...$content_types
        ), ARRAY_A);
        
        $entries = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $entries_table WHERE content_type_id IN (" . implode(',', array_fill(0, count($content_types), '%d')) . ")",
            ...$content_types
        ), ARRAY_A);
        
        restore_current_blog();
        
        foreach($target_sites as $site_id) {
            switch_to_blog($site_id);
            
            foreach($types as $type) {
                $wpdb->replace($wpdb->prefix . 'atlaspress_content_types', [
                    'id' => $type['id'],
                    'name' => $type['name'],
                    'slug' => $type['slug'],
                    'settings' => $type['settings'],
                    'status' => $type['status']
                ]);
            }
            
            foreach($entries as $entry) {
                $wpdb->replace($wpdb->prefix . 'atlaspress_entries', [
                    'id' => $entry['id'],
                    'content_type_id' => $entry['content_type_id'],
                    'title' => $entry['title'],
                    'slug' => $entry['slug'],
                    'data' => $entry['data'],
                    'status' => $entry['status'],
                    'author_id' => $entry['author_id']
                ]);
            }
            
            restore_current_blog();
        }
        
        wp_send_json_success(['synced' => count($target_sites) * (count($types) + count($entries))]);
    }
    
    public static function get_network_stats() {
        if(!is_multisite()) return [];
        
        $sites = get_sites(['number' => 100]);
        $stats = [];
        
        foreach($sites as $site) {
            switch_to_blog($site->blog_id);
            global $wpdb;
            
            $types_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}atlaspress_content_types");
            $entries_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}atlaspress_entries");
            
            $stats[] = [
                'site_id' => $site->blog_id,
                'site_name' => get_bloginfo('name'),
                'content_types' => (int)$types_count,
                'entries' => (int)$entries_count
            ];
            
            restore_current_blog();
        }
        
        return $stats;
    }
}