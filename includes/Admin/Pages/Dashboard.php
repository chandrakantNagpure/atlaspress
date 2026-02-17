<?php
namespace AtlasPress\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Dashboard {
    public static function render() {
        echo '<div class="wrap"><div id="atlaspress-admin-app"></div></div>';
    }
}
