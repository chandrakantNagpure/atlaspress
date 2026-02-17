<?php
namespace AtlasPress\Core;

class Autoloader {

    public static function register() {
        spl_autoload_register( [ __CLASS__, 'autoload' ] );
    }

    private static function autoload( $class ) {

        // Only load AtlasPress classes
        if ( strpos( $class, 'AtlasPress\\' ) !== 0 ) {
            return;
        }

        // Convert namespace to file path
        $path = str_replace(
            [ 'AtlasPress\\', '\\' ],
            [ '', DIRECTORY_SEPARATOR ],
            $class
        );

        $file = ATLASPRESS_PATH . 'includes/' . $path . '.php';

        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}
