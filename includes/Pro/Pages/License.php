<?php
namespace AtlasPress\Pro\Pages;

use AtlasPress\Pro\LicenseManager;

class License {
    
    public static function render() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            self::handle_action();
        }
        
        $license_info = LicenseManager::get_license_info();
        $is_active = LicenseManager::is_pro_active();
        
        ?>
        <div class="wrap">
            <h1>AtlasPress Pro License</h1>
            
            <?php if ($is_active): ?>
                <div class="notice notice-success" style="padding: 15px; margin: 20px 0;">
                    <h2 style="margin: 0 0 10px 0;">✓ Pro Version Active</h2>
                    <p style="margin: 0;"><strong>License Key:</strong> <?php echo esc_html(substr($license_info['key'], 0, 20) . '...'); ?></p>
                    <p style="margin: 5px 0 0 0;"><strong>Status:</strong> <span style="color: #46b450;">Active</span></p>
                    <?php if ($license_info['expires']): ?>
                        <p style="margin: 5px 0 0 0;"><strong>Expires:</strong> <?php echo esc_html(date('F j, Y', strtotime($license_info['expires']))); ?></p>
                    <?php endif; ?>
                </div>
                
                <form method="post" style="margin-top: 20px;">
                    <input type="hidden" name="action" value="deactivate">
                    <?php wp_nonce_field('atlaspress_license_action', 'atlaspress_license_nonce'); ?>
                    <button type="submit" class="button">Deactivate License</button>
                </form>
            <?php else: ?>
                <div class="notice notice-warning" style="padding: 15px; margin: 20px 0;">
                    <h2 style="margin: 0 0 10px 0;">⚠ Free Version</h2>
                    <p style="margin: 0;">You're using the free version of AtlasPress. Activate a pro license to unlock premium features.</p>
                </div>
                
                <div class="postbox" style="padding: 20px; margin-top: 20px; max-width: 600px;">
                    <h2>Activate Pro License</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="activate">
                        <?php wp_nonce_field('atlaspress_license_action', 'atlaspress_license_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="license_key">License Key</label></th>
                                <td>
                                    <input type="text" id="license_key" name="license_key" class="regular-text" placeholder="XXXX-XXXX-XXXX-XXXX" required>
                                    <p class="description">Enter your AtlasPress Pro license key</p>
                                </td>
                            </tr>
                        </table>
                        <button type="submit" class="button button-primary">Activate License</button>
                    </form>
                </div>
                
                <div class="postbox" style="padding: 20px; margin-top: 20px; max-width: 600px; background: #f0f6fc;">
                    <h2>Pro Features</h2>
                    <ul style="list-style: disc; padding-left: 20px;">
                        <li>Rate limiting with custom rules</li>
                        <li>Advanced analytics dashboard</li>
                        <li>Email notifications</li>
                        <li>Webhook retry logic</li>
                        <li>Request signing verification</li>
                        <li>Cloud file storage (S3, Cloudinary)</li>
                        <li>Priority support</li>
                    </ul>
                    <a href="https://atlaspress.com/pro" target="_blank" class="button button-primary">Get Pro License</a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    private static function handle_action() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_POST['atlaspress_license_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['atlaspress_license_nonce'])), 'atlaspress_license_action')) {
            echo '<div class="notice notice-error"><p>Invalid security token.</p></div>';
            return;
        }
        
        $action = sanitize_text_field($_POST['action']);
        
        if ($action === 'activate') {
            $license_key = sanitize_text_field($_POST['license_key']);
            $result = LicenseManager::activate_license($license_key);
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        } elseif ($action === 'deactivate') {
            $result = LicenseManager::deactivate_license();
            echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
        }
    }
}
