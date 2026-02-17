<?php
namespace AtlasPress\Admin\Pages;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SetupWizard {
    
    public static function render() {
        $step = isset( $_GET['step'] ) ? sanitize_text_field( wp_unslash( $_GET['step'] ) ) : 'welcome';
        
        echo '<div class="wrap">';
        echo '<h1>AtlasPress Setup Wizard</h1>';
        echo '<div id="atlaspress-setup-wizard" data-step="' . esc_attr($step) . '"></div>';
        echo '</div>';
    }
    
    public static function handle_setup() {
        if(!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'atlaspress_setup' ) ) {
            wp_send_json_error('Invalid nonce');
            return;
        }

        $setup_type = isset( $_POST['setup_type'] ) ? sanitize_text_field( wp_unslash( $_POST['setup_type'] ) ) : '';
        $project_name = isset( $_POST['project_name'] ) ? sanitize_text_field( wp_unslash( $_POST['project_name'] ) ) : '';
        
        if (empty($setup_type) || empty($project_name)) {
            wp_send_json_error('Missing required fields');
            return;
        }
        
        try {
            switch($setup_type) {
                case 'nextjs_forms':
                    self::setup_nextjs_forms($project_name);
                    break;
                case 'headless_cms':
                    self::setup_headless_cms($project_name);
                    break;
                case 'api_backend':
                    self::setup_api_backend($project_name);
                    break;
                case 'blank':
                    // No default content types for blank project
                    break;
                default:
                    wp_send_json_error('Invalid setup type');
                    return;
            }
            
            update_option('atlaspress_setup_completed', true);
            update_option('atlaspress_setup_type', $setup_type);
            update_option('atlaspress_project_name', $project_name);
            
            wp_send_json_success(['redirect' => admin_url('admin.php?page=atlaspress')]);
            
        } catch(Exception $e) {
            error_log( 'AtlasPress Setup Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            wp_send_json_error('Setup failed: ' . $e->getMessage());
        }
    }
    
    public static function handle_reset() {
        if(!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'atlaspress_setup' ) ) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        try {
            global $wpdb;
            
            // Delete all content types and entries
            $content_types_table = $wpdb->prefix . 'atlaspress_content_types';
            $entries_table = $wpdb->prefix . 'atlaspress_entries';
            
            if ($wpdb->get_var("SHOW TABLES LIKE '$content_types_table'") == $content_types_table) {
                $wpdb->query("DELETE FROM $content_types_table");
            }
            
            if ($wpdb->get_var("SHOW TABLES LIKE '$entries_table'") == $entries_table) {
                $wpdb->query("DELETE FROM $entries_table");
            }
            
            // Reset setup options
            delete_option('atlaspress_setup_completed');
            delete_option('atlaspress_setup_type');
            delete_option('atlaspress_project_name');
            
            // Clear cache if available
            if (class_exists('\AtlasPress\Core\Cache')) {
                \AtlasPress\Core\Cache::flush();
            }
            
            wp_send_json_success(['redirect' => admin_url('admin.php?page=atlaspress-setup')]);
            
        } catch(Exception $e) {
            error_log( 'AtlasPress Reset Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            wp_send_json_error('Reset failed: ' . $e->getMessage());
        }
    }
    
    private static function setup_nextjs_forms($project_name) {
        // Ensure table exists
        global $wpdb;
        $table = $wpdb->prefix . 'atlaspress_content_types';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            \AtlasPress\Core\Installer::install();
        }
        
        // Start with blank content types - users can create their own
    }
    
    private static function setup_headless_cms($project_name) {
        // Ensure table exists
        global $wpdb;
        $table = $wpdb->prefix . 'atlaspress_content_types';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            \AtlasPress\Core\Installer::install();
        }
        
        // Start with blank content types - users can create their own
    }
    
    private static function setup_api_backend($project_name) {
        // Ensure table exists
        global $wpdb;
        $table = $wpdb->prefix . 'atlaspress_content_types';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            \AtlasPress\Core\Installer::install();
        }
        
        // Start with blank content types - users can create their own
    }
}
