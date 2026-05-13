# EdgeOne Pages Accelerator

利用腾讯云 EdgeOne Pages 为 WordPress 网站提供静态资源加速和图片优化服务。

## 功能特性

- **静态资源加速**：将 CSS、JavaScript 等静态资源通过 EdgeOne Pages 分发
- **图片优化**：支持自动转换为 WebP 格式，减小图片体积
- **图片懒加载**：延迟加载图片，提高首屏加载速度
- **资源压缩**：支持 CSS 和 JavaScript 文件压缩
- **缓存控制**：自定义静态资源缓存时间
- **安全稳定**：完善的错误处理和异常捕获机制

## 安装步骤

1. 下载插件压缩包
2. 在 WordPress 后台 -> 插件 -> 安装插件 -> 上传插件
3. 激活插件
4. 在 WordPress 后台 -> 设置 -> EdgeOne Pages 配置相关参数

## 配置说明

### 基本设置

- **启用加速**：开启/关闭 EdgeOne Pages 加速功能
- **EdgeOne Pages 域名**：您在腾讯云 EdgeOne 控制台创建的 Pages 域名（如：xxx.pages.dev）

### 优化设置

- **启用 WebP 格式**：自动将图片转换为 WebP 格式
- **优化图片**：启用图片优化功能
- **图片懒加载**：延迟加载图片
- **压缩 CSS**：压缩 CSS 文件
- **压缩 JavaScript**：压缩 JavaScript 文件

### 缓存设置

- **静态资源缓存时间**：设置静态资源的缓存时间（默认 1 年）

## 使用要求

- WordPress 4.9 或更高版本
- 已在腾讯云 EdgeOne 控制台创建 Pages 站点

## 腾讯云 EdgeOne Pages 配置建议

1. 在腾讯云 EdgeOne 控制台创建 Pages 站点
2. 将 WordPress 站点的静态资源目录（wp-content）配置到 Pages 站点
3. 配置自定义域名（可选）
4. 启用图片优化和资源压缩功能

## 测试

插件包含测试文件，您可以在 `tests/` 目录下找到。运行测试：

```bash
php tests/test-edgeone-pages.php
```

## 许可证

GPLv2 或更高版本

## 作者

腾讯云 (Tencent Cloud)

## 贡献

欢迎提交 Issue 和 Pull Request！

## 版本历史

- v1.0.3: 修复错误处理器，拆分设置类，统一版本号
- v1.0.2: 优化图片优化逻辑，改进 URL 匹配
- v1.0.1: 优化代码结构，添加国际化支持
- v1.0.0: 初始版本

---

# EdgeOne Pages Accelerator

Accelerate your WordPress site using Tencent Cloud EdgeOne Pages.

## Features

- **Static Resource Acceleration**: Serve CSS, JavaScript and other static resources via EdgeOne Pages
- **Image Optimization**: Auto-convert images to WebP format for smaller file sizes
- **Image Lazy Loading**: Defer image loading to improve page speed
- **Resource Minification**: Minify CSS and JavaScript files
- **Cache Control**: Customizable cache duration for static resources
- **Security & Stability**: Robust error handling and exception catch mechanism

## Installation

1. Download the plugin zip file
2. In WordPress admin -> Plugins -> Add New -> Upload Plugin
3. Activate the plugin
4. Go to Settings -> EdgeOne Pages to configure

## Configuration

### General Settings

- **Enable Acceleration**: Turn EdgeOne Pages acceleration on/off
- **EdgeOne Pages Domain**: Your Pages domain from Tencent Cloud EdgeOne Console (e.g., xxx.pages.dev)

### Optimization Settings

- **Enable WebP**: Auto-convert images to WebP format
- **Optimize Images**: Enable image optimization
- **Lazy Load Images**: Defer image loading
- **Minify CSS**: Compress CSS files
- **Minify JavaScript**: Compress JavaScript files

### Cache Settings

- **Cache Duration**: Set cache time for static resources (default: 1 year)

## Requirements

- WordPress 4.9 or higher
- EdgeOne Pages site created in Tencent Cloud Console

## EdgeOne Pages Configuration Tips

1. Create a Pages site in Tencent Cloud EdgeOne Console
2. Configure your WordPress static resource directory (wp-content) to the Pages site
3. Configure custom domain (optional)
4. Enable image optimization and resource minification

## Testing

The plugin includes test files in the `tests/` directory. Run tests:

```bash
php tests/test-edgeone-pages.php
```

## License

GPLv2 or later

## Author

Tencent Cloud

## Contributing

Issues and pull requests are welcome!

## Changelog

- v1.0.3: Fix error handlers, split settings class, unify version numbers
- v1.0.2: Optimize image optimization logic, improve URL matching
- v1.0.1: Optimized code structure, added internationalization support
- v1.0.0: Initial release
