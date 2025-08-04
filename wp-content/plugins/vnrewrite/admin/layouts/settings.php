<?php
    $log_file = VNREWRITE_PATH . 'log.txt'; 
    $log = file_exists($log_file)?file_get_contents($log_file):'';

    if($cmd == 'clear-log' && $log != ''){
        wp_delete_file($log_file);
        echo '<script type="text/javascript">window.location="' . VNREWRITE_ADMIN_PAGE . '&notice=clear-log-success"</script>';
    }

    if (isset($_GET['notice']) && $_GET['notice'] == 'clear-log-success') {
        echo '<script>window.history.pushState("", "", "' . VNREWRITE_ADMIN_PAGE . '");</script>';
        echo '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"><p><strong>Xóa log thành công!</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    }
?>

<div class="poststuff" style="<?php if(isset($_GET['tab'])){echo 'display: none;';} ?>">
    <div class="postbox">
        <div class="postbox-header">
            <h2>Cấu hình</h2>
        </div>
        <div class="inside">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="user_key">User key</label></th>
                        <td>
                            <?php  
                                $user_key = isset($this->options['user_key'])?esc_attr($this->options['user_key']):'';
                            ?>
                            <input name="vnrewrite_option[user_key]" id="user_key" class="regular-text" value="<?php echo $user_key; ?>" type="text" required>
                            <p class="description">
                                <?php if ($user_key == ''): ?>
                                    <span class="red-text">Chưa nhập User key! </span> Đăng nhập vào tài khoản của bạn tại <a target="_blank" href="https://vnrewrite.com">vnrewrite.com</a> để lấy user key
                                <?php else: ?>
                                    <?php
                                        $end_time = get_option('vnrewrite_end_time', '');

                                        if ($end_time == -1) {
                                            echo '<span class="green-text">Hạn sử dụng:</span> <span class="blinker">Lifetime</span>';
                                        }elseif($end_time == 1 || $user_key == ''){
                                            echo '<span class="red-text">Chưa nhập User key! </span>';
                                        }elseif($end_time == 2){
                                            echo '<span class="red-text">User key không hợp lệ! </span>';
                                        }elseif($end_time == 3){
                                            echo '<span class="red-text">Tên miền không hợp lệ! </span>';
                                        }elseif ($end_time > 3) {
                                            $end_date = date('d-m-Y', get_option('gmt_offset')*60*60 + $end_time);
                                            if($end_time >= time()){
                                                if(($end_time - time()) <= 7*24*60*60){
                                                    echo '<span class="orange-text">Sắp hết hạn sử dụng:</span> <span class="blinker">' . $end_date . '</span>';
                                                }else{
                                                    echo '<span class="green-text">Hạn sử dụng:</span> <span class="blinker">' . $end_date . '</span>';
                                                }
                                            }else{
                                                echo '<span class="red-text">Tài khoản đã hết hạn sử dụng! </span><span class="blinker">' . $end_date . '</span>. Gia hạn <a target="_blank" href="https://www.facebook.com/groups/codengao/permalink/1737628890000537">tại đây</a>';
                                            }
                                        }
                                    ?>
                                <?php endif ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="type_ai">Loại AI</label></th>
                        <td>
                            <select form="vnrewrite-form" name="vnrewrite_option[type_ai]" id="type_ai">
                                <?php
                                    $type_ai = isset($this->options['type_ai'])?$this->options['type_ai']:'gemini';
                                ?>
                                <option value="gemini" <?php selected($type_ai, 'gemini'); ?>>Gemini</option>
                                <option value="openai" <?php selected($type_ai, 'openai'); ?>>OpenAI</option>
                                <option value="claude" <?php selected($type_ai, 'claude'); ?>>Claude</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="lang">Ngôn ngữ</label></th>
                        <td>
                            Viết lại sang tiếng
                            <select form="vnrewrite-form" name="vnrewrite_option[lang]" id="lang">
                                <?php
                                    $lang = isset($this->options['lang'])?$this->options['lang']:'Việt';
                                    $lang_arr = ['Ả Rập', 'Bengali', 'Bulgaria', 'Trung', 'Croatia', 'Séc', 'Đan Mạch', 'Hà Lan', 'Anh', 'Estonia', 'Phần Lan', 'Pháp', 'Đức', 'Hy Lạp', 'Do Thái', 'Hindi', 'Hungary', 'Indonesia', 'Ý', 'Nhật', 'Hàn', 'Latvia', 'Lithuania', 'Na Uy', 'Ba Lan', 'Bồ Đào Nha', 'Romania', 'Nga', 'Serbia', 'Slovak', 'Slovenia', 'Tây Ban Nha', 'Swahili', 'Thuỵ Điển', 'Thái', 'Thổ Nhĩ Kỳ', 'Ukraina', 'Việt'];
                                    foreach ($lang_arr as $lang_name) {
                                        echo '<option value="' . $lang_name . '" ' . selected($lang, $lang_name, false) . '>' . $lang_name . '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="rewrite_type">Tự động rewrite</label></th>
                        <td>
                            <p>
                                <select form="vnrewrite-form" name="vnrewrite_option[rewrite_type]" id="rewrite_type">
                                    <?php
                                        $rewrite_type = isset($this->options['rewrite_type'])?$this->options['rewrite_type']:'';
                                    ?>
                                    <option value="" <?php selected($rewrite_type, ''); ?>>Chọn loại rewrite (Tắt)</option>
                                    <option value="post" <?php selected($rewrite_type, 'post'); ?>>Bài viết</option>
                                    <option value="url" <?php selected($rewrite_type, 'url'); ?>>Url</option>
                                    <option value="keyword" <?php selected($rewrite_type, 'keyword'); ?>>Keyword</option>
                                    <option value="video" <?php selected($rewrite_type, 'video'); ?>>Video ID Youtube</option>
                                </select>
                                <span id="show-cron-time" style="<?php if($rewrite_type == ''){echo 'display:none';} ?>">
                                    <label for="cron_time">Tự động rewrite sau mỗi</label> 
                                    <input name="vnrewrite_option[cron_time]" id="cron_time" class="small-text" value="<?php echo isset($this->options['cron_time'])?esc_attr($this->options['cron_time']):'1'; ?>" type="number" min="1"> phút.
                                </span>
                            </p>

                            <div id="show-cron-post" style="<?php if($rewrite_type != 'post'){echo 'display:none';} ?>">
                                <hr>
                                <p style="margin-top: 15px;">
                                    <label for="cron_cat">Chỉ rewrite bài viết thuộc danh mục</label>
                                    <?php  
                                        $args_cate_cron = array(
                                            'show_count' => 1,
                                            'hide_empty' => 0,
                                            'hierarchical' => 1,
                                            'show_option_none' => 'All',
                                            'option_none_value' => '',
                                            'selected' => isset($this->options['cron_cat'])?$this->options['cron_cat']:'',
                                            'name' => 'vnrewrite_option[cron_cat]',
                                            'id' => 'cron_cat',
                                            'class' => ''
                                        );
                                        wp_dropdown_categories($args_cate_cron);
                                    ?>
                                    <label for="cron_status">và có trạng thái</label>
                                    <select form="vnrewrite-form" name="vnrewrite_option[cron_status]" id="cron_status">
                                        <?php
                                            $cron_status = isset($this->options['cron_status'])?$this->options['cron_status']:'';
                                        ?>
                                        <option value="" <?php selected($cron_status, ''); ?>>All</option>
                                        <option value="draft" <?php selected($cron_status, 'draft'); ?>>Bản nháp</option>
                                        <option value="publish" <?php selected($cron_status, 'publish'); ?>>Đã xuất bản</option>
                                    </select>
                                </p>
                            </div>

                            <div id="cron-video" style="<?php if($rewrite_type != 'video'){echo 'display:none;';} ?> margin-top: 15px">
                                <hr>
                                <p>Tự động cập nhật <strong>youtube id</strong> mới nhất từ <strong>youtube id list</strong> cho các danh mục sau mỗi
                                <input name="vnrewrite_option[cron_time_video_id_list]" id="cron_time_video_id_list" class="small-text" value="<?php echo isset($this->options['cron_time_video_id_list'])?esc_attr($this->options['cron_time_video_id_list']):'0'; ?>" type="number" min="0"> giờ. <code>(Set = 0 sẽ dừng)</code></p>
                                <ul class="cate-video-id-list">
                                    <?php
                                        $categories = get_categories(array(
                                            'hide_empty' => false,
                                            'hierarchical' => true
                                        ));

                                        $str_cate = '';
                                        foreach ($categories as $category) {
                                            $video_id_list_cate = get_term_meta($category->term_id, 'video_id_list', true);
                                            $str_cate .= '<li>';
                                                $str_cate .= '<label for="video_id_list_cate' . $category->term_id . '">' . esc_html($category->name) . '</label>';
                                                $str_cate .= '<textarea name="vnrewrite_option[video_id_list_cate][' . $category->term_id . ']" id="video_id_list_cate' . $category->term_id . '" class="large-text" rows="3">' . $video_id_list_cate . '</textarea>';
                                            $str_cate .= '</li>';
                                        }
                                        echo $str_cate;
                                    ?>

                                </ul>
                                <p class="clear-fix">- Nếu sử dụng nhiều <strong>youtube id list</strong> thì các youtube id list phân tách nhau bởi <code><strong>|</strong></code>. Ví dụ: <code><strong>youtube_id_list1|youtube_id_list2|youtube_id_list3</strong></code></p>
                                <p>- Khi một kênh youtube có video mới được thêm vào video list của kênh thì nó sẽ tự động lấy video mới và tạo thành bài viết</p>
                            </div>
                            <div id="show-cron-keyword" style="<?php if($rewrite_type != 'keyword'){echo 'display:none';} ?>">
                                <p style="margin-top: 15px;">
                                    <?php $gg_search_api = isset($this->options['gg_search_api'])?esc_attr($this->options['gg_search_api']):''; ?>
                                    <label for="gg_search_api">Custom Search API</label>
                                    <textarea name="vnrewrite_option[gg_search_api]" id="gg_search_api" class="large-text" rows="5"><?php echo $gg_search_api; ?></textarea>
                                    <p>- Để sử dụng tính năng này cần <a target="_blank" href="https://console.cloud.google.com/?hl=vi">bật Google Custom Search API và tạo api</a>. Với mỗi tài khoản Google có thể tạo 12 dự án, mỗi dự án chỉ tạo 1 api. Mỗi api được search miễn phí 100 lần/ngày. <a target="_blank" href="https://www.youtube.com/watch?v=moxTam1iJsw">Xem video hướng dẫn</a></p>
                                    <p>- Nếu sử dụng nhiều api key thì các api sẽ được sử dụng xoay vòng. Các api key phân tách nhau bởi <code><strong>|</strong></code>. Ví dụ: <code><strong>key1|key2|key3</strong></code></p>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gg_api_yt">Google API Youtube</label></th>
                        <td>
                            <?php $gg_api_yt = isset($this->options['gg_api_yt'])?esc_attr($this->options['gg_api_yt']):''; ?>
                            <input name="vnrewrite_option[gg_api_yt]" id="gg_api_yt" class="regular-text" value="<?php echo $gg_api_yt; ?>" type="text">
                            <p>- <a target="_blank" href="https://console.cloud.google.com/?hl=vi">Google API Youtube</a> dùng để lấy danh sách video từ list video id youtube. Nếu không thực hiện lấy video từ list video thì không cần nhập. <a target="_blank" href="https://www.youtube.com/watch?v=VRqQmbT32Vg">Xem video hướng dẫn</a></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="cron_publish">Hẹn giờ publish</label></th>
                        <td>
                            Auto publish sau mỗi
                            <input name="vnrewrite_option[cron_publish]" id="cron_publish" class="small-text" value="<?php echo isset($this->options['cron_publish'])?esc_attr($this->options['cron_publish']):'0'; ?>" type="number" min="0"> phút. <code>(Set = 0 sẽ dừng)</code>
                            <p>- Chỉ tự động xuất bản những bài viết có trạng thái <code><strong>draft</strong></code> và <code><strong>đã rewrite</strong></code></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="draft">User</label></th>
                        <td>
                            User đăng bài
                            <select form="vnrewrite-form" name="vnrewrite_option[user]" id="user">
                                <?php
                                    $cur_user = isset($this->options['user'])?$this->options['user']:1;
                                    $users = get_users(array('fields' => array('ID', 'user_login')));
                                    foreach ($users as $user) {
                                        echo '<option value="' . $user->ID . '" ' . selected($cur_user, $user->ID, false) . '>' . $user->user_login . '</option>';
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="draft">Bản nháp</label></th>
                        <td>
                            <input name="vnrewrite_option[draft]" id="draft" type="checkbox" value="1" <?php checked(isset($this->options['draft'])?1:'', 1); ?>>
                            <label for="draft">Bài viết sau khi rewrite sẽ có trạng thái là bản nháp</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="format_img">Hình ảnh</label></th>
                        <td>
                            <?php
                                $format_img = isset($this->options['format_img'])?$this->options['format_img']:'jpg';
                            ?>
                            <label for="format_img">Định dạng ảnh</label>
                            <select form="vnrewrite-form" name="vnrewrite_option[format_img]" id="format_img">
                                <option value="jpg" <?php selected($format_img, 'jpg'); ?>>jpg</option>
                                <option value="png" <?php selected($format_img, 'png'); ?>>png</option>
                                <option value="webp" <?php selected($format_img, 'webp'); ?>>webp</option>
                            </select>
                            <hr>
                            <?php
                                $resize_img = isset($this->options['resize_img'])?$this->options['resize_img']:0;
                            ?>
                            <label for="resize_img">Resize ảnh theo chiều ngang</label>
                            <input name="vnrewrite_option[resize_img]" id="resize_img" class="small-text" value="<?php echo $resize_img; ?>" type="number" min="0">
                            <code>(Set = 0 sẽ không resize)</code>
                            <p>- Nếu bài viết gốc có ảnh thì sẽ được tải về web và thay đổi theo các thông số đã set</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="link_cur">Liên kết nội bộ</label></th>
                        <td>
                            <p style="margin-bottom:15px">
                                <input name="vnrewrite_option[link_cur]" id="link_cur" type="checkbox" value="1" <?php checked(isset($this->options['link_cur'])?1:'', 1); ?>>
                                <label for="link_cur">Link về chính bài viết với textlink là keyword (Dành riêng cho rewrite keyword)</label>
                            </p>

                            <input name="vnrewrite_option[link_brand]" id="link_brand" type="checkbox" value="1" <?php checked(isset($this->options['link_brand'])?1:'', 1); ?>>
                            <label for="link_brand">Link về home với textlink là brand</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="log">Options</label></th>
                        <td>
                            <p style="margin-bottom: 8px;">
                                <input name="vnrewrite_option[log]" id="log" type="checkbox" value="1" <?php checked(isset($this->options['log'])?1:'', 1); ?>>
                                <label for="log">Bật log để ghi lại lỗi (nếu có)</label>
                            </p>
                            <textarea id="vnrewrite-log" class="large-text" rows="5"><?php echo $log; ?></textarea>
                            <?php if ($log != ''): ?>
                                <p>
                                    <a id="clear-log" class="button red-text" href="<?php echo VNREWRITE_ADMIN_PAGE . '&cmd=clear-log'; ?>">Xóa log</button>
                                    <a style="margin-left:5px" id="download-log" class="button" href="<?php echo VNREWRITE_URL . 'log.txt'; ?>" download>Tải log</a>
                                </p>
                            <?php endif ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="postbox">
        <div class="postbox-header">
            <h2>AI tạo content</h2>
        </div>
        <div class="inside">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="gemini_api_key">Gemini</label></th>
                        <td>
                            <p style="margin-bottom: 8px">API key</p>
                            <?php $gemini_api_key = isset($this->options['gemini_api_key'])?esc_attr($this->options['gemini_api_key']):''; ?>
                            <textarea name="vnrewrite_option[gemini_api_key]" id="gemini_api_key" class="large-text" rows="5"><?php echo $gemini_api_key; ?></textarea>
                            <?php if ($gemini_api_key == ''): ?>
                                <p class="red-text description">Chưa có Gemini API key!</p>
                            <?php endif ?>
                            <p>- Nếu sử dụng nhiều <a target="_blank" href="https://console.cloud.google.com">api key</a> thì các api sẽ được sử dụng xoay vòng. Các api key phân tách nhau bởi <code><strong>|</strong></code>. Ví dụ: <code><strong>key1|key2|key3</strong></code>. <a target="_blank" href="https://www.youtube.com/watch?v=sB5MPyb33Js">Xem video hướng dẫn</a></p>
                            <hr>
                            <?php
                                $gemini_model = isset($this->options['gemini_model'])?$this->options['gemini_model']:'gemini-1.5-pro';
                            ?>
                            Model
                            <select form="vnrewrite-form" name="vnrewrite_option[gemini_model]" id="gemini_model">
                                <option value="gemini-1.5-pro" <?php selected($gemini_model, 'gemini-1.5-pro'); ?>>gemini-1.5-pro</option>
                                <option value="gemini-1.5-flash" <?php selected($gemini_model, 'gemini-1.5-flash'); ?>>gemini-1.5-flash</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="openai_api_key" class="form-label">OpenAI</label></th>
                        <td>
                            <p style="margin-bottom: 8px">API key</p>
                            <?php $openai_api_key = isset($this->options['openai_api_key'])?esc_attr($this->options['openai_api_key']):''; ?>
                            <textarea name="vnrewrite_option[openai_api_key]" id="openai_api_key" class="large-text" rows="5"><?php echo $openai_api_key; ?></textarea>
                            <?php if ($openai_api_key == ''): ?>
                                <p class="red-text">Chưa có Openai API key!</p>
                            <?php endif ?>
                            <p>- Nếu sử dụng nhiều <a target="_blank" href="https://platform.openai.com/api-keys">api key</a> thì các api sẽ được sử dụng xoay vòng. Các api key phân tách nhau bởi <code><strong>|</strong></code>. Ví dụ: <code><strong>key1|key2|key3</strong></code></p>
                            <p>- Nếu sử dụng API key của toptukhoa.com thì cần nối thêm <code><strong>toptukhoa-</strong></code> vào trước API key. Ví dụ: <code><strong>toptukhoa-sk-8jP7Rsyq3H2Cnr4HtTdRY7kroctpy2PJuPQ6doAjPSt3AbkvKw5</strong></code></p>
                            <hr>
                            <?php
                                $openai_model = isset($this->options['openai_model'])?$this->options['openai_model']:'gpt-4o';
                            ?>
                            Model
                            <select form="vnrewrite-form" name="vnrewrite_option[openai_model]" id="openai_model">
                                <option value="gpt-4o" <?php selected($openai_model, 'gpt-4o'); ?>>gpt-4o</option>
                                <option value="gpt-4o-mini" <?php selected($openai_model, 'gpt-4o-mini'); ?>>gpt-4o-mini</option>
                                <option value="gpt-4-turbo" <?php selected($openai_model, 'gpt-4-turbo'); ?>>gpt-4-turbo</option>
                                <option value="gpt-3.5-turbo-16k" <?php selected($openai_model, 'gpt-3.5-turbo-16k'); ?>>gpt-3.5-turbo-16k</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="claude_api_key" class="form-label">Claude</label></th>
                        <td>
                            <p style="margin-bottom: 8px">API key</p>
                            <?php $claude_api_key = isset($this->options['claude_api_key'])?esc_attr($this->options['claude_api_key']):''; ?>
                            <textarea name="vnrewrite_option[claude_api_key]" id="claude_api_key" class="large-text" rows="5"><?php echo $claude_api_key; ?></textarea>
                            <?php if ($claude_api_key == ''): ?>
                                <p class="red-text">Chưa có Claude API key!</p>
                            <?php endif ?>
                            <p>- Nếu sử dụng nhiều <a target="_blank" href="https://console.anthropic.com/settings/keys">api key</a> thì các api sẽ được sử dụng xoay vòng. Các api key phân tách nhau bởi <code><strong>|</strong></code>. Ví dụ: <code><strong>key1|key2|key3</strong></code></p>
                            <p>- Nếu sử dụng API key của toptukhoa.com thì cần nối thêm <code><strong>toptukhoa-</strong></code> vào trước API key. Ví dụ: <code><strong>toptukhoa-sk-8jP7Rsyq3H2Cnr4HtTdRY7kroctpy2PJuPQ6doAjPSt3AbkvKw5</strong></code></p>
                            <hr>
                            <?php
                                $claude_model = isset($this->options['claude_model'])?$this->options['claude_model']:'claude-3-5-sonnet-20240620';
                            ?>
                            Model
                            <select form="vnrewrite-form" name="vnrewrite_option[claude_model]" id="claude_model">
                                <option value="claude-3-5-sonnet-20240620" <?php selected($claude_model, 'claude-3-5-sonnet-20240620'); ?>>Claude 3.5 Sonnet</option>
                                <option value="claude-3-opus-20240229" <?php selected($claude_model, 'claude-3-opus-20240229'); ?>>Claude 3 Opus</option>
                                <option value="claude-3-sonnet-20240229" <?php selected($claude_model, 'claude-3-sonnet-20240229'); ?>>Claude 3 Sonnet</option>
                                <option value="claude-3-haiku-20240307" <?php selected($claude_model, 'claude-3-haiku-20240307'); ?>>Claude 3 Haiku</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>