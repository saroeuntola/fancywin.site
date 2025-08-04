<?php
class VnRewriteAdmin{
    private $options;
    function __construct(){
        add_action('admin_menu', array($this, 'add_page'));
        add_action('admin_init', array($this, 'init_page'));
    }
    public function create_page(){
        $this->options = get_option('vnrewrite_option');
        require_once VNREWRITE_PATH . 'admin/layouts/layout.php';
    }

    public function add_page(){
        add_options_page(
            'Settings Admin',
            'VnRewrite',
            'manage_options',
            'vnrewrite-admin',
            array($this, 'create_page')
        );
    }
    public function sanitize($input){
        $new_input = array();
        
        if(isset($input['user_key'])){
            $new_input['user_key'] = sanitize_text_field($input['user_key']);
        }
        if(isset($input['lang'])){
            $new_input['lang'] = sanitize_text_field($input['lang']);
        }else{
            $new_input['lang'] = 'Việt';
        }
        if(isset($input['type_ai'])){
            $new_input['type_ai'] = sanitize_text_field($input['type_ai']);
        }else{
            $new_input['type_ai'] = 'gemini';
        }
        if(isset($input['rewrite_type'])){
            $new_input['rewrite_type'] = sanitize_text_field($input['rewrite_type']);
        }
        if(isset($input['cron_time'])){
            $cron_time = sanitize_text_field($input['cron_time']);
            if ($cron_time == '' || $cron_time < 0) {
                $cron_time = 0;
            }
            $new_input['cron_time'] = $cron_time;
        }

        if(isset($input['cron_time_video_id_list'])){
            $cron_time_video_id_list = sanitize_text_field($input['cron_time_video_id_list']);
            if ($cron_time_video_id_list == '' || $cron_time_video_id_list < 0) {
                $cron_time_video_id_list = 0;
            }
            $new_input['cron_time_video_id_list'] = $cron_time_video_id_list;
        }

        if(isset($input['video_id_list_cate'])){
            $video_id_list_cate = $input['video_id_list_cate'];
            if (!empty($video_id_list_cate)) {
                foreach ($video_id_list_cate as $key => $value) {
                    update_term_meta($key, 'video_id_list', sanitize_textarea_field($value));
                }
            }
        }

        if(isset($input['cron_cat'])){
            $new_input['cron_cat'] = sanitize_text_field($input['cron_cat']);
        }
        if(isset($input['cron_status'])){
            $new_input['cron_status'] = sanitize_text_field($input['cron_status']);
        }
        if(isset($input['cron_publish'])){
            $cron_publish = sanitize_text_field($input['cron_publish']);
            if ($cron_publish == '') {
                $cron_publish = 0;
            }
            $new_input['cron_publish'] = $cron_publish;
        }
        if(isset($input['format_img'])){
            $new_input['format_img'] = sanitize_text_field($input['format_img']);
        }
        if(isset($input['resize_img'])){
            $new_input['resize_img'] = sanitize_text_field($input['resize_img']);
        }
        if(isset($input['gg_search_api'])){
            $new_input['gg_search_api'] = sanitize_textarea_field($input['gg_search_api']);
        }
        if(isset($input['gg_api_yt'])){
            $new_input['gg_api_yt'] = sanitize_textarea_field($input['gg_api_yt']);
        }
        if(isset($input['user'])){
            $user = sanitize_text_field($input['user']);
            if ($user == '') {
                $user = 1;
            }
            $new_input['user'] = $user;
        }
        if(isset($input['draft'])){
            $new_input['draft'] = sanitize_text_field($input['draft']);
        }
        if(isset($input['link_cur'])){
            $new_input['link_cur'] = sanitize_text_field($input['link_cur']);
        }
        if(isset($input['link_brand'])){
            $new_input['link_brand'] = sanitize_text_field($input['link_brand']);
        }
        if(isset($input['log'])){
            $new_input['log'] = sanitize_text_field($input['log']);
        }
        //prompts
        if(isset($input['vnrewrite_ai_as_common'])){
            $new_input['vnrewrite_ai_as_common'] = sanitize_textarea_field($input['vnrewrite_ai_as_common']);
        }
        if(isset($input['vnrewrite_ai_as_cate'])){
            $vnrewrite_ai_as_cate = $input['vnrewrite_ai_as_cate'];
            if (!empty($vnrewrite_ai_as_cate)) {
                foreach ($vnrewrite_ai_as_cate as $key => $value) {
                    update_term_meta($key, 'vnrewrite_ai_as_cate', sanitize_textarea_field(htmlspecialchars($value, ENT_QUOTES)));
                }
            }
        }
        if(isset($input['vnrewrite_prompt_common'])){
            $new_input['vnrewrite_prompt_common'] = sanitize_textarea_field($input['vnrewrite_prompt_common']);
        }
        if(isset($input['vnrewrite_prompt_cate'])){
            $vnrewrite_prompt_cate = $input['vnrewrite_prompt_cate'];
            if (!empty($vnrewrite_prompt_cate)) {
                foreach ($vnrewrite_prompt_cate as $key => $value) {
                    update_term_meta($key, 'vnrewrite_prompt_cate', sanitize_textarea_field(htmlspecialchars($value, ENT_QUOTES)));
                }
            }
        }

        //gemini
        if(isset($input['gemini_api_key'])){
            $new_input['gemini_api_key'] = sanitize_textarea_field($input['gemini_api_key']);
        }
        if(isset($input['gemini_model'])){
            $new_input['gemini_model'] = sanitize_text_field($input['gemini_model']);
        }else{
            $new_input['gemini_model'] = 'gemini-1.5-pro';
        }
        //openai
        if(isset($input['openai_api_key'])){
            $new_input['openai_api_key'] = sanitize_textarea_field($input['openai_api_key']);
        }
        if(isset($input['openai_model'])){
            $new_input['openai_model'] = sanitize_text_field($input['openai_model']);
        }else{
            $new_input['openai_model'] = 'gpt-4o';
        }
        //claude
        if(isset($input['claude_api_key'])){
            $new_input['claude_api_key'] = sanitize_textarea_field($input['claude_api_key']);
        }
        if(isset($input['claude_model'])){
            $new_input['claude_model'] = sanitize_text_field($input['claude_model']);
        }else{
            $new_input['claude_model'] = 'claude-3-5-sonnet-20240620';
        }
        
        return $new_input;
    }
    public function init_page(){
        register_setting(
            'vnrewrite_option_group',
            'vnrewrite_option',
            array($this, 'sanitize')
        );
        add_settings_section(
            'section_id',
            '',
            array($this, 'section_info'),
            'vnrewrite-admin'
        );
    }
    public function create_callback($args){
        
    }
    public function section_info(){
    }
}
if(is_admin()){
    new VnRewriteAdmin();
}
?>