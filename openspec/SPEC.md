# EdgeOne Pages Accelerator 项目规范

## 1. 项目概述

- **项目名称**: EdgeOne Pages Accelerator
- **项目类型**: WordPress 插件
- **核心功能**: 利用腾讯云 EdgeOne Pages 为 WordPress 网站提供静态资源加速和图片优化服务
- **版本**: 1.0.1
- **最低 WordPress 版本**: 4.9

## 2. 目录结构

```
wp-edgeone-pages/
├── wp-edgeone-pages.php          # 主入口文件
├── includes/
│   ├── class-edgeone-pages-plugin.php    # 主插件类
│   ├── class-edgeone-pages-settings.php  # 设置页面
│   ├── class-edgeone-pages-filters.php   # 过滤器处理
│   └── class-edgeone-pages-loader.php    # 钩子加载器
├── assets/
│   └── js/
│       └── lazyload.min.js        # 懒加载脚本
├── languages/                     # 国际化文件
├── openspec/                      # 项目规范文档
│   └── SPEC.md                    # 本文件
├── README.md                      # 项目说明（中英双语）
└── LICENSE                        # GPLv2 许可证
```

## 3. 代码规范

### 3.1 文件头注释
```php
<?php
/**
 * File: relative/path/to/file.php v1.0.1
 * Description: 文件功能描述
 */
```

### 3.2 版本管理
- 主版本号定义在 `wp-edgeone-pages.php` 的 `EDGEONE_PAGES_VERSION` 常量
- 所有文件头版本号必须与常量保持一致
- 每次发布需同步更新所有文件版本号

### 3.3 国际化
- 所有用户可见字符串必须使用 `__()` 或 `_e()` 函数
- 文本域: `edgeone-pages`
- 翻译文件命名: `edgeone-pages-{locale}.po`

### 3.4 WordPress 最佳实践
- 激活/停用钩子必须在类外部注册
- 使用 `plugin_dir_path()` 和 `plugin_dir_url()` 获取路径
- 所有选项必须通过 `sanitize_options()` 过滤

## 4. 功能规格

### 4.1 核心功能
| 功能 | 描述 | 开关选项 |
|------|------|----------|
| 静态资源加速 | CSS/JS 通过 EdgeOne Pages 分发 | enabled |
| 图片优化 | 添加优化参数 | optimize_images |
| WebP 转换 | 自动转换图片格式 | webp_enabled |
| 图片懒加载 | 延迟加载图片 | lazy_load |
| CSS 压缩 | 添加压缩参数 | minify_css |
| JS 压缩 | 添加压缩参数 | minify_js |
| 缓存控制 | 设置缓存时间 | cache_control |

### 4.2 配置选项
```php
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
```

### 4.3 过滤器钩子
| 钩子名称 | 优先级 | 参数 | 功能 |
|----------|--------|------|------|
| script_loader_src | 10 | $src, $handle | 替换 JS 资源 URL |
| style_loader_src | 10 | $src, $handle | 替换 CSS 资源 URL |
| wp_get_attachment_url | 10 | $url, $post_id | 替换媒体附件 URL |
| the_content | 10 | $content | 替换内容中图片 URL |

### 4.4 管理页面
- 位置: 设置 -> EdgeOne Pages
- 权限: manage_options
- 页面slug: edgeone-pages

## 5. 安全规范

### 5.1 输入验证
- 域名: 正则验证 `/^[a-zA-Z0-9]([a-zA-Z0-9\-\.]*[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/`
- 缓存时间: `max(0, min($value, 31536000))`
- 所有 checkbox 值统一为 '0' 或 '1'

### 5.2 输出转义
- 使用 `esc_attr()` 转义 HTML 属性
- 使用 `esc_html()` 转义 HTML 内容
- 使用 `esc_url()` 转义 URL

### 5.3 错误处理
- 设置错误处理器捕获 PHP 错误
- 设置异常处理器捕获未处理异常
- 错误日志记录到 PHP error_log

## 6. 性能规范

### 6.1 懒加载
- 使用原生 `loading="lazy"` 属性
- 提供 lazyload.min.js 作为备用

### 6.2 缓存策略
- 默认缓存时间: 31536000 秒 (1年)
- 范围: 0 - 31536000 秒

## 7. 变更日志

### v1.0.1 (当前版本)
- 修复激活钩子位置（移到类外部）
- 添加域名格式验证
- 添加缓存时间范围验证
- 提取 is_enabled() 和 get_edgeone_url() 辅助方法
- 添加错误和异常处理器
- 拆分代码到多个文件
- 修复国际化硬编码字符串
