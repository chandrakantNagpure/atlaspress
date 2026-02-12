<?php
namespace AtlasPress\Core;

class ProVersion {
    
    public static function init() {
        add_action('admin_menu', [self::class, 'add_pro_menu']);
        add_action('wp_ajax_atlaspress_check_license', [self::class, 'check_license']);
    }
    
    public static function is_pro_active() {
        return get_option('atlaspress_pro_license', '') !== '';
    }
    
    public static function add_pro_menu() {
        if (!self::is_pro_active()) {
            add_submenu_page(
                'atlaspress',
                'Upgrade to Pro',
                'Upgrade to Pro',
                'manage_options',
                'atlaspress-pro',
                [self::class, 'pro_page']
            );
        }
    }
    
    public static function pro_page() {
        ?>
        <div class="wrap">
            <h1>AtlasPress Pro</h1>
            <div class="atlaspress-pro-features">
                <h2>Unlock Enterprise Features</h2>
                <ul>
                    <li>API Key Authentication</li>
                    <li>CORS Domain Protection</li>
                    <li>Request Signing & Webhooks</li>
                    <li>Rate Limiting</li>
                    <li>Advanced Analytics</li>
                    <li>Priority Support</li>
                </ul>
                <p><strong>Current Version:</strong> Free (Open Access)</p>
                <p><strong>Pro Version:</strong> Enterprise Security & Features</p>
                <a href="#" class="button button-primary">Upgrade to Pro</a>
            </div>
        </div>
        <?php
    }
    
    public static function check_license() {
        $license = sanitize_text_field($_POST['license'] ?? '');
        
        // Validate license with your server
        $response = wp_remote_post('https://your-license-server.com/validate', [
            'body' => ['license' => $license]
        ]);
        
        if (is_wp_error($response)) {
            wp_send_json_error('License validation failed');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($data['valid']) {
            update_option('atlaspress_pro_license', $license);
            wp_send_json_success('License activated');
        } else {
            wp_send_json_error('Invalid license');
        }
    }
}
