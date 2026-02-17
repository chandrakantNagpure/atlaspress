<?php
namespace AtlasPress\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ApiSecurity {
    
    public static function init() {
        add_action('rest_api_init', [self::class, 'add_security_headers']);
        add_filter('rest_pre_dispatch', [self::class, 'validate_request'], 10, 3);
        add_action('send_headers', [self::class, 'send_cors_headers']);
    }
    
    public static function add_security_headers() {
        add_filter('rest_pre_serve_request', function($served, $result, $request) {
            if(strpos($request->get_route(), '/atlaspress/v1/') === 0) {
                header('X-Content-Type-Options: nosniff');
                header('X-Frame-Options: DENY');
                header('X-XSS-Protection: 1; mode=block');
                header('Referrer-Policy: strict-origin-when-cross-origin');
                self::add_cors_headers();
            }
            return $served;
        }, 10, 3);
    }
    
    public static function validate_request($result, $server, $request) {
        $route = $request->get_route();
        if(strpos($route, '/atlaspress/v1/') !== 0) return $result;

        // Allow preflight requests
        if ($request->get_method() === 'OPTIONS') {
            self::add_cors_headers();
            return new \WP_REST_Response(null, 200);
        }
        
        // CORS handling (free version defaults to open access)
        self::add_cors_headers();
        
        // API Key validation for sensitive endpoints
        if(self::requires_api_key($route)) {
            $api_key = $request->get_header('X-API-Key') ?: $request->get_param('api_key');
            if(!$api_key || !self::validate_api_key($api_key)) {
                return new \WP_Error('invalid_api_key', 'Invalid API key', ['status' => 401]);
            }
        }
        
        // Request signing validation
        if(self::requires_signature($route)) {
            if(!self::validate_signature($request)) {
                return new \WP_Error('invalid_signature', 'Invalid request signature', ['status' => 401]);
            }
        }
        
        return $result;
    }
    
    public static function authenticate_request($result) {
        if(!empty($result)) return $result;
        
        // JWT token validation
        $token = self::get_bearer_token();
        if($token && !self::validate_jwt($token)) {
            self::add_cors_headers();
            return new \WP_Error('invalid_token', 'Invalid JWT token', ['status' => 401]);
        }
        
        return $result;
    }
    
    private static function requires_api_key($route) {
        // Free package keeps API key enforcement disabled by default.
        return (bool) apply_filters('atlaspress_require_api_key', false, $route);
    }
    
    private static function requires_signature($route) {
        // Free package keeps request signature enforcement disabled by default.
        return (bool) apply_filters('atlaspress_require_signature', false, $route);
    }
    
    private static function validate_api_key($key) {
        $stored_keys = get_option('atlaspress_api_keys', []);
        return in_array(hash('sha256', $key), $stored_keys);
    }
    
    private static function validate_signature($request) {
        $signature = $request->get_header('X-Signature');
        $timestamp = $request->get_header('X-Timestamp');
        $body = $request->get_body();
        
        if(!$signature || !$timestamp) return false;
        
        // Check timestamp (prevent replay attacks)
        if(abs(time() - intval($timestamp)) > 300) return false; // 5 minutes
        
        $secret = get_option('atlaspress_webhook_secret', '');
        $expected = hash_hmac('sha256', $timestamp . $body, $secret);
        
        return hash_equals($signature, $expected);
    }
    
    private static function get_bearer_token() {
        $header = isset( $_SERVER['HTTP_AUTHORIZATION'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) ) : '';
        if(preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private static function validate_jwt($token) {
        // Simple JWT validation - in production use proper JWT library
        $parts = explode('.', $token);
        if(count($parts) !== 3) return false;
        
        $secret = get_option('atlaspress_jwt_secret', '');
        if(empty($secret)) return false;
        $signature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret, true);
        $expected = self::base64url_decode($parts[2]);
        
        return hash_equals($signature, $expected);
    }

    private static function base64url_decode($data) {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $padding = strlen($data) % 4;
        if($padding) {
            $data .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($data);
    }

    private static function add_cors_headers() {
        $allowed_origins = get_option('atlaspress_allowed_origins', []);
        $origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ) : '';
        if ($origin && (empty($allowed_origins) || in_array($origin, $allowed_origins, true))) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        } else {
            header('Access-Control-Allow-Origin: *');
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization, X-API-Key, X-Signature, X-Timestamp');
    }

    public static function send_cors_headers() {
        // Ensure CORS headers are sent even on auth errors for AtlasPress routes
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        if (strpos($uri, '/wp-json/atlaspress/v1/') !== false) {
            self::add_cors_headers();
        }
    }
    
    public static function generate_api_key($name = '') {
        $key = bin2hex(random_bytes(32));
        $keys = get_option('atlaspress_api_keys', []);
        $keys[$name ?: 'key_' . time()] = hash('sha256', $key);
        update_option('atlaspress_api_keys', $keys);
        return $key;
    }
    
    public static function set_allowed_origins($origins) {
        update_option('atlaspress_allowed_origins', array_filter($origins));
    }
}
