# JimForm HTML 表单示例代码

欢迎使用 JimForm WordPress 通用表单处理器的示例代码！

## 📁 文件说明

### 1. form-example.html
- **完整功能的表单示例**
- 包含专业的CSS样式
- 完整的JavaScript异步提交
- 状态提示和错误处理
- 适合直接使用或作为参考

### 2. simple-form.html
- **最简化的表单代码**
- 基础样式，易于定制
- 核心功能完整
- 适合快速集成到现有网站

## 🚀 快速开始

### 步骤1：修改API地址
将表单中的 `action` 属性修改为您的WordPress网站API地址：

```html
<!-- 将这个地址 -->
<form action="https://your-domain.com/wp-json/my-forms/v1/send/contact-form" method="POST">

<!-- 改为您的实际地址 -->
<form action="https://yoursite.com/wp-json/my-forms/v1/send/contact-form" method="POST">
```

### 步骤2：确保插件已安装
1. 在WordPress后台安装并激活 JimForm 插件
2. 安装并配置SMTP插件（如 WP Mail SMTP）
3. 在插件设置中配置接收邮箱

### 步骤3：上传并测试
1. 将HTML文件上传到您的网站
2. 填写测试表单
3. 检查是否收到邮件

## 🎯 核心优势

- **零配置** - 无需预设表单字段
- **跨域支持** - 静态网站也能使用
- **智能解析** - 自动处理任意表单字段
- **即插即用** - 修改action地址即可使用

## 📝 表单字段说明

### 必填字段
- 任何设置了 `required` 属性的字段

### 邮箱字段
- 建议命名为 `email`，插件会自动识别为回复邮箱

### 特殊字段
- `_redirect_url` - 提交成功后重定向的URL
- 以 `_` 开头的字段不会显示在邮件中

## 🌐 适用场景

- ✅ 静态网站表单处理
- ✅ 外部网站联系表单  
- ✅ 第三方页面表单提交
- ✅ 任何需要表单处理的场景

## 🛠 自定义指南

### 修改样式
- 所有CSS都在 `<style>` 标签中，可以自由修改
- 建议保持响应式设计

### 添加字段
```html
<div class="form-group">
    <label for="new-field">新字段</label>
    <input type="text" id="new-field" name="新字段名">
</div>
```

### 修改提示信息
在JavaScript中修改相应的提示文字即可。

## 📞 技术支持

如有问题，请访问：
- 完整文档：https://jimform.com/docs.html
- 联系我们：https://jimform.com/contact.html

## 📄 许可证

本示例代码完全免费使用，无任何限制。