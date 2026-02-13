<?php
namespace AtlasPress\Core;

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
    
    public static function can_manage_types($request = null) {
        if (current_user_can('atlaspress_manage_types')) {
            return true;
        }

        if ($request && \AtlasPress\Core\ApiSecurity::has_valid_api_key($request, [\AtlasPress\Core\ApiSecurity::SCOPE_TYPES_MANAGE])) {
            return true;
        }

        return false;
    }
    
    public static function can_edit_entries($request = null) {
        if (current_user_can('atlaspress_edit_entries')) {
            return true;
        }

        if ($request) {
            $method = strtoupper((string) $request->get_method());
            if ($method === 'GET') {
                if (\AtlasPress\Core\ApiSecurity::has_any_api_scope($request, [
                    \AtlasPress\Core\ApiSecurity::SCOPE_ENTRIES_READ,
                    \AtlasPress\Core\ApiSecurity::SCOPE_ENTRIES_WRITE,
                ])) {
                    return true;
                }
            } elseif ($method === 'DELETE') {
                if (\AtlasPress\Core\ApiSecurity::has_valid_api_key($request, [\AtlasPress\Core\ApiSecurity::SCOPE_ENTRIES_DELETE])) {
                    return true;
                }
            } elseif (\AtlasPress\Core\ApiSecurity::has_valid_api_key($request, [\AtlasPress\Core\ApiSecurity::SCOPE_ENTRIES_WRITE])) {
                return true;
            }
        }

        // Public reads are disabled by default and can be explicitly enabled via filter.
        if ($request && $request->get_method() === 'GET') {
            return (bool) apply_filters('atlaspress_allow_public_read', false, $request);
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

        if (\AtlasPress\Core\ApiSecurity::has_valid_api_key($request, [\AtlasPress\Core\ApiSecurity::SCOPE_ENTRIES_WRITE])) {
            return true;
        }

        // Public submissions are disabled by default and can be explicitly enabled via filter.
        return (bool) apply_filters('atlaspress_allow_public_submit', false, $request);
    }
    
    public static function can_delete_entries($request = null) {
        if (current_user_can('atlaspress_delete_entries')) {
            return true;
        }

        if ($request && \AtlasPress\Core\ApiSecurity::has_valid_api_key($request, [\AtlasPress\Core\ApiSecurity::SCOPE_ENTRIES_DELETE])) {
            return true;
        }

        return false;
    }
    
    public static function can_view_dashboard($request = null) {
        if (current_user_can('atlaspress_view_dashboard')) {
            return true;
        }

        if ($request && \AtlasPress\Core\ApiSecurity::has_valid_api_key($request, [\AtlasPress\Core\ApiSecurity::SCOPE_DASHBOARD_READ])) {
            return true;
        }

        return false;
    }
}
