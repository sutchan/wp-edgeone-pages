<?php
/**
 * File: includes/class-edgeone-pages-plugin.php v1.0.1
 * Description: EdgeOne Pages 主插件类
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once EDGEONE_PAGES_PLUGIN_DIR . 'includes/class-edgeone-pages-loader.php';
require_once EDGEONE_PAGES_PLUGIN_DIR . 'includes/class-edgeone-pages-settings.php';
require_once EDGEONE_PAGES_PLUGIN_DIR . 'includes/class-edgeone-pages-filters.php';

class EdgeOne_Pages_Plugin {

    private $options;
    private $loader;
    private $settings;
    private $filters;

    public function __construct() {
        set_error_handler(array($this, 'error_handler'));
        set_exception_handler(array($this, 'exception_handler'));

        $this->options = get_option('edgeone_pages_options');
        $this->init_components();
        $this->register_hooks();
    }

    private function init_components() {
        $this->settings = new EdgeOne_Pages_Settings($this->options);
        $this->filters = new EdgeOne_Pages_Filters($this->options);
        $this->loader = new EdgeOne_Pages_Loader();
    }

    private function register_hooks() {
        $this->loader->add_action('admin_menu', $this->settings, 'add_admin_menu');
        $this->loader->add_action('admin_init', $this->settings, 'register_settings');
        $this->loader->add_action('wp_enqueue_scripts', $this->filters, 'enqueue_scripts');
        $this->loader->add_action('admin_notices', $this->filters, 'admin_notice');

        $this->loader->add_filter('script_loader_src', $this->filters, 'filter_script_src', 10, 2);
        $this->loader->add_filter('style_loader_src', $this->filters, 'filter_style_src', 10, 2);
        $this->loader->add_filter('wp_get_attachment_url', $this->filters, 'filter_attachment_url', 10, 2);
        $this->loader->add_filter('the_content', $this->filters, 'filter_content_images');

        $this->loader->run();
    }

    public static function activate() {
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

    public static function deactivate() {
        delete_option('edgeone_pages_options');
    }

    public function error_handler($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        error_log(sprintf('[EdgeOne Pages] Error %d: %s in %s on line %d', $errno, $errstr, $errfile, $errline));
        return true;
    }

    public function exception_handler($exception) {
        error_log(sprintf('[EdgeOne Pages] Uncaught Exception: %s in %s on line %d', $exception->getMessage(), $exception->getFile(), $exception->getLine()));
    }
}
