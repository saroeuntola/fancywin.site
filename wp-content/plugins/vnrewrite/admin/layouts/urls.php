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
            'name' => 'cat_urls',
            'id' => 'cat-urls',
            'class' => ''
        );
        wp_dropdown_categories($args_cates);
    ?>
</p>
<?php if ($cat != ''): ?>
    <?php  
        $url        = 'url_' . $cat;
        $url_active = 'url_active_' . $cat;
        $url_miss   = 'url_miss_' . $cat;

        $url_txt        = VNREWRITE_DATA . $url . '.txt';
        $url_active_txt = VNREWRITE_DATA . $url_active . '.txt';
        $url_miss_txt   = VNREWRITE_DATA . $url_miss . '.txt';

        $url_value        = file_exists($url_txt)?file_get_contents($url_txt):'';
        $url_active_value = file_exists($url_active_txt)?file_get_contents($url_active_txt):'';
        $url_miss_value   = file_exists($url_miss_txt)?file_get_contents($url_miss_txt):'';

        function vnrewrite_line_count($file){
            if (file_exists($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                return count($lines);
            }else{
                return 0;
            }
        }
    ?>

    <div id="list-url" class="list-txt">
        <div class="poststuff">
            <div class="postbox">
                <div class="postbox-header">
                    <h2>Urls chưa rewrite (<?php echo vnrewrite_line_count($url_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $url; ?>" class="large-text" rows="20"><?php echo $url_value; ?></textarea>
                </div>
            </div>
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="green-text">Urls rewrite thành công (<?php echo vnrewrite_line_count($url_active_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $url_active; ?>" class="large-text" rows="20"><?php echo $url_active_value; ?></textarea>
                </div>
            </div>
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="red-text">Urls rewrite thất bại (<?php echo vnrewrite_line_count($url_miss_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $url_miss; ?>" class="large-text" rows="20"><?php echo $url_miss_value; ?></textarea>
                </div>
            </div>
        </div>
        <?php  
            if ($cat != ''){
                echo '<button type="button" class="button button-primary" id="save-url" data-cat="' . $cat . '">Save</button>';
            }
        ?>
        <p class="orange-text"><em>(*) Mỗi url trên 1 dòng</em></p>
    </div>
<?php endif ?>