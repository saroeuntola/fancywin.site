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
            'name' => 'cat_keywords',
            'id' => 'cat-keywords',
            'class' => ''
        );
        wp_dropdown_categories($args_cates);
    ?>
</p>
<?php if ($cat != ''): ?>
    <?php  
        $keyword        = 'keyword_' . $cat;
        $keyword_active = 'keyword_active_' . $cat;
        $keyword_miss   = 'keyword_miss_' . $cat;

        $keyword_txt        = VNREWRITE_DATA . $keyword . '.txt';
        $keyword_active_txt = VNREWRITE_DATA . $keyword_active . '.txt';
        $keyword_miss_txt   = VNREWRITE_DATA . $keyword_miss . '.txt';

        $keyword_value        = file_exists($keyword_txt)?file_get_contents($keyword_txt):'';
        $keyword_active_value = file_exists($keyword_active_txt)?file_get_contents($keyword_active_txt):'';
        $keyword_miss_value   = file_exists($keyword_miss_txt)?file_get_contents($keyword_miss_txt):'';

        function vnrewrite_line_count($file){
            if (file_exists($file)) {
                $lines = file($file, FILE_IGNORE_NEW_LINES);
                return count($lines);
            }else{
                return 0;
            }
        }
    ?>
    <div id="list-keyword" class="list-txt">
        <div class="poststuff">
            <div class="postbox">
                <div class="postbox-header">
                    <h2>Keywords chưa rewrite (<?php echo vnrewrite_line_count($keyword_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $keyword; ?>" class="large-text" rows="20"><?php echo $keyword_value; ?></textarea>
                </div>
            </div>
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="green-text">Keywords rewrite thành công (<?php echo vnrewrite_line_count($keyword_active_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $keyword_active; ?>" class="large-text" rows="20"><?php echo $keyword_active_value; ?></textarea>
                </div>
            </div>
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="red-text">Keywords rewrite thất bại (<?php echo vnrewrite_line_count($keyword_miss_txt); ?>)</h2>
                </div>
                <div class="inside">
                    <textarea wrap="off" id="<?php echo $keyword_miss; ?>" class="large-text" rows="20"><?php echo $keyword_miss_value; ?></textarea>
                </div>
            </div>
        </div>
        <?php  
            if ($cat != ''){
                echo '<button type="button" class="button button-primary" id="save-keyword" data-cat="' . $cat . '">Save</button>';
            }
        ?>
        <p class="orange-text"><em>(*) Mỗi keyword trên 1 dòng</em></p>
    </div>
<?php endif ?>