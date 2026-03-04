<?php
namespace AtlasPress\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Entries {
    public static function render() {
        echo '<div class="wrap"><h1>Entries</h1><div id="atlaspress-entries-app"></div></div>';
    }
}