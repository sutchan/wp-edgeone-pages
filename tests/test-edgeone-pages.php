<?php
/**
 * File: tests/test-edgeone-pages.php v1.0.1
 * Description: EdgeOne Pages 插件功能测试
 */

if (!defined('ABSPATH')) {
    exit;
}

class EdgeOne_Pages_Test {

    private $test_results = array();
    private $options = array(
        'enabled' => '1',
        'domain' => 'test.pages.dev',
        'webp_enabled' => '1',
        'cache_control' => '31536000',
        'optimize_images' => '1',
        'lazy_load' => '1',
        'minify_css' => '1',
        'minify_js' => '1',
    );

    public function run_all_tests() {
        echo "====================================\n";
        echo "EdgeOne Pages 插件功能测试\n";
        echo "====================================\n\n";

        $this->test_activate_deactivate();
        $this->test_settings_sanitize();
        $this->test_url_replacement();
        $this->test_image_optimization();
        $this->test_lazy_load();
        $this->test_minify_params();
        $this->test_content_filter();

        $this->print_results();
    }

    private function assert_true($condition, $test_name) {
        $this->test_results[] = array(
            'name' => $test_name,
            'passed' => $condition === true
        );
    }

    private function assert_equals($expected, $actual, $test_name) {
        $this->test_results[] = array(
            'name' => $test_name,
            'passed' => $expected === $actual,
            'expected' => $expected,
            'actual' => $actual
        );
    }

    private function assert_string_contains($needle, $haystack, $test_name) {
        $this->test_results[] = array(
            'name' => $test_name,
            'passed' => strpos($haystack, $needle) !== false
        );
    }

    private function print_results() {
        $passed = 0;
        $failed = 0;

        foreach ($this->test_results as $result) {
            $status = $result['passed'] ? '✓' : '✗';
            echo "$status {$result['name']}";
            if (!$result['passed'] && isset($result['expected'])) {
                echo " (Expected: {$result['expected']}, Got: {$result['actual']})";
            }
            echo "\n";

            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }

        echo "\n====================================\n";
        echo "测试结果: {$passed} 通过, {$failed} 失败\n";
        echo "====================================\n";
    }

    private function test_activate_deactivate() {
        echo "1. 测试插件激活/停用功能\n";

        EdgeOne_Pages_Plugin::activate();
        $options = get_option('edgeone_pages_options');
        $this->assert_equals('0', $options['enabled'], '激活后默认禁用');
        $this->assert_equals('', $options['domain'], '激活后域名为空');
        $this->assert_equals('31536000', $options['cache_control'], '激活后缓存时间为默认值');

        EdgeOne_Pages_Plugin::deactivate();
        $options = get_option('edgeone_pages_options');
        $this->assert_true($options === false, '停用后选项已删除');

        update_option('edgeone_pages_options', $this->options);
    }

    private function test_settings_sanitize() {
        echo "\n2. 测试设置选项验证\n";

        $settings = new EdgeOne_Pages_Settings($this->options);

        $input = array(
            'enabled' => '1',
            'domain' => 'valid.pages.dev',
            'webp_enabled' => '1',
            'cache_control' => '1000',
            'optimize_images' => '1',
            'lazy_load' => '1',
            'minify_css' => '1',
            'minify_js' => '1',
        );

        $sanitized = $settings->sanitize_options($input);
        $this->assert_equals('1', $sanitized['enabled'], '有效的启用值');
        $this->assert_equals('valid.pages.dev', $sanitized['domain'], '有效的域名');

        $invalid_input = array(
            'enabled' => '1',
            'domain' => 'invalid domain',
            'cache_control' => '-100',
        );

        $sanitized = $settings->sanitize_options($invalid_input);
        $this->assert_equals('', $sanitized['domain'], '无效域名被清空');
        $this->assert_equals(0, $sanitized['cache_control'], '负数缓存时间被修正为0');

        $high_cache = array('cache_control' => '99999999999');
        $sanitized = $settings->sanitize_options($high_cache);
        $this->assert_equals(31536000, $sanitized['cache_control'], '超长缓存时间被限制为最大值');
    }

    private function test_url_replacement() {
        echo "\n3. 测试 URL 替换逻辑\n";

        define('home_url', function() { return 'https://example.com'; });

        $filters = new EdgeOne_Pages_Filters($this->options);

        $js_url = 'https://example.com/wp-content/plugins/test.js';
        $result = $filters->filter_script_src($js_url, 'test-script');
        $this->assert_string_contains('https://test.pages.dev', $result, 'JS URL 正确替换');
        $this->assert_string_contains('edgeone_minify=js', $result, 'JS URL 包含压缩参数');

        $css_url = 'https://example.com/wp-content/themes/test.css';
        $result = $filters->filter_style_src($css_url, 'test-style');
        $this->assert_string_contains('https://test.pages.dev', $result, 'CSS URL 正确替换');
        $this->assert_string_contains('edgeone_minify=css', $result, 'CSS URL 包含压缩参数');

        $external_url = 'https://external.com/script.js';
        $result = $filters->filter_script_src($external_url, 'external-script');
        $this->assert_equals($external_url, $result, '外部 URL 不被替换');

        $disabled_options = $this->options;
        $disabled_options['enabled'] = '0';
        $filters_disabled = new EdgeOne_Pages_Filters($disabled_options);
        $result = $filters_disabled->filter_script_src($js_url, 'test-script');
        $this->assert_equals($js_url, $result, '插件禁用时 URL 不被替换');
    }

    private function test_image_optimization() {
        echo "\n4. 测试图片优化功能\n";

        $filters = new EdgeOne_Pages_Filters($this->options);

        $jpg_url = 'https://test.pages.dev/wp-content/uploads/photo.jpg';
        $result = $filters->add_image_optimization_params($jpg_url);
        $this->assert_string_contains('format=webp', $result, 'JPG 图片添加 WebP 参数');

        $png_url = 'https://test.pages.dev/wp-content/uploads/image.png';
        $result = $filters->add_image_optimization_params($png_url);
        $this->assert_string_contains('format=webp', $result, 'PNG 图片添加 WebP 参数');

        $no_webp_options = $this->options;
        $no_webp_options['webp_enabled'] = '0';
        $filters_no_webp = new EdgeOne_Pages_Filters($no_webp_options);
        $result = $filters_no_webp->add_image_optimization_params($jpg_url);
        $this->assert_true(strpos($result, 'format=webp') === false, 'WebP 禁用时不添加参数');

        $non_image_url = 'https://test.pages.dev/wp-content/file.txt';
        $result = $filters->add_image_optimization_params($non_image_url);
        $this->assert_equals($non_image_url, $result, '非图片 URL 不被修改');

        $url_with_query = 'https://test.pages.dev/photo.jpg?width=100';
        $result = $filters->add_image_optimization_params($url_with_query);
        $this->assert_string_contains('format=webp', $result, '带查询参数的图片 URL 正确处理');
        $this->assert_true(strpos($result, 'width=100') !== false, '原始查询参数保留');
    }

    private function test_lazy_load() {
        echo "\n5. 测试懒加载功能\n";

        $filters = new EdgeOne_Pages_Filters($this->options);

        $content = '<img src="image.jpg" alt="test" />';
        $result = $filters->add_lazy_load_attr($content);
        $this->assert_string_contains('loading="lazy"', $result, '添加懒加载属性');

        $content_with_existing = '<img src="image.jpg" alt="test" loading="eager" />';
        $result = $filters->add_lazy_load_attr($content_with_existing);
        $this->assert_true(strpos($result, 'loading="lazy"') !== false, '替换已有懒加载属性');
    }

    private function test_minify_params() {
        echo "\n6. 测试压缩参数功能\n";

        $filters = new EdgeOne_Pages_Filters($this->options);

        $url = 'https://test.pages.dev/script.js';
        $result = $filters->add_minify_param($url, 'js');
        $this->assert_string_contains('edgeone_minify=js', $result, 'JS 压缩参数正确添加');

        $url_with_query = 'https://test.pages.dev/script.js?v=1.0';
        $result = $filters->add_minify_param($url_with_query, 'js');
        $this->assert_string_contains('edgeone_minify=js', $result, '带查询参数的 URL 正确处理');
        $this->assert_true(strpos($result, '&edgeone_minify') !== false, '使用 & 连接参数');
    }

    private function test_content_filter() {
        echo "\n7. 测试内容过滤器\n";

        $filters = new EdgeOne_Pages_Filters($this->options);

        $content = '<img src="https://example.com/wp-content/uploads/photo.jpg" alt="test" />';
        $result = $filters->filter_content_images($content);
        $this->assert_string_contains('https://test.pages.dev', $result, '内容中图片 URL 正确替换');

        $disabled_options = $this->options;
        $disabled_options['enabled'] = '0';
        $filters_disabled = new EdgeOne_Pages_Filters($disabled_options);
        $result = $filters_disabled->filter_content_images($content);
        $this->assert_string_contains('https://example.com', $result, '插件禁用时内容图片 URL 不变');

        $no_domain_options = $this->options;
        $no_domain_options['domain'] = '';
        $filters_no_domain = new EdgeOne_Pages_Filters($no_domain_options);
        $result = $filters_no_domain->filter_content_images($content);
        $this->assert_string_contains('https://example.com', $result, '未配置域名时内容图片 URL 不变');
    }
}

if (defined('ABSPATH') && class_exists('EdgeOne_Pages_Filters') && class_exists('EdgeOne_Pages_Settings') && class_exists('EdgeOne_Pages_Plugin')) {
    $test = new EdgeOne_Pages_Test();
    $test->run_all_tests();
} else {
    echo "请在 WordPress 环境中运行测试。\n";
}
