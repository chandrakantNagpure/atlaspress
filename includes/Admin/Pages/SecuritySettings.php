<?php
namespace AtlasPress\Admin\Pages;

use AtlasPress\Core\ApiSecurity;
use AtlasPress\Core\ProVersion;
use AtlasPress\Core\Security;

class SecuritySettings {
    
    public static function render() {
        if($_POST['action'] ?? '' === 'save_security_settings') {
            self::save_settings();
        }
        
        $api_keys = get_option('atlaspress_api_keys', []);
        if(!is_array($api_keys)) {
            $api_keys = [];
        }
        $allowed_origins = get_option('atlaspress_allowed_origins', []);
        $webhook_secret = get_option('atlaspress_webhook_secret', '');
        $signature_policy = ApiSecurity::get_signature_policy();
        $api_key_details = ApiSecurity::get_api_key_details();
        $api_key_scopes = ApiSecurity::get_available_api_key_scopes();
        $rate_limit_rules = Security::get_effective_rate_limit_rules();
        $is_pro_active = ProVersion::is_pro_active();
        
        ?>
        <div class="wrap">
            <h1>Security Settings</h1>
            
            <form method="post" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <input type="hidden" name="action" value="save_security_settings">
                <?php wp_nonce_field('atlaspress_security_settings', 'atlaspress_security_nonce'); ?>
                
                <h2>🔑 API Keys</h2>
                <div id="atlaspress-security-app" 
                     data-keys="<?php echo esc_attr(wp_json_encode(array_keys($api_keys))); ?>" 
                     data-key-details="<?php echo esc_attr(wp_json_encode($api_key_details)); ?>"
                     data-key-scopes="<?php echo esc_attr(wp_json_encode($api_key_scopes)); ?>"
                     data-origins="<?php echo esc_attr(wp_json_encode($allowed_origins)); ?>" 
                     data-signature-policy="<?php echo esc_attr(wp_json_encode($signature_policy)); ?>"
                     data-rate-limit-rules="<?php echo esc_attr(wp_json_encode($rate_limit_rules)); ?>"
                     data-is-pro-active="<?php echo esc_attr($is_pro_active ? '1' : '0'); ?>"
                     data-secret="<?php echo esc_attr($webhook_secret ? 'Set' : 'Not Set'); ?>"></div>
                
                <button type="submit" class="button button-primary">Save Settings</button>
            </form>
        </div>
        <?php
    }
    
    private static function save_settings() {
        if(!current_user_can('manage_options')) return;

        if(!isset($_POST['atlaspress_security_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['atlaspress_security_nonce'])), 'atlaspress_security_settings')) {
            echo '<div class="notice notice-error"><p>Invalid security token.</p></div>';
            return;
        }
        
        // Save allowed origins
        $origins = array_filter(array_map('trim', explode("\n", sanitize_textarea_field($_POST['allowed_origins'] ?? ''))));
        ApiSecurity::set_allowed_origins($origins);

        ApiSecurity::set_signature_policy([
            'require_signed_form_capture' => sanitize_text_field(wp_unslash($_POST['require_signed_form_capture'] ?? '1')),
            'require_signed_hubspot_webhook' => sanitize_text_field(wp_unslash($_POST['require_signed_hubspot_webhook'] ?? '1')),
            'allow_legacy_signed_ingest' => sanitize_text_field(wp_unslash($_POST['allow_legacy_signed_ingest'] ?? '0')),
        ]);

        if(isset($_POST['rate_limit_rules']) && ProVersion::is_pro_active()) {
            Security::set_rate_limit_rules(wp_unslash($_POST['rate_limit_rules']));
        }
        
        // Generate webhook secret if not exists
        if(empty(get_option('atlaspress_webhook_secret'))) {
            update_option('atlaspress_webhook_secret', bin2hex(random_bytes(32)));
        }
        
        // Generate new API key if requested
        if(!empty($_POST['generate_api_key'])) {
            $key_name = sanitize_text_field(wp_unslash($_POST['api_key_name'] ?? 'Generated Key'));
            $scopes = ApiSecurity::sanitize_api_key_scopes(wp_unslash($_POST['api_key_scopes'] ?? []));
            $expires_at = ApiSecurity::sanitize_api_key_expiration(wp_unslash($_POST['api_key_expires_at'] ?? ''));
            $allowed_ips = ApiSecurity::sanitize_api_key_allowed_ips(wp_unslash($_POST['api_key_allowed_ips'] ?? []));
            $new_key = ApiSecurity::generate_api_key($key_name, $scopes, [
                'expires_at' => $expires_at,
                'allowed_ips' => $allowed_ips,
            ]);
            set_transient('atlaspress_new_api_key', $new_key, 300); // Show for 5 minutes
        }
        
        echo '<div class="notice notice-success"><p>Security settings saved!</p></div>';
    }
}
