<?php
namespace AtlasPress\Admin\Pages;

use AtlasPress\Core\ApiSecurity;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SecuritySettings {
    
    public static function render() {
        if($_POST['action'] ?? '' === 'save_security_settings') {
            self::save_settings();
        }
        
        $api_keys = get_option('atlaspress_api_keys', []);
        $allowed_origins = get_option('atlaspress_allowed_origins', []);
        $webhook_secret = get_option('atlaspress_webhook_secret', '');
        
        ?>
        <div class="wrap">
            <h1>Security Settings</h1>
            
            <form method="post" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <input type="hidden" name="action" value="save_security_settings">
                
                <h2>🔑 API Keys</h2>
                <div id="atlaspress-security-app" 
                     data-keys="<?php echo esc_attr(wp_json_encode(array_keys($api_keys))); ?>" 
                     data-origins="<?php echo esc_attr(wp_json_encode($allowed_origins)); ?>" 
                     data-secret="<?php echo esc_attr($webhook_secret ? 'Set' : 'Not Set'); ?>"></div>
                
                <button type="submit" class="button button-primary">Save Settings</button>
            </form>
        </div>
        <?php
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