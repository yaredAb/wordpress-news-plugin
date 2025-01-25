<?php
/*
Plugin Name: WP News Plugin
Author: Yared Sebsbe
Version: 1.0
*/

if(!defined('ABSPATH')) {
    exit;
}

define ('NEWS_PORTAL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define ('NEWS_PORTAL_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once NEWS_PORTAL_PLUGIN_PATH.'/includes/class-author-dashboard.php';
