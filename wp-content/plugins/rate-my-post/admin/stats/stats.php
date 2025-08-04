<?php

class Rate_My_Post_Stats
{
    public static $stats_record;

    public static function init()
    {
        add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
        add_filter('set_screen_option_sync_rules_per_page', [__CLASS__, 'set_screen'], 10, 3);
    }

    public static function screen_option()
    {
        $option = 'per_page';
        $args   = [
            'label'   => esc_html__('Stats', 'rate-my-post'),
            'default' => 20,
            'option'  => 'stats_per_page'
        ];

        add_screen_option($option, $args);

        require_once dirname(__FILE__) . '/stats_list.php';

        self::$stats_record = new Rate_My_Post_Stats_List();
    }

    public static function admin_page_callback()
    {
        ?>
        <div class="wrap rmp">

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-2">

                    <div id="post-body-content">

                        <p><?php esc_html_e('List of rated posts and pages! To see feedback or change ratings click on a post/page title below and find the FeedbackWP Metabox at the bottom.', 'rate-my-post'); ?></p>
                        <form method="post">
                            <?php
                            self::$stats_record->prepare_items();
                            self::$stats_record->display(); ?>
                        </form>

                    </div>

                    <div id="postbox-container-1" class="postbox-container">

                        <div class="meta-box-sortables">
                            <?php Rate_My_Post_Admin::sidebar_content(); ?>
                        </div>

                    </div>

                </div>

                <br class="clear">
            </div>

        </div>
        <?php
    }

    public static function set_screen($status, $option, $value)
    {
        if ('stats_per_page' == $option) {
            return $value;
        }

        return $status;
    }
}