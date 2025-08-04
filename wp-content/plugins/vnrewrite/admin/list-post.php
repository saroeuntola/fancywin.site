<?php  
    function rewrite_column($columns) {
        $new_columns = array(
            'cb' => $columns['cb'],
            'title' => $columns['title'],
            'rewrite' => __('Rewrite'),
        );
        if (!isset($columns['aiimage'])) {
            $new_columns['image'] = __('Image');
        }
        unset($columns['cb']);
        unset($columns['title']);
        return $new_columns + $columns;
    }
    function rewrite_display_column($column_name, $post_id) {
        $rewrite = get_post_meta($post_id, 'rewrite', true);
        if ($column_name == 'rewrite' && $rewrite != '') {
            if ($rewrite == 'error') {
                echo '<span style="color: red;">' . $rewrite . '</span>';
            }else{
                $url = get_post_meta($post_id, 'url', true);
                $youtube_id = get_post_meta($post_id, 'youtube_id', true);
                if ($url != '') {
                    echo str_replace('url', '<a target="_blank" href="' . $url . '">url</a>', $rewrite);
                }elseif ($youtube_id != '') {
                    echo str_replace('youtube', '<a target="_blank" href="https://www.youtube.com/watch?v=' . $youtube_id . '">youtube</a>', $rewrite);
                }else{
                    echo $rewrite;
                }
            }
        }elseif ($column_name == 'image') {
            if (has_post_thumbnail($post_id)) {
                $thumbnail = get_the_post_thumbnail($post_id, array(45, 45));
                echo '<a target="_blank" href="' . get_permalink($post_id) . '">' . $thumbnail . '</a>';
            }
        }
    }
    add_filter('manage_post_posts_columns', 'rewrite_column');
    add_action('manage_post_posts_custom_column', 'rewrite_display_column', 10, 2);

    //========== filter post
    function rewrite_post_filter() {
        global $post_type;
        if ('post' == $post_type) {
            echo '<select name="filter">';
                echo '<option value="">VnRewrite</option>';

                $selected = isset($_GET['filter']) && $_GET['filter'] == 'is_rewite' ? 'selected' : '';
                echo '<option value="is_rewite" ' . $selected . '>Đã rewrite</option>';

                $selected = isset($_GET['filter']) && $_GET['filter'] == 'not_rewrite' ? 'selected' : '';
                echo '<option value="not_rewrite" ' . $selected . '>Chưa rewrite</option>';

                $selected = isset($_GET['filter']) && $_GET['filter'] == 'success_rewite' ? 'selected' : '';
                echo '<option value="success_rewite" ' . $selected . '>Rewrite thành công</option>';

                $selected = isset($_GET['filter']) && $_GET['filter'] == 'error_rewite' ? 'selected' : '';
                echo '<option value="error_rewite" ' . $selected . '>Rewrite thất bại</option>';

                $selected = isset($_GET['filter']) && $_GET['filter'] == 'has_thumb' ? 'selected' : '';
                echo '<option value="has_thumb" ' . $selected . '>Có thumbnail</option>';

                $selected = isset($_GET['filter']) && $_GET['filter'] == 'not_thumb' ? 'selected' : '';
                echo '<option value="not_thumb" ' . $selected . '>Không có thumbnail</option>';
            echo '</select>';
        }
    }

    function rewrite_filter($query) {
        global $typenow;

        if ('post' == $typenow && isset($_GET['filter'])) {
            $filter = $_GET['filter'];

            $meta_query = $query->get('meta_query');
            if (!is_array($meta_query)) {
                $meta_query = array();
            }

            if ($filter == 'is_rewite') {
                $meta_query[] = array(
                    'key' => 'rewrite',
                    'compare' => 'EXISTS',
                );
            }
            if ($filter == 'not_rewrite') {
                $meta_query[] = array(
                    'key' => 'rewrite',
                    'compare' => 'NOT EXISTS',
                );
            }
            if ($filter == 'success_rewite') {
                $meta_query[] = array(
                    'key' => 'rewrite',
                    'compare' => 'EXISTS',
                );

                $meta_query[] = array(
                    'key' => 'rewrite',
                    'value' => 'error',
                    'compare' => '!=',
                );
            }
            if ($filter == 'error_rewite') {
                $meta_query[] = array(
                    'key' => 'rewrite',
                    'value' => 'error',
                );
            }

            if ($filter == 'has_thumb') {
                $meta_query[] = array(
                    'key' => '_thumbnail_id',
                    'compare' => 'EXISTS',
                );
            }
            if ($filter == 'not_thumb') {
                $meta_query[] = array(
                    'key' => '_thumbnail_id',
                    'compare' => 'NOT EXISTS',
                );
            }

            $query->set('meta_query', $meta_query);
        }
    }

    add_action('restrict_manage_posts', 'rewrite_post_filter');
    add_action('pre_get_posts', 'rewrite_filter');

    //bulk_actions
    function vnrewrite_add_bulk_actions($actions) {
        $actions['vnrewrite_separator'] = '↓ VnRewrite';
        
        $actions['vnrewrite_remove_rewrite'] = '&nbsp;&nbsp;&nbsp;&nbsp;' . __('Xóa trạng thái đã rewrite', 'text-domain');
        $actions['vnrewrite_remove_post_permanently'] = '&nbsp;&nbsp;&nbsp;&nbsp;' . __('Xóa vĩnh viễn bài viết và file đính kèm', 'text-domain');
        
        return $actions;
    }
    add_filter('bulk_actions-edit-post', 'vnrewrite_add_bulk_actions');

    function vnrewrite_add_bulk_action_disable_option() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('select[name="action"] option[value="vnrewrite_separator"]').attr('disabled', 'disabled');
            });
        </script>
        <?php
    }
    add_action('admin_footer', 'vnrewrite_add_bulk_action_disable_option');

    function vnrewrite_add_custom_bulk_actions_css() {
        ?>
        <style type="text/css">
            select[name="action"] option[value="vnrewrite_separator"]{
                font-weight: bold;
                color: #000;
                background-color: #f1f1f1;
            }
        </style>
        <?php
    }
    add_action('admin_head', 'vnrewrite_add_custom_bulk_actions_css');


    function vnrewrite_add_bulk_action_confirmation() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#doaction').click(function(e) {
                    var selectedAction = $('select[name="action"]').val();

                    var checkedPosts = $('tbody th.check-column input[type="checkbox"]:checked').length;
                    
                    if (checkedPosts === 0) {
                        return;
                    }
                    
                    if (selectedAction === 'vnrewrite_remove_rewrite') {
                        if (!confirm('<?php _e("Bạn có chắc chắn muốn xóa trạng thái đã rewrite?", "text-domain"); ?>')) {
                            e.preventDefault();
                        }
                    }
                    
                    if (selectedAction === 'vnrewrite_remove_post_permanently') {
                        if (!confirm('<?php _e("Bạn có chắc chắn muốn xóa vĩnh viễn bài viết và file đính kèm? Hành động này không thể hoàn tác!", "text-domain"); ?>')) {
                            e.preventDefault();
                        }
                    }
                });
            });
        </script>
        <?php
    }
    add_action('admin_footer', 'vnrewrite_add_bulk_action_confirmation');

    function vnrewrite_handle_remove_rewrite_action($redirect_to, $doaction, $post_ids) {
        if ($doaction !== 'vnrewrite_remove_rewrite') {
            return $redirect_to;
        }

        foreach ($post_ids as $post_id) {
            $rewrite = get_post_meta($post_id, 'rewrite', true);
            if (!empty($rewrite)) {
                delete_post_meta($post_id, 'rewrite');
            }
        }

        $redirect_to = add_query_arg('rewrite_removed', count($post_ids), $redirect_to);
        return $redirect_to;
    }
    add_filter('handle_bulk_actions-edit-post', 'vnrewrite_handle_remove_rewrite_action', 10, 3);

    function vnrewrite_handle_remove_post_permanently_action($redirect_to, $doaction, $post_ids) {
        if ($doaction !== 'vnrewrite_remove_post_permanently') {
            return $redirect_to;
        }

        foreach ($post_ids as $post_id) {
            $attachments = get_attached_media('', $post_id);
            
            foreach ($attachments as $attachment) {
                wp_delete_attachment($attachment->ID, true);
            }

            wp_delete_post($post_id, true);
        }

        $redirect_to = add_query_arg('posts_deleted', count($post_ids), $redirect_to);
        return $redirect_to;
    }
    add_filter('handle_bulk_actions-edit-post', 'vnrewrite_handle_remove_post_permanently_action', 10, 3);
?>