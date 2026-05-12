<?php
/**
 * File: includes/class-edgeone-pages-filters.php v1.0.2
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
        $pattern = '/<img([^>]+)src="(' . preg_quote($home_url, '/') . '[^"]+)"([^>]*)>/i';

        $content = preg_replace_callback($pattern, function($matches) use ($home_url, $edgeone_domain) {
            $src = str_replace($home_url, $edgeone_domain, $matches[2]);
            return '<img' . $matches[1] . 'src="' . esc_url($src) . '"' . $matches[3] . '>';
        }, $content);

        if (!empty($this->options['lazy_load']) && $this->options['lazy_load'] == '1') {
            $content = $this->add_lazy_load_attr($content);
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
        if ($this->is_plugin_enabled() && !$this->is_domain_configured()) {
        ?>
        <div class="notice notice-warning">
            <p><?php _e('EdgeOne Pages 已启用，但未配置加速域名，请在 <a href="options-general.php?page=edgeone-pages">设置页面</a> 中配置。', 'edgeone-pages'); ?></p>
        </div>
        <?php
        }
    }

    public function send_cache_headers() {
        if (!$this->is_enabled()) {
            return;
        }

        if (headers_sent()) {
            $this->log_error('无法发送缓存头，响应头已发送');
            return;
        }

        $cache_control = isset($this->options['cache_control']) ? intval($this->options['cache_control']) : 31536000;

        if ($cache_control > 0) {
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';

            $static_extensions = array('.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot');
            $is_static = false;
            foreach ($static_extensions as $ext) {
                if (stripos($request_uri, $ext) !== false) {
                    $is_static = true;
                    break;
                }
            }

            if ($is_static) {
                header('Cache-Control: public, max-age=' . $cache_control);
            }
        }
    }

    private function is_enabled() {
        return $this->is_plugin_enabled() && $this->is_domain_configured();
    }

    private function is_plugin_enabled() {
        return !empty($this->options['enabled']) && $this->options['enabled'] == '1';
    }

    private function is_domain_configured() {
        return !empty($this->options['domain']);
    }

    private function get_edgeone_url($relative_path) {
        return 'https://' . $this->options['domain'] . $relative_path;
    }

    private function add_minify_param($url, $type) {
        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . 'edgeone_minify=' . $type;
    }

    private function add_image_optimization_params($url) {
        $parsed = parse_url($url);
        if (!isset($parsed['path'])) {
            return $url;
        }

        $path = strtolower($parsed['path']);
        $image_extensions = array('.jpg', '.jpeg', '.png', '.gif', '.webp');
        $is_image = false;

        foreach ($image_extensions as $ext) {
            if (substr($path, -strlen($ext)) === $ext) {
                $is_image = true;
                break;
            }
        }

        if (!$is_image) {
            return $url;
        }

        $params = array();

        if (!empty($this->options['webp_enabled']) && $this->options['webp_enabled'] == '1') {
            $params[] = 'format=webp';
        }

        if (empty($params)) {
            return $url;
        }

        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . implode('&', $params);
    }

    private function add_image_optimization_to_content($content) {
        $pattern = '/<img([^>]+)src="([^"]+)"([^>]*)>/i';
        return preg_replace_callback($pattern, function($matches) {
            $src = $matches[2];
            $optimized_src = $this->add_image_optimization_params($src);
            return '<img' . $matches[1] . 'src="' . esc_url($optimized_src) . '"' . $matches[3] . '>';
        }, $content);
    }

    private function add_lazy_load_attr($content) {
        return preg_replace_callback(
            '/<img([^>]*)>/i',
            function($matches) {
                $attrs = $matches[1];
                if (preg_match('/\bloading\s*=/i', $attrs)) {
                    return $matches[0];
                }
                return '<img loading="lazy"' . $attrs . '>';
            },
            $content
        );
    }

    private function log_error($message, $context = array()) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = '[EdgeOne Pages] ' . $message;
            if (!empty($context)) {
                $log_message .= ' | Context: ' . wp_json_encode($context);
            }
            error_log($log_message);
        }
    }
}
