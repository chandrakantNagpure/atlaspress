<?php
namespace AtlasPress\Admin\Pages;

use Exception;

class SetupWizard {
    
    public static function render() {
        $step = $_GET['step'] ?? 'welcome';
        
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
        if(!wp_verify_nonce($_POST['nonce'] ?? '', 'atlaspress_setup')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        $setup_type = sanitize_text_field($_POST['setup_type'] ?? '');
        $project_name = sanitize_text_field($_POST['project_name'] ?? '');
        
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
            error_log('AtlasPress Setup Error: ' . $e->getMessage());
            wp_send_json_error('Setup failed: ' . $e->getMessage());
        }
    }
    
    public static function handle_reset() {
        if(!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }
        
        // Verify nonce
        if(!wp_verify_nonce($_POST['nonce'] ?? '', 'atlaspress_setup')) {
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
            error_log('AtlasPress Reset Error: ' . $e->getMessage());
            wp_send_json_error('Reset failed: ' . $e->getMessage());
        }
    }
    
    private static function setup_nextjs_forms($project_name) {
        global $wpdb;
        $table = $wpdb->prefix . 'atlaspress_content_types';
        
        // Ensure table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            \AtlasPress\Core\Installer::install();
        }
        
        // Create common form types
        $forms = [
            ['name' => 'Contact Form', 'slug' => 'contact-form', 'fields' => [
                ['name' => 'name', 'type' => 'text', 'label' => 'Full Name', 'required' => true],
                ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                ['name' => 'message', 'type' => 'textarea', 'label' => 'Message', 'required' => true],
                ['name' => 'source', 'type' => 'text', 'label' => 'Source Page', 'required' => false]
            ]],
            ['name' => 'Newsletter Signup', 'slug' => 'newsletter', 'fields' => [
                ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                ['name' => 'preferences', 'type' => 'select', 'label' => 'Frequency', 'options' => ['weekly', 'monthly']]
            ]],
            ['name' => 'Support Ticket', 'slug' => 'support', 'fields' => [
                ['name' => 'subject', 'type' => 'text', 'label' => 'Subject', 'required' => true],
                ['name' => 'priority', 'type' => 'select', 'label' => 'Priority', 'options' => ['low', 'medium', 'high']],
                ['name' => 'description', 'type' => 'textarea', 'label' => 'Description', 'required' => true]
            ]]
        ];
        
        foreach($forms as $form) {
            $result = $wpdb->insert($table, [
                'name' => $form['name'],
                'slug' => $form['slug'],
                'settings' => wp_json_encode(['fields' => $form['fields']]),
                'status' => 'active',
                'created_at' => current_time('mysql')
            ]);
            
            if ($result === false) {
                throw new Exception('Database error: ' . $wpdb->last_error);
            }
        }
    }
    
    private static function setup_headless_cms($project_name) {
        global $wpdb;
        $table = $wpdb->prefix . 'atlaspress_content_types';
        
        // Ensure table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            \AtlasPress\Core\Installer::install();
        }
        
        // Create CMS content types
        $types = [
            ['name' => 'Blog Post', 'slug' => 'blog-post', 'fields' => [
                ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'required' => true],
                ['name' => 'content', 'type' => 'richtext', 'label' => 'Content', 'required' => true],
                ['name' => 'featured_image', 'type' => 'media', 'label' => 'Featured Image'],
                ['name' => 'category', 'type' => 'text', 'label' => 'Category'],
                ['name' => 'tags', 'type' => 'text', 'label' => 'Tags']
            ]],
            ['name' => 'Product', 'slug' => 'product', 'fields' => [
                ['name' => 'name', 'type' => 'text', 'label' => 'Product Name', 'required' => true],
                ['name' => 'price', 'type' => 'number', 'label' => 'Price', 'required' => true],
                ['name' => 'description', 'type' => 'richtext', 'label' => 'Description'],
                ['name' => 'images', 'type' => 'media', 'label' => 'Product Images', 'multiple' => true]
            ]]
        ];
        
        foreach($types as $type) {
            $result = $wpdb->insert($table, [
                'name' => $type['name'],
                'slug' => $type['slug'],
                'settings' => wp_json_encode(['fields' => $type['fields']]),
                'status' => 'active',
                'created_at' => current_time('mysql')
            ]);
            
            if ($result === false) {
                throw new Exception('Database error: ' . $wpdb->last_error);
            }
        }
    }
    
    private static function setup_api_backend($project_name) {
        global $wpdb;
        $table = $wpdb->prefix . 'atlaspress_content_types';
        
        // Ensure table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            \AtlasPress\Core\Installer::install();
        }
        
        // Create API-focused types
        $types = [
            ['name' => 'User Profile', 'slug' => 'user-profile', 'fields' => [
                ['name' => 'username', 'type' => 'text', 'label' => 'Username', 'required' => true],
                ['name' => 'profile_data', 'type' => 'json', 'label' => 'Profile Data'],
                ['name' => 'settings', 'type' => 'json', 'label' => 'User Settings']
            ]],
            ['name' => 'API Log', 'slug' => 'api-log', 'fields' => [
                ['name' => 'endpoint', 'type' => 'text', 'label' => 'Endpoint'],
                ['name' => 'method', 'type' => 'text', 'label' => 'HTTP Method'],
                ['name' => 'response_data', 'type' => 'json', 'label' => 'Response Data']
            ]]
        ];
        
        foreach($types as $type) {
            $result = $wpdb->insert($table, [
                'name' => $type['name'],
                'slug' => $type['slug'],
                'settings' => wp_json_encode(['fields' => $type['fields']]),
                'status' => 'active',
                'created_at' => current_time('mysql')
            ]);
            
            if ($result === false) {
                throw new Exception('Database error: ' . $wpdb->last_error);
            }
        }
    }
}