<?php
/**
 * Plugin Name: EdgeOne Pages Accelerator
 * Plugin URI: https://github.com/tencentcloud/wp-edgeone-pages
 * Description: 利用腾讯云 EdgeOne Pages 为 WordPress 网站提供静态资源加速和图片优化服务
 * Version: 1.0.0
 * Author: Tencent Cloud
 * Author URI: https://cloud.tencent.com/
 * License: GPLv2 or later
 * Text Domain: edgeone-pages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EDGEONE_PAGES_VERSION', '1.0.0');
define('EDGEONE_PAGES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDGEONE_PAGES_PLUGIN_URL', plugin_dir_url(__FILE__));

class EdgeOne_Pages_Plugin {

    private $options;

    public function __construct() {
        $this->options = get_option('edgeone_pages_options');
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('script_loader_src', array($this, 'filter_script_src'), 10, 2);
        add_filter('style_loader_src', array($this, 'filter_style_src'), 10, 2);
        add_filter('wp_get_attachment_url', array($this, 'filter_attachment_url'), 10, 2);
        add_filter('the_content', array($this, 'filter_content_images'));
        add_action('admin_notices', array($this, 'admin_notice'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    public function activate() {
        $default_options = array(
            'enabled' => '0',
            'domain' => '',
            'webp_enabled' => '0',
            'cache_control' => '31536000',
            'optimize_images' => '1',
            'lazy_load' => '1',
            'minify_css' => '1',
            'minify_js' => '1',
        );
        add_option('edgeone_pages_options', $default_options);
    }

    public function deactivate() {
        delete_option('edgeone_pages_options');
    }

    public function add_admin_menu() {
        add_options_page(
            'EdgeOne Pages 配置',
            'EdgeOne Pages',
            'manage_options',
            'edgeone-pages',
            array($this, 'render_admin_page')
        );
    }

    public function register_settings() {
        register_setting('edgeone_pages_group', 'edgeone_pages_options', array($this, 'sanitize_options'));
        
        add_settings_section(
            'edgeone_pages_general',
            '基本设置',
            array($this, 'render_general_section'),
            'edgeone-pages'
        );
        
        add_settings_field(
            'enabled',
            '启用加速',
            array($this, 'render_enabled_field'),
            'edgeone-pages',
            'edgeone_pages_general'
        );
        
        add_settings_field(
            'domain',
            'EdgeOne Pages 域名',
            array($this, 'render_domain_field'),
            'edgeone-pages',
            'edgeone_pages_general'
        );
        
        add_settings_section(
            'edgeone_pages_optimization',
            '优化设置',
            array($this, 'render_optimization_section'),
            'edgeone-pages'
        );
        
        add_settings_field(
            'webp_enabled',
            '启用 WebP 格式',
            array($this, 'render_webp_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );
        
        add_settings_field(
            'optimize_images',
            '优化图片',
            array($this, 'render_optimize_images_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );
        
        add_settings_field(
            'lazy_load',
            '图片懒加载',
            array($this, 'render_lazy_load_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );
        
        add_settings_field(
            'minify_css',
            '压缩 CSS',
            array($this, 'render_minify_css_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );
        
        add_settings_field(
            'minify_js',
            '压缩 JavaScript',
            array($this, 'render_minify_js_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );
        
        add_settings_section(
            'edgeone_pages_cache',
            '缓存设置',
            array($this, 'render_cache_section'),
            'edgeone-pages'
        );
        
        add_settings_field(
            'cache_control',
            '静态资源缓存时间（秒）',
            array($this, 'render_cache_control_field'),
            'edgeone-pages',
            'edgeone_pages_cache'
        );
    }

    public function sanitize_options($input) {
        $sanitized = array();
        $sanitized['enabled'] = isset($input['enabled']) ? '1' : '0';
        $sanitized['domain'] = sanitize_text_field($input['domain']);
        $sanitized['webp_enabled'] = isset($input['webp_enabled']) ? '1' : '0';
        $sanitized['cache_control'] = intval($input['cache_control']);
        $sanitized['optimize_images'] = isset($input['optimize_images']) ? '1' : '0';
        $sanitized['lazy_load'] = isset($input['lazy_load']) ? '1' : '0';
        $sanitized['minify_css'] = isset($input['minify_css']) ? '1' : '0';
        $sanitized['minify_js'] = isset($input['minify_js']) ? '1' : '0';
        return $sanitized;
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('您没有权限访问此页面。'));
        }
        ?>
        <div class="wrap">
            <h1>EdgeOne Pages 配置</h1>
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
        echo '<p>配置腾讯云 EdgeOne Pages 加速域名和基础设置</p>';
    }

    public function render_enabled_field() {
        $enabled = isset($this->options['enabled']) ? $this->options['enabled'] : '0';
        echo '<input type="checkbox" name="edgeone_pages_options[enabled]" value="1" ' . checked('1', $enabled, false) . ' />';
    }

    public function render_domain_field() {
        $domain = isset($this->options['domain']) ? $this->options['domain'] : '';
        echo '<input type="text" name="edgeone_pages_options[domain]" value="' . esc_attr($domain) . '" class="regular-text" placeholder="例如：xxx.pages.dev" />';
    }

    public function render_optimization_section() {
        echo '<p>配置图片优化和资源压缩选项</p>';
    }

    public function render_webp_field() {
        $enabled = isset($this->options['webp_enabled']) ? $this->options['webp_enabled'] : '0';
        echo '<input type="checkbox" name="edgeone_pages_options[webp_enabled]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">自动将图片转换为 WebP 格式以减小文件大小</p>';
    }

    public function render_optimize_images_field() {
        $enabled = isset($this->options['optimize_images']) ? $this->options['optimize_images'] : '1';
        echo '<input type="checkbox" name="edgeone_pages_options[optimize_images]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">启用图片优化功能</p>';
    }

    public function render_lazy_load_field() {
        $enabled = isset($this->options['lazy_load']) ? $this->options['lazy_load'] : '1';
        echo '<input type="checkbox" name="edgeone_pages_options[lazy_load]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">延迟加载图片，提高首屏加载速度</p>';
    }

    public function render_minify_css_field() {
        $enabled = isset($this->options['minify_css']) ? $this->options['minify_css'] : '1';
        echo '<input type="checkbox" name="edgeone_pages_options[minify_css]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">压缩 CSS 文件，减小文件大小</p>';
    }

    public function render_minify_js_field() {
        $enabled = isset($this->options['minify_js']) ? $this->options['minify_js'] : '1';
        echo '<input type="checkbox" name="edgeone_pages_options[minify_js]" value="1" ' . checked('1', $enabled, false) . ' />';
        echo '<p class="description">压缩 JavaScript 文件，减小文件大小</p>';
    }

    public function render_cache_section() {
        echo '<p>配置静态资源缓存策略</p>';
    }

    public function render_cache_control_field() {
        $cache_control = isset($this->options['cache_control']) ? $this->options['cache_control'] : '31536000';
        echo '<input type="number" name="edgeone_pages_options[cache_control]" value="' . esc_attr($cache_control) . '" class="regular-text" min="0" />';
        echo '<p class="description">默认值：31536000 秒（1年）</p>';
    }

    public function admin_notice() {
        if (isset($this->options['enabled']) && $this->options['enabled'] == '1') {
            if (empty($this->options['domain'])) {
                ?>
                <div class="notice notice-warning">
                    <p><?php _e('EdgeOne Pages 已启用，但未配置加速域名，请在 <a href="options-general.php?page=edgeone-pages">设置页面</a> 中配置。', 'edgeone-pages'); ?></p>
                </div>
                <?php
            }
        }
    }

    public function enqueue_scripts() {
        if (!isset($this->options['enabled']) || $this->options['enabled'] != '1') {
            return;
        }
        
        if (isset($this->options['lazy_load']) && $this->options['lazy_load'] == '1') {
            wp_enqueue_script(
                'edgeone-lazyload',
                EDGEONE_PAGES_PLUGIN_URL . 'assets/js/lazyload.min.js',
                array(),
                EDGEONE_PAGES_VERSION,
                true
            );
        }
    }

    public function filter_script_src($src, $handle) {
        if (!isset($this->options['enabled']) || $this->options['enabled'] != '1') {
            return $src;
        }
        
        if (empty($this->options['domain'])) {
            return $src;
        }
        
        if (strpos($src, home_url()) === 0) {
            $relative_path = str_replace(home_url(), '', $src);
            $src = 'https://' . $this->options['domain'] . $relative_path;
            
            if (isset($this->options['minify_js']) && $this->options['minify_js'] == '1') {
                $src = $this->add_minify_param($src, 'js');
            }
        }
        
        return $src;
    }

    public function filter_style_src($src, $handle) {
        if (!isset($this->options['enabled']) || $this->options['enabled'] != '1') {
            return $src;
        }
        
        if (empty($this->options['domain'])) {
            return $src;
        }
        
        if (strpos($src, home_url()) === 0) {
            $relative_path = str_replace(home_url(), '', $src);
            $src = 'https://' . $this->options['domain'] . $relative_path;
            
            if (isset($this->options['minify_css']) && $this->options['minify_css'] == '1') {
                $src = $this->add_minify_param($src, 'css');
            }
        }
        
        return $src;
    }

    public function filter_attachment_url($url, $post_id) {
        if (!isset($this->options['enabled']) || $this->options['enabled'] != '1') {
            return $url;
        }
        
        if (empty($this->options['domain'])) {
            return $url;
        }
        
        if (strpos($url, home_url()) === 0) {
            $relative_path = str_replace(home_url(), '', $url);
            $url = 'https://' . $this->options['domain'] . $relative_path;
            
            if (isset($this->options['optimize_images']) && $this->options['optimize_images'] == '1') {
                $url = $this->add_image_optimization_params($url);
            }
        }
        
        return $url;
    }

    public function filter_content_images($content) {
        if (!isset($this->options['enabled']) || $this->options['enabled'] != '1') {
            return $content;
        }
        
        if (empty($this->options['domain'])) {
            return $content;
        }
        
        $home_url = home_url();
        $edgeone_domain = 'https://' . $this->options['domain'];
        
        $content = str_replace('src="' . $home_url, 'src="' . $edgeone_domain, $content);
        
        if (isset($this->options['lazy_load']) && $this->options['lazy_load'] == '1') {
            $content = $this->add_lazy_load_attr($content);
        }
        
        if (isset($this->options['optimize_images']) && $this->options['optimize_images'] == '1') {
            $content = $this->add_image_optimization_to_content($content);
        }
        
        return $content;
    }

    private function add_minify_param($url, $type) {
        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . 'edgeone_minify=' . $type;
    }

    private function add_image_optimization_params($url) {
        $image_extensions = array('.jpg', '.jpeg', '.png', '.gif', '.webp');
        $lower_url = strtolower($url);
        
        foreach ($image_extensions as $ext) {
            if (strpos($lower_url, $ext) !== false) {
                $separator = strpos($url, '?') === false ? '?' : '&';
                
                $params = array();
                if (isset($this->options['webp_enabled']) && $this->options['webp_enabled'] == '1') {
                    $params[] = 'format=webp';
                }
                
                if (!empty($params)) {
                    $url .= $separator . implode('&', $params);
                }
                break;
            }
        }
        
        return $url;
    }

    private function add_image_optimization_to_content($content) {
        $pattern = '/<img[^>]+src="([^"]+)"[^>]*>/i';
        return preg_replace_callback($pattern, function($matches) {
            $src = $matches[1];
            $optimized_src = $this->add_image_optimization_params($src);
            return str_replace($src, $optimized_src, $matches[0]);
        }, $content);
    }

    private function add_lazy_load_attr($content) {
        return str_replace('<img ', '<img loading="lazy" ', $content);
    }
}

new EdgeOne_Pages_Plugin();
