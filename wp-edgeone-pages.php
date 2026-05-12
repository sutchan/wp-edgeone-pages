<?php
/**
 * Plugin Name: EdgeOne Pages Accelerator
 * Plugin URI: https://github.com/tencentcloud/wp-edgeone-pages
 * Description: 利用腾讯云 EdgeOne Pages 为 WordPress 网站提供静态资源加速和图片优化服务
 * Version: 1.0.2
 * Author: Tencent Cloud
 * Author URI: https://cloud.tencent.com/
 * License: GPLv2 or later
 * Text Domain: edgeone-pages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EDGEONE_PAGES_VERSION', '1.0.2');
define('EDGEONE_PAGES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDGEONE_PAGES_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once EDGEONE_PAGES_PLUGIN_DIR . 'includes/class-edgeone-pages-plugin.php';

register_activation_hook(__FILE__, array('EdgeOne_Pages_Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('EdgeOne_Pages_Plugin', 'deactivate'));

new EdgeOne_Pages_Plugin();
