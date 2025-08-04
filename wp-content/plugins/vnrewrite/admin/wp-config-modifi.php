<?php
class VnRewritetWPConfig {
    private $option_name = 'vnrewrite_wp_config_status';

    public function __construct() {
        add_action('admin_init', array($this, 'handle_config_changes'));
        add_action('admin_notices', array($this, 'show_admin_notices'));
        add_action('admin_init', array($this, 'sync_config_status'));
        add_action('admin_head', array($this, 'add_custom_styles'));
    }

    public function handle_config_changes() {
        if (isset($_GET['vnrewrite_action']) && check_admin_referer('vnrewrite_config_action')) {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            $config_file = ABSPATH . 'wp-config.php';
            if (!is_writable($config_file)) {
                set_transient('vnrewrite_admin_notice', array('type' => 'error', 'message' => 'The wp-config.php file is not writable.'), 60);
                wp_redirect(admin_url('options-general.php?page=vnrewrite-admin&tab=notes'));
                exit;
            }

            $config_content = file_get_contents($config_file);
            $action = $_GET['vnrewrite_action'];
            $current_status = get_option($this->option_name, array());

            switch ($action) {
                case 'toggle_debug_log':
                    $debug_log_status = !empty($current_status['debug_log']);
                    $config_content = $this->update_define($config_content, 'WP_DEBUG', !$debug_log_status);
                    $config_content = $this->update_define($config_content, 'WP_DEBUG_LOG', !$debug_log_status);
                    $current_status['debug_log'] = !$debug_log_status;
                    break;
                case 'toggle_alternate_cron':
                    $alt_cron_status = !empty($current_status['alternate_cron']);
                    $config_content = $this->update_define($config_content, 'ALTERNATE_WP_CRON', !$alt_cron_status);
                    $current_status['alternate_cron'] = !$alt_cron_status;
                    break;
                case 'toggle_wp_cron':
                    $cron_status = empty($current_status['disable_wp_cron']);
                    $config_content = $this->update_define($config_content, 'DISABLE_WP_CRON', $cron_status);
                    $current_status['disable_wp_cron'] = $cron_status;
                    break;
            }

            if (file_put_contents($config_file, $config_content)) {
                update_option($this->option_name, $current_status);
                set_transient('vnrewrite_admin_notice', array('type' => 'success', 'message' => 'The wp-config.php file has been updated successfully.'), 60);
            } else {
                set_transient('vnrewrite_admin_notice', array('type' => 'error', 'message' => 'Failed to update the wp-config.php file.'), 60);
            }

            wp_redirect(admin_url('options-general.php?page=vnrewrite-admin&tab=notes'));
            exit;
        }
    }

    public function show_admin_notices() {
        $notice = get_transient('vnrewrite_admin_notice');
        if ($notice) {
            ?>
            <div class="notice notice-<?php echo $notice['type']; ?> is-dismissible">
                <p><?php echo $notice['message']; ?></p>
            </div>
            <?php
            delete_transient('vnrewrite_admin_notice');
        }
    }

    private function update_define($content, $constant, $value) {
        $value_string = $value ? 'true' : 'false';
        $pattern = "/define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,.*?\);/";
        $replacement = "define('" . $constant . "', " . $value_string . ");";

        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, $replacement, $content);
        } else {
            $insertion_point = strpos($content, "/* That's all, stop editing!");
            if ($insertion_point === false) {
                $insertion_point = strpos($content, "require_once ABSPATH . 'wp-settings.php';");
            }
            if ($insertion_point !== false) {
                return substr_replace($content, $replacement . "\n", $insertion_point, 0);
            }
        }
        return $content;
    }

    public function sync_config_status() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'vnrewrite-admin') {
            return;
        }

        $config_file = ABSPATH . 'wp-config.php';
        $config_content = file_get_contents($config_file);

        $current_status = array(
            'debug_log' => $this->check_define_status($config_content, 'WP_DEBUG_LOG'),
            'alternate_cron' => $this->check_define_status($config_content, 'ALTERNATE_WP_CRON'),
            'disable_wp_cron' => $this->check_define_status($config_content, 'DISABLE_WP_CRON')
        );

        $stored_status = get_option($this->option_name, array());

        if ($current_status !== $stored_status) {
            update_option($this->option_name, $current_status);
        }
    }

    private function check_define_status($content, $constant) {
        $pattern = "/define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,\s*(true|false)\s*\);/i";
        if (preg_match($pattern, $content, $matches)) {
            return strtolower($matches[1]) === 'true';
        }
        return false;
    }

    public function add_custom_styles() {
        ?>
        <style type="text/css">
            .vnrewrite-toggle-button {
                margin-right: 10px !important;
                min-width: 150px;
                text-align: center;
            }
            .vnrewrite-toggle-button.button-primary {
                background-color: #00a32a !important;
                border-color: #00a32a !important;
            }
            .vnrewrite-toggle-button.button-secondary {
                background-color: #f0f0f1 !important;
                color: #2c3338 !important;
                border-color: #2c3338 !important;
            }
        </style>
        <?php
    }
}

new VnRewritetWPConfig();
?>