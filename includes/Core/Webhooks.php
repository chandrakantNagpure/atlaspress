<?php
namespace AtlasPress\Core;

class Webhooks {

    const RETRY_HOOK = 'atlaspress_webhook_retry_event';
    const RETRY_JOB_PREFIX = 'atlaspress_webhook_retry_job_';
    const RETRY_LOG_OPTION = 'atlaspress_webhook_retry_log';

    private static $hooks = [];

    public static function init() {
        add_action('atlaspress_entry_created', [self::class, 'trigger_created'], 10, 1);
        add_action('atlaspress_entry_updated', [self::class, 'trigger_updated'], 10, 1);
        add_action('atlaspress_entry_deleted', [self::class, 'trigger_deleted'], 10, 1);
        add_action(self::RETRY_HOOK, [self::class, 'process_retry_job'], 10, 1);
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
        $event = sanitize_key((string) $event);
        $url = esc_url_raw((string) $url);
        $secret = sanitize_text_field((string) $secret);
        if($event === '' || $url === '' || !wp_http_validate_url($url)) {
            return false;
        }

        $stored_hooks = get_option('atlaspress_webhooks', []);
        if(!is_array($stored_hooks)) {
            $stored_hooks = [];
        }

        if(!isset($stored_hooks[$event]) || !is_array($stored_hooks[$event])) {
            $stored_hooks[$event] = [];
        }

        $stored_hooks[$event][] = [
            'url' => $url,
            'secret' => $secret
        ];

        self::$hooks = $stored_hooks;
        update_option('atlaspress_webhooks', $stored_hooks);
        return true;
    }

    public static function trigger($event, $data) {
        $hooks = get_option('atlaspress_webhooks', []);

        if(!is_array($hooks) || !isset($hooks[$event]) || !is_array($hooks[$event])) return;

        foreach($hooks[$event] as $hook) {
            $hook_config = self::normalize_hook_config($hook);
            if($hook_config === false) {
                continue;
            }

            self::dispatch_webhook($event, $data, $hook_config, 1);
        }
    }

    public static function process_retry_job($job_id) {
        if(!is_string($job_id) || $job_id === '') {
            return;
        }

        $transient_key = self::RETRY_JOB_PREFIX . sanitize_key($job_id);
        $job = get_transient($transient_key);
        delete_transient($transient_key);

        if(!is_array($job)) {
            return;
        }

        $event = sanitize_key((string) ($job['event'] ?? ''));
        $attempt = max(1, (int) ($job['attempt'] ?? 1));
        $data = $job['data'] ?? [];
        $hook = self::normalize_hook_config($job['hook'] ?? []);

        if($event === '' || $hook === false) {
            return;
        }

        if(!self::retry_is_enabled($event, $hook)) {
            return;
        }

        self::dispatch_webhook($event, $data, $hook, $attempt);
    }

    public static function clear_scheduled_retries() {
        wp_clear_scheduled_hook(self::RETRY_HOOK);
    }

    public static function getHooks() {
        return get_option('atlaspress_webhooks', []);
    }

    public static function get_retry_log() {
        $log = get_option(self::RETRY_LOG_OPTION, []);
        return is_array($log) ? $log : [];
    }

    private static function normalize_hook_config($hook) {
        if(!is_array($hook)) {
            return false;
        }

        $url = esc_url_raw((string) ($hook['url'] ?? ''));
        if($url === '' || !wp_http_validate_url($url)) {
            return false;
        }

        return [
            'url' => $url,
            'secret' => sanitize_text_field((string) ($hook['secret'] ?? '')),
        ];
    }

    private static function dispatch_webhook($event, $data, $hook, $attempt = 1) {
        $request = self::build_request_args($event, $data, $hook);
        $response = wp_remote_post($hook['url'], $request);

        if(self::is_success_response($response)) {
            if($attempt > 1) {
                self::append_retry_log([
                    'event' => sanitize_key((string) $event),
                    'url' => esc_url_raw((string) $hook['url']),
                    'attempt' => (int) $attempt,
                    'status' => 'success',
                    'status_code' => self::get_response_status_code($response),
                    'message' => 'Webhook delivered after retry',
                ]);
            }

            return true;
        }

        $max_attempts = self::get_max_attempts();
        $retry_scheduled = false;

        if(
            self::is_retryable_response($response)
            && self::retry_is_enabled($event, $hook)
            && $attempt < $max_attempts
        ) {
            $retry_scheduled = self::schedule_retry_job($event, $data, $hook, $attempt + 1);
        }

        self::append_retry_log([
            'event' => sanitize_key((string) $event),
            'url' => esc_url_raw((string) $hook['url']),
            'attempt' => (int) $attempt,
            'status' => 'failed',
            'status_code' => self::get_response_status_code($response),
            'message' => self::get_response_error_message($response),
            'retry_scheduled' => $retry_scheduled ? 1 : 0,
        ]);

        return false;
    }

    private static function build_request_args($event, $data, $hook) {
        $timestamp = time();
        $payload = [
            'event' => $event,
            'data' => $data,
            'timestamp' => $timestamp
        ];
        $body = wp_json_encode($payload);
        if(!is_string($body)) {
            $body = '{}';
        }

        $headers = [
            'Content-Type' => 'application/json',
            'X-AtlasPress-Event' => (string) $event,
        ];

        $secret = trim((string) ($hook['secret'] ?? ''));
        if($secret !== '') {
            $nonce = wp_generate_uuid4();
            $legacy_data_body = wp_json_encode($data);
            if(!is_string($legacy_data_body)) {
                $legacy_data_body = '{}';
            }

            $headers['X-AtlasPress-Timestamp'] = (string) $timestamp;
            $headers['X-AtlasPress-Nonce'] = $nonce;
            $headers['X-AtlasPress-Request-Signature'] = ApiSecurity::compute_request_signature($secret, $timestamp, $nonce, $body);

            // Backward-compatible signature headers for existing consumers.
            $headers['X-AtlasPress-Signature'] = hash_hmac('sha256', $legacy_data_body, $secret);
            $headers['X-Signature'] = hash_hmac('sha256', (string) $timestamp . $body, $secret);
            $headers['X-Timestamp'] = (string) $timestamp;
        }

        $timeout = (int) apply_filters('atlaspress_webhook_timeout', 15, $event, $hook);
        if($timeout <= 0) {
            $timeout = 15;
        }

        return [
            'body' => $body,
            'headers' => $headers,
            'timeout' => $timeout,
        ];
    }

    private static function is_success_response($response) {
        if(is_wp_error($response)) {
            return false;
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        return $status_code >= 200 && $status_code < 300;
    }

    private static function is_retryable_response($response) {
        if(is_wp_error($response)) {
            return true;
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        if($status_code === 0) {
            return true;
        }

        return in_array($status_code, [408, 425, 429, 500, 502, 503, 504], true);
    }

    private static function get_response_status_code($response) {
        if(is_wp_error($response)) {
            return 0;
        }

        return (int) wp_remote_retrieve_response_code($response);
    }

    private static function get_response_error_message($response) {
        if(is_wp_error($response)) {
            return sanitize_text_field((string) $response->get_error_message());
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $status_message = sanitize_text_field((string) wp_remote_retrieve_response_message($response));
        if($status_message === '') {
            $status_message = 'HTTP ' . $status_code;
        }

        return $status_message;
    }

    private static function retry_is_enabled($event, $hook) {
        $default = ProVersion::is_pro_active();
        return (bool) apply_filters('atlaspress_webhook_retry_enabled', $default, $event, $hook);
    }

    private static function get_max_attempts() {
        $max_attempts = (int) apply_filters('atlaspress_webhook_retry_max_attempts', 3);
        if($max_attempts < 1) {
            $max_attempts = 1;
        }

        return $max_attempts;
    }

    private static function get_retry_delay_seconds($next_attempt) {
        $base_delay = (int) apply_filters('atlaspress_webhook_retry_base_delay', 60);
        $max_delay = (int) apply_filters('atlaspress_webhook_retry_max_delay', 900);

        if($base_delay < 15) {
            $base_delay = 15;
        }
        if($max_delay < $base_delay) {
            $max_delay = $base_delay;
        }

        $next_attempt = max(2, (int) $next_attempt);
        $multiplier = 2 ** ($next_attempt - 2);
        $delay = (int) ($base_delay * $multiplier);

        return min($delay, $max_delay);
    }

    private static function get_retry_job_ttl_seconds() {
        $ttl = (int) apply_filters('atlaspress_webhook_retry_job_ttl', DAY_IN_SECONDS);
        if($ttl < HOUR_IN_SECONDS) {
            $ttl = HOUR_IN_SECONDS;
        }

        return $ttl;
    }

    private static function schedule_retry_job($event, $data, $hook, $next_attempt) {
        $delay = self::get_retry_delay_seconds($next_attempt);
        $job_id = wp_generate_uuid4();
        $job_payload = [
            'event' => sanitize_key((string) $event),
            'data' => self::normalize_job_data($data),
            'hook' => $hook,
            'attempt' => max(1, (int) $next_attempt),
        ];

        $transient_key = self::RETRY_JOB_PREFIX . sanitize_key($job_id);
        set_transient($transient_key, $job_payload, self::get_retry_job_ttl_seconds());

        $scheduled_at = time() + $delay;
        wp_schedule_single_event($scheduled_at, self::RETRY_HOOK, [$job_id]);
        $is_scheduled = wp_next_scheduled(self::RETRY_HOOK, [$job_id]);

        if(!$is_scheduled) {
            delete_transient($transient_key);
            return false;
        }

        return true;
    }

    private static function normalize_job_data($data) {
        $json = wp_json_encode($data);
        if(!is_string($json)) {
            return [];
        }

        $decoded = json_decode($json, true);
        if(is_array($decoded)) {
            return $decoded;
        }

        return [];
    }

    private static function append_retry_log($entry) {
        $log = get_option(self::RETRY_LOG_OPTION, []);
        if(!is_array($log)) {
            $log = [];
        }

        $log_entry = is_array($entry) ? $entry : [];
        $log_entry['recorded_at'] = current_time('mysql');
        $log[] = $log_entry;

        $max_entries = (int) apply_filters('atlaspress_webhook_retry_log_max_entries', 100);
        if($max_entries < 10) {
            $max_entries = 10;
        }

        if(count($log) > $max_entries) {
            $log = array_slice($log, -$max_entries);
        }

        update_option(self::RETRY_LOG_OPTION, $log, false);
    }
}
