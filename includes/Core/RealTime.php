<?php
namespace AtlasPress\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RealTime {
    
    public static function init() {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_heartbeat']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_heartbeat']);
        add_filter('heartbeat_received', [self::class, 'heartbeat_received'], 10, 2);
        add_action('atlaspress_entry_created', [self::class, 'broadcast_created'], 10, 1);
        add_action('atlaspress_entry_updated', [self::class, 'broadcast_updated'], 10, 1);
    }
    
    public static function broadcast_created($data) {
        self::broadcast_change('atlaspress_entry_created', $data);
    }
    
    public static function broadcast_updated($data) {
        self::broadcast_change('atlaspress_entry_updated', $data);
    }
    
    public static function enqueue_heartbeat($hook) {
        if(strpos($hook, 'atlaspress') === false) return;
        
        wp_enqueue_script('heartbeat');
        wp_add_inline_script('heartbeat', '
            jQuery(document).ready(function($) {
                $(document).on("heartbeat-send", function(e, data) {
                    data.atlaspress_listen = true;
                });
                
                $(document).on("heartbeat-tick", function(e, data) {
                    if(data.atlaspress_updates) {
                        window.dispatchEvent(new CustomEvent("atlaspress-update", {
                            detail: data.atlaspress_updates
                        }));
                    }
                });
            });
        ');
    }
    
    public static function heartbeat_received($response, $data) {
        if(!isset($data['atlaspress_listen'])) return $response;
        
        $updates = get_transient('atlaspress_live_updates_' . get_current_user_id());
        if($updates) {
            $response['atlaspress_updates'] = $updates;
            delete_transient('atlaspress_live_updates_' . get_current_user_id());
        }
        
        return $response;
    }
    
    public static function broadcast_change($event, $data) {
        $users = get_users(['capability' => 'atlaspress_edit_entries']);
        
        foreach($users as $user) {
            $existing = get_transient('atlaspress_live_updates_' . $user->ID) ?: [];
            $existing[] = ['event' => $event, 'data' => $data, 'time' => time()];
            
            set_transient('atlaspress_live_updates_' . $user->ID, $existing, 300);
        }
    }
}