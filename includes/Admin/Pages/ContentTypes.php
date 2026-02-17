<?php
namespace AtlasPress\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ContentTypes {
    public static function render() {
        echo '<div class="wrap"><h1>Content Types</h1><div id="atlaspress-content-types-app"></div></div>';
    }
}
