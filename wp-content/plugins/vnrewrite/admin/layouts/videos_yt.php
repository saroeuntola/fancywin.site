<p style="margin-top: 10px; margin-bottom: 5px;">
    <?php
        $cat = isset($_GET['cat'])?$_GET['cat']:'';
        $args_cates = array(
            'show_count' => 0,
            'hide_empty' => 0,
            'hierarchical' => 1,
            'show_option_none' => 'Chọn danh mục',
            'option_none_value' => '',
            'selected' => $cat,
            'name' => 'cat_videos',
            'id' => 'cat-videos',
            'class' => ''
        );
        wp_dropdown_categories($args_cates);

        if ($cat != ''){
            echo '<span><input name="" id="video-list-id" class="regular-text" value="" type="text" placeholder="Nhập ID list video" style="margin: 0px 3px 0 10px;">
            <button type="button" class="button" id="get-video-list" data-cat="' . $cat . '">Get video from ID list</button></span>';
        }

    ?>
</p>
<?php if ($cat != ''): ?>
    <?php  
        $video        = 'video_' . $cat;
        $video_active = 'video_active_' . $cat;
        $video_miss   = 'video_miss_' . $cat;

        $video_txt        = VNREWRITE_DATA . $video . '.txt';
        $video_active_txt = VNREWRITE_DATA . $video_active . '.txt';
        $video_miss_txt   = VNREWRITE_DATA . $video_miss . '.txt';

        $video_value        = file_exists($video_txt)?file_get_contents($video_txt):'';
        $video_active_value = file_exists($video_active_txt)?file_get_contents($video_active_txt):'';
        $video_miss_value   = file_exists($video_miss_txt)?file_get_contents($video_miss_txt):'';

        function vnrewrite_line_count($file){
            if (file_exists($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                return count($lines);
            }else{
                return 0;
            }
        }
    ?>
    <div id="list-video" class="list-txt">
        <div class="poststuff">
            <div class="postbox">
                <div class="postbox-header">
                    <h2>Video ID Youtube chưa rewrite (<?php echo vnrewrite_line_count($video_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $video; ?>" class="large-text" rows="20"><?php echo $video_value; ?></textarea>
                </div>
            </div>
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="green-text">Video ID Youtube rewrite thành công (<?php echo vnrewrite_line_count($video_active_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $video_active; ?>" class="large-text" rows="20"><?php echo $video_active_value; ?></textarea>
                </div>
            </div>
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="red-text">Video ID Youtube rewrite thất bại (<?php echo vnrewrite_line_count($video_miss_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $video_miss; ?>" class="large-text" rows="20"><?php echo $video_miss_value; ?></textarea>
                </div>
            </div>
        </div>
        <?php  
            if ($cat != ''){
                echo '<button type="button" class="button button-primary" id="save-video" data-cat-video="' . $cat . '">Save</button>';
            }
        ?>
        <p class="orange-text"><em>(*) Mỗi video id Youtube trên 1 dòng</em></p>
    </div>
<?php endif ?>