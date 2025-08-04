<?php

class CronVnRewrite {
    private $options;
    private $rewrite;

    public function __construct() {
        $this->options = get_option('vnrewrite_option');
        $this->setup_hooks();
    }

    private function setup_hooks() {
        add_action('update_option_vnrewrite_option', array($this, 'on_options_updated'), 10, 3);
        
        if ($this->should_setup_rewrite_cron()) {
            $this->setup_rewrite_cron();
        }

        if ($this->should_setup_publish_cron()) {
            $this->setup_publish_cron();
        }

        if ($this->should_setup_update_video_id_list_cron()) {
            $this->setup_update_video_id_list_cron();
        }
    }

    private function should_setup_rewrite_cron() {
        return isset($this->options['rewrite_type']) && $this->options['rewrite_type'] != '';
    }

    private function should_setup_publish_cron() {
        return isset($this->options['cron_publish']) && $this->options['cron_publish'] > 0;
    }

    private function should_setup_update_video_id_list_cron() {
        return isset($this->options['cron_time_video_id_list']) && $this->options['cron_time_video_id_list'] > 0;
    }

    private function setup_rewrite_cron() {
        require_once VNREWRITE_PATH . 'admin/rewrite.php';
        $this->rewrite = new VnRewrite();

        add_filter('cron_schedules', array($this, 'vnrewrite_schedule'));
        add_action('vnrewrite', array($this, 'vnrewrite_action'));

        if (!wp_next_scheduled('vnrewrite')) {
            wp_schedule_event(time(), 'time_rewrite', 'vnrewrite');
        }
    }

    private function setup_publish_cron() {
        add_filter('cron_schedules', array($this, 'vnrewrite_publish_schedule'));
        add_action('vnrewrite_publish', array($this, 'vnrewrite_publish_action'));

        if (!wp_next_scheduled('vnrewrite_publish')) {
            wp_schedule_event(time(), 'time_publish', 'vnrewrite_publish');
        }
    }

    private function setup_update_video_id_list_cron() {
        add_filter('cron_schedules', array($this, 'vnrewrite_update_video_id_list_schedule'));
        add_action('vnrewrite_update_video_id_list', array($this, 'vnrewrite_update_video_id_list_action'));

        if (!wp_next_scheduled('vnrewrite_update_video_id_list')) {
            wp_schedule_event(time(), 'time_update_video_id_list', 'vnrewrite_update_video_id_list');
        }
    }

    public function on_options_updated($old_value, $new_value, $option) {
        $this->options = $new_value;

        $cron_related_keys = ['cron_time', 'cron_publish', 'rewrite_type', 'cron_time_video_id_list'];
        $needs_update = false;

        foreach ($cron_related_keys as $key) {
            if (isset($old_value[$key], $new_value[$key]) && $old_value[$key] !== $new_value[$key]) {
                $needs_update = true;
                break;
            }
        }

        if ($needs_update) {
            $this->update_cron_schedules();
            update_option('vnrewrite_mess_time', time());
        }
    }

    public function update_cron_schedules() {
        $this->clear_all_schedules();

        if ($this->should_setup_rewrite_cron()) {
            $this->setup_rewrite_cron();
        }

        if ($this->should_setup_publish_cron()) {
            $this->setup_publish_cron();
        }

        if ($this->should_setup_update_video_id_list_cron()) {
            $this->setup_update_video_id_list_cron();
        }
    }

    private function clear_all_schedules() {
        $hooks = ['vnrewrite', 'vnrewrite_publish', 'vnrewrite_update_video_id_list'];
        foreach ($hooks as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }

    public function vnrewrite_schedule($schedules) {
        $time = isset($this->options['cron_time']) ? $this->options['cron_time'] : 1;
        if ($time < 1) {
            $time = 1;
        }
        $schedules['time_rewrite'] = array(
            'interval'  => $time * 60,
            'display'   => $time . ' Minutes'
        );
        return $schedules;
    }

    public function vnrewrite_action() {
        $current_time = time();
        $cron_time = isset($this->options['cron_time']) ? $this->options['cron_time'] : 1;
        $cron_interval = $cron_time * 60;
        $max_execution_time = 5 * 60; // Tối đa 5 phút, có thể điều chỉnh

        $last_run = get_transient('vnrewrite_last_run');
        $rewrite_start_time = get_transient('vnrewrite_start');
        
        if ($last_run && $current_time - $last_run < $cron_interval) {
            return;
        }
        
        if ($rewrite_start_time) {
            $elapsed_time = $current_time - $rewrite_start_time;
            if ($elapsed_time < $max_execution_time) {
                return;
            } else {
                delete_transient('vnrewrite_start');
            }
        }
        
        set_transient('vnrewrite_start', $current_time, $max_execution_time);
        set_transient('vnrewrite_last_run', $current_time, $cron_interval);
        
        try {
            switch ($this->options['rewrite_type']) {
                case 'post':
                    $this->vnrewrite_action_post();
                    break;
                case 'url':
                    $this->vnrewrite_action_url();
                    break;
                case 'keyword':
                    $this->vnrewrite_action_keyword();
                    break;
                case 'video':
                    $this->vnrewrite_action_video();
                    break;
            }
        } finally {
            delete_transient('vnrewrite_start');
        }
    }

    private function vnrewrite_action_post() {
        $args = array(
            'post_type'      => 'post',
            'post_status'    => (isset($this->options['cron_status']) && !empty($this->options['cron_status'])) ? $this->options['cron_status'] : array('draft', 'publish'),
            'posts_per_page' => 1,
            'orderby'        => 'rand',
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => 'rewrite',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );
        
        if (isset($this->options['cron_cat']) && $this->options['cron_cat'] > 0) {
            $args['cat'] = $this->options['cron_cat'];
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $post_id = $query->posts[0];
            $this->rewrite->rewrite($post_id, false, false, false, false);
        } else {
            $mess = '<span class="orange-text">Đã rewrite hết bài viết!</span>';
            $this->rewrite->log_mess($mess, false, true, 2);
        }
    }

    private function vnrewrite_action_url() {
        $this->rewrite->rewrite(false, true, false, false, false);
    }

    private function vnrewrite_action_keyword() {
        $this->rewrite->rewrite(false, false, true, false, false);
    }

    private function vnrewrite_action_video() {
        $this->rewrite->rewrite(false, false, false, true, false);
    }

    public function vnrewrite_publish_schedule($schedules) {
        $time = isset($this->options['cron_publish']) ? $this->options['cron_publish'] : 0;
        $schedules['time_publish'] = array(
            'interval'  => $time * 60,
            'display'   => $time . ' Minutes'
        );
        return $schedules;
    }

    public function vnrewrite_publish_action() {
        $args = array(
            'post_type'   => 'post',
            'numberposts' => 1,
            'post_status' => 'draft',
            'orderby'     => 'rand',
            'fields'      => 'ids',
            'meta_query' => array(
                array(
                    'key'     => 'rewrite',
                    'compare' => 'EXISTS',
                )
            )
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $post_id = $query->posts[0];
            $row = array(
                'ID'                => $post_id,
                'post_status'       => 'publish',
                'post_date'         => current_datetime()->format('Y-m-d H:i:s'),
                'post_date_gmt'     => gmdate('Y-m-d H:i:s', time()),
                'post_modified'     => current_datetime()->format('Y-m-d H:i:s'),
                'post_modified_gmt' => gmdate('Y-m-d H:i:s', time())
            );
            wp_update_post($row);
        }
    }

    public function vnrewrite_update_video_id_list_schedule($schedules) {
        $time = isset($this->options['cron_time_video_id_list']) ? $this->options['cron_time_video_id_list'] : 0;
        $schedules['time_update_video_id_list'] = array(
            'interval'  => $time * 60 * 60,
            'display'   => 'Every ' . $time . ' hours'
        );
        return $schedules;
    }

    public function vnrewrite_update_video_id_list_action() {
        $cates = get_categories(array(
            'hide_empty' => false,
            'hierarchical' => true
        ));

        foreach ($cates as $cate) {
            $this->update_video_id_list($cate->term_id);
            sleep(2);
        }
    }

    public function update_video_id_list($cate_id){
        $all_video_ids    = [];
        $video_arr        = [];
        $video_active_arr = [];
        $video_miss_arr   = [];

        $video_id_list = get_term_meta($cate_id, 'video_id_list', true);
        if (!empty($video_id_list)) {
            $video_id_list_arr =  explode('|', $video_id_list);
            if (!empty($video_id_list_arr)) {
                foreach ($video_id_list_arr as $playlist_id) {
                    $video_ids = $this->get_video_ids($playlist_id);
                    $all_video_ids = array_merge($all_video_ids, $video_ids);
                }
            }
        }

        if (!empty($all_video_ids)) {

            $all_video_ids      = array_values(array_unique($all_video_ids));

            $video              = 'video_' . $cate_id;
            $video_active       = 'video_active_' . $cate_id;
            $video_miss         = 'video_miss_' . $cate_id;

            $video_txt          = VNREWRITE_DATA . $video . '.txt';
            $video_active_txt   = VNREWRITE_DATA . $video_active . '.txt';
            $video_miss_txt     = VNREWRITE_DATA . $video_miss . '.txt';

            $video_value        = file_exists($video_txt)?file_get_contents($video_txt):'';
            $video_active_value = file_exists($video_active_txt)?file_get_contents($video_active_txt):'';
            $video_miss_value   = file_exists($video_miss_txt)?file_get_contents($video_miss_txt):'';


            $input_video        = (file_exists($video_txt)?file_get_contents($video_txt):'') . implode("\n", $all_video_ids);
            $input_video_active = file_exists($video_active_txt)?file_get_contents($video_active_txt):'';
            $input_video_miss   = file_exists($video_miss_txt)?file_get_contents($video_miss_txt):'';

            $video_arr          = array_unique(explode("\n", $input_video));
            $video_active_arr   = array_unique(explode("\n", $input_video_active));
            $video_miss_arr     = array_unique(explode("\n", $input_video_miss));

            $video_arr          = array_diff($video_arr, $video_active_arr, $video_miss_arr);

            $new_input_video    = implode("\n", $video_arr);
            
            if ($new_input_video != '') {
                file_put_contents($video_txt, $new_input_video);
            }

        }
    }

    public function get_video_ids($playlist_id) {
        $video_ids = array();

        if (!empty($playlist_id)) {
            $url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=contentDetails&maxResults=5&playlistId=' . $playlist_id . '&key=' . $this->options['gg_api_yt'];

            $response = file_get_contents($url);
            $data = json_decode($response, true);

            foreach ($data['items'] as $item) {
                $video_ids[] = $item['contentDetails']['videoId'];
            }
        }

        return $video_ids;
    }
}

$cron_vnrewrite = new CronVnRewrite();