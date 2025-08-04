<?php
class VnRewrite{
    private $options;

    public function __construct(){
        $this->options = get_option('vnrewrite_option');
    }

    public function rewrite($post_id = false, $url = false, $keyword = false, $video = false, $ajax = false){
        set_time_limit(0);
        update_option('vnrewrite_run', 'running');
        update_option('vnrewrite_last_run', time());
        $in = array(
            'token'   => $this->options['user_key'],
            'keyword' => ($keyword !== false)?'ok':''
        );
        $out = $this->vnrewrite_user($in);
        if (!empty($out)) {
            if (isset($out['end_time'])) {
                update_option('vnrewrite_end_time', $out['end_time']);
            }
            
            if (isset($out['vnrewrite_warning'])) {
                update_option('vnrewrite_warning', $out['vnrewrite_warning']);
            }

            $type_ai = $this->options['type_ai'];
            $api_key_str = '';
            if ($type_ai == 'gemini') {
                $api_key_str = $this->options['gemini_api_key'];
            }elseif ($type_ai == 'openai') {
                $api_key_str = $this->options['openai_api_key'];
            }elseif ($type_ai == 'claude') {
                $api_key_str = $this->options['claude_api_key'];
            }

            $api_key = '';
            if ($api_key_str != '') {
                $api_key = $this->set_api_key($api_key_str, $type_ai . '_api_key_last');
            }

            if (isset($out['p']) && $out['p'] != '') {
                if ($api_key != '') {
                    if ($post_id !== false) {
                        $mess = '<span class="orange-text">Đang lấy post: ' . get_permalink($post_id) . '</span>';
                        $this->log_mess($mess, false, true, 0);
                        $this->rewrite_post($type_ai, $api_key, $out['p'], $out['p2'], $out['pi'], $post_id, $ajax);
                    }elseif($url !== false) {
                        $mess = '<span class="orange-text">Đang đọc danh sách url...</span>';
                        $this->log_mess($mess, false, true, 0);
                        $this->rewrite_url($type_ai, $api_key, $out['p'], $out['p2'], $out['pi']);
                    }elseif($keyword !== false) {
                        $mess = '<span class="orange-text">Đang đọc danh sách keyword...</span>';
                        $this->log_mess($mess, false, true, 0);
                        $this->rewrite_keyword($type_ai, $api_key, $out['p'], $out['p2'], $out['pi']);
                    }elseif($video !== false) {
                        $mess = '<span class="orange-text">Đang đọc danh sách video id youtube...</span>';
                        $this->log_mess($mess, false, true, 0);
                        $this->rewrite_video($type_ai, $api_key, $out['p'], $out['p2'], $out['pi']);
                    }
                }else{
                    $mess = '<span class="orange-text">Không có ' . $type_ai . ' API key</span>';
                    $this->log_mess($mess, false, true, 0);
                }
            }
        }
    }

    private function rewrite_post($type_ai, $api_key, $p, $p2, $pi, $post_id, $ajax = false){
        $title = get_post_field('post_title', $post_id);
        $content = get_post_field('post_content', $post_id);

        if (empty($title) || empty($content)) {
            return false;
        }

        $cate_id = get_the_category($post_id)[0]->cat_ID;

        $ai_as_cate = get_term_meta($cate_id, 'vnrewrite_ai_as_cate', true);
        $ai_as = !empty($ai_as_cate)?$ai_as_cate:$this->options['vnrewrite_ai_as_common'];
        $prompt_cate = get_term_meta($cate_id, 'vnrewrite_prompt_cate', true);
        $user_prompt = !empty($prompt_cate)?$prompt_cate:$this->options['vnrewrite_prompt_common'];

        $imgs = $this->extract_img($content);
        $m_str = '';
        if (!empty($imgs)) {
            $m = [];
            foreach ($imgs as $img) {
                $m[] = '![' . $img['alt'] . '](' . $img['src'] . ')';
            }

            $m_str = implode(', ', $m);
        }

        $pi = !empty($m_str)?str_replace(array('[lang]', '[images]'), array($this->options['lang'], $m_str), $pi):'';

        $prompt = $ai_as . str_replace('[lang]', $this->options['lang'], $p) . $user_prompt . $pi . $p2 . "\n\n[nội dung] = " . str_replace("\\", "", sanitize_textarea_field($title . "\n" . $content));

        $new_content = $this->vnrewrite_type_ai($type_ai, $api_key, $prompt);

        if (empty($new_content)) {
            $mess = '<span class="red-text">' . $type_ai . ' rewrite Post thất bại! Kết quả trả về từ ' . $type_ai . ' rỗng!</span>';
            $this->log_mess($mess, false, true, 0);
            update_post_meta($post_id, 'rewrite', 'error');
            return false;
        }

        $content_arr = $this->check_content_m($new_content);

        if (empty($content_arr)) {
            $mess = '<span class="red-text">' . $type_ai . ' rewrite Post thất bại! Cấu trúc bài viết không hợp lệ!</span>';
            $this->log_mess($mess, false, true, 0);
            update_post_meta($post_id, 'rewrite', 'error');
            return false;
        }

        require_once VNREWRITE_PATH . 'lib/Parsedown.php';
        $parser = new Parsedown();
        $post_title = $content_arr['title'];
        $post_content = $parser->text($content_arr['content']);
        $post_status = isset($this->options['draft'])?'draft':'publish';

        $permalink = get_permalink($post_id);

        $row = array(
            'ID'             => $post_id,
            'post_title'     => $post_title,
            'post_content'   => $post_content,
            'post_status'    => $post_status,
            'post_author'    => isset($this->options['user'])?$this->options['user']:1,
            'meta_input'     => array(
                'rewrite' => $type_ai . ' - post'
            )
        );
        
        $update = wp_update_post($row);
        if (!is_wp_error($update)) {
            if (has_post_thumbnail($post_id)) {
                delete_post_thumbnail($post_id);
            }
            $content_replace = $this->replace_img($post_id, $post_content);
            if (!empty($content_replace)) {
                wp_update_post(array('ID' => $post_id, 'post_content' => $content_replace));
            }

            $mess = '<span class="green-text">' . $type_ai . ' rewrite Post thành công: <a target="_blank" href="' . $permalink . '">' . $post_title . '</a></span>';
            if ($ajax) {
                echo json_encode(array('title' => $post_title, 'status' => $post_status, 'rewrite_type' => $rewrite_type));
            }
            $this->log_mess($mess, false, true, 0);
            return true;
        }else{
            $mess = '<span class="red-text">rewrite_post: ' . $type_ai . ' rewrite Post thất bại, lỗi khi update post: ' . $permalink . ' | ' . $update->get_error_message() . '</span>';
            $this->log_mess($mess, false, true, 0);
            update_post_meta($post_id, 'rewrite', 'error');
            return false;
        }
    }

    private function rewrite_url($type_ai, $api_key, $p, $p2, $pi){
        $cates = get_categories(array('hide_empty' => false));
        $cate_id_arr = array();
        $url = '';

        foreach ($cates as $cate) {
            $file_path = VNREWRITE_DATA . 'url_' . $cate->cat_ID . '.txt';
            if (file_exists($file_path) && filesize($file_path) > 1) {
                $cate_id_arr[] = $cate->cat_ID;
            }
        }

        if (empty($cate_id_arr)) {
            $mess = '<span class="red-text">Không có url. Vui lòng nhập danh sách url cho các danh mục (1)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }
        
        $rand_cate_id   = $cate_id_arr[rand(0, count($cate_id_arr) - 1)];
        $url_txt        = VNREWRITE_DATA . 'url_' . $rand_cate_id . '.txt';
        $url_value      = file_exists($url_txt)?file_get_contents($url_txt):'';
        $url_arr        = explode("\n", $url_value);
        $url_miss_txt   = VNREWRITE_DATA . 'url_miss_' . $rand_cate_id . '.txt';
        $url_active_txt = VNREWRITE_DATA . 'url_active_' . $rand_cate_id . '.txt';

        if (empty($url_arr)) {
            $mess = '<span class="red-text">Không có url. Vui lòng nhập danh sách url cho các danh mục (2)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $url = trim($url_arr[rand(0, count($url_arr) - 1)]);
        if (empty($url)) {
            $mess = '<span class="red-text">Không có url. Vui lòng nhập danh sách url cho các danh mục (3)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $long = false;
        if (substr($url, -6) === '[long]') {
            $url = substr($url, 0, -6);
            $long = true;
        }

        global $wpdb;
        $dup_url = $wpdb->get_row('SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = "url" AND meta_value = "' . $url . '"');

        if (!empty($dup_url)){
            $url_write_long = ($long !== false)?$url .= '[long]':$url;
            $this->write_txt($url_write_long, $url_txt, $url_arr, $url_miss_txt);
            $mess = '<span class="red-text">Rewrite Url thất bại! Url này đã rewrite rồi!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        if (!$this->check_url($url)) {
            $url_write_long = ($long !== false)?$url .= '[long]':$url;
            $this->write_txt($url_write_long, $url_txt, $url_arr, $url_miss_txt);
            $mess = '<span class="red-text">Rewrite Url thất bại! Không kết nối được tới "' . $url . '"!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $crawl = $this->crawl($url);
        if (empty($crawl)) {
            $url_write_long = ($long !== false)?$url .= '[long]':$url;
            $this->write_txt($url_write_long, $url_txt, $url_arr, $url_miss_txt);
            $mess = '<span class="red-text">Rewrite Url thất bại! Không crawl được url "' . $url . '"!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $ai_as_cate = get_term_meta($rand_cate_id, 'vnrewrite_ai_as_cate', true);
        $ai_as = !empty($ai_as_cate)?$ai_as_cate:$this->options['vnrewrite_ai_as_common'];
        $prompt_cate = get_term_meta($rand_cate_id, 'vnrewrite_prompt_cate', true);
        $user_prompt = !empty($prompt_cate)?$prompt_cate:$this->options['vnrewrite_prompt_common'];

        $imgs = $this->extract_img($crawl['content']);
        $m_str = '';
        if (!empty($imgs)) {
            $m = [];
            foreach ($imgs as $img) {
                $m[] = '![' . $img['alt'] . '](' . $img['src'] . ')';
            }

            $m_str = implode(', ', $m);
        }

        $pi = !empty($m_str)?str_replace(array('[lang]', '[images]'), array($this->options['lang'], $m_str), $pi):'';

        $content_source = str_replace("\\", "", sanitize_textarea_field($crawl['title'] . "\n" . $crawl['content']));
        if($type_ai == 'gemini' && $long !== false){
            $content_source = $url;
        }

        $prompt = $ai_as . str_replace('[lang]', $this->options['lang'], $p) . $user_prompt . $pi . $p2 . "\n\n[nội dung] = " . $content_source;

        $new_content = $this->vnrewrite_type_ai($type_ai, $api_key, $prompt);

        if (empty($new_content)) {
            $url_write_long = ($long !== false)?$url .= '[long]':$url;
            $this->write_txt($url_write_long, $url_txt, $url_arr, $url_miss_txt);
            $mess = '<span class="red-text">' . $type_ai . ' rewrite Url thất bại! Kết quả trả về từ ' . $type_ai . ' rỗng!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $content_arr = $this->check_content_m($new_content);

        if (empty($content_arr)) {
            $url_write_long = ($long !== false)?$url .= '[long]':$url;
            $this->write_txt($url_write_long, $url_txt, $url_arr, $url_miss_txt);
            $mess = '<span class="red-text">' . $type_ai . ' rewrite Url thất bại! Cấu trúc bài viết không hợp lệ!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        require_once VNREWRITE_PATH . 'lib/Parsedown.php';
        $parser = new Parsedown();
        $post_title = $content_arr['title'];
        $post_name = $this->convert_to_slug($post_title);
        $post_content = $parser->text($content_arr['content']);
        $post_status = isset($this->options['draft'])?'draft':'publish';

        $row = array(
            'post_title'     => $post_title,
            'post_name'      => $post_name,
            'post_content'   => $post_content,
            'post_status'    => $post_status,
            'post_author'    => isset($this->options['user'])?$this->options['user']:1,
            'post_type'      => 'post',
            'post_category'  => array($rand_cate_id),
            'meta_input'     => array(
                'rewrite' => $type_ai . ' - url',
                'url'     => $url
            )
        );

        $post_id = wp_insert_post($row);
        if(!is_wp_error($post_id)){
            $content_replace = $this->replace_img($post_id, $post_content);
            if (!empty($content_replace)) {
                wp_update_post(array('ID' => $post_id, 'post_content' => $content_replace));
            }
            $url_write_long = ($long !== false)?$url .= '[long]':$url;
            $this->write_txt($url_write_long, $url_txt, $url_arr, $url_active_txt);
            $mess = '<span class="green-text">' . $type_ai . ' rewrite Url thành công: <a target="_blank" href="' . get_permalink($post_id) . '">' . $post_title . '</a></span>';
            $this->log_mess($mess, false, true, 2);
            return true;
        }else{
            $url_write_long = ($long !== false)?$url .= '[long]':$url;
            $this->write_txt($url_write_long, $url_txt, $url_arr, $url_miss_txt);
            $mess = '<span class="red-text">' . $type_ai . ' rewrite Url thất bại! Lỗi khi insert post: ' . $post_id->get_error_message() . '</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }
    }

    private function rewrite_keyword($type_ai, $api_key, $p, $p2, $pi){
        $cates = get_categories(array('hide_empty' => false));
        $cate_id_arr = array();
        $list = array();
        $input = array();
        $keyword = '';
        foreach ($cates as $cate) {
            $file_path = VNREWRITE_DATA . 'keyword_' . $cate->cat_ID . '.txt';
            if (file_exists($file_path) && filesize($file_path) > 1) {
                $cate_id_arr[] = $cate->cat_ID;
            }
        }

        if (empty($cate_id_arr)) {
            $mess = '<span class="red-text">Không có keyword. Vui lòng nhập danh sách keyword cho các danh mục (1)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }
        
        $rand_cate_id       = $cate_id_arr[rand(0, count($cate_id_arr) - 1)];
        $keyword_txt        = VNREWRITE_DATA . 'keyword_' . $rand_cate_id . '.txt';
        $keyword_value      = file_exists($keyword_txt)?file_get_contents($keyword_txt):'';
        $keyword_arr        = explode("\n", $keyword_value);
        $keyword_miss_txt   = VNREWRITE_DATA . 'keyword_miss_' . $rand_cate_id . '.txt';
        $keyword_active_txt = VNREWRITE_DATA . 'keyword_active_' . $rand_cate_id . '.txt';

        if (empty($keyword_arr)) {
            $mess = '<span class="red-text">Không có keyword. Vui lòng nhập danh sách keyword cho các danh mục (2)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }
        
        $keyword = trim($keyword_arr[rand(0, count($keyword_arr) - 1)]);
        if (empty($keyword)) {
            $mess = '<span class="red-text">Không có keyword. Vui lòng nhập danh sách keyword cho các danh mục (2)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        global $wpdb;
        $dup_keyword = $wpdb->get_row('SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = "keyword" AND meta_value = "' . $keyword . '"');
        if (!empty($dup_keyword)){
            $this->write_txt($keyword, $keyword_txt, $keyword_arr, $keyword_miss_txt);
            $mess = '<span class="red-text">Thất bại! keyword này đã rewrite rồi!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $links = $this->gg_search_api($keyword);

        if (empty($links)) {
            $this->write_txt($keyword, $keyword_txt, $keyword_arr, $keyword_miss_txt);
            $mess = '<span class="red-text">Thất bại! google search không có kết quả hợp lệ!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        if ($links == 'error'){
            $this->write_txt($keyword, $keyword_txt, $keyword_arr, $keyword_miss_txt);
            $mess = '<span class="red-text">gg_search_api: Lỗi! Vui lòng kiểm tra Google Search API key</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        foreach ($links as $url) {
            if ($this->check_url($url)) {
                $crawl = $this->crawl($url);
                if (!empty($crawl)) {
                    $ai_as_cate = get_term_meta($rand_cate_id, 'vnrewrite_ai_as_cate', true);
                    $ai_as = !empty($ai_as_cate)?$ai_as_cate:$this->options['vnrewrite_ai_as_common'];
                    $prompt_cate = get_term_meta($rand_cate_id, 'vnrewrite_prompt_cate', true);
                    $user_prompt = !empty($prompt_cate)?$prompt_cate:$this->options['vnrewrite_prompt_common'];

                    $imgs = $this->extract_img($crawl['content']);
                    $m_str = '';
                    if (!empty($imgs)) {
                        $m = [];
                        foreach ($imgs as $img) {
                            $m[] = '![' . $img['alt'] . '](' . $img['src'] . ')';
                        }

                        $m_str = implode(', ', $m);
                    }

                    $pi = !empty($m_str)?str_replace(array('[lang]', '[images]'), array($this->options['lang'], $m_str), $pi):'';

                    $prompt = $ai_as . str_replace('[lang]', $this->options['lang'], $p) . $user_prompt . $pi . $p2 . "\n\n[nội dung] = " . str_replace("\\", "", sanitize_textarea_field($keyword . "\n" . $crawl['content']));

                    $new_content = $this->vnrewrite_type_ai($type_ai, $api_key, $prompt);
                    if (!empty($new_content)) {
                        $content_arr = $this->check_content_m($new_content);
                        if (!empty($content_arr)) {
                            require_once VNREWRITE_PATH . 'lib/Parsedown.php';
                            $parser = new Parsedown();
                            $post_title = $content_arr['title'];
                            $post_name = $this->convert_to_slug($keyword);
                            $post_content = $parser->text($content_arr['content']);
                            $post_status = isset($this->options['draft'])?'draft':'publish';

                            $row = array(
                                'post_title'     => $post_title,
                                'post_name'      => $post_name,
                                'post_content'   => $post_content,
                                'post_status'    => $post_status,
                                'post_author'    => isset($this->options['user'])?$this->options['user']:1,
                                'post_type'      => 'post',
                                'post_category'  => array($rand_cate_id),
                                'meta_input'     => array(
                                    'rewrite' => $type_ai . ' - keyword',
                                    'keyword' => $keyword,
                                    'rank_math_focus_keyword' => $keyword
                                )
                            );

                            $post_id = wp_insert_post($row);
                            if(!is_wp_error($post_id)){
                                $content_replace = $this->replace_img($post_id, $post_content);
                                if (!empty($content_replace)) {
                                    wp_update_post(array('ID' => $post_id, 'post_content' => $content_replace));
                                }
                                $this->write_txt($keyword, $keyword_txt, $keyword_arr, $keyword_active_txt);
                                $mess = '<span class="green-text">' . $type_ai . ' rewrite Keyword thành công: <a target="_blank" href="' . get_permalink($post_id) . '">' . $post_title . '</a></span>';
                                $this->log_mess($mess, false, true, 2);
                                return true;
                            }

                        }
                    }
                }
            }
            sleep(5);
        }

        $this->write_txt($keyword, $keyword_txt, $keyword_arr, $keyword_miss_txt);
        $mess = '<span class="red-text">Rewrite Keyword thất bại!</span>';
        $this->log_mess($mess, false, true, 0);
        return false;
    }

    private function rewrite_video($type_ai, $api_key, $p, $p2, $pi){
        $cates = get_categories(array('hide_empty' => false));
        $cate_id_arr = array();
        $list = array();
        $input = array();
        $video_id = '';
        foreach ($cates as $cate) {
            $file_path = VNREWRITE_DATA . 'video_' . $cate->cat_ID . '.txt';
            if (file_exists($file_path) && filesize($file_path) > 1) {
                $cate_id_arr[] = $cate->cat_ID;
            }
        }

        if (empty($cate_id_arr)) {
            $mess = '<span class="red-text">Không có video ID youtube. Vui lòng nhập danh sách video ID youtube cho các danh mục (1)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $rand_cate_id      = $cate_id_arr[rand(0, count($cate_id_arr) - 1)];
        $video_txt         = VNREWRITE_DATA . 'video_' . $rand_cate_id . '.txt';
        $video_value       = file_exists($video_txt)?file_get_contents($video_txt):'';
        $video_arr         = explode("\n", $video_value);
        $video_miss_txt    = VNREWRITE_DATA . 'video_miss_' . $rand_cate_id . '.txt';
        $video_active_txt  = VNREWRITE_DATA . 'video_active_' . $rand_cate_id . '.txt';

        if (empty($video_arr)) {
            $mess = '<span class="red-text">Không có video ID youtube. Vui lòng nhập danh sách video ID youtube cho các danh mục (2)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $video_id = trim($video_arr[rand(0, count($video_arr) - 1)]);
        if (empty($video_id)) {
            $mess = '<span class="red-text">Không có video ID youtube. Vui lòng nhập danh sách video ID youtube cho các danh mục (3)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        global $wpdb;
        $dup_video = $wpdb->get_row('SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = "video_id" AND meta_value = "' . $video_id . '"');
        if (!empty($dup_video)) {
            $this->write_txt($video_id, $video_txt, $video_arr, $video_miss_txt);
            $mess = '<span class="red-text">Youtube này đã rewrite rồi!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $sub_title = $this->get_sub_title_yt($video_id);
        if (empty($sub_title)) {
            $this->write_txt($video_id, $video_txt, $video_arr, $video_miss_txt);
            $mess = '<span class="red-text">Video ID "' . $video_id . '" không hợp lệ! (Không có sub hoặc sub quá ngắn...)</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $ai_as_cate = get_term_meta($rand_cate_id, 'vnrewrite_ai_as_cate', true);
        $ai_as = !empty($ai_as_cate)?$ai_as_cate:$this->options['vnrewrite_ai_as_common'];
        $prompt_cate = get_term_meta($rand_cate_id, 'vnrewrite_prompt_cate', true);
        $user_prompt = !empty($prompt_cate)?$prompt_cate:$this->options['vnrewrite_prompt_common'];

        $imgs = $this->extract_img_yt($video_id, $sub_title['title']);
        $m_str = '';
        if (!empty($imgs)) {
            $m = [];
            foreach ($imgs as $img) {
                $m[] = '![' . $img['alt'] . '](' . $img['src'] . ')';
            }

            $m_str = implode(', ', $m);
        }

        $pi = !empty($m_str)?str_replace(array('[lang]', '[images]'), array($this->options['lang'], $m_str), $pi):'';

        $prompt = $ai_as . str_replace('[lang]', $this->options['lang'], $p) . $user_prompt . $pi . $p2 . "\n\n[nội dung] = " . str_replace("\\", "", sanitize_textarea_field($sub_title['title'] . "\n" . $sub_title['sub']));

        $new_content = $this->vnrewrite_type_ai($type_ai, $api_key, $prompt);

        if (empty($new_content)) {
            $this->write_txt($video_id, $video_txt, $video_arr, $video_miss_txt);
            $mess = '<span class="red-text">' . $type_ai . ' rewrite Youtube thất bại! Kết quả trả về từ ' . $type_ai . ' rỗng!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        $content_arr = $this->check_content_m($new_content);

        if (empty($content_arr)) {
            $this->write_txt($video_id, $video_txt, $video_arr, $video_miss_txt);
            $mess = '<span class="red-text">' . $type_ai . ' rewrite Youtube thất bại! Cấu trúc bài viết không hợp lệ!</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }

        require_once VNREWRITE_PATH . 'lib/Parsedown.php';
        $parser = new Parsedown();
        $post_title = $content_arr['title'];
        $post_name = $this->convert_to_slug($post_title);
        $post_content = $parser->text($content_arr['content']);
        $post_status = isset($this->options['draft'])?'draft':'publish';

        $row = array(
            'post_title'     => $post_title,
            'post_name'      => $post_name,
            'post_content'   => $post_content,
            'post_status'    => $post_status,
            'post_author'    => isset($this->options['user'])?$this->options['user']:1,
            'post_type'      => 'post',
            'post_category'  => array($rand_cate_id),
            'meta_input'     => array(
                'rewrite' => $type_ai . ' - youtube',
                'youtube_id' => $video_id
            )
        );

        $post_id = wp_insert_post($row);
        if(!is_wp_error($post_id)){
            $content_replace = $this->replace_img($post_id, $post_content);
            if (!empty($content_replace)) {
                wp_update_post(array('ID' => $post_id, 'post_content' => $content_replace));
            }
            $this->write_txt($video_id, $video_txt, $video_arr, $video_active_txt);
            $mess = '<span class="green-text">' . $type_ai . ' rewrite Youtube thành công: <a target="_blank" href="' . get_permalink($post_id) . '">' . $post_title . '</a></span>';
            $this->log_mess($mess, false, true, 2);
            return true;
        }else{
            $this->write_txt($video_id, $video_txt, $video_arr, $video_miss_txt);
            $mess = '<span class="red-text">' . $type_ai . ' rewrite Youtube thất bại! Lỗi khi insert post: ' . $post_id->get_error_message() . '</span>';
            $this->log_mess($mess, false, true, 0);
            return false;
        }
        
    }

    private function gg_search_api($keyword){
        $links = array();

        if ($this->options['gg_search_api'] == ''){
            $mess = '<span class="red-text">Không có google search API</span>';
            $this->log_mess($mess, true, true, 0);
            return 'error';
        }

        $mess = '<span class="orange-text">Đang google search API với từ khóa "' . $keyword . '"</span>';
        $this->log_mess($mess, false, true, 0);

        $gg_search_api = $this->set_api_key($this->options['gg_search_api'], 'gg_search_api_key_last');
        $cx = '642c649fb1dfd423b';
        $url = 'https://customsearch.googleapis.com/customsearch/v1?cx=' . urlencode($cx) . '&q=' . urlencode($keyword) . '&key=' . $gg_search_api;

        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            $mess = '<span class="red-text">gg_search_api: ' . $response->get_error_message() . '</span>';
            $this->log_mess($mess, true, false, 0);
            return $links;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            $mess = '<span class="red-text">gg_search_api: ' . $data['error']['message'] . '</span>';
            $this->log_mess($mess, true, false, 0);
            return 'error';
        }

        if (isset($data['items'])) {
            $remove = 'google\.|facebook\.com|tiktok\.com|wikipedia\.|wiki|pinterest|shopee\.vn|tiki\.vn|ebay\.com|amazon\.|quora\.com|reddit\.com|medium\.com|stackoverflow\.com|yelp\.com|lumendatabase\.org|sex|clip|video|hentai|porn|xxx|adult|blowjob|sucking|drug|gun|dict|etsy\.com|vibrator|chastity|movie|porn|penis|erection|vape|tobacco|cigarette|marijuana|gundict|lazada|tripadvisor\.com|\.jpg|\.jpeg|\.png|\.gif|\.svg|\.pdf|\.doc|\.docx|\.JPG|\.JPEG|\.PNG|\.GIF|\.SVG|\.PDF|\.DOC|\.DOCX|%23';

            foreach ($data['items'] as $item) {
                if (!preg_match('/' . $remove . '/', $item['link'])) {
                    $links[] = $item['link'];
                }
            }
        }

        return $links;
    }

    private function vnrewrite_type_ai($type_ai, $api_key, $prompt) {
        if ($type_ai == 'gemini') {
            return $this->gemini($api_key, $prompt);
        }elseif ($type_ai == 'openai') {
            return $this->openai($api_key, $prompt);
        }elseif ($type_ai == 'claude') {
            if (strpos($api_key, 'toptukhoa-') === 0) {
                return $this->openai($api_key, $prompt, true);
            }else{
                return $this->claude($api_key, $prompt);
            }
        }else{
            return false;
        }
    }

    private function gemini($api_key, $prompt) {
        $mess = '<span class="orange-text">Gemini đang rewrite ...</span>';
        $this->log_mess($mess, false, true, 0);
        
        $data = '';

        $url = 'https://generativelanguage.googleapis.com/v1/models/' . $this->options['gemini_model'] . ':generateContent?key=' . trim($api_key);

        $request_body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array(
                            'text' => $prompt
                        )
                    )
                )
            ),
            'safetySettings' => array(
                array(
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_NONE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_NONE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_NONE'
                ),
                array(
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE'
                )
            )
        );

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($request_body),
            'timeout' => 300,
            'sslverify' => false
        );

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $this->log_mess('<span class="red-text">gemini: ' . $response->get_error_message() . ' (api_key: ' . $api_key . ')</span>', true, true, 0);
        } else {
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);

            if (isset($result['error']['message'])) {
                $this->log_mess('gemini: ' . $result['error']['message'] . ' (Code: ' . $result['error']['code'] . ') (api_key: ' . $api_key . ')', true, true, 0);
            }

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
               $data = $result['candidates'][0]['content']['parts'][0]['text'];
            }
        }

        return $data;
    }

    private function openai($api_key, $prompt, $toptukhoa = false) {
        $model = $this->options['openai_model'];
        $mess = '<span class="orange-text">Openai đang rewrite ...</span>';
        if($toptukhoa !== false && $this->options['type_ai'] == 'claude'){
            $model = $this->options['claude_model'];
            $mess = '<span class="orange-text">Claude đang rewrite ...</span>';
        }
        
        $this->log_mess($mess, false, true, 0);
        
        $data = '';

        $request_body = array(
            'model' => $model,
            'messages' => array(
                array('role' => 'user', 'content' => $prompt)
            )
        );

        $url = 'https://api.openai.com/v1/chat/completions';
        if (strpos($api_key, 'toptukhoa-') === 0) {
            $api_key = substr($api_key, strlen('toptukhoa-'));
            $url = str_replace('api.openai.com', 'gpt.toptukhoa.com', $url);
        }

        $response = wp_remote_post(
            $url,
            array(
                'body' => json_encode($request_body),
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'timeout' => 300,
                'sslverify' => false,
            )
        );

        if (is_wp_error($response)) {
            $this->log_mess('<span class="red-text">openai: ' . $response->get_error_message() . ' (api_key: ' . $api_key . ')</span>', true, true, 0);
        } else {
            $result = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($result['error'])) {
                $this->log_mess('<span class="red-text">openai: ' . $result['error']['message'] . ' (api_key: ' . $api_key . ')</span>', true, true, 0);
            } else {
                if (isset($result['choices'][0]['message']['content'])) {
                    $data = $result['choices'][0]['message']['content'];
                }
            }
        }

        return $data;
    }

    private function claude($api_key, $prompt) {
        $mess = '<span class="orange-text">Claude đang rewrite ...</span>';
        $this->log_mess($mess, false, true, 0);
        
        $data = '';

        $request_body = array(
            'model' => $this->options['claude_model'],
            'max_tokens' => 4096,
            'messages' => array(
                array('role' => 'user', 'content' => $prompt)
            )
        );

        $response = wp_remote_post(
            'https://api.anthropic.com/v1/messages',
            array(
                'body' => json_encode($request_body),
                'headers' => array(
                    'x-api-key' =>  trim($api_key),
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json'
                ),
                'timeout' => 300,
                'sslverify' => false
            )
        );

        if (is_wp_error($response)) {
            $this->log_mess('<span class="red-text">claude: ' . $response->get_error_message() . ' (api_key: ' . $api_key . ')</span>', true, true, 0);
        } else {
            $result = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($result['error'])) {
                $this->log_mess('<span class="red-text">claude: Error type: ' . $result['error']['type'] . ' - Error message: ' . $result['error']['message'] . ' (api_key: ' . $api_key . ')</span>', true, true, 0);
            }elseif(isset($result['content'][0]['text'])) {
                $data = $result['content'][0]['text'];
            }else{
                $this->log_mess('<span class="red-text">claude: Unexpected response format (api_key: ' . $api_key . ')</span>', true, true, 0);
            }
        }

        return $data;
    }

    private function crawl($url){
        $crawl = array();

        $mess = '<span class="orange-text">Đang crawl url: ' . $url . '</span>';
        $this->log_mess($mess, false, true, 0);

        $html = $this->remote_crawl($url);
        if (!empty($html)) {
            require_once VNREWRITE_PATH . 'lib/simple_html_dom.php';
            $dom = new simple_html_dom();
            $dom->load($html);

            foreach(['nav', 'header', 'footer', 'time', 'noscript', 'script', 'style', 'iframe'] as $tag) {
                foreach($dom->find($tag) as $item) {
                    $item->remove();
                }
            }
            //1
            $content = '';
            $p_elements = $dom->find('p');
            foreach ($p_elements as $index => $p) {
                if (isset($p_elements[$index + 1]) && $p->parent() === $p_elements[$index + 1]->parent()) {
                    $content .= $p->parent()->innertext;
                    break;
                }
            }
            //2
            if (str_word_count(strip_tags($content)) < 350) {
                $check_elements = $dom->find('[class*=content], [id*=content], [class*=detail], [id*=detail]');
                $content = '';

                foreach ($check_elements as $element) {
                    $text = $element->plaintext;
                    if (str_word_count(strip_tags($text)) >= 350) {
                        $content = $element->innertext;
                        break;
                    }
                }
            }
            //3
            if (str_word_count(strip_tags($content)) < 350) {
                $content = '';
                $max_density = 0;
                $max_density_element = null;

                foreach ($dom->find('*') as $element) {
                    $density = $this->get_text_density($element);
                    if ($density > $max_density) {
                        $max_density = $density;
                        $max_density_element = $element;
                    }
                }

                if ($max_density_element) {
                    $parent_element = $max_density_element->parent();
                    $content = $parent_element->innertext;
                }
            }

            if (!empty($content)) {
                $title_element = $dom->find('h1', 0)?:$dom->find('title', 0);
                $title = $title_element?$title_element->plaintext:'';
                $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                $crawl = array('content' => $content, 'title' => $title, 'url' => $url);
            }

            $dom->clear();
            unset($dom);
        }

        return $crawl;
    }

    private function remote_crawl($url) {
        $args = array(
            'timeout' => 120,
            'redirection' => 15,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            'sslverify' => false
        );

        $response = wp_remote_get($url, $args);

        if (!is_wp_error($response)) {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code === 200) {
                return wp_remote_retrieve_body($response);
            } else {
                $mess = '<span class="red-text">Error: Unexpected HTTP response code - ' . $response_code . '</span>';
            }
        } else {
            $mess = '<span class="red-text">remote_crawl: ' . $response->get_error_message() . '</span>';
        }

        $this->log_mess($mess, true, true, 0);
        return '';
    }

    private function vnrewrite_user($in) {
        $data = array();
        $home_arr = parse_url(home_url());
        $domain = $home_arr['host'];

        $url = 'https://vnrewrite.com/api2/';

        $response = wp_remote_post($url, array(
            'body' => $in,
            'headers' => array(
                'Referer' => $domain,
            ),
            'timeout' => 60,
            'sslverify' => false
        ));

        if (is_wp_error($response)) {
            $mess = '<span class="red-text">vnrewrite_user: ' . $response->get_error_message() . '</span>';
            $this->log_mess($mess, true, true, 0);
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
        }

        return $data;
    }

    private function set_api_key($api_key_str, $type_api_key_last = 'gemini_api_key_last'){
        $api_key_arr = explode('|', $api_key_str);
        if (count($api_key_arr) == 1) {
            $api_key = $api_key_arr[0];
        }else{
            $api_key_last = get_option($type_api_key_last, '');
            if ($api_key_last == '') {
                $api_key = $api_key_arr[0];
            }else{
                $key = array_search($api_key_last, $api_key_arr);
                if ($key !== false) {
                    $next_key = $key + 1;
                    if ($next_key < count($api_key_arr)) {
                        $api_key = $api_key_arr[$next_key];
                    }else{
                        $api_key = $api_key_arr[0];
                    }
                }else{
                    $api_key = $api_key_arr[0];
                }
            }
            update_option($type_api_key_last, $api_key);
        }

        return $api_key;
    }

    private function check_content_m($content){
        $result = array();
        $title = '';
        preg_match_all('/^# (.+)$/m', $content, $matches);
        if (count($matches[0]) == 1) {
            $title = $matches[1][0];

            $h1Pos = strpos($content, $matches[0][0]);

            $textBeforeH1 = substr($content, 0, $h1Pos);
            if (trim($textBeforeH1) !== '') {
                $content = substr($content, $h1Pos);
            }
            $content = preg_replace('/^# .+$/m', '', $content, 1);
            $content = ltrim($content);
        }

        if (preg_match('/^## .+$/m', $content, $h2Match)) {
            $h2Pos = strpos($content, $h2Match[0]);
            if ($h2Pos === 0) {
                $content = preg_replace('/^## .+$/m', '', $content, 1);
                $content = ltrim($content);
            }
        }

        $non_space_chars = preg_replace('/\s+/', '', $content);
        if(mb_strlen($non_space_chars) < 1000){
            $this->log_mess('Nội dung bài viết không hợp lệ (quá ngắn)', true, true, 0);
            return $result;
        }

        if (empty($title)) {
            $this->log_mess('Tiêu đề bài viết cấu trúc không hợp lệ! (không phải H1)', true, true, 0);
            return $result;
        }

        $result = array('title' => $title, 'content' => $content);

        return $result;
    }

    private function get_sub_title_yt($video_id) {
        $result = array();
        $title = '';
        $sub = '';

        $url = "https://www.youtube.com/watch?v=" . $video_id;
        $response = wp_remote_get($url, array('sslverify' => false));

        if (is_wp_error($response)) {
            $mess = '<span class="red-text">get_sub_yt: ' . $response->get_error_message() . '</span>';
            $this->log_mess($mess, true, false, 0);
            return $result;
        }

        $body = wp_remote_retrieve_body($response);
        preg_match('/<title>(.*?)<\/title>/', $body, $title_matches);
        $title = isset($title_matches[1])?str_replace('- YouTube', '', $title_matches[1]):'';
        if(empty($title)){
            $mess = '<span class="red-text">get_sub_yt: Không lấy được tiêu đề video</span>';
            $this->log_mess($mess, true, false, 0);
            return $result;
        }

        preg_match('/"captionTracks":\[\{"baseUrl":"(.*?)"/', $body, $matches);

        if (isset($matches[1])) {
            $subtitle_url = json_decode('"' . $matches[1] . '"');
            $subtitle_response = wp_remote_get($subtitle_url, array('sslverify' => false));

            if (is_wp_error($subtitle_response)) {
                $mess = '<span class="red-text">get_sub_yt: ' . $subtitle_response->get_error_message() . '</span>';
                $this->log_mess($mess, true, false, 0);
                return $result;
            }

            $subtitle_body = wp_remote_retrieve_body($subtitle_response);
            $subtitles = [];
            $xml = simplexml_load_string($subtitle_body);
            
            if ($xml === false) {
                return "Lỗi khi phân tích XML.";
                $mess = '<span class="red-text">get_sub_yt: Lỗi khi phân tích sub</span>';
                $this->log_mess($mess, true, false, 0);
                return $result;
            }
            
            foreach ($xml->text as $text) {
                $subtitles[] = (string)$text;
            }

            $sub = implode('<br>', $subtitles);

            if (str_word_count($sub) < 100) {
                $mess = '<span class="red-text">get_sub_yt: Sub quá ngắn</span>';
                $this->log_mess($mess, true, true, 0);
                return $result;
            }

            $result = array('title' => $title, 'sub' => $sub);
        }

        return $result;
    }

    private function extract_img_yt($video_id, $title){
        if ($this->check_url('https://img.youtube.com/vi/' . $video_id . '/hq720.jpg')) {
            $img = array(
                'src' => 'https://img.youtube.com/vi/' . $video_id . '/hq720.jpg',
                'alt' => $title,
                'width' => 1280,
                'height' => 720
            );
        }elseif ($this->check_url('https://img.youtube.com/vi/' . $video_id . '/sddefault.jpg')) {
            $img = array(
                'src' => 'https://img.youtube.com/vi/' . $video_id . '/sddefault.jpg',
                'alt' => $title,
                'width' => 640,
                'height' => 480
            );
        }else{
            $img = array(
                'src' => 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg',
                'alt' => $title,
                'width' => 480,
                'height' => 360
            );
        }

        return array($img);
    }

    private function extract_img($content) {
        preg_match_all('/<img(.+?)src="(.+?)"(.+?)alt="(.+?)"(.+?)>/i', $content, $matches);

        $imgs = [];
        $check_dup_src = [];

        for ($i = 0; $i < count($matches[0]); $i++) {
            $src = $matches[2][$i];
            $alt = $matches[4][$i];

            $img_size = @getimagesize($src);
            $w = isset($img_size[0])?$img_size[0]:0;
            $h = isset($img_size[1])?$img_size[1]:0;

            if (!isset($check_dup_src[$src]) && $w >= 400 && $h >= 225 && $w/$h <= 1.8 ) {
                $m = "![$alt]($src)";
                $imgs[] = array(
                    'src' => $src,
                    'alt' => $alt,
                    'width' => $w,
                    'height' => $h
                );
                $check_dup_src[$src] = true;
            }
        }

        return $imgs;
    }

    private function save_img($post_id, $arr) {
        $result = array();

        if (empty($arr)) {
            return $result;
        }

        $source_src = $arr['src'];
        $alt = $arr['alt'];
        $source_width = $arr['width'];
        $source_height = $arr['height'];

        $slug = $this->convert_to_slug($alt);

        $format_img = isset($this->options['format_img']) ? $this->options['format_img'] : 'jpg';
        $file_name = (!empty($slug)?$slug . '-':'') . substr(uniqid(), 0, 6) . '.' . $format_img;
        $upload_dir = wp_upload_dir();
        $path = $upload_dir['path'] . '/' . $file_name;

        $response = wp_safe_remote_get($source_src, array(
            'timeout' => 90,
            'sslverify' => false
        ));

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $tmp = wp_remote_retrieve_body($response);

            if (file_put_contents($path, $tmp) !== false) {

                $size = array('width' => $source_width, 'height' => $source_height);

                $width = (isset($this->options['resize_img']) && $this->options['resize_img'] != 0)?$this->options['resize_img']:$source_width;
                
                $image = null;
                $image_type = exif_imagetype($path);

                switch ($image_type) {
                    case IMAGETYPE_JPEG:
                        $image = imagecreatefromjpeg($path);
                        break;
                    case IMAGETYPE_PNG:
                        $image = imagecreatefrompng($path);
                        break;
                    case IMAGETYPE_WEBP:
                        if (function_exists('imagecreatefromwebp')) {
                            $image = imagecreatefromwebp($path);
                        } else {
                            $this->log_mess('save_img: GD library does not support WebP format', true, false, 0);
                            return $result;
                        }
                        break;
                    default:
                        $this->log_mess('save_img: Unsupported image format', true, false, 0);
                        return $result;
                }

                if ($image !== null) {
                    $original_width = imagesx($image);
                    $original_height = imagesy($image);
                    $new_height = ($width / $original_width) * $original_height;
                    $resized_image = imagescale($image, $width, $new_height);

                    switch ($format_img) {
                        case 'jpg':
                        case 'jpeg':
                            imagejpeg($resized_image, $path);
                            break;
                        case 'png':
                            imagepng($resized_image, $path);
                            break;
                        case 'webp':
                            imagewebp($resized_image, $path);
                            break;
                    }

                    imagedestroy($image);
                    imagedestroy($resized_image);

                    $size = array('width' => $width, 'height' => $new_height);
                } else {
                    $this->log_mess('save_img: Failed to create image resource', true, false, 0);
                    unset($tmp);
                    return $result;
                }

                if (!function_exists('wp_generate_attachment_metadata')) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                }

                $url = $upload_dir['url'] . '/' . $file_name;
                $filetype = wp_check_filetype($file_name, null);
                $attachment = array(
                    'guid'           => $url,
                    'post_mime_type' => $filetype['type'],
                    'post_title'     => $alt,
                    'post_content'   => $alt,
                    'post_excerpt'   => $alt,
                    'post_status'    => 'inherit',
                    'post_author'    => isset($this->options['user'])?$this->options['user']:1
                );

                $attachment_id = wp_insert_attachment($attachment, $path, $post_id);
                if (!is_wp_error($attachment_id)) {

                    if (!has_post_thumbnail($post_id)) {
                        set_post_thumbnail($post_id, $attachment_id);
                        $attach_data = wp_generate_attachment_metadata($attachment_id, $path);
                    }else{
                        $attach_data = $this->custom_generate_attachment_metadata($attachment_id, $path);
                    }

                    wp_update_attachment_metadata($attachment_id, $attach_data);
                    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);

                    $result = array('src' => $url, 'size' => $size);

                    if (strpos($source_src, $_SERVER['HTTP_HOST']) !== false) {
                        $parsed_url = parse_url($source_src);
                        $source_path = $upload_dir['path'] . '/' . basename($parsed_url['path']);
                        
                        $source_attachment_id = attachment_url_to_postid($source_src);
                        if (file_exists($source_path)) {
                            unlink($source_path);
                        }
                        if ($source_attachment_id) {
                            wp_delete_attachment($source_attachment_id, true);
                        }
                    }

                } else {
                    $this->log_mess('save_img: Lỗi khi chèn đính kèm: ' . $attachment_id->get_error_message(), true, false, 0);
                }
            } else {
                $this->log_mess('save_img: Lỗi khi sao chép file ảnh vào: ' . $path, true, false, 0);
            }

            unset($tmp);
        } else {
            $mess = is_wp_error($response)?$response->get_error_message():'Response code is not 200';
            $this->log_mess('save_img: Lỗi khi tải hình ảnh từ URL: ' . $mess, true, false, 0);
        }

        return $result;
    }

    private function custom_generate_attachment_metadata($attachment_id, $path) {
        $metadata = array();
        
        $metadata['file'] = _wp_relative_upload_path($path);

        $image_size = @getimagesize($path);
        if ($image_size) {
            $metadata['width'] = $image_size[0];
            $metadata['height'] = $image_size[1];
        }

        $metadata['image_meta'] = wp_read_image_metadata($path);

        return $metadata;
    }

    private function check_url($url) {
        if (!wp_http_validate_url($url)) {
            return false;
        }

        $response = wp_remote_head($url, array(
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            'sslverify' => false
        ));

        if (is_array($response) && !is_wp_error($response)) {
            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code == 200) {
                return true;
            }
        }

        return false;
    }

    private function replace_img($post_id, $content){
        $imgs = $this->extract_img($content);
        if (!empty($imgs)) {
            $flag = false;
            foreach ($imgs as $img_arr) {
                $img_save = $this->save_img($post_id, $img_arr);
                if (!empty($img_save)) {
                    $cap = !empty($img_arr['alt'])?'<em class="cap-ai">' . $img_arr['alt'] . '</em>':'';

                    $pattern = '/<img[^>]*src="' . preg_quote($img_arr['src'], '/') . '"[^>]*>/';
                    $replacement = '<img class="img-ai" src="' . $img_save['src'] . '" alt="' . $img_arr['alt'] . '" width="' . $img_save['size']['width'] . '" height="' . $img_save['size']['height'] . '" />' . $cap;
                    $content = preg_replace($pattern, $replacement, $content);
                    $flag = true;
                }
            }

            if ($flag === false) {
                $content = '';
            }
        }else{
            $content = '';
        }
        return $content;
    }

    private function convert_to_slug($string) {
        if (class_exists('Transliterator')) {
            $transliterator = Transliterator::create('Any-Latin; Latin-ASCII; [\u0100-\u7fff] remove');
            $string = $transliterator->transliterate($string);
        }
        $slug = sanitize_title($string);
        return $slug;
    }

    private function get_text_density($element) {
        $text = strip_tags($element->innertext);
        $word_count = str_word_count($text);
        $tag_count = substr_count($element->innertext, '<');
        if ($tag_count == 0) $tag_count = 1;
        return $word_count / $tag_count;
    }

    private function write_txt($item, $item_txt, $item_arr, $end_txt) {
        $item_end_value = file_exists($end_txt)?file_get_contents($end_txt):'';
        $item_end_arr   = explode("\n", $item_end_value);

        array_push($item_end_arr, $item);
        $item_arr = array_diff($item_arr, $item_end_arr);
        file_put_contents($item_txt, implode("\n", $item_arr));
        file_put_contents($end_txt, implode("\n", $item_end_arr));
    }

    public function log_mess($mess, $on_log = true, $update_mess = false, $sleep = 0) {
        if ($update_mess) {
            update_option('vnrewrite_mess', $mess);
            update_option('vnrewrite_mess_time', time());
            if ($sleep > 0) {
                sleep($sleep);
            }
        }

        if (isset($this->options['log']) && $on_log) {
            $log_file = VNREWRITE_PATH . 'log.txt';
            if (!file_exists($log_file)) {
                touch($log_file);
                chmod($log_file, 0644);
            }
            file_put_contents($log_file, '[' . current_datetime()->format('d-m-Y H:i') . '] ' . wp_strip_all_tags($mess) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
}
?>