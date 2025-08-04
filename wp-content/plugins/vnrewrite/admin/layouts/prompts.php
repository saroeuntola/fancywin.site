<div class="poststuff" style="<?php if(!isset($_GET['tab']) || (isset($_GET['tab']) & $_GET['tab'] != 'prompts')){echo 'display: none;';} ?>">
    <div class="postbox">
        <div class="postbox-header">
            <h2>Vai trò AI</h2>
        </div>
        <div class="inside">
            <table class="form-table">
                <tbody>
                    <tr>
                        <td>
                        <?php
                            $categories = get_categories(array(
                                'hide_empty' => false,
                                'hierarchical' => true
                            ));

                            $str_cate = '<p><label for="vnrewrite_ai_as_common"><strong>All (Chung cho tất cả danh mục)</strong></label><textarea name="vnrewrite_option[vnrewrite_ai_as_common]" id="vnrewrite_ai_as_common" class="large-text" rows="4">' . (isset($this->options['vnrewrite_ai_as_common'])?esc_attr($this->options['vnrewrite_ai_as_common']):'') . '</textarea></p>';
                            foreach ($categories as $category) {
                                $vnrewrite_ai_as_cate = get_term_meta($category->term_id, 'vnrewrite_ai_as_cate', true);
                                $str_cate .= '<p>';
                                    $str_cate .= '<label for="vnrewrite_ai_as_cate' . $category->term_id . '"><strong>' . esc_html($category->name) . '</strong></label>';
                                    $str_cate .= '<textarea name="vnrewrite_option[vnrewrite_ai_as_cate][' . $category->term_id . ']" id="vnrewrite_ai_as_cate' . $category->term_id . '" class="large-text" rows="4">' . $vnrewrite_ai_as_cate . '</textarea>';
                                $str_cate .= '</p>';
                            }
                            echo $str_cate;
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>- Set vai trò AI cho từng danh mục thì khi tạo bài viết vai trò AI sẽ lấy tương ứng theo danh mục đó. Nếu để trống sẽ lấy vai trò AI chung<code><strong>All</code></strong>. Prompt này sử dụng <code><strong>tiếng Việt</strong></code></p> 
                            <p>- Ví dụ bên dưới là prompt set vai trò cho AI về chủ đề ẩm thực Việt:</p>
                            <p><code>Bạn là một chuyên gia ẩm thực và đầu bếp lành nghề với kiến thức sâu rộng về ẩm thực Việt Nam. Bạn có nhiều năm kinh nghiệm trong việc chế biến các món ăn truyền thống và sáng tạo món mới, đồng thời là người sáng tạo nội dung chính cho website "<strong><?php echo get_bloginfo('name'); ?></strong>".</code></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="postbox">
        <div class="postbox-header">
            <h2>Prompt</h2>
        </div>
        <div class="inside">
            <table class="form-table">
                <tbody>
                    <tr>
                        <td>
                            <?php
                                $categories = get_categories(array(
                                    'hide_empty' => false,
                                    'hierarchical' => true
                                ));

                                $str_cate = '<p><label for="vnrewrite_prompt_common"><strong>All (Chung cho tất cả danh mục)</strong></label><textarea name="vnrewrite_option[vnrewrite_prompt_common]" id="vnrewrite_prompt_common" class="large-text" rows="4">' . (isset($this->options['vnrewrite_prompt_common'])?esc_attr($this->options['vnrewrite_prompt_common']):'') . '</textarea></p>';
                                foreach ($categories as $category) {
                                    $vnrewrite_prompt_cate = get_term_meta($category->term_id, 'vnrewrite_prompt_cate', true);
                                    $str_cate .= '<p>';
                                        $str_cate .= '<label for="vnrewrite_prompt_cate' . $category->term_id . '"><strong>' . esc_html($category->name) . '</strong></label>';
                                        $str_cate .= '<textarea name="vnrewrite_option[vnrewrite_prompt_cate][' . $category->term_id . ']" id="vnrewrite_prompt_cate' . $category->term_id . '" class="large-text" rows="4">' . $vnrewrite_prompt_cate . '</textarea>';
                                    $str_cate .= '</p>';
                                }
                                echo $str_cate;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>- Đây là các prompt tương ứng với từng danh mục, nó là yêu cầu về nội dung bài viết. Nếu không nhập prompt cho các danh mục thì sẽ sử dụng prompt chung <code><strong>All</strong></code> cho tất cả các danh mục. Prompt sẽ chỉ sử dụng <code><strong>tiếng Việt</strong></code></p> 
                            <p>- Ví dụ bên dưới là prompt về chủ đề ẩm thực Việt:</p>
                            <div style="background: #e5ddc4; padding: 5px; margin-top:5px">
                                1. Phong cách:<br>
                                - Sử dụng lại thông tin theo cách tự nói lại hoặc tái phát triển thông tin, thay vì chỉ đơn giản là copy hoặc biên dịch từ đoạn văn bản gốc.<br>
                                - Sử dụng ngôn ngữ thân thiện, dễ hiểu và đầy sáng tạo để thu hút sự chú ý của độc giả.<br>
                                - Khơi gợi niềm đam mê và tự tin của độc giả trong việc nấu ăn qua các câu chuyện, kinh nghiệm cá nhân và những lời khuyên hữu ích.<br>
                                - Kể chuyện, chia sẻ những trải nghiệm thực tế để tạo sự gần gũi và hấp dẫn.<br><br>

                                2. Tối ưu SEO:<br>
                                - Nghiên cứu từ khóa: Sử dụng từ khóa chính, LSI và từ khóa liên quan một cách tự nhiên và hợp lý trong bài viết.<br>
                                - Cấu trúc nội dung: Sử dụng các thẻ heading (H2, H3) ngắn gọn để cấu trúc bài viết logic, rõ ràng, dễ đọc và thân thiện với công cụ tìm kiếm.<br>
                                - Trích dẫn nguồn uy tín: Đảm bảo tính chuyên môn bằng cách tham khảo và trích dẫn các nguồn tài liệu ẩm thực uy tín. Lồng ghép ý kiến chuyên gia (bằng cách tạo tên và trích dẫn giả định) để tăng thêm độ tin cậy.<br>
                                - Ưu tiên nội dung chất lượng: Tập trung vào nội dung có giá trị, tránh nhồi nhét từ khóa quá mức.<br><br>

                                3. Cấu trúc bài viết:<br>
                                - Tiêu đề sử dụng thẻ H1 (#): Nổi bật, chứa từ khóa chính, nêu bật món ăn, gợi mở nội dung và tạo sự tò mò.<br>
                                - Mở đầu: Mở đầu bài viết với một mô tả ngắn gọn về nội dung chủ đề, kèm theo lời giới thiệu hấp dẫn để khơi gợi sự tò mò của người đọc. Từ khóa chính nên xuất hiện tự nhiên trong đoạn này.<br>
                                - Nội dung chính: Chia thành các phần với thẻ H2 (##) và H3 (###), mô tả chi tiết về các nguyên liệu, các bước thực hiện, lưu ý và mẹo nhỏ, cách trình bày món ăn và thông tin bổ sung như cách bảo quản, thành phần dinh dưỡng (nếu có).<br>
                                - Kết luận: Tóm tắt lại những điểm chính của bài viết. Khuyến khích người đọc thử làm món ăn và chia sẻ trải nghiệm của họ. Kêu gọi người đọc tương tác bằng cách để lại bình luận, chia sẻ bài viết hay khám phá thêm các nội dung khác trên website.<br><br>
                                    
                                4. Lưu ý:<br>
                                - Đảm bảo tính chính xác và độ tin cậy của thông tin.<br>
                            </div>
                            <p>- Prompt nên đánh số thứ tự như mẫu trên, đặc biệt mục <code>3. Cấu trúc bài viết:</code> cần mô tả rõ ràng title là h1 (#), các headings h2 (##), h3 (###) ... Việc này giúp AI hiểu rõ cấu trúc bài viết tránh dẫn đến tạo bài viết có cấu trúc không hợp lệ (tiêu đề không phải thẻ h1 ...)</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>