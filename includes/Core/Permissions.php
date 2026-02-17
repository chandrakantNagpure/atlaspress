<?php
namespace AtlasPress\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Permissions {
    
    public static function init() {
        add_action('init', [self::class, 'add_capabilities']);
    }
    
    public static function add_capabilities() {
        $admin = get_role('administrator');
        $editor = get_role('editor');
        
        $caps = [
            'atlaspress_manage_types',
            'atlaspress_edit_entries',
            'atlaspress_delete_entries',
            'atlaspress_view_dashboard'
        ];
        
        foreach($caps as $cap) {
            if($admin) $admin->add_cap($cap);
            if($editor && in_array($cap, ['atlaspress_edit_entries', 'atlaspress_view_dashboard'])) {
                $editor->add_cap($cap);
            }
        }
    }
    
    public static function can_manage_types() {
        return current_user_can('atlaspress_manage_types');
    }
    
    public static function can_edit_entries($request = null) {
        if (current_user_can('atlaspress_edit_entries')) {
            return true;
        }
        // Allow public read access for GET requests
        if ($request && $request->get_method() === 'GET') {
            return true;
        }
        return false;
    }

    public static function can_submit_entries($request) {
        if (current_user_can('atlaspress_edit_entries')) {
            return true;
        }

        if (!$request) {
            return false;
        }

        $validation = \AtlasPress\Core\ApiSecurity::validate_request(null, null, $request);
        return !is_wp_error($validation);
    }
    
    public static function can_delete_entries() {
        return current_user_can('atlaspress_delete_entries');
    }
    
    public static function can_view_dashboard() {
        return current_user_can('atlaspress_view_dashboard');
    }
}
