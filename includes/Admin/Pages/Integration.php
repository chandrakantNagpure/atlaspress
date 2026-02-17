<?php
namespace AtlasPress\Admin\Pages;

class Integration
{

    public static function init()
    {
        add_action('admin_menu', [self::class, 'add_menu']);
    }

    public static function add_menu()
    {
        add_submenu_page(
            'atlaspress',
            'Integration',
            'Integration',
            'manage_options',
            'atlaspress-integration',
            [self::class, 'render_page']
        );
    }

    public static function render_page()
    {
        // Use HTTPS if available, otherwise use HTTP
        if (is_ssl()) {
            $site_url = set_url_scheme(home_url('/'), 'https');
        } else {
            $site_url = home_url('/');
        }
        $content_types = self::get_content_types();
        ?>
        <div class="wrap">
            <h1>AtlasPress Integration</h1>
            <p>Copy and paste this code into any website to automatically track all form submissions.</p>

            <div class="postbox" style="padding: 20px; margin: 20px 0;">
                <h2>Universal Form Tracker</h2>
                <p><strong>Step 1:</strong> Add this script tag to your website's

                    <head> section:
                </p>

                <textarea readonly
                    style="width: 100%; height: 120px; font-family: monospace; font-size: 12px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;"><script>
                        window.atlasPressConfig = {
                            baseUrl: '<?php echo esc_js($site_url); ?>',
                            contentTypeId: 1,
                            debug: true
                        };
                    </script>
        <script src="<?php echo esc_url($site_url); ?>/wp-content/plugins/atlaspress/assets/js/atlaspress-client.js"></script></textarea>

                <p><strong>That's it!</strong> The script will automatically:</p>
                <ul>
                    <li>Detect all forms on the page</li>
                    <li>Capture form submissions</li>
                    <li>Send data to your WordPress AtlasPress plugin</li>
                </ul>
            </div>

            <div class="postbox" style="padding: 20px; margin: 20px 0;">
                <h2>Available Content Types</h2>
                <?php if (empty($content_types)): ?>
                    <p>No content types found. <a href="<?php echo admin_url('admin.php?page=atlaspress-content-types'); ?>">Create
                            your first content type</a>.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($content_types as $type): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($type->id); ?></strong></td>
                                    <td><?php echo esc_html($type->name); ?></td>
                                    <td><code><?php echo esc_html($type->slug); ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="postbox" style="padding: 20px; margin: 20px 0;">
                <h2>Advanced Configuration</h2>
                <p>For more control, you can customize the configuration:</p>

                <textarea readonly
                    style="width: 100%; height: 200px; font-family: monospace; font-size: 12px; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;"><script>
                        window.atlasPressConfig = {
                            baseUrl: '<?php echo esc_js($site_url); ?>',
                            contentTypeId: 1,
                            debug: false,
                            exclude: [
                                '.no-track',
                                '#search-form',
                                '[data-no-atlaspress]'
                            ]
                        };
                    </script>
        <script src="<?php echo esc_url($site_url); ?>/wp-content/plugins/atlaspress/assets/js/atlaspress-client.js"></script></textarea>
            </div>
        </div>
        <?php
    }

    private static function get_content_types()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'atlaspress_content_types';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return [];
        }

        return $wpdb->get_results("SELECT id, name, slug FROM $table_name ORDER BY name");
    }
}
