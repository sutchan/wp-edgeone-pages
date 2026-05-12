<?php
/**
 * File: includes/class-edgeone-pages-filters.php v1.0.1
 * Description: WordPress 过滤器处理
 */

if (!defined('ABSPATH')) {
    exit;
}

class EdgeOne_Pages_Filters {

    private $options;

    public function __construct($options) {
        $this->options = $options;
    }

    public function filter_script_src($src, $handle) {
        if (!$this->is_enabled()) {
            return $src;
        }

        if (strpos($src, home_url()) === 0) {
            $relative_path = str_replace(home_url(), '', $src);
            $src = $this->get_edgeone_url($relative_path);

            if (!empty($this->options['minify_js']) && $this->options['minify_js'] == '1') {
                $src = $this->add_minify_param($src, 'js');
            }
        }

        return $src;
    }

    public function filter_style_src($src, $handle) {
        if (!$this->is_enabled()) {
            return $src;
        }

        if (strpos($src, home_url()) === 0) {
            $relative_path = str_replace(home_url(), '', $src);
            $src = $this->get_edgeone_url($relative_path);

            if (!empty($this->options['minify_css']) && $this->options['minify_css'] == '1') {
                $src = $this->add_minify_param($src, 'css');
            }
        }

        return $src;
    }

    public function filter_attachment_url($url, $post_id) {
        if (!$this->is_enabled()) {
            return $url;
        }

        if (strpos($url, home_url()) === 0) {
            $relative_path = str_replace(home_url(), '', $url);
            $url = $this->get_edgeone_url($relative_path);

            if (!empty($this->options['optimize_images']) && $this->options['optimize_images'] == '1') {
                $url = $this->add_image_optimization_params($url);
            }
        }

        return $url;
    }

    public function filter_content_images($content) {
        if (!$this->is_enabled()) {
            return $content;
        }

        $home_url = home_url();
        $edgeone_domain = $this->get_edgeone_url('');

        $content = str_replace('src="' . $home_url, 'src="' . $edgeone_domain, $content);

        if (!empty($this->options['lazy_load']) && $this->options['lazy_load'] == '1') {
            $content = $this->add_lazy_load_attr($content);
        }

        if (!empty($this->options['optimize_images']) && $this->options['optimize_images'] == '1') {
            $content = $this->add_image_optimization_to_content($content);
        }

        return $content;
    }

    public function enqueue_scripts() {
        if (!$this->is_enabled()) {
            return;
        }

        if (!empty($this->options['lazy_load']) && $this->options['lazy_load'] == '1') {
            wp_enqueue_script(
                'edgeone-lazyload',
                EDGEONE_PAGES_PLUGIN_URL . 'assets/js/lazyload.min.js',
                array(),
                EDGEONE_PAGES_VERSION,
                true
            );
        }
    }

    public function admin_notice() {
        if (isset($this->options['enabled']) && $this->options['enabled'] == '1' && empty($this->options['domain'])) {
        ?>
        <div class="notice notice-warning">
            <p><?php _e('EdgeOne Pages 已启用，但未配置加速域名，请在 <a href="options-general.php?page=edgeone-pages">设置页面</a> 中配置。', 'edgeone-pages'); ?></p>
        </div>
        <?php
        }
    }

    private function is_enabled() {
        return isset($this->options['enabled']) && $this->options['enabled'] == '1' && !empty($this->options['domain']);
    }

    private function get_edgeone_url($relative_path) {
        return 'https://' . $this->options['domain'] . $relative_path;
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
                if (!empty($this->options['webp_enabled']) && $this->options['webp_enabled'] == '1') {
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
