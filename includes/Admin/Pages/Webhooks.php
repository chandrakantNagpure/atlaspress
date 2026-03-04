<?php
namespace AtlasPress\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Webhooks
{

    public static function render()
    {
        $allowed_origins = get_option('atlasly_allowed_origins', []);
        $cors_status = empty($allowed_origins) ? 'All Origins (*)' : implode(', ', $allowed_origins);
        ?>
        <div class="wrap">
            <h1>Webhooks &amp; CORS Settings</h1>

            <div class="postbox" style="padding: 20px; margin: 20px 0;">
                <h2>CORS - Allowed Origins</h2>
                <p><strong>Current Status:</strong> <?php echo esc_html($cors_status); ?></p>
                <p><em>CORS settings are available in the Pro version.</em></p>
            </div>

            <div id="atlaspress-webhooks-app"></div>
        </div>
        <?php
    }
}
