<?php
/**
 * Plugin Name: VnRewrite
 * Plugin URI: https://vnrewrite.com/
 * Description: VnRewrite
 * Version: 5.6
 * Author: thienvt
 * Author URI: https://www.facebook.com/thienvt36/
 * License: GPLv2
 */

if (!defined('ABSPATH')) {
    exit;
}

$dir_vnrewrite_data = ABSPATH . 'wp-content/uploads/vnrewrite';
if(!file_exists($dir_vnrewrite_data)){
    mkdir($dir_vnrewrite_data);
}
define('VNREWRITE_DATA', $dir_vnrewrite_data . '/');
define('VNREWRITE_PATH', plugin_dir_path( __FILE__ ) . '/');
define('VNREWRITE_URL', plugins_url('/', __FILE__ ));
define('VNREWRITE_ADMIN_PAGE', admin_url('options-general.php?page=vnrewrite-admin'));

function vnrewrite_script() {
    if (isset($_GET['page']) && $_GET['page'] == 'vnrewrite-admin') {
        wp_enqueue_script('vnrewrite', VNREWRITE_URL . 'admin/vnrewrite.js', array('jquery'), null, false);
        wp_localize_script('vnrewrite', 'vnrewrite_obj', array(
            'ajaxurl'             => admin_url('admin-ajax.php'),
            'rewrite_urls'        => VNREWRITE_ADMIN_PAGE . '&tab=urls',
            'rewrite_keywords'    => VNREWRITE_ADMIN_PAGE . '&tab=keywords',
            'rewrite_videos'      => VNREWRITE_ADMIN_PAGE . '&tab=videos-yt',
            'config_nonce' => wp_create_nonce('vnrewrite_config_action'),
            'read_log_nonce' => wp_create_nonce('vnrewrite_read_log'),
            'current_tab' => isset($_GET['tab']) ? $_GET['tab'] : '',
            'debug_enabled' => (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG)
        ));
    }
}
add_action('admin_enqueue_scripts', 'vnrewrite_script');

require_once VNREWRITE_PATH . 'admin/admin.php';
require_once VNREWRITE_PATH . 'admin/cron.php';
require_once VNREWRITE_PATH . 'admin/ajax.php';
require_once VNREWRITE_PATH . 'admin/list-post.php';
require_once VNREWRITE_PATH . 'admin/wp-config-modifi.php';

add_filter('the_content', 'vnrewrite_update_content');
function vnrewrite_update_content($content){
    if(is_single()){
        $post_id = get_the_ID();
        $keyword = get_post_meta($post_id, 'keyword', true);
        if ($keyword != '') {
            $options = get_option('vnrewrite_option');

            if (isset($options['link_cur'])) {
                preg_match_all('/<h[2-6]>.*?<\/h[2-6]>/', $content, $matches);
                foreach ($matches[0] as $match) {
                    $placeholder = preg_replace('/' . $keyword . '/i', 'KEYWORD_PLACEHOLDER', $match);
                    $content = str_replace($match, $placeholder, $content);
                }
                $content = preg_replace('/' . $keyword . '/i', '<a href="' . get_permalink($post_id) . '">' . ucwords($keyword) . '</a>', $content, 1);
                $content = str_replace('KEYWORD_PLACEHOLDER', ucwords($keyword), $content);
            }

            if (isset($options['link_brand'])) {
                $brand = get_bloginfo('name');
                $content = preg_replace('/(.*?)(' . $brand . ')(?!.*?' . $brand . ')/s', '$1<a href="' . home_url() . '">$2</a>', $content);
            }
        }
    }
    return $content;
}

//img
function rewrite_disable_scaled_images($threshold) {
    return 0;
}
add_filter('big_image_size_threshold', 'rewrite_disable_scaled_images');

function rewrite_only_thumbnail_size($sizes) {
    foreach ($sizes as $size => $details) {
        if ($size !== 'thumbnail') {
            unset($sizes[$size]);
        }
    }
    return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'rewrite_only_thumbnail_size');

function rewrite_remove_all_custom_image_sizes() {
    global $_wp_additional_image_sizes;
    
    if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
        foreach ($_wp_additional_image_sizes as $size => $details) {
            if ($size !== 'thumbnail') {
                remove_image_size($size);
            }
        }
    }
}
add_action('init', 'rewrite_remove_all_custom_image_sizes');

function rewrite_remove_image_sizes_theme() {
    foreach (get_intermediate_image_sizes() as $size) {
        if ($size !== 'thumbnail') {
            remove_image_size($size);
        }
    }
}
add_action('after_setup_theme', 'rewrite_remove_image_sizes_theme', 11);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_action_links_vnrewrite');
function add_action_links_vnrewrite ($actions) {
   $mylinks = array(
      '<a href="' . admin_url('options-general.php?page=vnrewrite-admin') . '">Settings</a>',
   );
   $actions = array_merge( $actions, $mylinks );
   return $actions;
}

//update
require_once VNREWRITE_PATH . 'lib/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://vnrewrite.com/wp-content/uploads/update.json',
    __FILE__,
    'vnrewrite'
);
?>