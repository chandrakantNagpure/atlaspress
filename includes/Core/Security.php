<?php
namespace AtlasPress\Core;

class Security {
    
    public static function init() {
        add_action('rest_api_init', [self::class, 'add_rate_limiting']);
    }
    
    public static function add_rate_limiting() {
        if (!self::is_rate_limiting_enabled()) {
            return;
        }

        add_filter('rest_request_before_callbacks', [self::class, 'rate_limit_check'], 10, 3);
    }
    
    public static function rate_limit_check($response, $handler, $request) {
        $route = $request->get_route();
        
        if(strpos($route, '/atlaspress/v1/') !== 0) return $response;
        
        $ip = self::get_client_ip();
        $key = 'atlaspress_rate_limit_' . md5($ip);
        $requests = get_transient($key) ?: 0;
        
        $limit = apply_filters('atlaspress_rate_limit', 100); // 100 requests per hour
        
        if($requests >= $limit) {
            return new \WP_Error('rate_limit_exceeded', 'Rate limit exceeded', ['status' => 429]);
        }
        
        set_transient($key, $requests + 1, HOUR_IN_SECONDS);
        
        return $response;
    }

    private static function is_rate_limiting_enabled() {
        // Free package keeps custom API rate limiting disabled by default.
        return (bool) apply_filters('atlaspress_enable_rate_limiting', false);
    }
    
    public static function generate_api_key($user_id) {
        $key = wp_generate_password(32, false);
        $hash = wp_hash_password($key);
        
        update_user_meta($user_id, 'atlaspress_api_key', $hash);
        
        return $key;
    }
    
    public static function validate_api_key($key) {
        $users = get_users(['meta_key' => 'atlaspress_api_key']);
        
        foreach($users as $user) {
            $stored_hash = get_user_meta($user->ID, 'atlaspress_api_key', true);
            if(wp_check_password($key, $stored_hash)) {
                return $user;
            }
        }
        
        return false;
    }
    
    private static function get_client_ip() {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach($headers as $header) {
            if(!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
