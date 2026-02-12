<?php
namespace AtlasPress\Core;

class Webhooks {
    
    private static $hooks = [];
    
    public static function init() {
        add_action('atlaspress_entry_created', [self::class, 'trigger_created'], 10, 1);
        add_action('atlaspress_entry_updated', [self::class, 'trigger_updated'], 10, 1);
        add_action('atlaspress_entry_deleted', [self::class, 'trigger_deleted'], 10, 1);
    }
    
    public static function trigger_created($data) {
        self::trigger('atlaspress_entry_created', $data);
    }
    
    public static function trigger_updated($data) {
        self::trigger('atlaspress_entry_updated', $data);
    }
    
    public static function trigger_deleted($data) {
        self::trigger('atlaspress_entry_deleted', $data);
    }
    
    public static function register($event, $url, $secret = '') {
        if(!isset(self::$hooks[$event])) {
            self::$hooks[$event] = [];
        }
        
        self::$hooks[$event][] = [
            'url' => $url,
            'secret' => $secret
        ];
        
        update_option('atlaspress_webhooks', self::$hooks);
    }
    
    public static function trigger($event, $data) {
        $hooks = get_option('atlaspress_webhooks', []);
        
        if(!isset($hooks[$event])) return;
        
        foreach($hooks[$event] as $hook) {
            wp_remote_post($hook['url'], [
                'body' => wp_json_encode([
                    'event' => $event,
                    'data' => $data,
                    'timestamp' => current_time('timestamp')
                ]),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-AtlasPress-Signature' => hash_hmac('sha256', wp_json_encode($data), $hook['secret'])
                ],
                'timeout' => 15
            ]);
        }
    }
    
    public static function getHooks() {
        return get_option('atlaspress_webhooks', []);
    }
}