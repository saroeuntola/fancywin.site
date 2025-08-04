<?php

class Rate_My_Post_Analytics
{
    public static $analytics_record;

    public static function init()
    {
        add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
        add_filter('set_screen_option_sync_rules_per_page', [__CLASS__, 'set_screen'], 10, 3);
    }

    public static function screen_option()
    {
        $option = 'per_page';
        $args   = [
            'label'   => esc_html__('Analytics', 'rate-my-post'),
            'default' => 20,
            'option'  => 'analytics_per_page'
        ];

        add_screen_option($option, $args);

        require_once dirname( __FILE__ ) . '/analytics_list.php';

        self::$analytics_record = new Rate_My_Post_Analytics_List();
    }

    public static function admin_page_callback()
    {
        ?>
        <div class="wrap rmp">
            <div id="poststuff">
                <p><?php esc_html_e( 'Here you can see the details about the recent votes on your website.', 'rate-my-post' ); ?></p>
                <div id="post-body" class="metabox-holder">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <?php
                                self::$analytics_record->prepare_items();
                                self::$analytics_record->display(); ?>
                            </form>
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
        if ('analytics_per_page' == $option) {
            return $value;
        }

        return $status;
    }
}