<?php
/**
 * This fucntion is used to create custom meta boxes in pages/posts to render the left/right sidebar
 *
 * @package The NewsMag
 */
add_action('add_meta_boxes', 'the_newsmag_create_review');

/**
 * Adding the Custom Meta Box
 */
function the_newsmag_create_review() {
	// Adding the layout meta box for single post page
	add_meta_box('page-layout-home', esc_html__('Create Review', 'the-newsmag'), 'hcf_display_callback', 'post', 'side', 'default');
}

/**
 * Save the custom metabox data
 */
if (!function_exists('the_newsmag_save_custom_meta_create_review')) :
    /**
     * Meta box display callback.
     *
     * @param WP_Post $post Current post object.
     */
    function hcf_display_callback( $post ) {
        $_wp_editor_expand = $_content_editor_dfw = false;
        if ( post_type_supports( $post_type, 'editor' ) && ! wp_is_mobile() &&
            ! ( $is_IE && preg_match( '/MSIE [5678]/', $_SERVER['HTTP_USER_AGENT'] ) ) &&
            apply_filters( 'wp_editor_expand', true, $post_type ) ) {

            wp_enqueue_script( 'editor-expand' );
            $_content_editor_dfw = true;
            $_wp_editor_expand   = ( get_user_setting( 'editor_expand', 'on' ) === 'on' );
        }
        if ( post_type_supports( $post_type, 'editor' ) ) {
            $_wp_editor_expand_class = '';
            if ( $_wp_editor_expand ) {
                $_wp_editor_expand_class = ' wp-editor-expand';
            }
        }
        $chk = get_post_meta(get_the_ID(), 'hcf_show_review', true);
        ?>
        <div class="hcf_box">
            <style scoped>
                .hcf_box{
                    display: grid;
                    grid-template-columns: max-content 1fr;
                    grid-row-gap: 10px;
                    grid-column-gap: 20px;
                }
                .hcf_field{
                    display: contents;
                }
            </style>
            <p class="meta-options hcf_field">
                <label for="hcf_show_review">Hiển thị đánh giá</label>
                <input id="hcf_show_review"
                    type="checkbox"
                    name="hcf_show_review"
                    <?php checked( $chk, 'on' );?>
                >
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_author">Tác giả</label>
                <input id="hcf_author"
                    type="text"
                    name="hcf_author"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_author', true ) ); ?>">
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_publishing">Nhà xuất bản</label>
                <input id="hcf_publishing"
                    type="text"
                    name="hcf_publishing"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_publishing', true ) ); ?>">
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_status">Tình Trạng</label>
                <input id="hcf_status"
                    type="text"
                    name="hcf_status"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_status', true ) ); ?>">
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_chap">Số tập</label>
                <input id="hcf_chap"
                    type="text"
                    name="hcf_chap"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_chap', true ) ); ?>">
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_core">Cốt truyện</label>
                <input id="hcf_core"
                    type="text"
                    name="hcf_core"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_core', true ) ); ?>"
                >
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_figure">Nhận vật</label>
                <input id="hcf_figure"
                    type="text"
                    name="hcf_figure"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_figure', true ) ); ?>"
                >
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_paint">Nét vẽ</label>
                <input id="hcf_paint"
                    type="text"
                    name="hcf_paint"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_paint', true ) ); ?>"
                >
            </p>
            <p class="meta-options hcf_field">
                <label for="hcf_quality">Chất lượng</label>
                <input id="hcf_quality"
                    type="text"
                    name="hcf_quality"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_quality', true ) ); ?>"
                >
            </p>
            <div class="meta-options hcf_field">
                <label for="hcf_summay">Tổng kết</label>
                <?php
                    wp_editor(
                        get_post_meta( get_the_ID(), 'hcf_summay', true ),
                        'hcf_summay',
                        array(
                            '_content_editor_dfw' => $_content_editor_dfw,
                            'drag_drop_upload'    => true,
                            'tabfocus_elements'   => 'content-html,save-post',
                            'editor_height'       => 300,
                            'tinymce'             => array(
                                'resize'                  => false,
                                'wp_autoresize_on'        => $_wp_editor_expand,
                                'add_unload_trigger'      => false,
                                'wp_keep_scroll_position' => ! $is_IE,
                            ),
                        )
                    );
                ?>
            </div>
            <p class="meta-options hcf_field">
                <label for="hcf_author">Bạn đọc bình chọn</label>
                <input id="hcf_author"
                    type="text"
                    name="hcf_author"
                    value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'hcf_author', true ) ); ?>">
            </p>
            <div class="meta-options hcf_field">
                <label for="hcf_add_point">ĐIỂM CỘNG</label>
                <?php
                    wp_editor(
                        get_post_meta( get_the_ID(), 'hcf_add_point', true ),
                        'hcf_add_point',
                        array(
                            '_content_editor_dfw' => $_content_editor_dfw,
                            'drag_drop_upload'    => true,
                            'tabfocus_elements'   => 'content-html,save-post',
                            'editor_height'       => 300,
                            'tinymce'             => array(
                                'resize'                  => false,
                                'wp_autoresize_on'        => $_wp_editor_expand,
                                'add_unload_trigger'      => false,
                                'wp_keep_scroll_position' => ! $is_IE,
                            ),
                        )
                    );
                ?>
            </div>
            <div class="meta-options hcf_field">
                <label for="hcf_minus_point">ĐIỂM TRỪ</label>
                <?php
                    wp_editor(
                        get_post_meta( get_the_ID(), 'hcf_minus_point', true ),
                        'hcf_minus_point',
                        array(
                            '_content_editor_dfw' => $_content_editor_dfw,
                            'drag_drop_upload'    => true,
                            'tabfocus_elements'   => 'content-html,save-post',
                            'editor_height'       => 300,
                            'tinymce'             => array(
                                'resize'                  => false,
                                'wp_autoresize_on'        => $_wp_editor_expand,
                                'add_unload_trigger'      => false,
                                'wp_keep_scroll_position' => ! $is_IE,
                            ),
                        )
                    );
                ?>
            </div>
        </div>
        <?php
    }
	function hcf_save_meta_box( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( $parent_id = wp_is_post_revision( $post_id ) ) {
            $post_id = $parent_id;
        }
        $fields = [
            'hcf_author',
            'hcf_publishing',
            'hcf_status',
            'hcf_year_published',
            'hcf_chap',
            'hcf_chap',
            'hcf_core',
            'hcf_figure',
            'hcf_paint',
            'hcf_quality',
            'hcf_point',
            'hcf_vote',
        ];
        $fieldEditor = [
            'hcf_summay',
            'hcf_add_point',
            'hcf_minus_point'
        ];
            foreach ( $fields as $field ) {
                if ( array_key_exists( $field, $_POST ) ) {
                    update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
                }
            }
            foreach ( $fieldEditor as $field ) {
                if ( array_key_exists( $field, $_POST ) ) {
                    update_post_meta( $post_id, $field, $_POST[$field]);
                }
            }
            if ( isset($_POST['hcf_show_review']) ) {
                update_post_meta($post_id, 'hcf_show_review', $_POST['hcf_show_review']);
            }else{
                delete_post_meta($post_id, 'hcf_show_review');
            }
        }
    add_action( 'save_post', 'hcf_save_meta_box' );

endif;

add_action('pre_post_update', 'the_newsmag_save_custom_meta_create_review');
