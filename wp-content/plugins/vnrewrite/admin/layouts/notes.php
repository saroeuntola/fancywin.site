<div class="poststuff">
    <div class="postbox" style="border: 1px solid #c3c4c7; border-top: none;">
        <div class="postbox-header">
            <h2>Cron Job</h2>
        </div>
        <div class="inside" style="padding: 0 20px 20px 20px;">
            <p><strong>- Bước 1</strong>: Nếu button <code>3. DISABLE_WP_CRON</code> là <strong>đang bật</strong> thì hãy click để tắt nó đi</p>
            <p><strong>- Bước 2</strong>: Nếu button <code>3. DISABLE_WP_CRON</code> là <strong>đang tắt</strong> mà cron vẫn không hoạt động thì kiểm tra xem button <code>2. ALTERNATE_WP_CRON</code> có phải là <strong>đang tắt</strong> hay không? Nếu đúng là <strong>đang tắt</strong> thì hãy click vào để bật lên</p>
            <p><strong>- Bước 3</strong>: Nếu thực hiện 2 bước trên mà cron vẫn không hoạt động thì hãy set cron server:</p>
            <p><code><strong>*/1 * * * * wget -q -O – <?php echo home_url('/'); ?>wp-cron.php?doing_wp_cron >/dev/null 2>&1</strong></code></p>
            <p style="font-style: italic;">(*) Xem một số cách tạo cronjob server dưới đây:</p>
            <p>- Tạo cronjob trên Cpanel: <a target="_blank" href="https://wiki.matbao.net/kb/huong-dan-su-dung-cron-jobs-tren-cpanel/">https://wiki.matbao.net/kb/huong-dan-su-dung-cron-jobs-tren-cpanel/</a></p>
            <p>- Tạo cronjob trên DirectAdmin: <a target="_blank" href="https://wiki.tino.org/docs/huong-dan-tao-cron-jobs-tren-directadmin/">https://wiki.tino.org/docs/huong-dan-tao-cron-jobs-tren-directadmin/</a></p>
            <p>- Tạo cronjob trên Cyberpanel: <a target="_blank" href="https://wiki.tino.org/docs/cronjob-tren-cyberpanel/">https://wiki.tino.org/docs/cronjob-tren-cyberpanel/</a></p>
        </div>
    </div>
    
    <div class="postbox" style="border: 1px solid #c3c4c7; border-top: none;">
        <div class="postbox-header">
            <h2>Liên hệ</h2>
        </div>
        <div class="inside" style="padding: 0 20px 20px 20px;">
            <p>- Phone/zalo: 033 439 0000</p>
            <p>- FB: <a target="_blank" href="https://www.facebook.com/thienvt36">https://www.facebook.com/thienvt36</a> (Để được hỗ trợ nhanh nhất vui lòng inbox)</p>
            <p>- GR: <a target="_blank" href="https://www.facebook.com/groups/codengao">https://www.facebook.com/groups/codengao</a></p>
            <p>- Website: <a target="_blank" href="https://vncrawl.com">https://vncrawl.com</a>, <a target="_blank" href="https://vnrewrite.com">https://vnrewrite.com</a>, <a target="_blank" href="https://vngpt.pro">https://vngpt.pro</a>, <a target="_blank" href="https://vnstories.com">https://vnstories.com</a>, <a target="_blank" href="https://thienvt.com">https://thienvt.com</a></p>
        </div>
    </div>
</div>


<?php  
    $current_status = get_option('vnrewrite_wp_config_status', array());
    $debug_log_status = !empty($current_status['debug_log']);
    $alt_cron_status = !empty($current_status['alternate_cron']);
    $cron_status = !empty($current_status['disable_wp_cron']);
?>

<div class="poststuff">
    <div class="postbox" style="border: 1px solid #c3c4c7; border-top: none; width: 100%;">
        <div class="postbox-header">
            <h2>WP Config</h2>
        </div>
        <div class="inside" style="padding: 0 20px 20px 20px;">
            <p><code>3 button bên dưới can thiệp vào file wp-config.php mục đích để mình hỗ trợ người dùng khi plugin có lỗi. Không nên tự ý thao tác nếu không hiểu rõ, hãy <a target="_blank" href="https://www.facebook.com/thienvt36">inbox fb</a> để được hỗ trợ</code></p>
            <div style="margin-top: 20px;">
                <a href="#" class="button <?php echo (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) ? 'button-primary' : 'button-secondary'; ?> vnrewrite-toggle-button" data-action="toggle_debug_log">1. 
                    <?php echo (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) ? '<strong>WP_DEBUG_LOG</strong> (đang bật)' : '<strong>WP_DEBUG_LOG</strong> (đang tắt)'; ?>
                </a>
                <a href="<?php echo wp_nonce_url(add_query_arg('vnrewrite_action', 'toggle_alternate_cron'), 'vnrewrite_config_action'); ?>" 
                   class="button <?php echo $alt_cron_status ? 'button-primary' : 'button-secondary'; ?> vnrewrite-toggle-button">2. 
                    <?php echo $alt_cron_status ? '<strong>ALTERNATE_WP_CRON</strong> (đang bật)' : '<strong>ALTERNATE_WP_CRON</strong> (đang tắt)'; ?>
                </a>
                <a href="<?php echo wp_nonce_url(add_query_arg('vnrewrite_action', 'toggle_wp_cron'), 'vnrewrite_config_action'); ?>" 
                   class="button <?php echo $cron_status ? 'button-primary' : 'button-secondary'; ?> vnrewrite-toggle-button">3. 
                    <?php echo $cron_status ? '<strong>DISABLE_WP_CRON</strong> (đang bật)' : '<strong>DISABLE_WP_CRON</strong> (đang tắt)'; ?>
                </a>
            </div>

            <div id="vnrewrite-error-log-container" style="margin-top: 20px;<?php echo !$debug_log_status ? ' display: none;' : ''; ?>">
                <strong>debug.log</strong>
                <textarea id="vnrewrite-error-log" rows="20" style="width: 100%;" readonly></textarea>
                <button id="vnrewrite-refresh-log" class="button">Refresh Log</button>
                <button id="vnrewrite-clear-log" class="button">Clear Log</button>
            </div>
        </div>
    </div>
</div>