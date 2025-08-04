<?php

/**
 * Plugin Name: FlyingPress
 * Plugin URI: https://flyingpress.com
 * Description: Lightning-Fast WordPress on Autopilot
 * Version: 4.15.3
 * Requires PHP: 7.4
 * Requires at least: 4.7
 * Author: FlyingWeb
 */

defined('ABSPATH') or die('No script kiddies please!');

require_once dirname(__FILE__) . '/vendor/autoload.php';

define('FLYING_PRESS_VERSION', '4.15.3');
define('FLYING_PRESS_FILE', __FILE__);
define('FLYING_PRESS_FILE_NAME', plugin_basename(__FILE__));
define('FLYING_PRESS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FLYING_PRESS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FLYING_PRESS_CACHE_DIR', WP_CONTENT_DIR . '/cache/flying-press/');
define('FLYING_PRESS_CACHE_URL', WP_CONTENT_URL . '/cache/flying-press/');

!is_dir(FLYING_PRESS_CACHE_DIR) && mkdir(FLYING_PRESS_CACHE_DIR, 0755, true);

FlyingPress\WPCache::init();
FlyingPress\Htaccess::init();
FlyingPress\AdvancedCache::init();
FlyingPress\Integrations::init();
FlyingPress\AutoPurge::init();
FlyingPress\License::init();
FlyingPress\Config::init();
FlyingPress\Cron::init();
FlyingPress\Caching::init();
FlyingPress\RestApi::init();
FlyingPress\AdminBar::init();
FlyingPress\Optimizer::init();
FlyingPress\FlyingCDN::init();
FlyingPress\Dashboard::init();
FlyingPress\Database::init();
FlyingPress\Compatibility::init();
FlyingPress\Permalink::init();
FlyingPress\Shortcuts::init();
FlyingPress\Tracking::init();
FlyingPress\WpCLI::init();
