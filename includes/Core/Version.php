<?php
namespace AtlasPress\Core;

class Version {
    
    const CURRENT_VERSION = '1.0.0';
    const DB_VERSION = '1.0.0';
    
    public static function init() {
        add_action('plugins_loaded', [self::class, 'check_version']);
        add_action('upgrader_process_complete', [self::class, 'upgrade_completed'], 10, 2);
    }
    
    public static function check_version() {
        $installed_version = get_option('atlaspress_version', '0.0.0');
        
        if(version_compare($installed_version, self::CURRENT_VERSION, '<')) {
            self::upgrade($installed_version);
        }
    }
    
    public static function upgrade($from_version) {
        // Version-specific upgrades
        if(version_compare($from_version, '0.5.0', '<')) {
            self::upgrade_to_050();
        }
        
        if(version_compare($from_version, '1.0.0', '<')) {
            self::upgrade_to_100();
        }
        
        // Update version
        update_option('atlaspress_version', self::CURRENT_VERSION);
        update_option('atlaspress_db_version', self::DB_VERSION);
        
        // Clear cache after upgrade
        Cache::flush();
    }
    
    private static function upgrade_to_050() {
        // Add new database indexes
        global $wpdb;
        $table = $wpdb->prefix . 'atlaspress_entries';
        $wpdb->query("ALTER TABLE $table ADD INDEX idx_search (title, status)");
    }
    
    private static function upgrade_to_100() {
        // Add new capabilities
        Permissions::init();
        
        // Migrate old data if needed
        self::migrate_legacy_data();
    }
    
    private static function migrate_legacy_data() {
        global $wpdb;
        
        // Check if old data exists and migrate
        $old_table = $wpdb->prefix . 'old_atlaspress_data';
        if($wpdb->get_var("SHOW TABLES LIKE '$old_table'")) {
            // Migration logic here
        }
    }
    
    public static function upgrade_completed($upgrader_object, $options) {
        if($options['type'] == 'plugin' && isset($options['plugins'])) {
            foreach($options['plugins'] as $plugin) {
                if($plugin == plugin_basename(ATLASPRESS_PATH . 'atlaspress.php')) {
                    self::check_version();
                    break;
                }
            }
        }
    }
    
    public static function get_version_info() {
        return [
            'current' => self::CURRENT_VERSION,
            'installed' => get_option('atlaspress_version', '0.0.0'),
            'db_version' => self::DB_VERSION,
            'installed_db' => get_option('atlaspress_db_version', '0.0.0'),
            'setup_completed' => get_option('atlaspress_setup_completed', false),
            'setup_type' => get_option('atlaspress_setup_type', ''),
            'install_date' => get_option('atlaspress_install_date', '')
        ];
    }
}