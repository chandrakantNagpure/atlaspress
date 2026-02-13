<?php
namespace AtlasPress\Pro;

class LicenseManager {

    const OPTION_KEY = 'atlaspress_pro_license_key';
    const OPTION_STATUS = 'atlaspress_pro_license_status';
    const OPTION_EXPIRES = 'atlaspress_pro_license_expires';
    const LEGACY_OPTION_KEY = 'atlaspress_pro_license';
    const OPTION_SERVER_URL = 'atlaspress_license_server_url';
    const CRON_HOOK = 'atlaspress_daily_license_check';

    private static $default_license_server = '';

    public static function init() {
        self::migrate_legacy_license();

        add_action('admin_init', [self::class, 'migrate_legacy_license']);
        add_action(self::CRON_HOOK, [self::class, 'check_license']);
        add_action('wp_ajax_atlaspress_check_license', [self::class, 'ajax_check_license']);

        self::maybe_schedule_license_check();
    }

    public static function migrate_legacy_license() {
        $legacy_key = get_option(self::LEGACY_OPTION_KEY, '');
        $current_key = get_option(self::OPTION_KEY, '');

        if (!empty($legacy_key) && empty($current_key)) {
            update_option(self::OPTION_KEY, $legacy_key);
            update_option(self::OPTION_STATUS, 'active');
        }
    }

    public static function maybe_schedule_license_check() {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::CRON_HOOK);
        }
    }

    public static function clear_scheduled_checks() {
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    public static function ajax_check_license() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        check_ajax_referer('atlaspress_license_action', 'nonce');

        $license_key = sanitize_text_field($_POST['license'] ?? '');
        $result = self::activate_license($license_key);

        if ($result['success']) {
            wp_send_json_success([
                'message' => $result['message'],
                'license' => self::get_license_info(),
            ]);
        }

        wp_send_json_error($result['message']);
    }

    public static function is_pro_active() {
        $license = get_option(self::OPTION_KEY);
        $status = get_option(self::OPTION_STATUS);
        
        return !empty($license) && $status === 'active';
    }
    
    public static function activate_license($license_key) {
        $license_key = sanitize_text_field($license_key);
        if ($license_key === '') {
            return ['success' => false, 'message' => 'License key is required'];
        }

        if (self::is_test_license_key($license_key)) {
            if (self::allow_test_license_keys()) {
                self::persist_active_license($license_key, date('Y-m-d', strtotime('+1 year')));
                return ['success' => true, 'message' => 'Test license activated for local development'];
            }

            return ['success' => false, 'message' => 'Test license keys are disabled for this environment. Set WP_ENVIRONMENT_TYPE to local/staging or use a real license key.'];
        }

        $server_url = self::get_license_server_url();
        if ($server_url === '') {
            return ['success' => false, 'message' => self::get_license_server_setup_message()];
        }

        $response = wp_remote_post($server_url . '/activate', [
            'body' => [
                'license_key' => $license_key,
                'site_url' => get_site_url(),
                'product' => 'atlaspress-pro'
            ],
            'timeout' => 15
        ]);
        
        if (is_wp_error($response)) {
            return ['success' => false, 'message' => self::format_connection_error_message($response, $server_url)];
        }
        
        $body = self::decode_response($response);
        
        if (self::is_successful_response($body)) {
            self::persist_active_license($license_key, sanitize_text_field($body['expires'] ?? ''));
            return ['success' => true, 'message' => 'License activated successfully!'];
        }
        
        return ['success' => false, 'message' => $body['message'] ?? 'Invalid license key'];
    }
    
    public static function deactivate_license() {
        $license_key = get_option(self::OPTION_KEY);
        
        if (empty($license_key)) {
            return ['success' => false, 'message' => 'No license key found'];
        }
        
        $server_url = self::get_license_server_url();
        if ($server_url !== '' && !(self::is_test_license_key($license_key) && self::allow_test_license_keys())) {
            wp_remote_post($server_url . '/deactivate', [
                'body' => [
                    'license_key' => $license_key,
                    'site_url' => get_site_url()
                ],
                'timeout' => 15
            ]);
        }
        
        delete_option(self::OPTION_KEY);
        delete_option(self::OPTION_STATUS);
        delete_option(self::OPTION_EXPIRES);
        delete_option(self::LEGACY_OPTION_KEY);
        
        return ['success' => true, 'message' => 'License deactivated'];
    }
    
    public static function check_license() {
        $license_key = get_option(self::OPTION_KEY);
        
        if (empty($license_key)) {
            return;
        }

        if (self::is_test_license_key($license_key)) {
            update_option(self::OPTION_STATUS, self::allow_test_license_keys() ? 'active' : 'test_key_not_allowed');
            return;
        }

        $server_url = self::get_license_server_url();
        if ($server_url === '') {
            update_option(self::OPTION_STATUS, 'invalid_config');
            return;
        }
        
        $response = wp_remote_post($server_url . '/check', [
            'body' => [
                'license_key' => $license_key,
                'site_url' => get_site_url()
            ],
            'timeout' => 15
        ]);
        
        if (!is_wp_error($response)) {
            $body = self::decode_response($response);
            if (!is_array($body)) {
                return;
            }

            if (isset($body['status'])) {
                update_option(self::OPTION_STATUS, sanitize_text_field($body['status']));
            } elseif (self::is_successful_response($body)) {
                update_option(self::OPTION_STATUS, 'active');
            } else {
                update_option(self::OPTION_STATUS, 'invalid');
            }

            if (isset($body['expires'])) {
                update_option(self::OPTION_EXPIRES, sanitize_text_field($body['expires']));
            }
        }
    }
    
    public static function get_license_info() {
        return [
            'key' => get_option(self::OPTION_KEY),
            'status' => get_option(self::OPTION_STATUS),
            'expires' => get_option(self::OPTION_EXPIRES)
        ];
    }

    private static function get_license_server_url() {
        $constant_url = defined('ATLASPRESS_LICENSE_SERVER_URL') ? (string) ATLASPRESS_LICENSE_SERVER_URL : '';
        $option_url = (string) get_option(self::OPTION_SERVER_URL, '');
        $base_url = $constant_url !== '' ? $constant_url : ($option_url !== '' ? $option_url : self::$default_license_server);
        $url = apply_filters('atlaspress_license_server_url', $base_url);
        $url = untrailingslashit((string) $url);

        if ($url === '') return '';
        if (!wp_http_validate_url($url)) return '';

        $host = wp_parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '' || self::is_placeholder_license_host($host)) {
            return '';
        }

        return $url;
    }

    private static function get_license_server_setup_message() {
        return 'License server is not configured. Set ATLASPRESS_LICENSE_SERVER_URL (for example https://licenses.example.com/api/v1) or atlaspress_license_server_url filter.';
    }

    private static function format_connection_error_message($response, $server_url) {
        $message = $response->get_error_message();
        $host = wp_parse_url($server_url, PHP_URL_HOST);
        $safe_host = is_string($host) && $host !== '' ? sanitize_text_field($host) : 'unknown host';

        if (is_string($message) && stripos($message, 'Could not resolve host') !== false) {
            return 'Connection error: could not resolve license server host "' . $safe_host . '". Verify ATLASPRESS_LICENSE_SERVER_URL points to a real domain.';
        }

        return 'Connection error: ' . $message;
    }

    private static function is_placeholder_license_host($host) {
        $host = strtolower((string) $host);
        if ($host === '') {
            return true;
        }

        return $host === 'your-license-server.com'
            || str_ends_with($host, '.your-license-server.com');
    }

    private static function decode_response($response) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($body) ? $body : [];
    }

    private static function is_successful_response($body) {
        if (!is_array($body)) return false;

        if (!empty($body['success'])) return true;
        if (!empty($body['valid'])) return true;

        return false;
    }

    private static function persist_active_license($license_key, $expires = '') {
        update_option(self::OPTION_KEY, $license_key);
        update_option(self::OPTION_STATUS, 'active');
        update_option(self::OPTION_EXPIRES, sanitize_text_field((string) $expires));
        update_option(self::LEGACY_OPTION_KEY, $license_key);
    }

    private static function is_test_license_key($license_key) {
        return is_string($license_key) && preg_match('/^TEST-[A-Z0-9-]+$/i', $license_key) === 1;
    }

    private static function allow_test_license_keys() {
        $environment = wp_get_environment_type();
        $site_host = strtolower((string) wp_parse_url(get_site_url(), PHP_URL_HOST));
        $is_local_host = in_array($site_host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($site_host, '.local')
            || str_ends_with($site_host, '.test')
            || str_ends_with($site_host, '.localhost');

        $default = $environment !== 'production' || $is_local_host;
        return (bool) apply_filters('atlaspress_allow_test_license_keys', $default);
    }
}
