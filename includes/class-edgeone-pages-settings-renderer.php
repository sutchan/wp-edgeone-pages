<?php
/**
 * File: includes/class-edgeone-pages-settings-renderer.php v1.0.3
 * Description: 设置页面渲染器
 */

if (!defined('ABSPATH')) {
    exit;
}

class EdgeOne_Pages_Settings_Renderer {

    private $options;

    public function __construct($options) {
        $this->options = $options;
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('您没有权限访问此页面。', 'edgeone-pages'));
        }
        ?>
        <div class="wrap">
            <h1><?php _e('EdgeOne Pages 配置', 'edgeone-pages'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('edgeone_pages_group');
                do_settings_sections('edgeone-pages');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_general_section() {
        echo '<p>' . __('配置腾讯云 EdgeOne Pages 加速域名和基础设置', 'edgeone-pages') . '</p>';
    }

    public function render_enabled_field() {
        $enabled = isset($this->options['enabled']) ? $this->options['enabled'] : '0';
        echo '<input type="checkbox" name="edgeone_pages_options[enabled]" value="1" ' . checked('1', $enabled, false) . ' />';
    }

    public function render_domain_field() {
        $domain = isset($this->options['domain']) ? $this->options['domain'] : '';
        echo '<input type="text" name="edgeone_pages_options[domain]" value="' . esc_attr($domain) . '" class="regular-text" placeholder="' . esc_attr__('例如：xxx.pages.dev', 'edgeone-pages') . '" />';
    }

    public function render_optimization_section() {
        echo '<p>' . __('配置图片优化和资源压缩选项', 'edgeone-pages') . '</p>';
    }

    public function render_webp_field() {
        $enabled = isset($this->options['webp_enabled']) ? $this->options['webp_enabled'] : '0';
        echo '<input type="checkbox" name="edgeone_pages_options[webp_enabled]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">' . __('自动将图片转换为 WebP 格式以减小文件大小', 'edgeone-pages') . '</p>';
    }

    public function render_optimize_images_field() {
        $enabled = isset($this->options['optimize_images']) ? $this->options['optimize_images'] : '1';
        echo '<input type="checkbox" name="edgeone_pages_options[optimize_images]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">' . __('启用图片优化功能', 'edgeone-pages') . '</p>';
    }

    public function render_lazy_load_field() {
        $enabled = isset($this->options['lazy_load']) ? $this->options['lazy_load'] : '1';
        echo '<input type="checkbox" name="edgeone_pages_options[lazy_load]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">' . __('延迟加载图片，提高首屏加载速度', 'edgeone-pages') . '</p>';
    }

    public function render_minify_css_field() {
        $enabled = isset($this->options['minify_css']) ? $this->options['minify_css'] : '1';
        echo '<input type="checkbox" name="edgeone_pages_options[minify_css]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">' . __('压缩 CSS 文件，减小文件大小', 'edgeone-pages') . '</p>';
    }

    public function render_minify_js_field() {
        $enabled = isset($this->options['minify_js']) ? $this->options['minify_js'] : '1';
        echo '<input type="checkbox" name="edgeone_pages_options[minify_js]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">' . __('压缩 JavaScript 文件，减小文件大小', 'edgeone-pages') . '</p>';
    }

    public function render_cache_section() {
        echo '<p>' . __('配置静态资源缓存策略', 'edgeone-pages') . '</p>';
    }

    public function render_cache_control_field() {
        $cache_control = isset($this->options['cache_control']) ? $this->options['cache_control'] : '31536000';
        echo '<input type="number" name="edgeone_pages_options[cache_control]" value="' . esc_attr($cache_control) . '" class="regular-text" min="0" />';
        echo '<p class="description">' . __('默认值：31536000 秒（1年）', 'edgeone-pages') . '</p>';
    }
}
