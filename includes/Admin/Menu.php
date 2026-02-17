<?php
namespace AtlasPress\Admin;

use AtlasPress\Admin\Pages\Dashboard;
use AtlasPress\Admin\Pages\ContentTypes;
use AtlasPress\Admin\Pages\Entries;
use AtlasPress\Admin\Pages\SetupWizard;
use AtlasPress\Admin\Pages\Webhooks;
use AtlasPress\Core\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Menu
{
    public static function register()
    {
        $setup_completed = get_option('atlaspress_setup_completed', false);
        $reconfigure = isset($_GET['reconfigure']) && $_GET['reconfigure'] == '1';

        if (!$setup_completed || $reconfigure) {
            add_menu_page('AtlasPress Setup', 'AtlasPress Setup', 'manage_options', 'atlaspress-setup', [SetupWizard::class, 'render'], 'dashicons-database', 25);
            return;
        }

        if (Permissions::can_view_dashboard()) {
            add_menu_page('AtlasPress', 'AtlasPress', 'atlaspress_view_dashboard', 'atlaspress', [Dashboard::class, 'render'], 'dashicons-database', 25);
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
