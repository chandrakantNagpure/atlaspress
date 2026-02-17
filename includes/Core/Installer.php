<?php
namespace AtlasPress\Core;

class Installer {
    public static function install() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        // Content Types table
        $table1 = $wpdb->prefix . 'atlaspress_content_types';
        $sql1 = "CREATE TABLE $table1 (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(190) NOT NULL,
            slug VARCHAR(190) NOT NULL,
            description TEXT,
            settings LONGTEXT,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset;";

        // Content Entries table
        $table2 = $wpdb->prefix . 'atlaspress_entries';
        $sql2 = "CREATE TABLE $table2 (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            content_type_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(255),
            slug VARCHAR(255),
            data LONGTEXT,
            status VARCHAR(20) DEFAULT 'published',
            author_id BIGINT UNSIGNED,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY content_type_id (content_type_id),
            KEY author_id (author_id),
            KEY status (status),
            KEY slug (slug),
            KEY created_at (created_at),
            FULLTEXT KEY search_content (title, data)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql1);
        dbDelta($sql2);
        
        // Add additional indexes for performance
        self::maybe_add_index($table1, 'idx_status_created', 'status, created_at');
        self::maybe_add_index($table2, 'idx_type_status_created', 'content_type_id, status, created_at');
    }

    private static function maybe_add_index($table, $index_name, $columns) {
        global $wpdb;
        $exists = $wpdb->get_var($wpdb->prepare(
            "SHOW INDEX FROM $table WHERE Key_name = %s",
            $index_name
        ));
        if(!$exists) {
            $wpdb->query("ALTER TABLE $table ADD INDEX $index_name ($columns)");
        }
    }
}
