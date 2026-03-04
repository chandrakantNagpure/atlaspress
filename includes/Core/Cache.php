<?php
namespace AtlasPress\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Cache {
    
    private static $prefix = 'atlaspress_';
    private static $default_expiry = 3600; // 1 hour

    public static function get($key, $default = null) {
        $value = get_transient(self::$prefix . $key);
        return $value !== false ? $value : $default;
    }

    public static function set($key, $value, $expiry = null) {
        $expiry = $expiry ?? self::$default_expiry;
        return set_transient(self::$prefix . $key, $value, $expiry);
    }

    public static function delete($key) {
        return delete_transient(self::$prefix . $key);
    }

    public static function flush($pattern = '') {
        global $wpdb;
        
        if($pattern) {
            $pattern = self::$prefix . $pattern;
        } else {
            $pattern = self::$prefix;
        }
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_' . $pattern . '%'
        ));
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_' . $pattern . '%'
        ));
    }

    public static function remember($key, $callback, $expiry = null) {
        $value = self::get($key);
        
        if($value === null) {
            $value = $callback();
            self::set($key, $value, $expiry);
        }
        
        return $value;
    }
}