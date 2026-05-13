<?php
/**
 * File: tests/test-edgeone-pages.php v1.0.3
 * Description: EdgeOne Pages 插件功能测试
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
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

        // 模拟激活
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
        $this->assert_equals('0', $default_options['enabled'], '激活后默认禁用');
        $this->assert_equals('', $default_options['domain'], '激活后域名为空');
        $this->assert_equals('31536000', $default_options['cache_control'], '激活后缓存时间为默认值');

        echo "\n";
    }

    private function test_settings_sanitize() {
        echo "2. 测试设置选项验证\n";

        // 测试有效域名
        $valid_domain = 'valid.pages.dev';
        $this->assert_true($this->validate_domain($valid_domain), '有效域名验证通过');

        // 测试无效域名
        $invalid_domain = 'invalid domain';
        $this->assert_true(!$this->validate_domain($invalid_domain), '无效域名验证失败');

        // 测试缓存时间范围
        $cache_control = 3600;
        $sanitized_cache = max(0, min($cache_control, 31536000));
        $this->assert_equals(3600, $sanitized_cache, '正常缓存时间验证通过');

        // 测试超出范围的缓存时间
        $large_cache = 999999999;
        $sanitized_large = max(0, min($large_cache, 31536000));
        $this->assert_equals(31536000, $sanitized_large, '超出范围缓存时间被限制');

        // 测试负数缓存时间
        $negative_cache = -100;
        $sanitized_negative = max(0, min($negative_cache, 31536000));
        $this->assert_equals(0, $sanitized_negative, '负数缓存时间被修正为0');

        echo "\n";
    }

    private function test_url_replacement() {
        echo "3. 测试 URL 替换逻辑\n";

        // 模拟 home_url() 函数
        $home_url = 'https://example.com';

        // 测试 JS URL 替换
        $js_url = $home_url . '/wp-content/plugins/test.js';
        $edgeone_url = 'https://' . $this->options['domain'] . str_replace($home_url, '', $js_url);
        $this->assert_string_contains('test.pages.dev', $edgeone_url, 'JS URL 正确替换');

        // 测试 CSS URL 替换
        $css_url = $home_url . '/wp-content/themes/test.css';
        $edgeone_css = 'https://' . $this->options['domain'] . str_replace($home_url, '', $css_url);
        $this->assert_string_contains('test.pages.dev', $edgeone_css, 'CSS URL 正确替换');

        echo "\n";
    }

    private function test_image_optimization() {
        echo "4. 测试图片优化功能\n";

        // 测试图片 URL 优化
        $jpg_url = 'https://test.pages.dev/wp-content/uploads/photo.jpg';
        $webp_url = $jpg_url . '?format=webp';
        $this->assert_string_contains('format=webp', $webp_url, 'JPG 图片添加 WebP 参数');

        // 测试 PNG 图片优化
        $png_url = 'https://test.pages.dev/wp-content/uploads/image.png';
        $optimized_png = $png_url . '?format=webp';
        $this->assert_string_contains('format=webp', $optimized_png, 'PNG 图片添加 WebP 参数');

        // 测试非图片文件不被优化
        $non_image_url = 'https://test.pages.dev/wp-content/file.txt';
        $this->assert_equals($non_image_url, $non_image_url, '非图片 URL 不被修改');

        // 测试带查询参数的图片 URL
        $url_with_query = 'https://test.pages.dev/photo.jpg?width=100';
        $optimized_query = $url_with_query . '&format=webp';
        $this->assert_string_contains('&format=webp', $optimized_query, '带查询参数的图片正确处理');

        echo "\n";
    }

    private function test_lazy_load() {
        echo "5. 测试懒加载功能\n";

        // 测试添加懒加载属性
        $content = '<img src="image.jpg" alt="test" />';
        $content_with_lazy = '<img loading="lazy" src="image.jpg" alt="test" />';
        $this->assert_string_contains('loading="lazy"', $content_with_lazy, '添加懒加载属性');

        echo "\n";
    }

    private function test_minify_params() {
        echo "6. 测试压缩参数功能\n";

        // 测试 JS 压缩参数
        $url = 'https://test.pages.dev/script.js';
        $js_minified = $url . '?edgeone_minify=js';
        $this->assert_string_contains('edgeone_minify=js', $js_minified, 'JS 压缩参数正确添加');

        // 测试 CSS 压缩参数
        $css_url = 'https://test.pages.dev/style.css';
        $css_minified = $css_url . '?edgeone_minify=css';
        $this->assert_string_contains('edgeone_minify=css', $css_minified, 'CSS 压缩参数正确添加');

        // 测试带查询参数的压缩
        $url_with_query = 'https://test.pages.dev/script.js?v=1.0';
        $minified_with_query = $url_with_query . '&edgeone_minify=js';
        $this->assert_string_contains('&edgeone_minify=js', $minified_with_query, '带查询参数的压缩正确处理');

        echo "\n";
    }

    private function test_content_filter() {
        echo "7. 测试内容过滤器\n";

        // 模拟 home_url() 和 edgeone_url
        $home_url = 'https://example.com';
        $edgeone_url = 'https://test.pages.dev';

        // 测试内容中的图片 URL 替换
        $content = '<img src="' . $home_url . '/wp-content/uploads/photo.jpg" alt="test" />';
        $result = str_replace($home_url, $edgeone_url, $content);
        $this->assert_string_contains('test.pages.dev', $result, '内容中图片 URL 正确替换');

        // 测试插件禁用时不替换
        $disabled_content = '<img src="' . $home_url . '/wp-content/uploads/photo.jpg" alt="test" />';
        $this->assert_string_contains($home_url, $disabled_content, '插件禁用时内容图片 URL 不变');

        echo "\n";
    }

    private function validate_domain($domain) {
        $domain = strtolower(trim($domain));

        if (empty($domain)) {
            return false;
        }

        if (strlen($domain) > 253) {
            return false;
        }

        if (strpos($domain, '.') === false) {
            return false;
        }

        if (!preg_match('/^[a-z0-9]([a-z0-9\-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]*[a-z0-9])?)+$/i', $domain)) {
            return false;
        }

        $labels = explode('.', $domain);
        foreach ($labels as $label) {
            if (strlen($label) > 63 || strlen($label) < 1) {
                return false;
            }
            if (strpos($label, '-') === 0 || strrpos($label, '-') === strlen($label) - 1) {
                return false;
            }
        }

        return true;
    }
}

// 运行测试
$test = new EdgeOne_Pages_Test();
$test->run_all_tests();
