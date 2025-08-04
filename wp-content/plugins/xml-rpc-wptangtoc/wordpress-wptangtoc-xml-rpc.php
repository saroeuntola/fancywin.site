<?php
/**
 * Plugin Name: Tắt xml rpc wordpress
 * Plugin URI: https://wptangtoc.com
 * Description: Phát triển bởi WP Tăng Tốc
 * Version: 1.0.1
 * Author: Gia Tuấn
 * Author URI: https://wptangtoc.com
 * License: GPLv2 or later
 */
 

add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', 'wptangtoc_remove_x_pingback');
add_filter('pings_open', '__return_false', 9999);
function wptangtoc_remove_x_pingback($headers) {
unset($headers['X-Pingback'], $headers['x-pingback']);
return $headers;
}

function wptangtoc_cham_soc_khach_hang_xml($links) {
    $plugin_shortcuts = array(
        '<a href="https://wptangtoc.com" target="_blank" style="color:#b40404;">Yêu cầu hỗ trợ</a>'
    );
    return array_merge($links, $plugin_shortcuts);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wptangtoc_cham_soc_khach_hang_xml');
