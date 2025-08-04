<style>#show-cron-keyword{margin-top: 20px}#show-cron-post{margin-top: 20px}.poststuff{display:flex;margin-right:-15px;margin-left:-15px;padding-top:0;min-width: 763px;}.postbox{margin:15px;width: calc(50% - 30px);box-shadow: none;}p.submit{margin-top:0;padding-top:0}.form-table th{width:160px}.green-text{color:darkgreen!important}.orange-text{color:darkorange!important}.red-text{color:#dc3545!important}code{font-size:.875em;color:#d63384;word-wrap:break-word}.text-center{text-align:center!important}.text-right{text-align:right!important}.text-nowrap{white-space:nowrap!important}.wp-list-table{margin-top:10px}#show-post select{vertical-align:top}.tablenav-pages{margin-top:10px!important}.vnrewrite-warning{border-left:4px solid #007bba;padding:3px 10px;margin:10px 0;background:#f7ecd7;display:block}.vnrewrite-warning p{font-size:14px}#the-list tr:hover{background-color:#ddd}#the-list td,#the-list th{vertical-align:middle;padding:3px}.gpt-parameter{display:flex;margin:0}.gpt-parameter li label{display:inline-block;margin-bottom:5px}#setting-error-settings_updated{margin:20px 0}@media (max-width:1140px){.poststuff{display:block;margin-left:0;margin-right:0}.postbox{width:100%;margin:15px 0}}.blinker{color:purple!important;animation:1s linear infinite blinker;font-size:1rem}@keyframes blinker{50%{opacity:0}}#vnrewrite-form th, #vnrewrite-form td{border: 1px solid #c5c7c9}#vnrewrite-form th{padding-left: 15px}.postbox-header{background-color:#d7e9f7;border: 1px solid #c3c4c7;border-bottom: none;}.poststuff .inside{margin:0;padding: 0}.postbox{border: none}#vnrewrite-form table{margin-top: 0}ul.cate-video-id-list li{width:50%; float: left;}.poststuff .stuffbox>h3, .poststuff h2, .poststuff h3.hndle{font-size: 14px;padding: 8px 12px;margin: 0;line-height: 1.4;}.vnrewrite textarea::-webkit-scrollbar{width: 8px;height: 8px;}.vnrewrite textarea::-webkit-scrollbar-track{background-color: #f1f1f1;}.vnrewrite textarea::-webkit-scrollbar-thumb{background-color: #999;border-radius: 4px;}.vnrewrite textarea::-webkit-scrollbar-thumb:hover{background-color: #777;}ul.category-list{max-width:100%}ul.category-list li{display:inline-block;width:33.33%}ul.category-list ul.children{display:inline-block}@media screen and (max-width: 782px){ul.category-list li{width:50%;margin:10px 0}}@media screen and (max-width: 360px){ul.category-list li{width:100%;margin:10px 0}}.clear-fix{clear: both;}#list-url .poststuff{display:block;padding:0 15px}#list-url .postbox{width:100%;margin:15px 0}#list-url .postbox-header{margin-right:16px}.list-txt .postbox-header{margin-right: 5px;}.vnrewrite textarea, .vnrewrite select, .vnrewrite input, .vnrewrite #submit{border-radius: 0}</style>

<div class="wrap vnrewrite">
    <h1 class="wp-heading-inline">VnRewrite</h1>
    <?php  
        if (get_option('vnrewrite_warning') != '') {
            echo '<div class="vnrewrite-warning">' . get_option('vnrewrite_warning') . '</div>';
        }
    ?>
    <div id="mess-bound" class="text-center" style="margin-top:20px">
        <?php
            $mess = get_option('vnrewrite_mess');
            $mess_time  = get_option('vnrewrite_mess_time');
            if ($mess && $mess_time > 0 && $mess_time <= time()){
                echo '<span class="blinker" id="mess-time" data-mess-time="' . $mess_time . '"></span> s trước - <span id="mess">' . $mess . '</span>';
            }
        ?>
    </div>
    <hr class="wp-header-end">
    <p class="subsubsub" style="float: none; font-size: 14px;">
        <a href="<?php echo VNREWRITE_ADMIN_PAGE; ?>" class="<?php if(!isset($_GET['tab'])){echo 'current';} ?>">Settings</a> | 
        <a class="<?php if(isset($_GET['tab']) && $_GET['tab'] == 'prompts'){echo 'current';} ?>" href="<?php echo VNREWRITE_ADMIN_PAGE . '&tab=prompts'; ?>">Prompts</a> |
        <a class="<?php if(isset($_GET['tab']) && $_GET['tab'] == 'keywords'){echo 'current';} ?>" href="<?php echo VNREWRITE_ADMIN_PAGE . '&tab=keywords'; ?>">Keywords</a> |
        <a class="<?php if(isset($_GET['tab']) && $_GET['tab'] == 'urls'){echo 'current';} ?>" href="<?php echo VNREWRITE_ADMIN_PAGE . '&tab=urls'; ?>">Urls</a> |
        <a class="<?php if(isset($_GET['tab']) && $_GET['tab'] == 'videos-yt'){echo 'current';} ?>" href="<?php echo VNREWRITE_ADMIN_PAGE . '&tab=videos-yt'; ?>">Videos Youtube</a> |
        <a class="<?php if(isset($_GET['tab']) && $_GET['tab'] == 'notes'){echo 'current';} ?>" href="<?php echo VNREWRITE_ADMIN_PAGE . '&tab=notes'; ?>">Lưu ý</a>
    </p>
    <?php  
        $cmd = isset($_GET['cmd'])?$_GET['cmd']:'';
        if (!isset($_GET['tab']) || $_GET['tab'] == 'prompts') {
            echo '<form method="post" action="options.php" id="vnrewrite-form">';
                require_once VNREWRITE_PATH . 'admin/layouts/settings.php';
                require_once VNREWRITE_PATH . 'admin/layouts/prompts.php';

                submit_button();
                settings_fields('vnrewrite_option_group');
                do_settings_sections('vnrewrite-admin');
            echo '</form>';
        }else{
            if ($_GET['tab'] == 'keywords') {
                require_once VNREWRITE_PATH . 'admin/layouts/keywords.php';
            }elseif ($_GET['tab'] == 'urls') {
                require_once VNREWRITE_PATH . 'admin/layouts/urls.php';
            }elseif ($_GET['tab'] == 'videos-yt') {
                require_once VNREWRITE_PATH . 'admin/layouts/videos_yt.php';
            }elseif ($_GET['tab'] == 'notes') {
                require_once VNREWRITE_PATH . 'admin/layouts/notes.php';
            }
        }
    ?>
</div>