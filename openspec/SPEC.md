# EdgeOne Pages Accelerator 项目规范

## 1. 项目概述

- **项目名称**: EdgeOne Pages Accelerator
- **项目类型**: WordPress 插件
- **核心功能**: 利用腾讯云 EdgeOne Pages 为 WordPress 网站提供静态资源加速和图片优化服务
- **版本**: 1.0.1
- **最低 WordPress 版本**: 4.9
- **文本域**: edgeone-pages

## 2. 目录结构

```
wp-edgeone-pages/
├── wp-edgeone-pages.php              # 主入口文件
├── includes/
│   ├── class-edgeone-pages-plugin.php    # 主插件类
│   ├── class-edgeone-pages-settings.php  # 设置页面
│   ├── class-edgeone-pages-filters.php   # 过滤器处理
│   └── class-edgeone-pages-loader.php    # 钩子加载器
├── assets/
│   └── js/
│       └── lazyload.min.js            # 懒加载脚本
├── dist/                              # 构建产物目录
│   ├── wp-edgeone-pages.php
│   ├── includes/
│   ├── assets/
│   ├── languages/
│   ├── README.md
│   └── LICENSE
├── languages/                         # 国际化文件
│   └── edgeone-pages.pot             # 模板文件
├── openspec/                          # 项目规范文档
│   └── SPEC.md                        # 本文件
├── README.md                          # 项目说明
├── LICENSE                            # GPLv2 许可证
├── .gitignore
├── .gitattributes
└── wp-edgeone-pages.code-workspace    # VS Code 工作区配置
```

## 3. 代码规范

### 3.1 文件头注释

所有 PHP 文件必须包含标准化的文件头注释：

```php
<?php
/**
 * File: includes/class-edgeone-pages-plugin.php v1.0.1
 * Description: EdgeOne Pages 主插件类
 */
```

### 3.2 版本管理

- 主版本号定义在 `wp-edgeone-pages.php` 的 `EDGEONE_PAGES_VERSION` 常量
- 所有文件头版本号必须与常量保持一致
- 每次发布需同步更新所有文件版本号
- 版本格式遵循 SemVer 2.0.0

### 3.3 命名规范

| 类型 | 规范 | 示例 |
|------|------|------|
| 类名 | PascalCase | EdgeOne_Pages_Plugin |
| 方法名 | camelCase | filterScriptSrc |
| 变量名 | snake_case | $default_options |
| 常量 | UPPER_SNAKE_CASE | EDGEONE_PAGES_VERSION |
| 钩子名 | kebab-case | edgeone_pages_before_filter |

### 3.4 国际化

- 所有用户可见字符串必须使用 `__()` 或 `_e()` 函数
- 文本域: `edgeone-pages`
- 翻译文件命名: `edgeone-pages-{locale}.po/mo`
- 模板文件: `edgeone-pages.pot`

### 3.5 WordPress 最佳实践

- 激活/停用钩子必须在类外部注册（主入口文件中）
- 使用 `plugin_dir_path()` 和 `plugin_dir_url()` 获取路径
- 所有选项必须通过 `sanitize_options()` 过滤
- 始终检查 `ABSPATH` 常量防止直接访问

## 4. 功能规格

### 4.1 核心功能

| 功能 | 描述 | 配置键 | 默认值 |
|------|------|--------|--------|
| 全局开关 | 启用/禁用插件 | enabled | '0' |
| 加速域名 | EdgeOne Pages 域名 | domain | '' |
| 静态资源加速 | CSS/JS 通过 EdgeOne Pages 分发 | enabled | '0' |
| 图片优化 | 添加优化参数 | optimize_images | '1' |
| WebP 转换 | 自动转换图片格式 | webp_enabled | '0' |
| 图片懒加载 | 延迟加载图片 | lazy_load | '1' |
| CSS 压缩 | 添加压缩参数 | minify_css | '1' |
| JS 压缩 | 添加压缩参数 | minify_js | '1' |
| 缓存控制 | 设置缓存时间（秒） | cache_control | '31536000' |

### 4.2 默认配置

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

### 4.4 动作钩子

| 钩子名称 | 优先级 | 功能 |
|----------|--------|------|
| admin_menu | 10 | 添加管理菜单 |
| admin_init | 10 | 注册设置项 |
| wp_enqueue_scripts | 10 | 加载前端脚本 |
| admin_notices | 10 | 显示管理通知 |

### 4.5 管理页面

- 位置: 设置 → EdgeOne Pages
- 权限: `manage_options`
- 页面 slug: `edgeone-pages`
- 设置组: `edgeone_pages_group`
- 设置页: `edgeone_pages_settings`

## 5. 安全规范

### 5.1 输入验证

| 字段 | 验证规则 |
|------|----------|
| 域名 | `/^[a-zA-Z0-9]([a-zA-Z0-9\-\.]*[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/` |
| 缓存时间 | `max(0, min($value, 31536000))` |
| 布尔选项 | 统一为 '0' 或 '1' |

### 5.2 输出转义

| 场景 | 函数 |
|------|------|
| HTML 属性 | `esc_attr()` |
| HTML 内容 | `esc_html()` |
| URL | `esc_url()` |
| JS 输出 | `esc_js()` |
| 表单值 | `esc_attr()` |

### 5.3 错误处理

- 设置错误处理器捕获 PHP 错误
- 设置异常处理器捕获未处理异常
- 错误日志记录到 PHP error_log
- 格式: `[EdgeOne Pages] {类型}: {消息} in {文件} on line {行号}`

### 5.4 安全最佳实践

- 禁止直接访问文件（检查 `ABSPATH`）
- 使用 WordPress 内置函数处理选项
- 避免直接 SQL 查询
- 使用 `wp_nonce_field()` 保护表单提交

## 6. 性能规范

### 6.1 懒加载

- 使用原生 `loading="lazy"` 属性
- 提供 `lazyload.min.js` 作为降级方案
- 仅对内容区域图片应用懒加载

### 6.2 缓存策略

- 默认缓存时间: 31536000 秒 (1年)
- 范围: 0 - 31536000 秒
- 静态资源添加 `Cache-Control` 响应头

### 6.3 资源优化

- CSS/JS 添加版本号避免缓存问题
- 图片添加优化参数: `imageMogr2/thumbnail/`
- WebP 格式转换支持

## 7. API 规范

### 7.1 辅助方法

| 方法 | 返回类型 | 功能 |
|------|----------|------|
| is_enabled() | bool | 检查插件是否启用 |
| get_edgeone_url($src) | string | 将 URL 转换为 EdgeOne 加速 URL |
| get_options() | array | 获取所有配置选项 |

### 7.2 过滤器扩展

开发者可通过以下过滤器扩展功能：

- `edgeone_pages_filter_skip_handle`: 跳过特定资源处理
- `edgeone_pages_custom_url`: 自定义 URL 转换逻辑

## 8. 测试规范

### 8.1 测试类型

- **单元测试**: 测试独立函数和方法
- **集成测试**: 测试组件间交互
- **功能测试**: 测试完整功能流程

### 8.2 测试覆盖

- 核心功能覆盖率 ≥ 80%
- 安全相关代码覆盖率 100%
- 过滤器和钩子覆盖率 ≥ 80%

## 9. 构建规范

### 9.1 构建产物

构建产物输出到 `dist/` 目录，包含：
- 主入口文件
- 所有 includes 文件
- 静态资源
- 翻译文件
- README 和 LICENSE

### 9.2 构建流程

1. 复制源文件到 dist/
2. 压缩 JS/CSS 资源
3. 更新版本号
4. 生成翻译文件

## 10. 变更日志

### v1.0.1
- 修复激活钩子位置（移到类外部）
- 添加域名格式验证
- 添加缓存时间范围验证
- 提取 `is_enabled()` 和 `get_edgeone_url()` 辅助方法
- 添加错误和异常处理器
- 拆分代码到多个文件
- 修复国际化硬编码字符串

### v1.0.0
- 初始版本
- 支持静态资源加速
- 支持图片优化和 WebP 转换
- 支持图片懒加载
- 支持 CSS/JS 压缩
- 支持缓存控制配置

---

*文档版本: v1.0.1*
*最后更新: 2026-05-12*