<?php
/**
 * File: includes/class-edgeone-pages-settings-validator.php v1.0.3
 * Description: 设置验证器
 */

if (!defined('ABSPATH')) {
    exit;
}

class EdgeOne_Pages_Settings_Validator {

    public function sanitize_options($input) {
        $sanitized = array();
        $sanitized['enabled'] = isset($input['enabled']) ? '1' : '0';

        $domain = sanitize_text_field($input['domain']);
        if (!empty($domain)) {
            if (!$this->validate_domain($domain)) {
                add_settings_error('edgeone_pages_group', 'invalid_domain', __('请输入有效的域名（例如：xxx.pages.dev）', 'edgeone-pages'), 'error');
                $this->log_error('域名验证失败', array('domain' => $domain));
                $domain = '';
            }
        }
        $sanitized['domain'] = $domain;

        $sanitized['webp_enabled'] = isset($input['webp_enabled']) ? '1' : '0';

        $cache_control = intval($input['cache_control']);
        $cache_control = max(0, min($cache_control, 31536000));
        $sanitized['cache_control'] = $cache_control;

        $sanitized['optimize_images'] = isset($input['optimize_images']) ? '1' : '0';
        $sanitized['lazy_load'] = isset($input['lazy_load']) ? '1' : '0';
        $sanitized['minify_css'] = isset($input['minify_css']) ? '1' : '0';
        $sanitized['minify_js'] = isset($input['minify_js']) ? '1' : '0';

        $this->log_error('配置已更新', array('enabled' => $sanitized['enabled'], 'domain' => $sanitized['domain'] ? '已配置' : '未配置'));

        return $sanitized;
    }

    public function validate_domain($domain) {
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

    private function log_error($message, $context = array()) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = '[EdgeOne Pages Settings] ' . $message;
            if (!empty($context)) {
                $log_message .= ' | Context: ' . wp_json_encode($context);
            }
            error_log($log_message);
        }
    }
}
