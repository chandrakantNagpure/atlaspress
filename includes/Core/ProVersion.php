<?php
namespace AtlasPress\Core;

use AtlasPress\Pro\LicenseManager;
use AtlasPress\Pro\Pages\License;

class ProVersion {
    
    public static function init() {
        // Compatibility wrapper; canonical Pro licensing is handled by LicenseManager.
        LicenseManager::init();
    }
    
    public static function is_pro_active() {
        return LicenseManager::is_pro_active();
    }
    
    public static function add_pro_menu() {
        // Kept for backward compatibility; menu is registered in Admin\Menu.
    }
    
    public static function pro_page() {
        License::render();
    }
    
    public static function check_license() {
        LicenseManager::ajax_check_license();
    }
}
