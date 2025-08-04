<?php
    //debug log
    add_action('wp_ajax_vnrewrite_toggle_debug_log', 'vnrewrite_toggle_debug_log');
    function vnrewrite_toggle_debug_log() {
        $response = array('success' => false, 'message' => '', 'newNonce' => '');

        try {
            if (!check_ajax_referer('vnrewrite_config_action', 'nonce', false)) {
                throw new Exception('Invalid nonce. Please refresh the page and try again.');
            }

            if (!current_user_can('manage_options')) {
                throw new Exception('Insufficient permissions');
            }

            $config_file = ABSPATH . 'wp-config.php';
            if (!is_writable($config_file)) {
                throw new Exception('wp-config.php is not writable');
            }

            $config_content = file_get_contents($config_file);
            $debug_status = defined('WP_DEBUG') && WP_DEBUG;
            $debug_log_status = defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;

            // Toggle debug settings
            if ($debug_status && $debug_log_status) {
                // Turn off both
                $config_content = preg_replace("/define\s*\(\s*['\"]WP_DEBUG['\"]\s*,\s*true\s*\);/", "define('WP_DEBUG', false);", $config_content);
                $config_content = preg_replace("/define\s*\(\s*['\"]WP_DEBUG_LOG['\"]\s*,\s*true\s*\);/", "define('WP_DEBUG_LOG', false);", $config_content);
                $new_status = false;
            } else {
                // Turn on both
                $config_content = vnrewrite_update_or_add_define($config_content, 'WP_DEBUG', 'true');
                $config_content = vnrewrite_update_or_add_define($config_content, 'WP_DEBUG_LOG', 'true');
                $new_status = true;
            }

            if (file_put_contents($config_file, $config_content) === false) {
                throw new Exception('Failed to write to wp-config.php');
            }

            $response['success'] = true;
            $response['message'] = 'Debug settings toggled successfully';
            $response['status'] = $new_status;
            $response['newNonce'] = wp_create_nonce('vnrewrite_config_action');
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log('VnRewrite Error: ' . $e->getMessage());
        }

        wp_send_json($response);
    }

    function vnrewrite_update_or_add_define($content, $constant, $value) {
        $pattern = "/define\s*\(\s*['\"]" . preg_quote($constant, '/') . "['\"]\s*,.*?\);/";
        $replacement = "define('" . $constant . "', " . $value . ");";

        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, $replacement, $content);
        } else {
            $insertion_point = strpos($content, "/* That's all, stop editing! Happy publishing. */");
            if ($insertion_point === false) {
                $insertion_point = strpos($content, "require_once ABSPATH . 'wp-settings.php';");
            }
            if ($insertion_point !== false) {
                return substr_replace($content, $replacement . "\n", $insertion_point, 0);
            } else {
                return $content . "\n" . $replacement . "\n";
            }
        }
    }
    
    add_action('wp_ajax_vnrewrite_read_log', 'vnrewrite_read_error_log');
    function vnrewrite_read_error_log() {
        $response = array('success' => false, 'message' => '', 'data' => '');

        try {
            if (!check_ajax_referer('vnrewrite_read_log', 'nonce', false)) {
                throw new Exception('Invalid nonce. Please refresh the page and try again.');
            }

            if (!current_user_can('manage_options')) {
                throw new Exception('Insufficient permissions');
            }

            $log_file = WP_CONTENT_DIR . '/debug.log';
            
            if (!file_exists($log_file)) {
                throw new Exception('Debug log file does not exist: ' . $log_file);
            }

            if (!is_readable($log_file)) {
                throw new Exception('Debug log file is not readable: ' . $log_file);
            }

            $log_content = file_get_contents($log_file);
            
            if ($log_content === false) {
                throw new Exception('Failed to read debug log file');
            }

            // Limit the size of the log content if it's too large
            $max_length = 1000000; // Approximately 1MB
            if (strlen($log_content) > $max_length) {
                $log_content = substr($log_content, -$max_length);
                $log_content = "... (log truncated)\n" . $log_content;
            }

            $response['success'] = true;
            $response['data'] = $log_content;
            $response['message'] = 'Log read successfully';
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log('VnRewrite Error reading log: ' . $e->getMessage());
        }

        wp_send_json($response);
    }

    add_action('wp_ajax_vnrewrite_clear_log', 'vnrewrite_clear_log');
    function vnrewrite_clear_log() {
        $response = array('success' => false, 'message' => '');

        try {
            if (!check_ajax_referer('vnrewrite_read_log', 'nonce', false)) {
                throw new Exception('Invalid nonce. Please refresh the page and try again.');
            }

            if (!current_user_can('manage_options')) {
                throw new Exception('Insufficient permissions');
            }

            $log_file = WP_CONTENT_DIR . '/debug.log';
            
            if (!file_exists($log_file)) {
                throw new Exception('Debug log file does not exist.');
            }

            if (!is_writable($log_file)) {
                throw new Exception('Debug log file is not writable.');
            }

            // Clear the content of the file
            if (file_put_contents($log_file, '') !== false) {
                $response['success'] = true;
                $response['message'] = 'Log file cleared successfully.';
            } else {
                throw new Exception('Failed to clear the log file.');
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
            error_log('vnrewrite Error clearing log: ' . $e->getMessage());
        }

        wp_send_json($response);
    }

    //ajax rewrite post
    add_action('wp_ajax_rewrite_ajax', 'rewrite_ajax');
    add_action('wp_ajax_nopriv_rewrite_ajax', 'rewrite_ajax');
    function rewrite_ajax(){
        if (isset($_POST['id']) && (!isset($GLOBALS['rewrite_post_executed']) || !$GLOBALS['rewrite_post_executed'])) {
            $GLOBALS['rewrite_post_executed'] = true;
            require_once VNREWRITE_PATH . 'admin/rewrite.php';
            $rewrite = new VnRewrite();
            $rewrite->rewrite($_POST['id'], false, false, false, false, true);
            $GLOBALS['rewrite_post_executed'] = false;
        }
        die();
    }

    function vnrewrite_line_count_ajax($file){
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES);
            return count($lines);
        }else{
            return 0;
        }
    }

    //ajax rewrite urls
    add_action('wp_ajax_vnrewrite_save_url', 'vnrewrite_save_url');
    add_action('wp_ajax_nopriv_vnrewrite_save_url', 'vnrewrite_save_url');
    function vnrewrite_save_url(){
        $url_arr = array();
        $url_active_arr   = array();
        $url_miss_arr     = array();

        $cat = isset($_POST['cat'])?$_POST['cat']:'';

        $url        = 'url_' . $cat;
        $url_active = 'url_active_' . $cat;
        $url_miss   = 'url_miss_' . $cat;

        $url_txt        = VNREWRITE_DATA . $url . '.txt';
        $url_active_txt = VNREWRITE_DATA . $url_active . '.txt';
        $url_miss_txt   = VNREWRITE_DATA . $url_miss . '.txt';

        $input_url        = isset($_POST['input_url'])?$_POST['input_url']:'';
        $input_url_active = isset($_POST['input_url_active'])?$_POST['input_url_active']:'';
        $input_url_miss   = isset($_POST['input_url_miss'])?$_POST['input_url_miss']:'';

        $url_arr        = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_url))));
        $url_active_arr = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_url_active))));
        $url_miss_arr   = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_url_miss))));

        $url_arr = array_diff($url_arr, $url_active_arr, $url_miss_arr);

        $new_input_url          = implode("\n", $url_arr);
        $new_input_url_active   = implode("\n", $url_active_arr);
        $new_input_url_miss     = implode("\n", $url_miss_arr);
        
        if ($new_input_url != '') {
            file_put_contents($url_txt, $new_input_url);
        }else{
            wp_delete_file($url_txt);
        }

        if ($new_input_url_active != '') {
            file_put_contents($url_active_txt, $new_input_url_active);
        }else{
            wp_delete_file($url_active_txt);
        }

        if ($new_input_url_miss != '') {
            file_put_contents($url_miss_txt, $new_input_url_miss);
        }else{
            wp_delete_file($url_miss_txt);
        }
        echo '<div class="poststuff">';
            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="hndle">Urls chưa rewrite (' . vnrewrite_line_count_ajax($url_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $url . '" class="large-text" rows="20">' . $new_input_url . '</textarea>';
                echo '</div>';
            echo '</div>';

            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="green-text">Urls rewrite thành công (' . vnrewrite_line_count_ajax($url_active_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $url_active . '" class="large-text" rows="20">' . $new_input_url_active . '</textarea>';
                echo '</div>';
            echo '</div>';

            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="red-text">Urls rewrite thất bại (' . vnrewrite_line_count_ajax($url_miss_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $url_miss . '" class="large-text" rows="20">' . $new_input_url_miss . '</textarea>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        echo '<button type="button" class="button button-primary" id="save-url" data-cat="' . $cat . '">Save</button>';
        echo '<p class="orange-text"><em>(*) Mỗi url trên 1 dòng</em></p>';
        die();
    }

    //ajax rewrite keywords
    add_action('wp_ajax_vnrewrite_save_keyword', 'vnrewrite_save_keyword');
    add_action('wp_ajax_nopriv_vnrewrite_save_keyword', 'vnrewrite_save_keyword');
    function vnrewrite_save_keyword(){
        $keyword_arr = array();
        $keyword_active_arr   = array();
        $keyword_miss_arr     = array();

        $cat = isset($_POST['cat'])?$_POST['cat']:'';

        $keyword        = 'keyword_' . $cat;
        $keyword_active = 'keyword_active_' . $cat;
        $keyword_miss   = 'keyword_miss_' . $cat;

        $keyword_txt        = VNREWRITE_DATA . $keyword . '.txt';
        $keyword_active_txt = VNREWRITE_DATA . $keyword_active . '.txt';
        $keyword_miss_txt   = VNREWRITE_DATA . $keyword_miss . '.txt';

        $input_keyword        = isset($_POST['input_keyword'])?$_POST['input_keyword']:'';
        $input_keyword_active = isset($_POST['input_keyword_active'])?$_POST['input_keyword_active']:'';
        $input_keyword_miss   = isset($_POST['input_keyword_miss'])?$_POST['input_keyword_miss']:'';

        $keyword_arr        = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_keyword))));
        $keyword_active_arr = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_keyword_active))));
        $keyword_miss_arr   = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_keyword_miss))));

        $keyword_arr = array_diff($keyword_arr, $keyword_active_arr, $keyword_miss_arr);

        $new_input_keyword          = implode("\n", $keyword_arr);
        $new_input_keyword_active   = implode("\n", $keyword_active_arr);
        $new_input_keyword_miss     = implode("\n", $keyword_miss_arr);
        
        if ($new_input_keyword != '') {
            file_put_contents($keyword_txt, $new_input_keyword);
        }else{
            wp_delete_file($keyword_txt);
        }

        if ($new_input_keyword_active != '') {
            file_put_contents($keyword_active_txt, $new_input_keyword_active);
        }else{
            wp_delete_file($keyword_active_txt);
        }

        if ($new_input_keyword_miss != '') {
            file_put_contents($keyword_miss_txt, $new_input_keyword_miss);
        }else{
            wp_delete_file($keyword_miss_txt);
        }
        echo '<div class="poststuff">';
            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="hndle">Keywords chưa rewrite (' . vnrewrite_line_count_ajax($keyword_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $keyword . '" class="large-text" rows="20">' . $new_input_keyword . '</textarea>';
                echo '</div>';
            echo '</div>';

            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="green-text">Keywords rewrite thành công (' . vnrewrite_line_count_ajax($keyword_active_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $keyword_active . '" class="large-text" rows="20">' . $new_input_keyword_active . '</textarea>';
                echo '</div>';
            echo '</div>';

            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="red-text">Keywords rewrite thất bại (' . vnrewrite_line_count_ajax($keyword_miss_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $keyword_miss . '" class="large-text" rows="20">' . $new_input_keyword_miss . '</textarea>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        echo '<button type="button" class="button button-primary" id="save-keyword" data-cat="' . $cat . '">Save</button>';
        echo '<p class="orange-text"><em>(*) Mỗi keyword trên 1 dòng</em></p>';
        die();
    }

    //ajax rewrite video yt
    add_action('wp_ajax_vnrewrite_save_video', 'vnrewrite_save_video');
    add_action('wp_ajax_nopriv_vnrewrite_save_video', 'vnrewrite_save_video');
    function vnrewrite_save_video(){
        $video_arr = array();
        $video_active_arr   = array();
        $video_miss_arr     = array();

        $cat = isset($_POST['cat'])?$_POST['cat']:'';

        $video        = 'video_' . $cat;
        $video_active = 'video_active_' . $cat;
        $video_miss   = 'video_miss_' . $cat;

        $video_txt        = VNREWRITE_DATA . $video . '.txt';
        $video_active_txt = VNREWRITE_DATA . $video_active . '.txt';
        $video_miss_txt   = VNREWRITE_DATA . $video_miss . '.txt';

        $input_video        = isset($_POST['input_video'])?$_POST['input_video']:'';
        $input_video_active = isset($_POST['input_video_active'])?$_POST['input_video_active']:'';
        $input_video_miss   = isset($_POST['input_video_miss'])?$_POST['input_video_miss']:'';

        $video_arr        = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_video))));
        $video_active_arr = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_video_active))));
        $video_miss_arr   = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_video_miss))));

        $video_arr = array_diff($video_arr, $video_active_arr, $video_miss_arr);

        $new_input_video          = implode("\n", $video_arr);
        $new_input_video_active   = implode("\n", $video_active_arr);
        $new_input_video_miss     = implode("\n", $video_miss_arr);
        
        if ($new_input_video != '') {
            file_put_contents($video_txt, $new_input_video);
        }else{
            wp_delete_file($video_txt);
        }

        if ($new_input_video_active != '') {
            file_put_contents($video_active_txt, $new_input_video_active);
        }else{
            wp_delete_file($video_active_txt);
        }

        if ($new_input_video_miss != '') {
            file_put_contents($video_miss_txt, $new_input_video_miss);
        }else{
            wp_delete_file($video_miss_txt);
        }
        echo '<div class="poststuff">';
            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="hndle">Video ID Youtube chưa rewrite (' . vnrewrite_line_count_ajax($video_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $video . '" class="large-text" rows="20">' . $new_input_video . '</textarea>';
                echo '</div>';
            echo '</div>';

            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="green-text">Video ID Youtube rewrite thành công (' . vnrewrite_line_count_ajax($video_active_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $video_active . '" class="large-text" rows="20">' . $new_input_video_active . '</textarea>';
                echo '</div>';
            echo '</div>';

            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="red-text">Video ID Youtube rewrite thất bại (' . vnrewrite_line_count_ajax($video_miss_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $video_miss . '" class="large-text" rows="20">' . $new_input_video_miss . '</textarea>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        echo '<button type="button" class="button button-primary" id="save-video" data-cat-video="' . $cat . '">Save</button>';
        echo '<p class="orange-text"><em>(*) Mỗi video id Youtube trên 1 dòng</em></p>';
        die();
    }

    //ajax rewrite video id list
    add_action('wp_ajax_vnrewrite_get_video_id_list', 'vnrewrite_get_video_id_list');
    add_action('wp_ajax_nopriv_vnrewrite_get_video_id_list', 'vnrewrite_get_video_id_list');
    function vnrewrite_get_video_id_list(){
        $video_arr = array();
        $video_active_arr   = array();
        $video_miss_arr     = array();

        $cat = isset($_POST['cat'])?$_POST['cat']:'';

        $video          = 'video_' . $cat;
        $video_active   = 'video_active_' . $cat;
        $video_miss     = 'video_miss_' . $cat;
        $video_id_list  = isset($_POST['video_id_list'])?$_POST['video_id_list']:'';

        $video_txt        = VNREWRITE_DATA . $video . '.txt';
        $video_active_txt = VNREWRITE_DATA . $video_active . '.txt';
        $video_miss_txt   = VNREWRITE_DATA . $video_miss . '.txt';

        $input_video        = (isset($_POST['input_video'])?$_POST['input_video'] . "\n":"") . implode("\n", get_video_ids($video_id_list));
        $input_video_active = isset($_POST['input_video_active'])?$_POST['input_video_active']:'';
        $input_video_miss   = isset($_POST['input_video_miss'])?$_POST['input_video_miss']:'';

        $video_arr        = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_video))));
        $video_active_arr = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_video_active))));
        $video_miss_arr   = array_unique(array_map('trim', explode("\n", preg_replace('/^\s*$\n|\\\/m', '', $input_video_miss))));

        $video_arr = array_diff($video_arr, $video_active_arr, $video_miss_arr);

        $new_input_video          = implode("\n", $video_arr);
        $new_input_video_active   = implode("\n", $video_active_arr);
        $new_input_video_miss     = implode("\n", $video_miss_arr);
        
        if ($new_input_video != '') {
            file_put_contents($video_txt, $new_input_video);
        }else{
            wp_delete_file($video_txt);
        }

        if ($new_input_video_active != '') {
            file_put_contents($video_active_txt, $new_input_video_active);
        }else{
            wp_delete_file($video_active_txt);
        }

        if ($new_input_video_miss != '') {
            file_put_contents($video_miss_txt, $new_input_video_miss);
        }else{
            wp_delete_file($video_miss_txt);
        }
        echo '<div class="poststuff">';
            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="hndle">Video ID Youtube chưa rewrite (' . vnrewrite_line_count_ajax($video_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $video . '" class="large-text" rows="20">' . $new_input_video . '</textarea>';
                echo '</div>';
            echo '</div>';

            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="green-text">Video ID Youtube rewrite thành công (' . vnrewrite_line_count_ajax($video_active_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $video_active . '" class="large-text" rows="20">' . $new_input_video_active . '</textarea>';
                echo '</div>';
            echo '</div>';

            echo '<div class="postbox">';
                echo '<div class="postbox-header">';
                    echo '<h2 class="red-text">Video ID Youtube rewrite thất bại (' . vnrewrite_line_count_ajax($video_miss_txt) . ')</h2>';
                echo '</div>';
                echo '<div class="inside">';
                    echo '<textarea wrap="off" id="' . $video_miss . '" class="large-text" rows="20">' . $new_input_video_miss . '</textarea>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        echo '<button type="button" class="button button-primary" id="save-video" data-cat-video="' . $cat . '">Save</button>';
        echo '<p class="orange-text"><em>(*) Mỗi video id Youtube trên 1 dòng</em></p>';
        die();
    }

    function get_video_ids($playlist_id) {
        $options = get_option('vnrewrite_option');

        $video_ids = array();

        if (!empty($playlist_id)) {
            $url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&maxResults=50&playlistId=' . $playlist_id . '&key=' . $options['gg_api_yt'];

            do {
                $response = file_get_contents($url);
                $data = json_decode($response, true);

                foreach ($data['items'] as $item) {
                    $video_ids[] = $item['contentDetails']['videoId'];
                }

                $next_page_token = isset($data['nextPageToken']) ? $data['nextPageToken'] : null;

                if ($next_page_token) {
                    $url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&maxResults=50&playlistId=' . $playlist_id . '&key=' . $options['gg_api_yt'] . '&pageToken=' . $next_page_token;
                }

            } while ($next_page_token);
        }

        return $video_ids;
    }

    //check_mess
    add_action('wp_ajax_vnrewrite_check_mess', 'vnrewrite_check_mess');
    add_action('wp_ajax_nopriv_vnrewrite_check_mess', 'vnrewrite_check_mess');
    function vnrewrite_check_mess(){
        $mess = get_option('vnrewrite_mess');
        $mess_time = get_option('vnrewrite_mess_time');
        if ($mess && $mess_time) {
            echo json_encode(array($mess_time, $mess));
            ob_flush();
            flush();
        }else{
            echo json_encode(array(time(), 'VnRewrite'));
        }
        die();
    }
?>