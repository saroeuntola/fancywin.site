<?php
// FlyingPress

$config = array (
  'cache_lifespan' => 'never',
  'cache_ignore_queries' => 
  array (
    0 => 'adgroupid',
    1 => 'adid',
    2 => 'age-verified',
    3 => 'ao_noptimize',
    4 => 'campaignid',
    5 => 'cache_bust',
    6 => 'cn-reloaded',
    7 => 'dm_i',
    8 => 'ef_id',
    9 => 'epik',
    10 => 'fb_action_ids',
    11 => 'fb_action_types',
    12 => 'fb_source',
    13 => 'fbclid',
    14 => 'gad_source',
    15 => 'gbraid',
    16 => 'gclid',
    17 => 'gclsrc',
    18 => 'gdfms',
    19 => 'gdftrk',
    20 => 'gdffi',
    21 => '_ga',
    22 => '_gl',
    23 => 'mkwid',
    24 => 'mc_cid',
    25 => 'mc_eid',
    26 => 'msclkid',
    27 => 'mtm_campaign',
    28 => 'mtm_cid',
    29 => 'mtm_content',
    30 => 'mtm_keyword',
    31 => 'mtm_medium',
    32 => 'mtm_source',
    33 => 'pcrid',
    34 => 'pk_campaign',
    35 => 'pk_cid',
    36 => 'pk_content',
    37 => 'pk_keyword',
    38 => 'pk_medium',
    39 => 'pk_source',
    40 => 'pp',
    41 => 'ref',
    42 => 'redirect_log_mongo_id',
    43 => 'redirect_mongo_id',
    44 => 'sb_referer_host',
    45 => 's_kwcid',
    46 => 'srsltid',
    47 => 'sscid',
    48 => 'trk_contact',
    49 => 'trk_msg',
    50 => 'trk_module',
    51 => 'trk_sid',
    52 => 'ttclid',
    53 => 'utm_campaign',
    54 => 'utm_content',
    55 => 'utm_expid',
    56 => 'utm_id',
    57 => 'utm_medium',
    58 => 'utm_source',
    59 => 'utm_term',
  ),
  'cache_include_queries' => 
  array (
  ),
  'cache_logged_in' => false,
  'cache_mobile' => false,
  'cache_bypass_urls' => 
  array (
  ),
  'cache_bypass_cookies' => 
  array (
  ),
  'cache_preload' => true,
  'license_key' => '',
  'license_active' => false,
  'license_status' => '',
  'css_minify' => true,
  'css_rucss' => false,
  'css_rucss_method' => 'async',
  'css_rucss_exclude_stylesheets' => 
  array (
  ),
  'css_rucss_include_selectors' => 
  array (
  ),
  'js_minify' => true,
  'js_preload_links' => true,
  'js_defer' => false,
  'js_defer_inline' => false,
  'js_defer_excludes' => 
  array (
  ),
  'js_delay' => false,
  'js_delay_method' => 'selected',
  'js_delay_all_excludes' => 
  array (
  ),
  'js_delay_selected' => 
  array (
    0 => 'googletagmanager.com',
    1 => 'google-analytics.com',
    2 => 'googleoptimize.com',
    3 => 'adsbygoogle.js',
    4 => 'xfbml.customerchat.js',
    5 => 'fbevents.js',
    6 => 'widget.manychat.com',
    7 => 'cookie-law-info',
    8 => 'grecaptcha.execute',
    9 => 'static.hotjar.com',
    10 => 'hs-scripts.com',
    11 => 'embed.tawk.to',
    12 => 'disqus.com/embed.js',
    13 => 'client.crisp.chat',
    14 => 'matomo.js',
    15 => 'usefathom.com',
    16 => 'code.tidio.co',
    17 => 'metomic.io',
    18 => 'js.driftt.com',
    19 => 'cdn.onesignal.com',
    20 => 'clarity.ms',
  ),
  'js_lazy_render_selectors' => 
  array (
  ),
  'js_lazy_render' => true,
  'self_host_third_party_css_js' => true,
  'fonts_optimize_google_fonts' => true,
  'fonts_display_swap' => true,
  'fonts_preload_urls' => 
  array (
  ),
  'img_lazyload' => true,
  'img_lazyload_exclude_count' => 2,
  'img_lazyload_excludes' => 
  array (
  ),
  'img_width_height' => true,
  'img_localhost_gravatar' => false,
  'img_preload' => true,
  'img_responsive' => true,
  'iframe_lazyload' => false,
  'iframe_youtube_placeholder' => false,
  'bloat_remove_google_fonts' => false,
  'bloat_disable_woo_cart_fragments' => false,
  'bloat_disable_woo_assets' => false,
  'bloat_disable_xml_rpc' => false,
  'bloat_disable_rss_feed' => false,
  'bloat_disable_block_css' => false,
  'bloat_disable_oembeds' => false,
  'bloat_disable_emojis' => false,
  'bloat_disable_cron' => false,
  'bloat_disable_jquery_migrate' => false,
  'bloat_disable_dashicons' => false,
  'bloat_remove_restapi' => false,
  'bloat_post_revisions_control' => false,
  'bloat_post_revisions_limit' => 'disable',
  'bloat_heartbeat_control' => false,
  'bloat_heartbeat_behaviour' => 'default',
  'bloat_heartbeat_frequency' => 15,
  'cdn' => false,
  'cdn_type' => 'custom',
  'cdn_url' => '',
  'cdn_file_types' => 'all',
  'flying_cdn_api_key' => '',
  'db_post_revisions' => false,
  'db_post_auto_drafts' => false,
  'db_post_trashed' => false,
  'db_comments_spam' => false,
  'db_comments_trashed' => false,
  'db_transients_expired' => false,
  'db_transients_all' => false,
  'db_optimize_tables' => false,
  'db_schedule_clean' => 'never',
  'settings_tracking' => false,
);

if (!headers_sent()) {
  // Set response cache headers
  header('x-flying-press-cache: MISS');
  header('x-flying-press-source: PHP');
}

// Skip WP CLI requests
if (defined('WP_CLI') && WP_CLI) {
  return false;
}

// Check if cache_bust is set
if (isset($_GET['cache_bust'])) {
  return false;
}

// Check if the request method is HEAD or GET
if (!isset($_SERVER['REQUEST_METHOD']) || !in_array($_SERVER['REQUEST_METHOD'], ['HEAD', 'GET'])) {
  return false;
}

// Check if current page has any cookies set that should not be cached
foreach ($config['cache_bypass_cookies'] as $cookie) {
  if (preg_grep("/$cookie/i", array_keys($_COOKIE))) {
    return false;
  }
}

// Default file name is "index.php"
$file_name = 'index';

// Check if the user is logged in
$is_user_logged_in = preg_grep('/^wordpress_logged_in_/i', array_keys($_COOKIE));
if ($is_user_logged_in && !$config['cache_logged_in']) {
  return false;
}

// Append "-logged-in" to the file name if the user is logged in
$file_name .= $is_user_logged_in ? '-logged-in' : '';

// Add user role to cache file name
$file_name .= isset($_COOKIE['fp_logged_in_roles']) ? '-' . $_COOKIE['fp_logged_in_roles'] : '';

// Add currency code to cache if Aelia Currency Switcher is enabled
$file_name .= isset($_COOKIE['aelia_cs_selected_currency'])
  ? '-' . $_COOKIE['aelia_cs_selected_currency']
  : '';

// Add currency code to cache if YITH Currency Switcher is enabled
$file_name .= isset($_COOKIE['yith_wcmcs_currency']) ? '-' . $_COOKIE['yith_wcmcs_currency'] : '';

// Add currency code to cache file name if WCML Currency Switcher is active
$file_name .= isset($_COOKIE['wcml_currency']) ? '-' . $_COOKIE['wcml_currency'] : '';

// Check if user agent is mobile and append "mobile" to the file name
$is_mobile =
  isset($_SERVER['HTTP_USER_AGENT']) &&
  preg_match(
    '/Mobile|Android|Silk\/|Kindle|BlackBerry|Opera (Mini|Mobi)/i',
    $_SERVER['HTTP_USER_AGENT']
  );
$file_name .= $config['cache_mobile'] && $is_mobile ? '-mobile' : '';

// Remove ignored query string parameters and generate hash of the remaining
$query_strings = array_diff_key($_GET, array_flip($config['cache_ignore_queries']));
$file_name .= !empty($query_strings) ? '-' . md5(serialize($query_strings)) : '';

// File paths for cache files
$host = $_SERVER['HTTP_HOST'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = urldecode($path);
$cache_file_path = WP_CONTENT_DIR . "/cache/flying-press/$host/$path/$file_name.html.gz";

// Check if the gzipped cache file exists
if (!file_exists($cache_file_path)) {
  return false;
}

// Set the necessary headers
ini_set('zlib.output_compression', 0);
header('Content-Encoding: gzip');

// CDN cache headers
header("Cache-Tag: $host");
header('CDN-Cache-Control: max-age=2592000');

// Set cache HIT response header
header('x-flying-press-cache: HIT');

// Add Last modified response header
$cache_last_modified = filemtime($cache_file_path);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $cache_last_modified) . ' GMT');

// Get last modified since from request header
$http_modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])
  ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
  : 0;

// If file is not modified during this time, send 304
if ($http_modified_since >= $cache_last_modified) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
  exit();
}

header('Content-Type: text/html; charset=UTF-8');

readfile($cache_file_path);
exit();