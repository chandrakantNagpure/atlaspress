<?php
namespace AtlasPress\Core;

class Security {

    const OPTION_RATE_LIMIT_RULES = 'atlaspress_rate_limit_rules';
    
    public static function init() {
        add_action('rest_api_init', [self::class, 'add_rate_limiting']);
    }
    
    public static function add_rate_limiting() {
        add_filter('rest_request_before_callbacks', [self::class, 'rate_limit_check'], 10, 3);
    }
    
    public static function rate_limit_check($response, $handler, $request) {
        $route = $request->get_route();
        
        if(strpos($route, '/atlaspress/v1/') !== 0) return $response;

        // Logged-in users are governed by WordPress capabilities and are excluded
        // from anonymous/IP-based AtlasPress throttling.
        if (is_user_logged_in()) return $response;
        
        $identifier = self::get_rate_limit_identifier($request);
        $is_public_route = self::is_public_route($route);
        $key = 'atlaspress_rate_limit_' . md5(($is_public_route ? 'public' : 'private') . '|' . $route . '|' . $identifier);
        $requests = get_transient($key) ?: 0;

        $rules = self::get_effective_rate_limit_rules();
        $default_limit = $is_public_route ? ($rules['public'] ?? 300) : ($rules['private'] ?? 100);
        $route_limit = self::resolve_route_limit($route, $rules['route_overrides'] ?? [], (int) $default_limit);
        $limit = (int) apply_filters('atlaspress_rate_limit', $route_limit, $route, $request);
        if($limit <= 0) return $response;
        
        if($requests >= $limit) {
            return new \WP_Error('rate_limit_exceeded', 'Rate limit exceeded', ['status' => 429]);
        }
        
        set_transient($key, $requests + 1, HOUR_IN_SECONDS);
        
        return $response;
    }
    
    private static function get_rate_limit_identifier($request) {
        if($request && method_exists($request, 'get_header')) {
            $api_key = $request->get_header('X-API-Key') ?: $request->get_param('api_key');
            if(is_string($api_key) && $api_key !== '') {
                return 'key:' . hash('sha256', $api_key);
            }
        }

        return 'ip:' . self::get_client_ip();
    }

    private static function is_public_route($route) {
        $public_routes = apply_filters('atlaspress_public_routes', [
            '/atlaspress/v1/form-capture',
            '/atlaspress/v1/hubspot/webhook'
        ]);

        if(!is_array($public_routes)) {
            return false;
        }

        foreach($public_routes as $pattern) {
            if(!is_string($pattern) || $pattern === '') continue;
            if(self::route_matches($route, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private static function route_matches($route, $pattern) {
        if($route === $pattern) {
            return true;
        }

        if(strpos($pattern, '*') === false) {
            return false;
        }

        $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';
        return (bool) preg_match($regex, $route);
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

    public static function get_default_rate_limit_rules() {
        return [
            'public' => 300,
            'private' => 100,
            'route_overrides' => [],
        ];
    }

    public static function sanitize_rate_limit_rules($rules) {
        if(is_string($rules)) {
            $decoded = json_decode($rules, true);
            if(is_array($decoded)) {
                $rules = $decoded;
            } else {
                $rules = [];
            }
        }

        if(!is_array($rules)) {
            $rules = [];
        }

        $defaults = self::get_default_rate_limit_rules();
        $public = self::sanitize_rate_limit_value($rules['public'] ?? $defaults['public']);
        $private = self::sanitize_rate_limit_value($rules['private'] ?? $defaults['private']);

        $route_overrides = [];
        $override_input = $rules['route_overrides'] ?? [];
        if(is_array($override_input)) {
            foreach($override_input as $entry) {
                if(!is_array($entry)) {
                    continue;
                }

                $pattern = sanitize_text_field((string) ($entry['pattern'] ?? ''));
                $pattern = trim($pattern);
                if($pattern === '' || strpos($pattern, '/atlaspress/v1/') !== 0) {
                    continue;
                }

                $route_overrides[] = [
                    'pattern' => $pattern,
                    'limit' => self::sanitize_rate_limit_value($entry['limit'] ?? $private),
                ];

                if(count($route_overrides) >= 25) {
                    break;
                }
            }
        }

        return [
            'public' => $public,
            'private' => $private,
            'route_overrides' => $route_overrides,
        ];
    }

    public static function get_rate_limit_rules() {
        $stored = get_option(self::OPTION_RATE_LIMIT_RULES, self::get_default_rate_limit_rules());
        return self::sanitize_rate_limit_rules($stored);
    }

    public static function get_effective_rate_limit_rules() {
        if(!ProVersion::is_pro_active()) {
            return self::get_default_rate_limit_rules();
        }

        return self::get_rate_limit_rules();
    }

    public static function set_rate_limit_rules($rules) {
        $sanitized = self::sanitize_rate_limit_rules($rules);
        if(!ProVersion::is_pro_active()) {
            return false;
        }

        update_option(self::OPTION_RATE_LIMIT_RULES, $sanitized, false);
        return $sanitized;
    }
    
    private static function get_client_ip() {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach($headers as $header) {
            if(!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if(filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '0.0.0.0';
    }

    private static function resolve_route_limit($route, $route_overrides, $default_limit) {
        if(!is_array($route_overrides) || empty($route_overrides)) {
            return self::sanitize_rate_limit_value($default_limit);
        }

        foreach($route_overrides as $entry) {
            if(!is_array($entry)) {
                continue;
            }

            $pattern = trim((string) ($entry['pattern'] ?? ''));
            if($pattern === '') {
                continue;
            }

            if(self::route_matches($route, $pattern)) {
                return self::sanitize_rate_limit_value($entry['limit'] ?? $default_limit);
            }
        }

        return self::sanitize_rate_limit_value($default_limit);
    }

    private static function sanitize_rate_limit_value($value) {
        $limit = (int) $value;
        if($limit < 1) {
            $limit = 1;
        }
        if($limit > 100000) {
            $limit = 100000;
        }

        return $limit;
    }
}
