jQuery(document).ready(function($) {
    "use strict";

    //debug log
    $('.vnrewrite-toggle-button[data-action="toggle_debug_log"]').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);

        $.ajax({
            url: vnrewrite_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'vnrewrite_toggle_debug_log',
                nonce: vnrewrite_obj.config_nonce
            },
            success: function(response) {
                if (response.success) {
                    var newStatus = response.status;
                    $button.toggleClass('button-primary button-secondary');
                    $button.html(newStatus ? '1. <strong>WP_DEBUG_LOG</strong> (đang bật)' : '1. <strong>WP_DEBUG_LOG</strong> (đang tắt)');
                    
                    vnrewrite_obj.config_nonce = response.newNonce;
                    
                    if (newStatus) {
                        $('#vnrewrite-error-log-container').show();
                        refreshErrorLog();
                    } else {
                        $('#vnrewrite-error-log-container').hide();
                    }
                    
                    vnrewrite_obj.debug_enabled = newStatus;
                } else {
                    alert('Failed to toggle debug settings: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                alert('An error occurred while toggling debug settings. Please check the console for more details.');
            }
        });
    });

    function refreshErrorLog() {
        $.ajax({
            url: vnrewrite_obj.ajaxurl,
            type: 'POST',
            data: {
                action: 'vnrewrite_read_log',
                nonce: vnrewrite_obj.read_log_nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#vnrewrite-error-log').val(response.data);
                } else {
                    $('#vnrewrite-error-log').val('Failed to read log: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                $('#vnrewrite-error-log').val('An error occurred while fetching the log. Please check the console for more details.');
            }
        });
    }

    $('#vnrewrite-refresh-log').on('click', refreshErrorLog);

    $('#vnrewrite-clear-log').on('click', function() {
        if (confirm('Are you sure you want to clear the debug log?')) {
            $.ajax({
                url: vnrewrite_obj.ajaxurl,
                type: 'POST',
                data: {
                    action: 'vnrewrite_clear_log',
                    nonce: vnrewrite_obj.read_log_nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        refreshErrorLog(); // Refresh the log content after clearing
                    } else {
                        alert('Failed to clear log: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown);
                    alert('An error occurred while clearing the log. Please check the console for more details.');
                }
            });
        }
    });

    if (vnrewrite_obj.debug_enabled && $('#vnrewrite-error-log').length) {
        refreshErrorLog();
    }
    //===


    //settings
    $('#rewrite_type').change(function() {
        var rewrite_type = $(this).val();
        if (rewrite_type != '') {
            $('#show-cron-time').show();
            if (rewrite_type == 'post') {
                $('#show-cron-post').show();
                $('#show-cron-keyword').hide();
                $('#cron-video').hide();
            }else{
                $('#show-cron-post').hide();
                if (rewrite_type == 'keyword') {
                    $('#show-cron-keyword').show();
                    $('#cron-keyword-yt').hide();
                    $('#cron-video').hide();
                }else if(rewrite_type == 'keyword_yt'){
                    $('#show-cron-keyword').show();
                    $('#cron-keyword-yt').show();
                    $('#cron-video').hide();
                }else if(rewrite_type == 'url'){
                    $('#show-cron-keyword').hide();
                    $('#cron-video').hide();
                }else{
                    $('#cron-video').show();
                    $('#show-cron-keyword').hide();
                }
            }
        }else{
            $('#show-cron-time').hide();
            $('#show-cron-post').hide();
            $('#show-cron-keyword').hide();
            $('#cron-video').hide();
        }
    });

    $('#clear-log').click(function(event) {
        if (confirm('Bạn muốn xóa log?')){
            return true;
        }else{
            return false;
        }
    });

    //urls
    $('#cat-urls').change(function() {
        var cat_id = $(this).val();
        var url = vnrewrite_obj.rewrite_urls;
        if (cat_id != '') {
            url += '&cat=' + cat_id;
        }
        window.location=url;
    });

    save_url();
    function save_url(){
        $('#save-url').click(function(event){
            var cat = $(this).attr('data-cat');
            var input_url = $('#url_' + cat).val();
            var input_url_active = $('#url_active_' + cat).val();
            var input_url_miss = $('#url_miss_' + cat).val();
            $.ajax({
                method: 'POST',
                url: ajaxurl + '?' + new Date().getTime(),
                data: {
                    action: 'vnrewrite_save_url',
                    cat: cat,
                    input_url: input_url,
                    input_url_active: input_url_active,
                    input_url_miss: input_url_miss
                },
                beforeSend: function(){
                    $('#save-url').attr('disabled','disabled').html('<span class="spinner is-active"></span>');
                },
                success: function(content){
                    setTimeout(function () {
                        $('#list-url').html(content);
                        $('#save-url').removeAttr('disabled').html('Save');
                        save_url();
                    }, 300);
                }
            });
        });
    }

    //keywords
    $('#cat-keywords').change(function() {
        var cat_id = $(this).val();
        var url = vnrewrite_obj.rewrite_keywords;
        if (cat_id != '') {
            url += '&cat=' + cat_id;
        }
        window.location=url;
    });

    save_keyword();
    function save_keyword(){
        $('#save-keyword').click(function(event){
            var cat = $(this).attr('data-cat');
            var input_keyword = $('#keyword_' + cat).val();
            var input_keyword_active = $('#keyword_active_' + cat).val();
            var input_keyword_miss = $('#keyword_miss_' + cat).val();
            $.ajax({
                method: 'POST',
                url: ajaxurl + '?' + new Date().getTime(),
                data: {
                    action: 'vnrewrite_save_keyword',
                    cat: cat,
                    input_keyword: input_keyword,
                    input_keyword_active: input_keyword_active,
                    input_keyword_miss: input_keyword_miss
                },
                beforeSend: function(){
                    $('#save-keyword').attr('disabled','disabled').html('<span class="spinner is-active"></span>');
                },
                success: function(content){
                    setTimeout(function () {
                        $('#list-keyword').html(content);
                        $('#save-keyword').removeAttr('disabled').html('Save');
                        save_keyword();
                    }, 300);
                }
            });
        });
    }

    //keywords yt
    $('#cat-keywords-yt').change(function() {
        var cat_id = $(this).val();
        var url = vnrewrite_obj.rewrite_keywords_yt;
        if (cat_id != '') {
            url += '&cat=' + cat_id;
        }
        window.location=url;
    });

    save_keyword_yt();
    function save_keyword_yt(){
        $('#save-keyword-yt').click(function(event){
            var cat = $(this).attr('data-cat-yt');
            var input_keyword = $('#keyword_yt_' + cat).val();
            var input_keyword_active = $('#keyword_yt_active_' + cat).val();
            var input_keyword_miss = $('#keyword_yt_miss_' + cat).val();
            $.ajax({
                method: 'POST',
                url: ajaxurl + '?' + new Date().getTime(),
                data: {
                    action: 'vnrewrite_save_keyword_yt',
                    cat: cat,
                    input_keyword: input_keyword,
                    input_keyword_active: input_keyword_active,
                    input_keyword_miss: input_keyword_miss
                },
                beforeSend: function(){
                    $('#save-keyword-yt').attr('disabled','disabled').html('<span class="spinner is-active"></span>');
                },
                success: function(content){
                    setTimeout(function () {
                        $('#list-keyword-yt').html(content);
                        $('#save-keyword-yt').removeAttr('disabled').html('Save');
                        save_keyword_yt();
                    }, 300);
                }
            });
        });
    }

    //videos yt
    $('#cat-videos').change(function() {
        var cat_id = $(this).val();
        var url = vnrewrite_obj.rewrite_videos;
        if (cat_id != '') {
            url += '&cat=' + cat_id;
        }
        window.location=url;
    });

    save_video();
    function save_video(){
        $('#save-video').click(function(event){
            var cat = $(this).attr('data-cat-video');
            var input_video = $('#video_' + cat).val();
            var input_video_active = $('#video_active_' + cat).val();
            var input_video_miss = $('#video_miss_' + cat).val();
            $.ajax({
                method: 'POST',
                url: ajaxurl + '?' + new Date().getTime(),
                data: {
                    action: 'vnrewrite_save_video',
                    cat: cat,
                    input_video: input_video,
                    input_video_active: input_video_active,
                    input_video_miss: input_video_miss
                },
                beforeSend: function(){
                    $('#save-video').attr('disabled','disabled').html('<span class="spinner is-active"></span>');
                },
                success: function(content){
                    setTimeout(function () {
                        $('#list-video').html(content);
                        $('#save-video').removeAttr('disabled').html('Save');
                        get_video_id_list();
                        save_video();
                    }, 300);
                }
            });
        });
    }

    get_video_id_list();
    function get_video_id_list(){
        $('#get-video-list').click(function(event){
            var cat = $(this).data('cat');
            var input_video = $('#video_' + cat).val();
            var input_video_active = $('#video_active_' + cat).val();
            var input_video_miss = $('#video_miss_' + cat).val();
            var video_id_list = $('#video-list-id').val();
            $.ajax({
                method: 'POST',
                url: ajaxurl + '?' + new Date().getTime(),
                data: {
                    action: 'vnrewrite_get_video_id_list',
                    cat: cat,
                    input_video: input_video,
                    input_video_active: input_video_active,
                    input_video_miss: input_video_miss,
                    video_id_list: video_id_list
                },
                beforeSend: function(){
                    $('#get-video-list').attr('disabled','disabled').html('<span class="spinner is-active" style="float:none;margin-top:-2px;"></span>');
                    $('#save-video').attr('disabled','disabled').html('<span class="spinner is-active"></span>');
                },
                success: function(content){
                    setTimeout(function () {
                        $('#list-video').html(content);
                        $('#get-video-list').removeAttr('disabled').html('Get video from ID list');
                        $('#save-video').removeAttr('disabled').html('Save');
                        get_video_id_list();
                        save_video();
                    }, 300);
                }
            });
        });
    }

    //posts
    $('.check-column input[type="checkbox"]').change(function(){
        var ids = '';
        $('.check-column input[type="checkbox"]:not(#check-all)').each(function(){
            if(this.checked) {
                ids += $(this).val() + ',';
            }
        });
        ids = ids.replace(/,+$/, '');
        if (ids != '') {
            $('#ids').val(ids);
        }else{
            $('#ids').val('');
        }
    });

    $('.post-cmd').click(function(){
        var ids = $('#ids').val();
        if (ids != '') {
            if (confirm('Bạn muốn ' + $(this).text())){
                var url = vnrewrite_obj.rewrite_posts + '&cmd=' + $(this).attr('data-cmd') + '&ids=' + ids;
                window.location=url;
            }else{
                return false;
            }
        }else{
            alert('Vui lòng chọn ít nhất 1 bài viết!');
            return false;
        }
    });

    var ajax_rewrite_running = false;
    $('.rewrite-ajax').on('click', function(e) {
        e.preventDefault();

        if (ajax_rewrite_running) {
            return;
        }

        ajax_rewrite_running = true;

        $('.rewrite-ajax').attr('disabled', true);

        var cur = $(this);
        var id = cur.data('id');

        $.ajax({
            method: 'POST',
            url: ajaxurl + '?' + new Date().getTime(),
            data: {
                action: 'rewrite_ajax',
                id: id
            },
            beforeSend: function(){
                cur.html('<span class="spinner is-active"></span>');
            },
            success: function(content){
                cur.html('Rewrite');
                if (content.trim() !== '') {
                    var res = JSON.parse(content);
                    if (res['title'] !== null) {
                        $('#title-' + id).text(res['title']);
                    }
                    if (res['status'] !== null) {
                        $('#status-' + id).text(res['status']);
                    }
                    if (res['rewrite'] !== null) {
                        $('#rewrite-' + id).text(res['rewrite_type']);
                    }
                } else {
                    alert('Rewrite thất bại!');
                }

                ajax_rewrite_running = false;
                $('.rewrite-ajax').attr('disabled', false);
            },
            error: function() {
                ajax_rewrite_running = false;
                $('.rewrite-ajax').attr('disabled', false);
            }
        });
    });

    //mess
    function show_mess(id, mess_time){
        var countdown = function(){
            var seconds = 0;
            var time_cur = Math.floor(Date.now()/1000);
            if (time_cur >= mess_time) {
                seconds = time_cur - mess_time;
            }
            return seconds;
        }
        $('#' + id).html(countdown);
        setInterval(function(){
            $('#' + id).html(countdown);
        }, 1000);
    }
    if ($('#mess-time').length){
        var id = 'mess-time';
        var mess_time = $('#' + id).attr('data-mess-time');
        show_mess(id, mess_time);
    }
    setInterval(function(){
        $.ajax({
            method: "POST",
            url: ajaxurl + '?' + new Date().getTime(),
            data: {
                action: 'vnrewrite_check_mess'
            },
            success: function(content){
                var result = JSON.parse(content);
                if (result[0] != mess_time) {
                    $('#mess-bound').html();
                    $('#mess-bound').html('<span class="blinker" id="' + result[0] + '" data-mess-time="' + result[0] + '"></span> s trước - <span id="mess">' + result[1] + '</span>');
                    show_mess(result[0], result[0]);
                }
            }
        });
    }, 2000);

    //warning
    $('.notice-dismiss').click(function(){
        $('#setting-error-settings_updated').remove();
        window.history.pushState('', '', $('#ids').attr('data-url'));
    });
});