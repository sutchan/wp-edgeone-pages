<?php
/**
 * File: includes/class-edgeone-pages-settings.php v1.0.3
 * Description: WordPress 设置页面管理
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once EDGEONE_PAGES_PLUGIN_DIR . 'includes/class-edgeone-pages-settings-renderer.php';
require_once EDGEONE_PAGES_PLUGIN_DIR . 'includes/class-edgeone-pages-settings-validator.php';

class EdgeOne_Pages_Settings {

    private $options;
    private $renderer;
    private $validator;

    public function __construct($options) {
        $this->options = $options;
        $this->renderer = new EdgeOne_Pages_Settings_Renderer($options);
        $this->validator = new EdgeOne_Pages_Settings_Validator();
    }

    public function add_admin_menu() {
        add_options_page(
            __('EdgeOne Pages 配置', 'edgeone-pages'),
            __('EdgeOne Pages', 'edgeone-pages'),
            'manage_options',
            'edgeone-pages',
            array($this->renderer, 'render_admin_page')
        );
    }

    public function register_settings() {
        register_setting('edgeone_pages_group', 'edgeone_pages_options', array($this->validator, 'sanitize_options'));

        $this->add_general_section();
        $this->add_optimization_section();
        $this->add_cache_section();
    }

    private function add_general_section() {
        add_settings_section(
            'edgeone_pages_general',
            __('基本设置', 'edgeone-pages'),
            array($this->renderer, 'render_general_section'),
            'edgeone-pages'
        );

        add_settings_field(
            'enabled',
            __('启用加速', 'edgeone-pages'),
            array($this->renderer, 'render_enabled_field'),
            'edgeone-pages',
            'edgeone_pages_general'
        );

        add_settings_field(
            'domain',
            __('EdgeOne Pages 域名', 'edgeone-pages'),
            array($this->renderer, 'render_domain_field'),
            'edgeone-pages',
            'edgeone_pages_general'
        );
    }

    private function add_optimization_section() {
        add_settings_section(
            'edgeone_pages_optimization',
            __('优化设置', 'edgeone-pages'),
            array($this->renderer, 'render_optimization_section'),
            'edgeone-pages'
        );

        add_settings_field(
            'webp_enabled',
            __('启用 WebP 格式', 'edgeone-pages'),
            array($this->renderer, 'render_webp_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );

        add_settings_field(
            'optimize_images',
            __('优化图片', 'edgeone-pages'),
            array($this->renderer, 'render_optimize_images_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );

        add_settings_field(
            'lazy_load',
            __('图片懒加载', 'edgeone-pages'),
            array($this->renderer, 'render_lazy_load_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );

        add_settings_field(
            'minify_css',
            __('压缩 CSS', 'edgeone-pages'),
            array($this->renderer, 'render_minify_css_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );

        add_settings_field(
            'minify_js',
            __('压缩 JavaScript', 'edgeone-pages'),
            array($this->renderer, 'render_minify_js_field'),
            'edgeone-pages',
            'edgeone_pages_optimization'
        );
    }

    private function add_cache_section() {
        add_settings_section(
            'edgeone_pages_cache',
            __('缓存设置', 'edgeone-pages'),
            array($this->renderer, 'render_cache_section'),
            'edgeone-pages'
        );

        add_settings_field(
            'cache_control',
            __('静态资源缓存时间（秒）', 'edgeone-pages'),
            array($this->renderer, 'render_cache_control_field'),
            'edgeone-pages',
            'edgeone_pages_cache'
        );
    }
}
