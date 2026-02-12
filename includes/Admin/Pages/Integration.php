<?php
namespace AtlasPress\Admin\Pages;

class Integration {
    
    public static function init() {
        add_action('admin_menu', [self::class, 'add_menu']);
    }
    
    public static function add_menu() {
        add_submenu_page(
            'atlaspress',
            'Integration',
            'Integration',
            'manage_options',
            'atlaspress-integration',
            [self::class, 'render_page']
        );
    }
    
    public static function render_page() {
        $site_url = home_url();
        $content_types = self::get_content_types();
        ?>
        <div class="wrap">
            <h1>AtlasPress Integration</h1>
            <p>Copy and paste this code into any website to automatically track all form submissions.</p>
            
            <div class="postbox" style="padding: 20px; margin: 20px 0;">
                <h2>Universal Form Tracker</h2>
                <p><strong>Step 1:</strong> Add this script tag to your website's &lt;head&gt; section:</p>
                
                <textarea readonly style="width: 100%; height: 120px; font-family: monospace; font-size: 12px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">&lt;script&gt;
window.atlasPressConfig = {
    baseUrl: '<?php echo esc_js($site_url); ?>',
    contentTypeId: 1, // Change this to your content type ID
    debug: true // Set to false in production
};
&lt;/script&gt;
&lt;script src="<?php echo esc_url($site_url); ?>/wp-content/plugins/atlaspress/assets/js/atlaspress-client.js"&gt;&lt;/script&gt;</textarea>
                
                <p><strong>That's it!</strong> The script will automatically:</p>
                <ul>
                    <li>✅ Detect all forms on the page (including dynamic forms)</li>
                    <li>✅ Capture form submissions without breaking existing functionality</li>
                    <li>✅ Send data to your WordPress AtlasPress plugin</li>
                    <li>✅ Work with React, Vue, Angular, and plain HTML forms</li>
                </ul>
            </div>
            
            <div class="postbox" style="padding: 20px; margin: 20px 0;">
                <h2>Available Content Types</h2>
                <?php if (empty($content_types)): ?>
                    <p>No content types found. <a href="<?php echo admin_url('admin.php?page=atlaspress-content-types'); ?>">Create your first content type</a>.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Integration Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($content_types as $type): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($type->id); ?></strong></td>
                                    <td><?php echo esc_html($type->name); ?></td>
                                    <td><code><?php echo esc_html($type->slug); ?></code></td>
                                    <td>
                                        <button class="button button-small" onclick="copyIntegrationCode(<?php echo esc_js($type->id); ?>, '<?php echo esc_js($type->name); ?>')">
                                            Copy Code
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="postbox" style="padding: 20px; margin: 20px 0;">
                <h2>Advanced Configuration</h2>
                <p>For more control, you can customize the configuration:</p>
                
                <textarea readonly style="width: 100%; height: 200px; font-family: monospace; font-size: 12px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">&lt;script&gt;
window.atlasPressConfig = {
    baseUrl: '<?php echo esc_js($site_url); ?>',
    contentTypeId: 1, // Your content type ID
    debug: false, // Set to true for debugging
    exclude: [
        '.no-track', // Exclude forms with this class
        '#search-form', // Exclude specific form IDs
        '[data-no-atlaspress]' // Exclude forms with this attribute
    ]
};
&lt;/script&gt;
&lt;script src="<?php echo esc_url($site_url); ?>/wp-content/plugins/atlaspress/assets/js/atlaspress-client.js"&gt;&lt;/script&gt;</textarea>
            </div>
            
            <div class="postbox" style="padding: 20px; margin: 20px 0;">
                <h2>Testing</h2>
                <p>To test the integration:</p>
                <ol>
                    <li>Add the script to your website</li>
                    <li>Set <code>debug: true</code> in the configuration</li>
                    <li>Open browser developer tools (F12)</li>
                    <li>Submit any form on your website</li>
                    <li>Check the console for "AtlasPress: Form submitted successfully" messages</li>
                    <li>View submissions in <a href="<?php echo admin_url('admin.php?page=atlaspress-entries'); ?>">Form Submissions</a></li>
                </ol>
            </div>
        </div>
        
        <script>
        function copyIntegrationCode(contentTypeId, typeName) {
            const code = `<script>
window.atlasPressConfig = {
    baseUrl: '<?php echo esc_js($site_url); ?>',
    contentTypeId: ${contentTypeId}, // ${typeName}
    debug: true // Set to false in production
};
</script>
<script src="<?php echo esc_url($site_url); ?>/wp-content/plugins/atlaspress/assets/js/atlaspress-client.js"></script>`;
            
            navigator.clipboard.writeText(code).then(() => {
                alert('Integration code copied to clipboard!');
            });
        }
        </script>
        <?php
    }
    
    private static function get_content_types() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'atlaspress_content_types';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return [];
        }
        
        return $wpdb->get_results("SELECT id, name, slug FROM $table_name ORDER BY name");
    }
}