<?php
namespace AtlasPress\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AtlasPress\Core\ApiSecurity;

class SecuritySettings {
    
    public static function render() {
        $action = isset($_POST['action']) ? sanitize_key(wp_unslash($_POST['action'])) : '';
        if($action === 'save_security_settings') {
            self::save_settings();
        }
        
        $api_keys = get_option('atlasly_api_keys', []);
        $allowed_origins = get_option('atlasly_allowed_origins', []);
        $webhook_secret = get_option('atlasly_webhook_secret', '');
        
        ?>
        <div class="wrap">
            <h1>Security Settings</h1>
            
            <form method="post" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <input type="hidden" name="action" value="save_security_settings">
                <?php wp_nonce_field('atlasly_security_settings', 'atlasly_security_nonce'); ?>
                
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

        $nonce = isset($_POST['atlasly_security_nonce']) ? sanitize_text_field(wp_unslash($_POST['atlasly_security_nonce'])) : '';
        if(!wp_verify_nonce($nonce, 'atlasly_security_settings')) {
            return;
        }
        
        // Save allowed origins
        $origins_input = isset($_POST['allowed_origins']) ? sanitize_textarea_field(wp_unslash($_POST['allowed_origins'])) : '';
        $origins = array_filter(array_map('trim', explode("\n", $origins_input)));
        ApiSecurity::set_allowed_origins($origins);
        
        // Generate webhook secret if not exists
        if(empty(get_option('atlasly_webhook_secret'))) {
            update_option('atlasly_webhook_secret', bin2hex(random_bytes(32)));
        }
        
        // Generate new API key if requested
        if(!empty($_POST['generate_api_key'])) {
            $key_name = isset($_POST['api_key_name']) ? sanitize_text_field(wp_unslash($_POST['api_key_name'])) : 'Generated Key';
            if ($key_name === '') {
                $key_name = 'Generated Key';
            }
            $new_key = ApiSecurity::generate_api_key($key_name);
            set_transient('atlasly_new_api_key', $new_key, 300); // Show for 5 minutes
        }
        
        echo '<div class="notice notice-success"><p>Security settings saved!</p></div>';
    }
}
