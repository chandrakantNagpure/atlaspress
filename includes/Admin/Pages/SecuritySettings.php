<?php
namespace AtlasPress\Admin\Pages;

use AtlasPress\Core\ApiSecurity;

class SecuritySettings {
    
    public static function render() {
        if($_POST['action'] ?? '' === 'save_security_settings') {
            self::save_settings();
        }
        
        $api_keys = get_option('atlaspress_api_keys', []);
        $allowed_origins = get_option('atlaspress_allowed_origins', []);
        $webhook_secret = get_option('atlaspress_webhook_secret', '');
        
        echo '<div class="wrap">';
        echo '<h1>Security Settings</h1>';
        echo '<div id="atlaspress-security-app" data-keys="' . esc_attr(wp_json_encode(array_keys($api_keys))) . '" data-origins="' . esc_attr(wp_json_encode($allowed_origins)) . '" data-secret="' . esc_attr($webhook_secret ? 'Set' : 'Not Set') . '"></div>';
        echo '</div>';
    }
    
    private static function save_settings() {
        if(!current_user_can('manage_options')) return;
        
        // Save allowed origins
        $origins = array_filter(array_map('trim', explode("\n", $_POST['allowed_origins'] ?? '')));
        ApiSecurity::set_allowed_origins($origins);
        
        // Generate webhook secret if not exists
        if(empty(get_option('atlaspress_webhook_secret'))) {
            update_option('atlaspress_webhook_secret', bin2hex(random_bytes(32)));
        }
        
        // Generate new API key if requested
        if(!empty($_POST['generate_api_key'])) {
            $key_name = sanitize_text_field($_POST['api_key_name'] ?: 'Generated Key');
            $new_key = ApiSecurity::generate_api_key($key_name);
            set_transient('atlaspress_new_api_key', $new_key, 300); // Show for 5 minutes
        }
        
        echo '<div class="notice notice-success"><p>Security settings saved!</p></div>';
    }
}