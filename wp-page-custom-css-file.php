<?php
/*
Plugin Name: wp-page-custom-css-file
Plugin URI: https://github.com/hsgw/wp-page-custom-css-file
Description: 
Version: 1.0.1
Author: Takuya Urakawa
Author URI: 
License: CC0 1.0
*/

add_action('admin_menu', 'custom_css_file_add_meta_box');
function custom_css_file_add_meta_box()
{
    add_meta_box(
        'custom_css_file_meta_box',
        'Custom CSS file',
        'custom_css_file_meta_box_callback',
        array('post', 'page'),
        'side',
        'low'
    );
}

function custom_css_file_meta_box_callback($post)
{
    $css_filenames = get_post_meta($post->ID, 'custom_css_file_css_filename', true);
?>
    <p>CSSファイル（スペース区切り）</p>
    <input type="text" name="custom_css_file_css_filename" value="<?php echo esc_attr($css_filenames); ?>" style="width:100%;" multiple />
<?php
}

add_action('save_post', 'custom_css_file_save_meta_data');
function custom_css_file_save_meta_data($post_id)
{
    if (!isset($_POST['custom_css_file_css_filename'])) {
        return;
    }

    $css_filenames = sanitize_text_field($_POST['custom_css_file_css_filename']);
    $css_filenames = explode(' ', $css_filenames); // スペースで区切って配列に変換
    update_post_meta($post_id, 'custom_css_file_css_filename', implode(' ', $css_filenames)); // スペースで結合して保存
}

add_action('wp_enqueue_scripts', 'custom_css_file_enqueue_style');
function custom_css_file_enqueue_style()
{
    if (!is_singular()) {
        return;
    }

    $post_id = get_the_ID();
    $css_filenames = get_post_meta($post_id, 'custom_css_file_css_filename', true);
    $css_filenames = explode(' ', $css_filenames); // スペースで区切って配列に変換

    if (!$css_filenames) {
        return;
    }

    foreach ($css_filenames as $css_filename) {
        $css_filename = trim($css_filename); // 余分なスペースを除去
        if (!$css_filename) {
            continue;
        }

        $css_path = get_stylesheet_directory() . '/assets/css/' . $css_filename . '.css';
        $child_css_path = get_stylesheet_directory_uri() . '/assets/css/' . $css_filename . '.css';

        // 親テーマのCSSファイルを読み込む
        $parent_css_path = get_template_directory() . '/assets/css/' . $css_filename . '.css';
        $parent_css_url = get_template_directory_uri() . '/assets/css/' . $css_filename . '.css';
        if (file_exists($parent_css_path)) {
            wp_enqueue_style('parent-custom-css-file-' . $css_filename, $parent_css_url);
        }

        // 子テーマのCSSファイルを読み込む
        $child_css_path = get_stylesheet_directory() . '/assets/css/' . $css_filename . '.css';
        $child_css_url = get_stylesheet_directory_uri() . '/assets/css/' . $css_filename . '.css';
        if (file_exists($child_css_path)) {
            wp_enqueue_style('child-custom-css-file-' . $css_filename, $child_css_url);
        }
    }
}
