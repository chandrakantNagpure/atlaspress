<?php
namespace AtlasPress\Core;

class ApiSecurity {

    private static $default_public_routes = [
        '/atlaspress/v1/form-capture',
        '/atlaspress/v1/hubspot/webhook'
    ];

    const OPTION_REQUIRE_SIGNED_FORM_CAPTURE = 'atlaspress_require_signed_form_capture';
    const OPTION_REQUIRE_SIGNED_HUBSPOT_WEBHOOK = 'atlaspress_require_signed_hubspot_webhook';
    const OPTION_ALLOW_LEGACY_SIGNED_INGEST = 'atlaspress_allow_legacy_signed_ingest';
    const OPTION_API_KEYS = 'atlaspress_api_keys';

    const SCOPE_ALL = '*';
    const SCOPE_ENTRIES_READ = 'entries.read';
    const SCOPE_ENTRIES_WRITE = 'entries.write';
    const SCOPE_ENTRIES_DELETE = 'entries.delete';
    const SCOPE_TYPES_MANAGE = 'types.manage';
    const SCOPE_DASHBOARD_READ = 'dashboard.read';
    
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
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if ($origin && !self::is_origin_allowed($origin)) {
                return new \WP_Error('cors_forbidden', 'Origin not allowed', ['status' => 403]);
            }
            self::add_cors_headers();
            return new \WP_REST_Response(null, 200);
        }
        
        // CORS headers for allowed origins only
        self::add_cors_headers();

        // WordPress-authenticated users do not need API keys
        if (is_user_logged_in()) {
            return $result;
        }
        
        // API key validation for non-public AtlasPress endpoints
        if(self::requires_api_key($request)) {
            if(!self::has_valid_api_key($request)) {
                return new \WP_Error('invalid_api_key', 'Invalid API key', ['status' => 401]);
            }
        }
        
        // Request signing validation
        if(self::requires_signature($request)) {
            $signature_validation = self::validate_signature($request);
            if(is_wp_error($signature_validation)) {
                return $signature_validation;
            }
        }
        
        return $result;
    }

    public static function has_valid_api_key($request, $required_scopes = []) {
        if(!$request || !is_object($request) || !method_exists($request, 'get_header')) {
            return false;
        }

        $resolved = self::resolve_api_key_from_request($request);
        if(!$resolved) {
            return false;
        }

        $record = $resolved['record'];
        if(!empty($record['status']) && $record['status'] !== 'active') {
            return false;
        }

        if(self::record_is_expired($record)) {
            return false;
        }

        if(!self::request_ip_is_allowed_for_record($request, $record)) {
            return false;
        }

        if(!self::record_has_required_scopes($record, $required_scopes)) {
            return false;
        }

        self::touch_api_key_usage($resolved['name'], $record, $request);
        return true;
    }

    public static function has_any_api_scope($request, $scopes) {
        if(!is_array($scopes) || empty($scopes)) {
            return self::has_valid_api_key($request);
        }

        foreach($scopes as $scope) {
            if(self::has_valid_api_key($request, [$scope])) {
                return true;
            }
        }

        return false;
    }

    public static function get_available_api_key_scopes() {
        return [
            self::SCOPE_ENTRIES_READ => [
                'label' => 'Entries Read',
                'description' => 'Read entries, relationships, exports, and GraphQL queries',
            ],
            self::SCOPE_ENTRIES_WRITE => [
                'label' => 'Entries Write',
                'description' => 'Create/update entries and submit content',
            ],
            self::SCOPE_ENTRIES_DELETE => [
                'label' => 'Entries Delete',
                'description' => 'Delete entries and files',
            ],
            self::SCOPE_TYPES_MANAGE => [
                'label' => 'Types Manage',
                'description' => 'Create/update/delete content types and schemas',
            ],
            self::SCOPE_DASHBOARD_READ => [
                'label' => 'Dashboard Read',
                'description' => 'Access dashboard and analytics summaries',
            ],
        ];
    }

    public static function sanitize_api_key_scopes($scopes) {
        if(is_string($scopes)) {
            $decoded = json_decode($scopes, true);
            if(is_array($decoded)) {
                $scopes = $decoded;
            }
        }

        $normalized = self::normalize_scope_list($scopes);
        if(empty($normalized)) {
            return self::get_default_api_key_scopes();
        }

        return $normalized;
    }

    public static function sanitize_api_key_expiration($expires_at) {
        return self::normalize_expiration_datetime($expires_at);
    }

    public static function sanitize_api_key_allowed_ips($allowed_ips) {
        return self::normalize_allowed_ip_list($allowed_ips);
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
    
    private static function requires_api_key($request) {
        $route = $request->get_route();
        if(self::is_public_route($route)) return false;

        return (bool) apply_filters('atlaspress_require_api_key', true, $request);
    }
    
    private static function requires_signature($request) {
        $route = $request->get_route();
        if(self::is_public_route($route)) return false;

        $enabled = (bool) apply_filters('atlaspress_require_request_signature', false, $request);
        if(!$enabled) return false;

        return in_array($request->get_method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private static function is_public_route($route) {
        $public_routes = apply_filters('atlaspress_public_routes', self::$default_public_routes);
        if(!is_array($public_routes)) {
            $public_routes = self::$default_public_routes;
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
    
    private static function resolve_api_key_from_request($request) {
        if(!$request || !is_object($request)) {
            return false;
        }

        if(method_exists($request, 'get_attribute')) {
            $cached = $request->get_attribute('atlaspress_resolved_api_key');
            if(is_array($cached) && isset($cached['name'], $cached['record'])) {
                return $cached;
            }
        }

        $api_key = self::extract_api_key($request);
        if($api_key === '') {
            return false;
        }

        $resolved = self::resolve_api_key_record($api_key);
        if(!$resolved) {
            return false;
        }

        if(method_exists($request, 'set_attribute')) {
            $request->set_attribute('atlaspress_resolved_api_key', $resolved);
        }

        return $resolved;
    }

    private static function extract_api_key($request) {
        $api_key = $request->get_header('X-API-Key');
        if(!is_string($api_key) || trim($api_key) === '') {
            $api_key = $request->get_param('api_key');
        }

        return is_string($api_key) ? trim($api_key) : '';
    }

    private static function resolve_api_key_record($api_key) {
        $hash = hash('sha256', (string) $api_key);
        $store = self::get_api_key_store();

        foreach($store as $name => $record) {
            if(!is_array($record) || empty($record['hash'])) {
                continue;
            }

            if(hash_equals((string) $record['hash'], $hash)) {
                return [
                    'name' => (string) $name,
                    'record' => $record,
                ];
            }
        }

        return false;
    }

    private static function get_api_key_store() {
        $stored = get_option(self::OPTION_API_KEYS, []);
        return self::normalize_api_key_store($stored);
    }

    private static function normalize_api_key_store($stored) {
        if(!is_array($stored)) {
            return [];
        }

        $normalized = [];
        foreach($stored as $name => $record) {
            $sanitized_name = sanitize_text_field((string) $name);
            if($sanitized_name === '') {
                continue;
            }

            $normalized[$sanitized_name] = self::normalize_api_key_record($record);
        }

        return $normalized;
    }

    private static function normalize_api_key_record($record) {
        // Legacy format: option value stored as raw hash string.
        if(is_string($record)) {
            return [
                'hash' => sanitize_text_field($record),
                'scopes' => [self::SCOPE_ALL],
                'created_at' => '',
                'last_used_at' => '',
                'expires_at' => '',
                'allowed_ips' => [],
                'status' => 'active',
                'legacy' => true,
            ];
        }

        if(!is_array($record)) {
            return [
                'hash' => '',
                'scopes' => [],
                'created_at' => '',
                'last_used_at' => '',
                'expires_at' => '',
                'allowed_ips' => [],
                'status' => 'disabled',
                'legacy' => false,
            ];
        }

        $hash = sanitize_text_field((string) ($record['hash'] ?? ''));
        $legacy = !empty($record['legacy']);
        $scopes = self::normalize_scope_list($record['scopes'] ?? []);
        if(empty($scopes)) {
            $scopes = $legacy ? [self::SCOPE_ALL] : self::get_default_api_key_scopes();
        }

        $status = strtolower(sanitize_text_field((string) ($record['status'] ?? 'active')));
        if(!in_array($status, ['active', 'disabled'], true)) {
            $status = 'active';
        }

        return [
            'hash' => $hash,
            'scopes' => $scopes,
            'created_at' => sanitize_text_field((string) ($record['created_at'] ?? '')),
            'last_used_at' => sanitize_text_field((string) ($record['last_used_at'] ?? '')),
            'expires_at' => self::normalize_expiration_datetime($record['expires_at'] ?? ''),
            'allowed_ips' => self::normalize_allowed_ip_list($record['allowed_ips'] ?? []),
            'status' => $status,
            'legacy' => $legacy,
        ];
    }

    private static function normalize_scope_list($scopes) {
        if(is_string($scopes)) {
            $scopes = preg_split('/[\s,]+/', $scopes);
        }

        if(!is_array($scopes)) {
            return [];
        }

        $known_scopes = array_keys(self::get_available_api_key_scopes());
        $known_scopes[] = self::SCOPE_ALL;
        $known_scopes = array_values(array_unique($known_scopes));

        $normalized = [];
        foreach($scopes as $scope) {
            $scope = strtolower(trim((string) $scope));
            if($scope === '') {
                continue;
            }

            if(!in_array($scope, $known_scopes, true)) {
                continue;
            }

            $normalized[] = $scope;
        }

        if(in_array(self::SCOPE_ALL, $normalized, true)) {
            return [self::SCOPE_ALL];
        }

        return array_values(array_unique($normalized));
    }

    private static function record_has_required_scopes($record, $required_scopes) {
        if(!is_array($required_scopes)) {
            $required_scopes = [$required_scopes];
        }

        $required_scopes = array_values(array_unique(array_filter(array_map(function($scope) {
            return strtolower(trim((string) $scope));
        }, $required_scopes))));

        if(empty($required_scopes)) {
            return true;
        }

        foreach($required_scopes as $scope) {
            if(!self::record_has_scope($record, $scope)) {
                return false;
            }
        }

        return true;
    }

    private static function record_has_scope($record, $scope) {
        if(!is_array($record)) {
            return false;
        }

        $record_scopes = self::normalize_scope_list($record['scopes'] ?? []);
        if(in_array(self::SCOPE_ALL, $record_scopes, true)) {
            return true;
        }

        $scope = strtolower(trim((string) $scope));
        if($scope === '') {
            return false;
        }

        if(in_array($scope, $record_scopes, true)) {
            return true;
        }

        if($scope === self::SCOPE_ENTRIES_READ && in_array(self::SCOPE_ENTRIES_WRITE, $record_scopes, true)) {
            return true;
        }

        return false;
    }

    private static function record_is_expired($record) {
        if(!is_array($record)) {
            return true;
        }

        $expires_at = trim((string) ($record['expires_at'] ?? ''));
        if($expires_at === '') {
            return false;
        }

        $expiry_ts = strtotime($expires_at);
        if($expiry_ts === false) {
            return false;
        }

        return $expiry_ts <= current_time('timestamp');
    }

    private static function request_ip_is_allowed_for_record($request, $record) {
        if(!is_array($record)) {
            return false;
        }

        $rules = self::normalize_allowed_ip_list($record['allowed_ips'] ?? []);
        if(empty($rules)) {
            return true;
        }

        $client_ip = self::get_request_client_ip($request);
        if($client_ip === '') {
            return false;
        }

        foreach($rules as $rule) {
            if(self::ip_matches_rule($client_ip, $rule)) {
                return true;
            }
        }

        return false;
    }

    private static function touch_api_key_usage($key_name, $record, $request) {
        if(!is_string($key_name) || $key_name === '' || !is_array($record)) {
            return;
        }

        $store = self::get_api_key_store();
        if(!isset($store[$key_name])) {
            return;
        }

        $now = current_time('mysql');
        $last_used = (string) ($store[$key_name]['last_used_at'] ?? '');
        if($last_used !== '' && substr($last_used, 0, 16) === substr($now, 0, 16)) {
            return;
        }

        $store[$key_name]['last_used_at'] = $now;
        update_option(self::OPTION_API_KEYS, $store);

        if(method_exists($request, 'set_attribute')) {
            $request->set_attribute('atlaspress_resolved_api_key', [
                'name' => $key_name,
                'record' => $store[$key_name],
            ]);
        }
    }
    
    private static function validate_signature($request) {
        $secret = (string) get_option('atlaspress_webhook_secret', '');
        if(trim($secret) === '') {
            return new \WP_Error('signature_not_configured', 'Request signing secret is not configured', ['status' => 401]);
        }

        $require_nonce = (bool) apply_filters('atlaspress_require_signature_nonce', true, $request);
        $allow_legacy = (bool) apply_filters('atlaspress_allow_legacy_signatures', false, $request);

        return self::verify_signed_request($request, $secret, [
            'require_nonce' => $require_nonce,
            'allow_legacy_signatures' => $allow_legacy,
            'replay_context' => (string) $request->get_route(),
        ]);
    }

    public static function has_signature_headers($request) {
        if(!$request || !is_object($request) || !method_exists($request, 'get_header')) {
            return false;
        }

        $signature = self::get_signature_header($request);
        $timestamp = self::get_timestamp_header($request);

        return $signature !== '' && $timestamp !== '';
    }

    public static function verify_signed_request($request, $secret, $args = []) {
        if(!$request || !is_object($request) || !method_exists($request, 'get_header') || !method_exists($request, 'get_body')) {
            return new \WP_Error('invalid_request', 'Invalid request context for signature verification', ['status' => 400]);
        }

        $secret = trim((string) $secret);
        if($secret === '') {
            return new \WP_Error('signature_secret_missing', 'Signing secret is not configured', ['status' => 401]);
        }

        $defaults = [
            'timestamp_tolerance' => 300,
            'require_nonce' => true,
            'replay_protection' => true,
            'replay_ttl' => 600,
            'allow_legacy_signatures' => false,
            'replay_context' => method_exists($request, 'get_route') ? (string) $request->get_route() : 'atlaspress',
        ];

        $args = wp_parse_args($args, $defaults);
        $tolerance = (int) $args['timestamp_tolerance'];
        $replay_ttl = (int) $args['replay_ttl'];
        $require_nonce = (bool) $args['require_nonce'];
        $replay_protection = (bool) $args['replay_protection'];
        $allow_legacy = (bool) $args['allow_legacy_signatures'];
        $replay_context = sanitize_key((string) $args['replay_context']);
        if($replay_context === '') {
            $replay_context = 'atlaspress';
        }

        $tolerance = (int) apply_filters('atlaspress_signature_tolerance_seconds', $tolerance, $request);
        $replay_ttl = (int) apply_filters('atlaspress_signature_replay_ttl_seconds', $replay_ttl, $request);
        $replay_protection = (bool) apply_filters('atlaspress_signature_replay_protection_enabled', $replay_protection, $request);

        if($tolerance <= 0) {
            $tolerance = 300;
        }
        if($replay_ttl <= 0) {
            $replay_ttl = $tolerance;
        }
        $replay_ttl = max($replay_ttl, $tolerance);

        $signature = self::normalize_signature(self::get_signature_header($request));
        $timestamp_raw = self::get_timestamp_header($request);
        $nonce = self::get_nonce_header($request);
        $body = (string) $request->get_body();

        if($signature === '') {
            return new \WP_Error('missing_signature', 'Missing signature header', ['status' => 401]);
        }

        if($timestamp_raw === '' || !preg_match('/^\d+$/', $timestamp_raw)) {
            return new \WP_Error('invalid_timestamp', 'Missing or invalid timestamp header', ['status' => 401]);
        }

        $timestamp = (int) $timestamp_raw;
        if($timestamp <= 0 || abs(time() - $timestamp) > $tolerance) {
            return new \WP_Error('expired_signature', 'Request timestamp is outside the allowed window', ['status' => 401]);
        }

        if($require_nonce && $nonce === '' && !$allow_legacy) {
            return new \WP_Error('missing_nonce', 'Missing nonce header for signed request', ['status' => 401]);
        }

        $valid = false;
        $expected = self::compute_request_signature($secret, $timestamp, $nonce, $body);
        if(hash_equals($expected, $signature)) {
            $valid = true;
        } elseif($allow_legacy) {
            $legacy_with_timestamp = hash_hmac('sha256', $timestamp_raw . $body, $secret);
            $legacy_body_only = hash_hmac('sha256', $body, $secret);
            $valid = hash_equals($legacy_with_timestamp, $signature) || hash_equals($legacy_body_only, $signature);
        }

        if(!$valid) {
            return new \WP_Error('invalid_signature', 'Invalid request signature', ['status' => 401]);
        }

        if(!$replay_protection) {
            return true;
        }

        $nonce_key_part = $nonce !== '' ? $nonce : 'legacy-' . substr($signature, 0, 24);
        $replay_key = 'atlaspress_sig_replay_' . md5($replay_context . '|' . $timestamp_raw . '|' . $nonce_key_part);
        if(get_transient($replay_key)) {
            return new \WP_Error('signature_replay', 'Replay attack detected', ['status' => 409]);
        }

        set_transient($replay_key, 1, $replay_ttl);
        return true;
    }

    public static function compute_request_signature($secret, $timestamp, $nonce, $body) {
        $payload = self::build_signing_payload($timestamp, $nonce, $body);
        return hash_hmac('sha256', $payload, (string) $secret);
    }

    private static function get_signature_header($request) {
        $headers = [
            'X-AtlasPress-Request-Signature',
            'X-AtlasPress-Signature',
            'X-Signature',
        ];

        foreach($headers as $header) {
            $value = trim((string) $request->get_header($header));
            if($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private static function get_timestamp_header($request) {
        $headers = [
            'X-AtlasPress-Timestamp',
            'X-Timestamp',
        ];

        foreach($headers as $header) {
            $value = trim((string) $request->get_header($header));
            if($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private static function get_nonce_header($request) {
        $headers = [
            'X-AtlasPress-Nonce',
            'X-Nonce',
        ];

        foreach($headers as $header) {
            $value = trim((string) $request->get_header($header));
            if($value !== '') {
                return sanitize_text_field($value);
            }
        }

        return '';
    }

    private static function normalize_signature($signature) {
        $signature = trim((string) $signature);
        if($signature === '') {
            return '';
        }

        if(stripos($signature, 'sha256=') === 0) {
            $signature = substr($signature, 7);
        }

        return strtolower($signature);
    }

    private static function build_signing_payload($timestamp, $nonce, $body) {
        $timestamp = (string) $timestamp;
        $nonce = (string) $nonce;
        $body = (string) $body;

        return $timestamp . '.' . $nonce . '.' . $body;
    }
    
    private static function get_bearer_token() {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
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
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed_origin = self::get_allowed_origin_header_value($origin);

        if($allowed_origin === null) {
            return;
        }

        header('Access-Control-Allow-Origin: ' . $allowed_origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization, X-API-Key, X-Signature, X-Timestamp, X-Nonce, X-AtlasPress-Request-Signature, X-AtlasPress-Signature, X-AtlasPress-Timestamp, X-AtlasPress-Nonce');
    }

    private static function get_allowed_origin_header_value($origin) {
        if(!is_string($origin) || trim($origin) === '') {
            return null;
        }

        $normalized_origin = self::normalize_origin($origin);
        if($normalized_origin === null) {
            return null;
        }

        $allowed_origins = self::get_allowed_origins();
        if(empty($allowed_origins)) {
            $site_origin = self::get_site_origin();
            if($site_origin !== null && hash_equals($site_origin, $normalized_origin)) {
                return $normalized_origin;
            }
            return null;
        }

        if(in_array($normalized_origin, $allowed_origins, true)) {
            return $normalized_origin;
        }

        return null;
    }

    private static function is_origin_allowed($origin) {
        return self::get_allowed_origin_header_value($origin) !== null;
    }

    private static function get_allowed_origins() {
        $stored = get_option('atlaspress_allowed_origins', []);
        if(!is_array($stored)) {
            return [];
        }

        $normalized = [];
        foreach($stored as $origin) {
            $value = self::normalize_origin($origin);
            if($value !== null) {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    private static function get_site_origin() {
        return self::normalize_origin(home_url());
    }

    private static function normalize_origin($origin) {
        if(!is_string($origin)) {
            return null;
        }

        $origin = trim($origin);
        if($origin === '') {
            return null;
        }

        $parts = wp_parse_url($origin);
        if($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        if(!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($parts['host']);
        $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

        return $scheme . '://' . $host . $port;
    }

    public static function send_cors_headers() {
        // Ensure CORS headers are sent even on auth errors for AtlasPress routes
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/wp-json/atlaspress/v1/') !== false) {
            self::add_cors_headers();
        }
    }

    public static function get_signature_policy() {
        return [
            'require_signed_form_capture' => self::get_bool_option(self::OPTION_REQUIRE_SIGNED_FORM_CAPTURE, true),
            'require_signed_hubspot_webhook' => self::get_bool_option(self::OPTION_REQUIRE_SIGNED_HUBSPOT_WEBHOOK, true),
            'allow_legacy_signed_ingest' => self::get_bool_option(self::OPTION_ALLOW_LEGACY_SIGNED_INGEST, false),
        ];
    }

    public static function set_signature_policy($policy) {
        if(!is_array($policy)) {
            return self::get_signature_policy();
        }

        if(array_key_exists('require_signed_form_capture', $policy)) {
            update_option(self::OPTION_REQUIRE_SIGNED_FORM_CAPTURE, self::normalize_bool_value($policy['require_signed_form_capture']) ? '1' : '0');
        }

        if(array_key_exists('require_signed_hubspot_webhook', $policy)) {
            update_option(self::OPTION_REQUIRE_SIGNED_HUBSPOT_WEBHOOK, self::normalize_bool_value($policy['require_signed_hubspot_webhook']) ? '1' : '0');
        }

        if(array_key_exists('allow_legacy_signed_ingest', $policy)) {
            update_option(self::OPTION_ALLOW_LEGACY_SIGNED_INGEST, self::normalize_bool_value($policy['allow_legacy_signed_ingest']) ? '1' : '0');
        }

        return self::get_signature_policy();
    }
    
    public static function generate_api_key($name = '', $scopes = [], $options = []) {
        $key = bin2hex(random_bytes(32));
        $store = self::get_api_key_store();

        $name = self::build_unique_api_key_name($name, $store);
        $scopes = self::normalize_scope_list($scopes);
        if(empty($scopes)) {
            $scopes = self::get_default_api_key_scopes();
        }

        if(!is_array($options)) {
            $options = [];
        }

        $expires_at = self::normalize_expiration_datetime($options['expires_at'] ?? '');
        $allowed_ips = self::normalize_allowed_ip_list($options['allowed_ips'] ?? []);

        $store[$name] = [
            'hash' => hash('sha256', $key),
            'scopes' => $scopes,
            'created_at' => current_time('mysql'),
            'last_used_at' => '',
            'expires_at' => $expires_at,
            'allowed_ips' => $allowed_ips,
            'status' => 'active',
            'legacy' => false,
        ];

        update_option(self::OPTION_API_KEYS, $store);
        return $key;
    }

    public static function rotate_api_key($name) {
        $name = sanitize_text_field((string) $name);
        if($name === '') {
            return false;
        }

        $store = self::get_api_key_store();
        if(!isset($store[$name])) {
            return false;
        }

        $record = self::normalize_api_key_record($store[$name]);
        $new_key = bin2hex(random_bytes(32));
        $record['hash'] = hash('sha256', $new_key);
        $record['last_used_at'] = '';
        $record['legacy'] = false;
        if(empty($record['created_at'])) {
            $record['created_at'] = current_time('mysql');
        }

        $store[$name] = $record;
        update_option(self::OPTION_API_KEYS, $store);

        return [
            'name' => $name,
            'api_key' => $new_key,
        ];
    }

    public static function update_api_key_constraints($name, $options = []) {
        $name = sanitize_text_field((string) $name);
        if($name === '') {
            return false;
        }

        if(!is_array($options)) {
            $options = [];
        }

        $store = self::get_api_key_store();
        if(!isset($store[$name])) {
            return false;
        }

        $record = self::normalize_api_key_record($store[$name]);
        if(array_key_exists('expires_at', $options)) {
            $record['expires_at'] = self::normalize_expiration_datetime($options['expires_at']);
        }

        if(array_key_exists('allowed_ips', $options)) {
            $record['allowed_ips'] = self::normalize_allowed_ip_list($options['allowed_ips']);
        }

        $store[$name] = $record;
        update_option(self::OPTION_API_KEYS, $store);

        return true;
    }

    public static function set_api_key_status($name, $status) {
        $name = sanitize_text_field((string) $name);
        $status = strtolower(sanitize_text_field((string) $status));
        if($name === '' || !in_array($status, ['active', 'disabled'], true)) {
            return false;
        }

        $store = self::get_api_key_store();
        if(!isset($store[$name])) {
            return false;
        }

        $record = self::normalize_api_key_record($store[$name]);
        $record['status'] = $status;
        $store[$name] = $record;
        update_option(self::OPTION_API_KEYS, $store);

        return true;
    }

    public static function delete_api_key($name) {
        $name = sanitize_text_field((string) $name);
        if($name === '') {
            return false;
        }

        $store = self::get_api_key_store();
        if(!isset($store[$name])) {
            return false;
        }

        unset($store[$name]);
        update_option(self::OPTION_API_KEYS, $store);
        return true;
    }

    public static function get_api_key_details() {
        $store = self::get_api_key_store();
        if(empty($store)) {
            return [];
        }

        ksort($store);
        $details = [];
        foreach($store as $name => $record) {
            $details[] = [
                'name' => $name,
                'status' => sanitize_text_field((string) ($record['status'] ?? 'active')),
                'scopes' => self::normalize_scope_list($record['scopes'] ?? []),
                'created_at' => sanitize_text_field((string) ($record['created_at'] ?? '')),
                'last_used_at' => sanitize_text_field((string) ($record['last_used_at'] ?? '')),
                'expires_at' => sanitize_text_field((string) ($record['expires_at'] ?? '')),
                'allowed_ips' => self::normalize_allowed_ip_list($record['allowed_ips'] ?? []),
                'is_expired' => self::record_is_expired($record),
                'legacy' => !empty($record['legacy']),
            ];
        }

        return $details;
    }

    public static function get_default_api_key_scopes() {
        return [
            self::SCOPE_ENTRIES_READ,
            self::SCOPE_ENTRIES_WRITE,
        ];
    }

    private static function build_unique_api_key_name($name, $store) {
        $name = sanitize_text_field((string) $name);
        if($name === '') {
            $name = 'key_' . time();
        }

        $candidate = $name;
        $counter = 2;
        while(is_array($store) && array_key_exists($candidate, $store)) {
            $candidate = $name . '_' . $counter;
            $counter++;
        }

        return $candidate;
    }
    
    public static function set_allowed_origins($origins) {
        if(!is_array($origins)) {
            $origins = [];
        }

        $normalized = [];
        foreach($origins as $origin) {
            $value = self::normalize_origin($origin);
            if($value !== null) {
                $normalized[] = $value;
            }
        }

        update_option('atlaspress_allowed_origins', array_values(array_unique($normalized)));
    }

    private static function normalize_expiration_datetime($expires_at) {
        if(!is_string($expires_at)) {
            return '';
        }

        $expires_at = trim($expires_at);
        if($expires_at === '') {
            return '';
        }

        $timestamp = strtotime($expires_at);
        if($timestamp === false) {
            return '';
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private static function normalize_allowed_ip_list($allowed_ips) {
        if(is_string($allowed_ips)) {
            $decoded = json_decode($allowed_ips, true);
            if(is_array($decoded)) {
                $allowed_ips = $decoded;
            } else {
                $allowed_ips = preg_split('/[\r\n,]+/', $allowed_ips);
            }
        }

        if(!is_array($allowed_ips)) {
            return [];
        }

        $normalized = [];
        foreach($allowed_ips as $rule) {
            $rule = strtolower(trim((string) $rule));
            if($rule === '') {
                continue;
            }

            if(self::is_valid_ip_rule($rule)) {
                $normalized[] = $rule;
            }
        }

        return array_values(array_unique($normalized));
    }

    private static function is_valid_ip_rule($rule) {
        if($rule === '') {
            return false;
        }

        if(filter_var($rule, FILTER_VALIDATE_IP)) {
            return true;
        }

        return self::parse_cidr_rule($rule) !== false;
    }

    private static function get_request_client_ip($request) {
        $headers = [
            'X-Forwarded-For',
            'X-Real-IP',
            'CF-Connecting-IP',
        ];

        foreach($headers as $header) {
            $value = '';
            if($request && is_object($request) && method_exists($request, 'get_header')) {
                $value = (string) $request->get_header($header);
            }

            if($value === '' && isset($_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $header))])) {
                $value = (string) $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $header))];
            }

            if($value === '') {
                continue;
            }

            $parts = explode(',', $value);
            $ip = trim((string) $parts[0]);
            if(filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        $remote = $_SERVER['REMOTE_ADDR'] ?? '';
        $remote = trim((string) $remote);
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : '';
    }

    private static function ip_matches_rule($ip, $rule) {
        if(!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        if(filter_var($rule, FILTER_VALIDATE_IP)) {
            $ip_bin = @inet_pton($ip);
            $rule_bin = @inet_pton($rule);
            if($ip_bin !== false && $rule_bin !== false) {
                return hash_equals($rule_bin, $ip_bin);
            }

            return hash_equals(strtolower($rule), strtolower($ip));
        }

        return self::ip_in_cidr($ip, $rule);
    }

    private static function parse_cidr_rule($rule) {
        $rule = trim((string) $rule);
        if($rule === '' || strpos($rule, '/') === false) {
            return false;
        }

        list($network, $bits) = explode('/', $rule, 2);
        $network = trim($network);
        $bits = trim($bits);

        if(!filter_var($network, FILTER_VALIDATE_IP) || !is_numeric($bits)) {
            return false;
        }

        $max_bits = strpos($network, ':') !== false ? 128 : 32;
        $bits = (int) $bits;
        if($bits < 0 || $bits > $max_bits) {
            return false;
        }

        return [$network, $bits, $max_bits];
    }

    private static function ip_in_cidr($ip, $rule) {
        $parsed = self::parse_cidr_rule($rule);
        if($parsed === false) {
            return false;
        }

        list($network, $bits, $max_bits) = $parsed;

        $ip_bin = @inet_pton($ip);
        $network_bin = @inet_pton($network);
        if($ip_bin === false || $network_bin === false || strlen($ip_bin) !== strlen($network_bin)) {
            return false;
        }

        $expected_size = $max_bits === 128 ? 16 : 4;
        if(strlen($ip_bin) !== $expected_size) {
            return false;
        }

        $full_bytes = intdiv($bits, 8);
        $remaining_bits = $bits % 8;

        if($full_bytes > 0 && substr($ip_bin, 0, $full_bytes) !== substr($network_bin, 0, $full_bytes)) {
            return false;
        }

        if($remaining_bits === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $remaining_bits)) & 0xFF;
        $ip_byte = ord($ip_bin[$full_bytes]);
        $network_byte = ord($network_bin[$full_bytes]);

        return ($ip_byte & $mask) === ($network_byte & $mask);
    }

    private static function get_bool_option($key, $default = false) {
        $value = get_option($key, $default ? '1' : '0');
        return self::normalize_bool_value($value);
    }

    private static function normalize_bool_value($value) {
        if(is_bool($value)) {
            return $value;
        }

        if(is_numeric($value)) {
            return ((int) $value) === 1;
        }

        if(!is_string($value)) {
            return false;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }
}
