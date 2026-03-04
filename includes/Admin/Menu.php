<?php
namespace AtlasPress\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use AtlasPress\Admin\Pages\Dashboard;
use AtlasPress\Admin\Pages\ContentTypes;
use AtlasPress\Admin\Pages\Entries;
use AtlasPress\Admin\Pages\SetupWizard;
use AtlasPress\Admin\Pages\Webhooks;
use AtlasPress\Core\Permissions;

class Menu
{
    public static function register()
    {
        $setup_completed = get_option('atlasly_setup_completed', false);
        $reconfigure = isset($_GET['reconfigure']) && '1' === sanitize_text_field(wp_unslash($_GET['reconfigure']));

        if (!$setup_completed || $reconfigure) {
            add_menu_page('Atlasly Setup', 'Atlasly Setup', 'manage_options', 'atlaspress-setup', [SetupWizard::class, 'render'], 'dashicons-database', 80);
            return;
        }

        if (Permissions::can_view_dashboard()) {
            add_menu_page('Atlasly', 'Atlasly', 'atlaspress_view_dashboard', 'atlaspress', [Dashboard::class, 'render'], 'dashicons-database', 80);
        }

        if (Permissions::can_manage_types()) {
            add_submenu_page('atlaspress', 'Content Types', 'Content Types', 'atlaspress_manage_types', 'atlaspress-content-types', [ContentTypes::class, 'render']);
        }

        if (Permissions::can_edit_entries()) {
            add_submenu_page('atlaspress', 'Entries', 'Entries', 'atlaspress_edit_entries', 'atlaspress-entries', [Entries::class, 'render']);
        }

        if (Permissions::can_manage_types()) {
            add_submenu_page('atlaspress', 'Settings', 'Settings', 'atlaspress_manage_types', 'atlaspress-webhooks', [\AtlasPress\Admin\Pages\Webhooks::class, 'render']);
        }

        add_submenu_page('atlaspress', 'Help', 'Help', 'read', 'atlaspress-help', [\AtlasPress\Admin\Pages\Help::class, 'render']);
    }
}
